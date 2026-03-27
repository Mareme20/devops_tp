pipeline {
    agent any

    options {
        // Affiche l'heure de chaque étape dans les logs Jenkins.
        timestamps()
    }

    environment {
        // Isole les ressources Docker de chaque build Jenkins.
        COMPOSE_PROJECT_NAME = "devops-tp-${BUILD_NUMBER}"
        APP_IMAGE = 'devops-tp-php-apache'
        NEXUS_IMAGE_REPO = 'devops-tp'
        // Jenkins credential IDs (configure in Jenkins)
        NEXUS_CREDENTIALS_ID = 'nexus-creds'
    }
    

    stages {
        stage('Validate Compose') {
            steps {
                // Vérifie aussi les profils Nexus/ELK pour éviter une erreur tardive dans le pipeline.
                sh 'docker compose --profile artifact-repo --profile observability config >/dev/null'
            }
        }

        stage('Build Image') {
            steps {
                // Construit l'image PHP/Apache utilisée par le reste du pipeline.
                sh 'docker compose build php-apache'
            }
        }

        stage('Lint PHP') {
            steps {
                sh '''
                    # Lance un contrôle de syntaxe PHP dans l'image fraîchement buildée.
                    docker run --rm \
                      -v "$PWD/src:/var/www/html" \
                      ${APP_IMAGE} \
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

        stage('Start Nexus') {
            steps {
                sh '''
                    # Réutilise un Nexus déjà démarré sur l'hôte si présent,
                    # sinon démarre celui du projet.
                    EXISTING_NEXUS="$(docker ps --filter ancestor=sonatype/nexus3 --format '{{.Names}}' | head -n1)"

                    if [ -n "$EXISTING_NEXUS" ]; then
                      echo "Nexus already running: $EXISTING_NEXUS"
                      exit 0
                    fi

                    docker compose --profile artifact-repo up -d nexus
                    docker compose --profile artifact-repo run --rm nexus-init
                '''
            }
        }

                stage('Push to Nexus') {
            steps {
                withCredentials([usernamePassword(credentialsId: NEXUS_CREDENTIALS_ID, passwordVariable: 'NEXUS_PASS', usernameVariable: 'NEXUS_USER')]) {
                    sh '''
                        # On définit directement l'IP de la passerelle Docker (ou localhost si Jenkins est sur l'hôte)
                        TARGET_REGISTRY="172.17.0.14:8085"
                        
                        IMAGE_NAME=${APP_IMAGE}
                        TARGET_IMAGE=${TARGET_REGISTRY}/${NEXUS_IMAGE_REPO}

                        # Connexion
                        echo "$NEXUS_PASS" | docker login "$TARGET_REGISTRY" -u "$NEXUS_USER" --password-stdin
                        
                        # Tag et Envoi
                        docker tag "$IMAGE_NAME" "${TARGET_IMAGE}:${BUILD_NUMBER}"
                        docker tag "$IMAGE_NAME" "${TARGET_IMAGE}:latest"
                        docker push "${TARGET_IMAGE}:${BUILD_NUMBER}"
                        docker push "${TARGET_IMAGE}:latest"
                        
                        docker logout "$TARGET_REGISTRY"
                    '''
                }
            }
        }
    }

    post {
        success {
            echo "Pipeline succeeded: ${JOB_NAME} #${BUILD_NUMBER}"
        }
        failure {
            echo "Pipeline failed: ${JOB_NAME} #${BUILD_NUMBER}"
            // En cas d'échec, expose les logs Compose pour faciliter le diagnostic dans Jenkins.
            sh 'docker compose logs --no-color || true'
        }

        always {
            // Nettoie systématiquement les conteneurs, le réseau et les volumes du build.
            sh 'docker compose down -v --remove-orphans || true'
        }
    }
}
