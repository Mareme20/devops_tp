-- PostgreSQL crée la base automatiquement via POSTGRES_DB dans le docker-compose
-- On crée directement la table dans cette base

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE
);

-- Ajout d'un utilisateur de test
INSERT INTO users (name, email) 
VALUES ('Marieme', 'marieme@example.com')
ON CONFLICT (email) DO NOTHING;
