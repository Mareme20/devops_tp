pipeline {
    agent any

    options {
        timestamps()
    }

    environment {
        COMPOSE_PROJECT_NAME = "devops-tp-${BUILD_NUMBER}"
    }

    stages {
        stage('Validate Compose') {
            steps {
                sh 'docker compose config >/dev/null'
            }
        }

        stage('Build Image') {
            steps {
                sh 'docker compose build php-apache'
            }
        }

        stage('Lint PHP') {
            steps {
                sh '''
                    docker run --rm \
                      -v "$PWD/src:/var/www/html" \
                      devops-tp-php-apache \
                      sh -lc 'find /var/www/html -name "*.php" -print0 | xargs -0 -n1 php -l'
                '''
            }
        }

        stage('Start Services') {
            steps {
                sh 'docker compose up -d postgres php-apache'
            }
        }

        stage('Wait For Database') {
            steps {
                sh '''
                    for i in $(seq 1 20); do
                      if docker compose exec -T postgres pg_isready -U postgres -d crud_db >/dev/null 2>&1; then
                        exit 0
                      fi
                      sleep 3
                    done

                    echo "PostgreSQL did not become ready in time." >&2
                    docker compose logs postgres || true
                    exit 1
                '''
            }
        }

        stage('Smoke Test') {
            steps {
                sh '''
                    docker compose exec -T php-apache php -r '
                    $_SERVER["REQUEST_METHOD"] = "GET";
                    $_REQUEST = [];
                    ob_start();
                    require "/var/www/html/public/index.php";
                    $html = ob_get_clean();

                    if (strpos($html, "Marieme") === false) {
                        fwrite(STDERR, "Smoke test failed: expected seeded user not found.\\n");
                        exit(1);
                    }

                    echo "Smoke test OK\\n";
                    '
                '''
            }
        }
    }

    post {
        failure {
            sh 'docker compose logs --no-color || true'
        }

        always {
            sh 'docker compose down -v --remove-orphans || true'
        }
    }
}
