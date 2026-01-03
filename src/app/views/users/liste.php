<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Liste utilisateurs</title>
<!-- Retrait du slash avant assets -->
<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Gestion des Utilisateurs</h1>
        <button id="btnCreate" class="btn">
            <span>+</span> Ajouter un utilisateur
        </button>
    </header>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody id="usersTable">
                <?php foreach ($users as $u): ?>
                <tr data-id="<?= htmlspecialchars($u['id']) ?>">
                    <td style="color: var(--text-light)">#<?= htmlspecialchars($u['id']) ?></td>
                    <td class="name"><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                    <td class="email"><?= htmlspecialchars($u['email']) ?></td>
                    <td style="text-align:right">
                        <button class="btnEdit btn" data-id="<?= $u['id'] ?>">Modifier</button>
                        <button class="btnDelete btn" data-id="<?= $u['id'] ?>">Supprimer</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle">Ajouter</h2>
        <form id="userForm">
            <input type="hidden" name="id" id="userId">
            <div class="form-group">
                <label for="userName">Nom complet</label>
                <input type="text" name="name" id="userName" placeholder="Ex: Jean Dupont" required>
            </div>
            <div class="form-group">
                <label for="userEmail">Adresse Email</label>
                <input type="email" name="email" id="userEmail" placeholder="jean@exemple.com" required>
            </div>
            <div class="form-actions">
                <button type="button" id="btnCancel" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn" style="background: var(--primary); color: white;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<script src="assets/js/user.js"></script>
</body>
</html>
