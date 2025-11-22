<?php

include_once '../../controllers/AdminController.php';

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header('Location: admin-users.php');
    exit;
}

$adminController = new AdminController();
$userResult = $adminController->manageUsers('get', null, $user_id);

if (!$userResult['success']) {
    header('Location: admin-users.php');
    exit;
}

$user = $userResult['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $adminController->manageUsers('update', $_POST, $user_id);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: admin-users.php');
        exit;
    } else {
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>Modifier l'Utilisateur</h4>
                        <a href="admin-users.php" class="btn btn-secondary btn-sm">← Retour</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nom" class="form-label">Nom</label>
                                        <input type="text" class="form-control" id="nom" name="nom" 
                                               value="<?= htmlspecialchars($user['nom']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prenom" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" 
                                               value="<?= htmlspecialchars($user['prenom']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Rôle</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="statut" class="form-label">Statut</label>
                                        <select class="form-select" id="statut" name="statut" required>
                                            <option value="actif" <?= $user['statut'] === 'actif' ? 'selected' : '' ?>>Actif</option>
                                            <option value="inactif" <?= $user['statut'] === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h6>Changer le mot de passe (optionnel)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mot_de_passe" class="form-label">Nouveau mot de passe</label>
                                                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="confirm_mot_de_passe" class="form-label">Confirmer le mot de passe</label>
                                                <input type="password" class="form-control" id="confirm_mot_de_passe" name="confirm_mot_de_passe">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                <a href="admin-users.php" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>