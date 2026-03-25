pipeline {
    agent any

    options {
        // Affiche l'heure de chaque étape dans les logs Jenkins.
        timestamps()
    }

    environment {
        // Isole les ressources Docker de chaque build Jenkins.
        COMPOSE_PROJECT_NAME = "devops-tp-${BUILD_NUMBER}"
        // Docker Hub repository name (update with your Docker Hub repo: username/repo)
        DOCKERHUB_REPO = 'mareme20/devops-tp'
        // Jenkins credential IDs (configure in Jenkins)
        DOCKERHUB_CREDENTIALS_ID = 'dockerhub-creds'
        SLACK_CHANNEL = '#ci'
    }

    stages {
        stage('Validate Compose') {
            steps {
                // Vérifie que la configuration Docker Compose est valide avant d'aller plus loin.
                sh 'docker compose config >/dev/null'
            }
        }

        stage('Build Image') {
            steps {
                // Construit l'image PHP/Apache utilisée par le reste du pipeline.
                sh 'docker compose build php-apache'
            }
        }

        stage('Push to Docker Hub') {
            steps {
                script {
                    // Login to Docker Hub
                    docker.withRegistry('https://index.docker.io/v1/', DOCKERHUB_CREDENTIALS_ID) {
                        // Tag the built image
                        withCredentials([usernamePassword(credentialsId: DOCKERHUB_CREDENTIALS_ID, passwordVariable: 'DOCKERHUB_PASS', usernameVariable: 'DOCKERHUB_USER')]) {
                            sh """
                                echo \$DOCKERHUB_PASS | docker login -u \$DOCKERHUB_USER --password-stdin
                                IMAGE_NAME=\${COMPOSE_PROJECT_NAME}-php-apache
                                docker tag \$IMAGE_NAME ${DOCKERHUB_REPO}:\${BUILD_NUMBER}
                                docker tag \$IMAGE_NAME ${DOCKERHUB_REPO}:latest
                                docker push ${DOCKERHUB_REPO}:\${BUILD_NUMBER}
                                docker push ${DOCKERHUB_REPO}:latest
                                docker logout
                            """
                        }
</xai:function_call 

<xai:function_call name="edit_file">
<parameter name="path">/home/user/Bureau/devops_tp/Jenkinsfile
                    }
                }
            }
        }

        stage('Lint PHP') {
            steps {
                sh '''
                    # Lance un contrôle de syntaxe PHP dans l'image fraîchement buildée.
                    docker run --rm \
                      -v "$PWD/src:/var/www/html" \
                      ${COMPOSE_PROJECT_NAME}-php-apache \
                      sh -lc 'find /var/www/html -name "*.php" -print0 | xargs -0 -n1 php -l'
                '''
            }
        }

        stage('Start Services') {
            steps {
                // Démarre uniquement les services nécessaires à la validation CI.
                sh 'docker compose up -d postgres php-apache'
            }
        }

        stage('Wait For Database') {
            steps {
                sh '''
                    # Attends que PostgreSQL soit prêt avant de lancer le test applicatif.
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
                    # Rend la page principale depuis le conteneur PHP et vérifie la donnée seedée.
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
        success {
            slackSend(
                channel: SLACK_CHANNEL,
                color: 'good',
                message: "✅ *Pipeline succeeded!* Job: ${JOB_NAME} #${BUILD_NUMBER} (<${BUILD_URL}|Open>)"
            )
        }
        failure {
            slackSend(
                channel: SLACK_CHANNEL,
                color: 'danger',
                message: "❌ *Pipeline failed!* Job: ${JOB_NAME} #${BUILD_NUMBER} (<${BUILD_URL}|Open>)"
            )
            // En cas d'échec, expose les logs Compose pour faciliter le diagnostic dans Jenkins.
            sh 'docker compose logs --no-color || true'
        }

        always {
            // Nettoie systématiquement les conteneurs, le réseau et les volumes du build.
            sh 'docker compose down -v --remove-orphans || true'
        }
    }
}
