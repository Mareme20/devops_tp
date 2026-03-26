# Projet DevOps TP : Application CRUD PHP + PostgreSQL avec Jenkins, Nexus et ELK

## Aperçu [![Docker](https://img.shields.io/badge/Docker-Compose-blue)](https://docs.docker.com/compose/) [![PHP 8.2](https://img.shields.io/badge/PHP-8.2-green)](https://www.php.net/) [![PostgreSQL](https://img.shields.io/badge/PostgreSQL-13-orange)](https://www.postgresql.org/) [![Jenkins](https://img.shields.io/badge/Jenkins-Pipeline-red)](https://www.jenkins.io/) [![Nexus](https://img.shields.io/badge/Nexus-Repository-black)](https://www.sonatype.com/products/repository-oss) [![ELK](https://img.shields.io/badge/ELK-Observability-yellow)](https://www.elastic.co/elastic-stack)

Application web **CRUD** (Create, Read, Update, Delete) pour gérer des utilisateurs en **PHP 8.2** avec **Nginx** (proxy SSL), **Apache/PHP-FPM**, **PostgreSQL 13**, et **pgAdmin**.  
Le projet inclut aussi :
- un pipeline **Jenkins** pour builder, tester et publier l'image applicative,
- **Nexus Repository** comme registry Docker local,
- une stack **ELK** pour centraliser et visualiser les logs des conteneurs.

Déploiement zero-config via **Docker Compose**. HTTPS automatique (certificats auto-signés). `Nexus` et `ELK` sont activables à la demande via des profils Compose pour éviter de surcharger le lancement standard.

### Fonctionnalités
- Gestion utilisateurs : Lister, Ajouter, Modifier, Supprimer (table `users` : id, name, email).
- Routeur PHP simple (`src/public/index.php`) avec support AJAX/JSON.
- MVC léger : `UserController`, `User` model, vue `liste.php` (CSS/JS inclus).
- SSL via Nginx (redirect HTTP → HTTPS).

## Prérequis
- **Docker** + **Docker Compose** v2+.
- Ports libres : **80/443** (app HTTPS), **8083** (pgAdmin), **8081/8085** (Nexus UI + registry), **9200/5601/5044** (ELK).

⚠️ **HTTPS self-signed** : Acceptez le certificat dans le navigateur (localhost).

## Installation & Démarrage
```bash
cd /home/user/Bureau/devops_tp

# Build & lancement de l'application principale
docker-compose up --build

# Background
docker-compose up -d --build

# Lancer Nexus pour la démo Jenkins/registry
docker compose --profile artifact-repo up -d nexus
docker compose --profile artifact-repo run --rm nexus-init

# Lancer ELK pour la centralisation des logs
docker compose --profile observability up -d elasticsearch logstash kibana filebeat

# Tout lancer ensemble
docker compose --profile artifact-repo --profile observability up -d --build
```

### Accès
| Service | URL | Credentials |
|---------|-----|-------------|
| **App CRUD** | https://localhost (ou http://localhost → redirect) | - |
| **pgAdmin** | http://localhost:8083 | Email: `admin@example.com`<br>Pass: `root` |
| **Nexus UI** | http://localhost:8081 | User: `admin`<br>Pass: `admin123` ou contenu de `/nexus-data/admin.password` |
| **Nexus Docker Registry** | `localhost:8085` | même compte Nexus |
| **Elasticsearch** | http://localhost:9200 | - |
| **Kibana** | http://localhost:5601 | - |
| **DB Direct** | Host: `postgres`, DB: `crud_db`, User/Pass: `postgres/root` | - |

**Test** : App liste 'Marieme' (donnée init).

## Architecture
```
devops_tp/
├── Jenkinsfile               # Pipeline Jenkins : validation, build, smoke test
├── docker-compose.yml        # App principale + profils Nexus/ELK
├── elk/
│   ├── filebeat/filebeat.yml
│   └── logstash/pipeline/logstash.conf
├── nexus/
│   └── setup/init-nexus.sh   # Création auto du repo Docker hosted
├── php/Dockerfile             # PHP8.2 + PDO/PgSQL + Apache rewrite
├── nginx/conf/default.conf    # SSL proxy → php-apache
├── nginx/certs/localhost.*    # Certs self-signed
├── db/init.sql                # CREATE TABLE users + test data
├── src/
│   ├── public/index.php       # Routeur (index/store/update/delete)
│   ├── config/database.php
│   ├── app/controllers/UserController.php
│   ├── app/models/User.php
│   └── app/views/users/liste.php + assets/
└── db/init.sql                # CREATE TABLE users + donnée de test
```

