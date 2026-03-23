# Projet DevOps TP : Application CRUD PHP + PostgreSQL (Dockerisée)

## Aperçu
Application web CRUD simple en **PHP 8.2** avec **Apache**, base de données **PostgreSQL 13**, et interface d'administration **pgAdmin**.  
Gérée entièrement via **Docker Compose** pour un déploiement facile.

### Fonctionnalités
- Lister, créer, modifier, supprimer des utilisateurs (table `users`).
- Router simple en PHP (`src/public/index.php`).
- MVC basique : Controllers (`UserController.php`), Models (`User.php`), Views (`liste.php`).
- Support AJAX/JSON pour interactions frontend.

## Prérequis
- **Docker** et **Docker Compose** installés.
- Ports 8080 (app) et 8081 (pgAdmin) libres.

## Installation & Démarrage (Build & Run)
```bash
# Cloner ou naviguer vers le projet
cd /home/user/Bureau/devops_tp

# Lancer les services (build auto des images)
docker-compose up --build

# Ou en arrière-plan
docker-compose up -d --build
```

### Accès
- **Application CRUD** : http://localhost:8080
- **pgAdmin** : http://localhost:8081  
  Email: `admin@example.com` | Mot de passe: `root`
- **Base de données** : PostgreSQL `crud_db`, table `users` (données de test auto-insérées).

## Architecture
```
devops_tp/
├── docker-compose.yml     # Services : php-apache, postgres, pgadmin
├── php/Dockerfile        # Image PHP + extensions Postgres
├── db/init.sql           # Init DB + table users
├── src/
│   ├── public/index.php  # Point d'entrée / Router
│   ├── config/database.php
│   ├── app/controllers/UserController.php
│   ├── app/models/User.php
│   └── app/views/users/liste.php
└── .github/workflows/    # CI/CD (GitHub Actions)
```

```
┌─────────────────┐    ┌──────────────┐
│   Browser       │───▶│ php-apache   │
│ (localhost:8080)│    │  (port 8080) │
└─────────────────┘    └──────┬──────┘
                             │
                       ┌─────▼──────┐
                       │ PostgreSQL │
                       │ (crud_db)  │
                       └──────┬──────┘
                              │
                       ┌─────▼──────┐
                       │   pgAdmin  │
                       │ (localhost │
                       │   :8081)   │
                       └────────────┘
```

## Commandes Utiles
```bash
# Logs en temps réel
docker-compose logs -f

# Arrêt propre (persiste DB via volume)
docker-compose down

# Reset complet (supprime volumes/DB)
docker-compose down -v

# Reconstruire images
docker-compose build --no-cache
```

## Dépannage
- **Port occupé** : Changez `8080:80` ou `8081:80` dans `docker-compose.yml`.
- **DB non initialisée** : Vérifiez logs `docker-compose logs postgres`, relancez.
- **Erreurs PHP** : `docker-compose logs php-apache`.
- **Permissions volumes** : `sudo chown -R $USER:$USER src/`.
- **Test DB** : Connectez pgAdmin à host `postgres`, DB `crud_db`, user `postgres`/pass `root`.

## Scripts CI/CD
- GitHub Actions (`.github/workflows/`) pour tests/deploy (à configurer avec secrets).

Projet prêt à l'emploi ! 🚀
