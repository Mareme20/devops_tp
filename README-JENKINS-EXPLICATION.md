# Explication Processus CI/CD avec Jenkins + Nexus

## Flux du Pipeline Jenkinsfile

### 1. **Validate Compose**
```
sh 'docker compose --profile artifact-repo --profile observability config >/dev/null'
```
- Valide la stack principale et les profils `Nexus` / `ELK` **sans lancer de services**.

### 2. **Build Image**
```
sh 'docker compose build php-apache'
```
- Construit image PHP/Apache depuis `./php/Dockerfile`.
- Nom image : `devops-tp-${BUILD_NUMBER}-php-apache` (préfixe COMPOSE_PROJECT_NAME isole chaque build).

### 3. **Lint PHP**
```
docker run ${COMPOSE_PROJECT_NAME}-php-apache php -l *.php
```
- Vérifie syntaxe PHP avec image fraîche.

### 4. **Tests Intégration**
- `up -d postgres php-apache` : Lance services DB + app.
- `pg_isready` loop : Attend DB prête.
- Smoke test : Exécute `index.php`, vérifie user seedé "Marieme".

### 5. **Start Nexus**
```
docker compose --profile artifact-repo up -d nexus
docker compose --profile artifact-repo run --rm nexus-init
```
- Démarre `Nexus Repository`.
- Crée automatiquement un repository Docker hosted nommé `docker-hosted` sur le port `8085`.

### 6. **Push to Nexus**
```
TARGET_REGISTRY=${NEXUS_DOCKER_REGISTRY:-host.docker.internal:8085}
docker login $TARGET_REGISTRY
docker tag devops-tp-${BUILD_NUMBER}-php-apache $TARGET_REGISTRY/devops-tp:${BUILD_NUMBER}
docker push $TARGET_REGISTRY/devops-tp:${BUILD_NUMBER}
```
- Login sécurisé via Jenkins credential `nexus-creds`.
- Publication de l'image validée dans le registry Docker de Nexus.

### 7. **Notifications Slack**
```
slackSend channel: '#ci', message: \"✅ Succeeded #${BUILD_NUMBER}\"
```
- Succès (vert ✅) / Échec (rouge ❌) avec lien build.

### 8. **Cleanup (post { always })**
```
docker compose down -v --remove-orphans
```
- **Supprime** : conteneurs, réseaux, volumes.
- **Garde** : images Docker locales construites par le démon.

## Avantages
- **Isolation totale** par BUILD_NUMBER.
- **Pipeline complet** : Build → Test → Nexus → Push → Notif.
- **Reproductible** : même flux local (`docker compose up`).

## Config Requise
```
nexus-creds                          # Jenkins credential (user/pass Nexus)
NEXUS_DOCKER_REGISTRY                # optionnel : host.docker.internal:8085 ou localhost:8085
Slack plugin + channel '#ci'
```

**Test local** :
- `docker compose up -d` → app
- `docker compose --profile artifact-repo up -d nexus`
- `docker compose --profile artifact-repo run --rm nexus-init`