**Flux** :
```
Browser (https://localhost:443)
       ↓ Nginx SSL Proxy (cert self-signed, proxy_pass)
     php-apache:80 (/public → index.php Router)
       ↓ PDO/PgSQL
    PostgreSQL (crud_db, users table)
          ↑
       pgAdmin (localhost:8083)

Jenkins
  ↓ build + lint + smoke test
Nexus Registry (localhost:8085)

Docker container logs
  ↓ Filebeat
Logstash
  ↓
Elasticsearch
  ↓
Kibana
```

## Commandes Utiles
```bash
# Logs
docker-compose logs -f nginx    # ou php-apache, postgres

# Arrêt (DB persistée via volume)
docker-compose down

# Reset DB
docker-compose down -v

# Rebuild
docker-compose build --no-cache

# Shell conteneurs
docker-compose exec php-apache bash
docker-compose exec postgres psql -U postgres -d crud_db

# Initialiser Nexus si nécessaire
docker compose --profile artifact-repo run --rm nexus-init

# Voir les logs ELK
docker compose --profile observability logs -f filebeat
docker compose --profile observability logs -f logstash
```

## Jenkins
Le dépôt contient un [`Jenkinsfile`](Jenkinsfile) prêt à être utilisé dans un job Pipeline Jenkins.

### Ce que fait le pipeline
- Valide la configuration `docker-compose.yml`.
- Vérifie la syntaxe de tous les fichiers PHP avec `php -l`.
- Build l'image `php-apache`.
- Lance `Nexus` et prépare automatiquement un repository Docker hosted.
- Lance `postgres` et `php-apache`.
- Attend que PostgreSQL soit prêt.
- Exécute un smoke test qui rend la page principale et vérifie la présence de l'utilisateur seedé `Marieme`.
- Push l'image validée vers le registry Docker exposé par `Nexus`.
- Nettoie les conteneurs et volumes en fin de job.

### Prérequis Jenkins
- Jenkins doit tourner sur un agent ayant accès à **Docker** et **Docker Compose v2**.
- L'utilisateur Jenkins doit pouvoir exécuter `docker`.
- Ajouter un credential Jenkins de type username/password avec l'ID `nexus-creds`.
- Si Jenkins tourne dans un conteneur Docker, ajoutez `--add-host=host.docker.internal:host-gateway`.
- Si vous gardez Slack, conserver aussi la config du plugin Slack et le canal `#ci`.

### Créer le job
1. Dans Jenkins, créez un job de type **Pipeline** ou **Multibranch Pipeline**.
2. Pointez-le vers ce dépôt Git.
3. Laissez Jenkins utiliser automatiquement le fichier `Jenkinsfile` à la racine.
4. Configurez le credential `nexus-creds` avec le compte Nexus.
5. Si besoin, définissez `NEXUS_DOCKER_REGISTRY` :
   - `host.docker.internal:8085` si Jenkins tourne en conteneur
   - `localhost:8085` si Jenkins tourne directement sur l'hôte
6. Lancez un build.

### Lancer Jenkins localement avec Docker
```bash
docker run -d \
  --name jenkins \
  --add-host=host.docker.internal:host-gateway \
  -p 8080:8080 \
  -p 50000:50000 \
  -v jenkins_home:/var/jenkins_home \
  -v /var/run/docker.sock:/var/run/docker.sock \
  jenkins/jenkins:lts
```

Ensuite :
- Ouvrez `http://localhost:8080`
- Installez les plugins recommandés
- Créez votre job Pipeline

## ELK
La stack `ELK` centralise les logs Docker via `Filebeat -> Logstash -> Elasticsearch -> Kibana`.

### Afficher les logs dans Kibana
1. Lancez le profil observabilité.
2. Ouvrez `http://localhost:5601`.
3. Créez une Data View sur `docker-logs-*`.
4. Consultez les logs des services `nginx`, `php-apache`, `postgres`, `nexus`, etc.

## Nexus
Le profil `artifact-repo` lance `Nexus` et expose un registry Docker local sur `localhost:8085`.

### Image publiée par Jenkins
- `localhost:8085/devops-tp:latest`
- `localhost:8085/devops-tp:<BUILD_NUMBER>`

Projet **fonctionnel** et prêt pour une intégration Jenkins + Nexus + ELK.
