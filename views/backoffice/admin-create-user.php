<?php
// views/backoffice/admin-create-user.php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();

// Traitement du formulaire
$error_message = null;
$success_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $adminController->manageUsers('create', $_POST);
    
    if ($result['success']) {
        $success_message = $result['message'];
        // Réinitialiser le formulaire après succès
        $_POST = [];
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
    <title>Ajouter un Utilisateur - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
            <div class="container">
                <a class="navbar-brand" href="admin-dashboard.php">
                    <i class="fas fa-cogs"></i> Administration
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="admin-users.php">
                        <i class="fas fa-users"></i> Utilisateurs
                    </a>
                    <a class="nav-link" href="../frontoffice/auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Ajouter un Nouvel Utilisateur</h4>
                            <a href="admin-users.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Messages d'alerte -->
                            <?php if ($success_message): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" id="createUserForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nom" name="nom" 
                                                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                                            <div class="form-text">Le nom de famille de l'utilisateur</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                                   value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                                            <div class="form-text">Le prénom de l'utilisateur</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                    <div class="form-text">L'adresse email sera utilisée pour la connexion</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="dateNaissance" class="form-label">Date de Naissance</label>
                                            <input type="date" class="form-control" id="dateNaissance" name="dateNaissance" 
                                                   value="<?= htmlspecialchars($_POST['dateNaissance'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="adresse" class="form-label">Adresse</label>
                                            <input type="text" class="form-control" id="adresse" name="adresse" 
                                                   value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="">Sélectionner un rôle</option>
                                                <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                                <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                                <option value="moderator" <?= ($_POST['role'] ?? '') === 'moderator' ? 'selected' : '' ?>>Modérateur</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                                            <select class="form-select" id="statut" name="statut" required>
                                                <option value="">Sélectionner un statut</option>
                                                <option value="actif" <?= ($_POST['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                                                <option value="inactif" <?= ($_POST['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section mot de passe -->
                                <div class="card mt-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-lock me-2"></i>Mot de passe <span class="text-danger">*</span></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <small>
                                                <i class="fas fa-info-circle"></i>
                                                Le mot de passe doit contenir au moins 8 caractères
                                            </small>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="mot_de_passe" class="form-label">Mot de passe</label>
                                                    <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" 
                                                           minlength="8" required>
                                                    <div class="form-text">Minimum 8 caractères</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="confirm_mot_de_passe" class="form-label">Confirmer le mot de passe</label>
                                                    <input type="password" class="form-control" id="confirm_mot_de_passe" 
                                                           name="confirm_mot_de_passe" minlength="8" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-1"></i> Créer l'utilisateur
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">Réinitialiser</button>
                                    <a href="admin-users.php" class="btn btn-secondary">Annuler</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            const password = document.getElementById('mot_de_passe').value;
            const confirmPassword = document.getElementById('confirm_mot_de_passe').value;
            
            // Validation des mots de passe
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas !');
                document.getElementById('confirm_mot_de_passe').focus();
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères !');
                document.getElementById('mot_de_passe').focus();
                return false;
            }

            // Validation de l'email
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide !');
                document.getElementById('email').focus();
                return false;
            }
        });

        // Réinitialiser les messages d'alerte après 5 secondes
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>