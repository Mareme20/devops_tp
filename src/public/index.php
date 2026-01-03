<?php
// src/public/index.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/UserController.php';

// Instanciation
$controller = new UserController($pdo);

// Router simple via action POST/GET
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? 'index';

if ($method === 'POST') {
    // Les requêtes AJAX/form pointent ici
    switch ($action) {
        case 'store':
            $ok = $controller->store($_POST);
            echo json_encode(['success' => (bool)$ok]);
            exit;

        case 'update':
            $ok = $controller->update($_POST);
            echo json_encode(['success' => (bool)$ok]);
            exit;

        case 'delete':
            $ok = $controller->destroy($_POST['id'] ?? null);
            echo json_encode(['success' => (bool)$ok]);
            exit;
    }
}

// Affichage page
$users = $controller->index();
require __DIR__ . '/../app/views/users/liste.php';
