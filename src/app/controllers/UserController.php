<?php
// src/app/controllers/UserController.php
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new User($pdo);
    }

    public function index()
    {
        return $this->model->all();
    }

    public function store(array $data)
    {
        // Validation minimale (faire plus en prod)
        if (empty($data['name']) || empty($data['email'])) {
            return false;
        }
        return $this->model->create($data['name'], $data['email']);
    }

    public function update(array $data)
    {
        if (empty($data['id']) || empty($data['name']) || empty($data['email'])) {
            return false;
        }
        return $this->model->update($data['id'], $data['name'], $data['email']);
    }

    public function destroy($id)
    {
        return $this->model->delete($id);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }
}
