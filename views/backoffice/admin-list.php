<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();
$usersResult = $adminController->manageUsers('list');
$users = $usersResult['success'] ? $usersResult['users'] : [];

// Gérer les messages de succès/erreur
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        .btn { padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; margin: 0.2rem; }
        .btn-primary { background: #007bff; color: white; }
        .btn-edit { background: #28a745; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .search-form { margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Gestion des Utilisateurs</h1>

        <!-- Messages d'alerte -->
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="search-form">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Rechercher...">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </form>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id_utilisateur'] ?? $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['nom']) ?></td>
                    <td><?= htmlspecialchars($user['prenom']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?= $user['statut'] === 'actif' ? 'success' : 'secondary' ?>">
                            <?= ucfirst($user['statut']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="admin-edit.php?id=<?= $user['id_utilisateur'] ?? $user['id'] ?>" class="btn btn-edit">Modifier</a>
                        <form method="POST" action="admin-delete.php" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                            <input type="hidden" name="user_id" value="<?= $user['id_utilisateur'] ?? $user['id'] ?>">
                            <button type="submit" class="btn btn-delete">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div>
            <a href="admin-dashboard.php" class="btn btn-primary">Retour au Dashboard</a>
        </div>
    </div>
</body>
</html>