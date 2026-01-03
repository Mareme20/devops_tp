CREATE DATABASE IF NOT EXISTS crud_db;
USE crud_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL
);

//postgresql version
-- CREATE DATABASE crud_db; 
-- \c crud_db;
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE
);

-- Ajoutez un utilisateur pour tester l'affichage
INSERT INTO users (name, email) 
VALUES ('Marieme', 'marieme@example.com');
