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

$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur - Medsense Medical</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">

    <!-- Shared CSS -->
    <link rel="stylesheet"
        href="../frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet"
        href="../backoffice/dashboard_service/css/dashboard.css?v=<?php echo time(); ?>">

    <style>
        .edit-user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="../frontoffice/page-accueil/images/logo.jpeg" alt="Logo Medsense"
                style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="../backoffice/admin_hub.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-th-large"></i></span>
                <span>Hub Central</span>
            </a>
            <a href="admin-users.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                <span>Utilisateurs</span>
            </a>
            <a href="admin-medecins.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-user-md"></i></span>
                <span>Médecins</span>
            </a>
            <a href="admin-reports-statistics.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
                <span>Stats</span>
            </a>
            <a href="../backoffice/dashboard_service/dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
                <span>Services</span>
            </a>
            <a href="../../../projet_unifie/views/back-office/dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-newspaper"></i></span>
                <span>Blog</span>
            </a>
            <a href="../../../projet_unifie/views/backoffice/reponse/admin_reclamations.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <span>Réclamations</span>
            </a>
            <a href="../../controllers/logout.php" class="nav-link logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </header>

    <main class="dashboard-container">
        <!-- Hero Section -->
        <section class="hero-section text-start mb-4">
            <h1 class="hero-title" style="font-size: 1.75rem; text-align: left;">Modifier Utilisateur</h1>
            <p class="hero-description" style="text-align: left;">
                Modifiez les informations et le statut de l'utilisateur.
            </p>
        </section>

        <div class="row justify-content-center">
            <div class="col-md-8 mx-auto" style="max-width: 800px;">
                <div class="dashboard-card animate-fade-in-up">
                    <div class="dashboard-card-header d-flex justify-content-between align-items-center">
                        <h3 class="dashboard-card-title mb-0">
                            <i class="fas fa-user-edit me-2"></i>Informations de l'utilisateur
                        </h3>
                        <a href="admin-users.php" class="dashboard-btn btn-outline btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                    </div>

                    <div class="dashboard-card-body">
                        <?php if (isset($error_message)): ?>
                            <div
                                style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #fecaca;">
                                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>

                        <!-- User Info Summary -->
                        <div
                            style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border-left: 4px solid #3b82f6;">
                            <div style="display: flex; align-items: center; gap: 1.5rem;">
                                <div class="edit-user-avatar">
                                    <?= strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4
                                        style="margin: 0 0 0.5rem; font-size: 1.25rem; font-weight: 600; color: #1e293b;">
                                        <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                                    </h4>
                                    <p style="margin: 0 0 0.5rem; color: #64748b;">
                                        <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user['email']) ?>
                                    </p>
                                    <div style="display: flex; gap: 1rem;">
                                        <span class="dashboard-badge role-<?= $user['role'] ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                        <span
                                            class="dashboard-badge role-<?= ($user['statut'] == 'actif' ? 'success' : 'secondary') ?>">
                                            <?= ucfirst($user['statut']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <form method="POST" id="editUserForm">
                            <div
                                style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label for="role"
                                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">
                                        Rôle <span style="color: #ef4444;">*</span>
                                    </label>
                                    <select id="role" name="role" required
                                        style="width: 100%; padding: 0.625rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background-color: #fff;">
                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur
                                        </option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>
                                            Administrateur</option>
                                        <option value="moderator" <?= $user['role'] === 'moderator' ? 'selected' : '' ?>>
                                            Modérateur</option>
                                    </select>
                                    <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: #6b7280;">
                                        <i class="fas fa-info-circle me-1"></i> Niveau d'accès au système
                                    </p>
                                </div>
                                <div>
                                    <label for="statut"
                                        style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">
                                        Statut du compte <span style="color: #ef4444;">*</span>
                                    </label>
                                    <select id="statut" name="statut" required
                                        style="width: 100%; padding: 0.625rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background-color: #fff;">
                                        <option value="actif" <?= $user['statut'] === 'actif' ? 'selected' : '' ?>>Actif -
                                            Compte accessible</option>
                                        <option value="inactif" <?= $user['statut'] === 'inactif' ? 'selected' : '' ?>>
                                            Inactif - Compte désactivé</option>
                                    </select>
                                    <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: #6b7280;">
                                        <i class="fas fa-info-circle me-1"></i> État de connexion autorisé
                                    </p>
                                </div>
                            </div>

                            <div
                                style="background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                                <h6 style="margin: 0 0 0.5rem; font-weight: 600;"><i
                                        class="fas fa-exclamation-triangle me-2"></i>Attention</h6>
                                <p style="margin: 0; font-size: 0.875rem;">
                                    La modification du rôle et du statut prend effet immédiatement.
                                    Un compte désactivé ne pourra plus se connecter.
                                </p>
                            </div>

                            <div
                                style="display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                                <a href="admin-users.php" class="dashboard-btn btn-outline">Annuler</a>
                                <button type="submit" class="dashboard-btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('editUserForm').addEventListener('submit', function (e) {
            const statut = document.getElementById('statut').value;
            if (statut === 'inactif') {
                if (!confirm('Êtes-vous sûr de vouloir désactiver ce compte ? L\'utilisateur ne pourra plus se connecter.')) {
                    e.preventDefault();
                }
            }
        });

        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', function (e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>