<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();

$error_message = null;
$success_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_data = array_map(function ($value) {
        if (is_string($value)) {
            $value = strip_tags($value);
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $value = trim($value);
        }
        return $value;
    }, $_POST);

    $result = $adminController->manageUsers('create', $post_data);

    if ($result['success']) {
        $success_message = $result['message'];
        $_POST = [];
    } else {
        $error_message = $result['message'];
    }
}

function escape_data($data)
{
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

$dashboardData = $adminController->dashboard();
$stats = $dashboardData['stats'] ?? [];
$recentUsers = $dashboardData['recentUsers'] ?? [];
$pendingDoctors = $dashboardData['pendingDoctors'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Utilisateur - Medsense Medical</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">

    <!-- Shared CSS -->
    <link rel="stylesheet"
        href="../frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet"
        href="../backoffice/dashboard_service/css/dashboard.css?v=<?php echo time(); ?>">
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="hero-title" style="font-size: 1.75rem; text-align: left;">Nouvel Utilisateur</h1>
                    <p class="hero-description" style="text-align: left;">
                        Ajouter un nouveau compte utilisateur au système.
                    </p>
                </div>
                <a href="admin-users.php" class="dashboard-btn btn-outline">
                    <i class="fas fa-arrow-left me-2"></i> Retour à la liste
                </a>
            </div>
        </section>

        <?php if ($success_message): ?>
            <div
                style="padding: 1rem; background: #dcfce7; color: #166534; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #bbf7d0;">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div
                style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #fecaca;">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title">
                    <i class="fas fa-user-plus me-2"></i> Informations du compte
                </h3>
            </div>
            <div class="dashboard-card-body">
                <div
                    style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 1rem; margin-bottom: 2rem; border-radius: 4px;">
                    <strong style="color: #92400e; display: block; margin-bottom: 0.25rem;">Sécurité des
                        données</strong>
                    <span style="color: #b45309; font-size: 0.9rem;">
                        Toutes les données saisies sont automatiquement protégées contre les injections HTML et les
                        scripts malveillants.
                    </span>
                </div>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div>
                            <label
                                style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-main);">Nom
                                <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" name="nom" required
                                style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; font-family: inherit;"
                                placeholder="Ex: Dupont">
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">2 à 50
                                caractères alphabétiques uniquement</div>
                        </div>
                        <div>
                            <label
                                style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-main);">Prénom
                                <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" name="prenom" required
                                style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; font-family: inherit;"
                                placeholder="Ex: Jean">
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">2 à 50
                                caractères alphabétiques uniquement</div>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label
                            style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-main);">Email
                            <span style="color: var(--danger-color);">*</span></label>
                        <input type="email" name="email" required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; font-family: inherit;"
                            placeholder="Ex: jean.dupont@email.com">
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">L'adresse email
                            sera utilisée pour la connexion</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div>
                            <label
                                style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-main);">Rôle
                                <span style="color: var(--danger-color);">*</span></label>
                            <select name="role" required
                                style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-family: inherit;">
                                <option value="" disabled selected>Sélectionner un rôle</option>
                                <option value="patient">Patient</option>
                                <option value="medecin">Médecin</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div>
                            <label
                                style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-main);">Statut
                                <span style="color: var(--danger-color);">*</span></label>
                            <select name="statut" required
                                style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-family: inherit;">
                                <option value="actif" selected>Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="en_attente">En attente</option>
                            </select>
                        </div>
                    </div>

                    <div
                        style="background: #eff6ff; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #dbeafe;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1e3a8a;">Mot de
                            passe <span style="color: var(--danger-color);">*</span></label>
                        <div style="position: relative;">
                            <input type="password" name="mot_de_passe" id="password" required
                                style="width: 100%; padding: 0.75rem; padding-right: 40px; border: 1px solid #bfdbfe; border-radius: 8px; font-family: inherit;">
                            <button type="button" onclick="togglePassword()"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #60a5fa; cursor: pointer;">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div style="font-size: 0.8rem; color: #6b7280; margin-top: 0.5rem;">
                            <i class="fas fa-info-circle me-1"></i> Minimum 8 caractères
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                        <button type="reset" class="dashboard-btn btn-outline">Réinitialiser</button>
                        <button type="submit" class="dashboard-btn btn-primary">
                            <i class="fas fa-save me-2"></i> Créer l'utilisateur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>