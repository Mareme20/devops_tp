# Projet DevOps TP : Application CRUD PHP + PostgreSQL (Dockerisée avec Nginx SSL)

## Aperçu [![Docker](https://img.shields.io/badge/Docker-Compose-blue)](https://docs.docker.com/compose/) [![PHP 8.2](https://img.shields.io/badge/PHP-8.2-green)](https://www.php.net/) [![PostgreSQL](https://img.shields.io/badge/PostgreSQL-13-orange)](https://www.postgresql.org/)

Application web **CRUD** (Create, Read, Update, Delete) pour gérer des utilisateurs en **PHP 8.2** avec **Nginx** (proxy SSL), **Apache/PHP-FPM**, **PostgreSQL 13**, et **pgAdmin**.  
Déploiement zero-config via **Docker Compose**. HTTPS automatique (certificats auto-signés).

### Fonctionnalités
- Gestion utilisateurs : Lister, Ajouter, Modifier, Supprimer (table `users` : id, name, email).
- Routeur PHP simple (`src/public/index.php`) avec support AJAX/JSON.
- MVC léger : `UserController`, `User` model, vue `liste.php` (CSS/JS inclus).
- SSL via Nginx (redirect HTTP → HTTPS).

## Prérequis
- **Docker** + **Docker Compose** v2+.
- Ports libres : **80/443** (app HTTPS), **8083** (pgAdmin).

⚠️ **HTTPS self-signed** : Acceptez le certificat dans le navigateur (localhost).

## Installation & Démarrage
```bash
cd /home/user/Bureau/devops_tp

# Build & lancement (images custom)
docker-compose up --build

# Background
docker-compose up -d --build
```

### Accès
| Service | URL | Credentials |
|---------|-----|-------------|
| **App CRUD** | https://localhost (ou http://localhost → redirect) | - |
| **pgAdmin** | http://localhost:8083 | Email: `admin@example.com`<br>Pass: `root` |
| **DB Direct** | Host: `postgres`, DB: `crud_db`, User/Pass: `postgres/root` | - |

**Test** : App liste 'Marieme' (donnée init).

## Architecture
```
devops_tp/
├── docker-compose.yml          # Nginx, php-apache, postgres, pgadmin
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
└── .github/workflows/main.yml # CI/CD Docker Hub
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
```

## CI/CD (GitHub Actions)
- **main.yml** : Sur push main/tag, build/push image PHP → Docker Hub (`mareme2930/php-apache-crud`).
- Tests `docker-compose up`, notif Slack (configurer secrets : DOCKER_HUB_*, SLACK_WEBHOOK).
 

Projet **100% fonctionnel** 🚀. Contributions bienvenues !
