<?php
// src/config/database.php

// On utilise 'postgres' car c'est le nom du service dans docker-compose
$host = getenv('DB_HOST') ?: 'postgres'; 
$db   = getenv('DB_NAME') ?: 'crud_db';
$user = getenv('DB_USER') ?: 'postgres'; // Par défaut 'postgres' sur l'image officielle
$pass = getenv('DB_PASS') ?: 'root';

// Pour Postgres, le DSN change de format
$dsn = "pgsql:host=$host;port=5432;dbname=$db;";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('DB connection failed: ' . $e->getMessage());
}
