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
    $post_data = array_map(function($value) {
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

function escape_data($data) {
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
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    
    <style>
.dashboard-page {
    min-height: 100vh;
    background: #f8fafc;
}

.dashboard-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    grid-template-rows: 70px 1fr;
    grid-template-areas:
        "sidebar header"
        "sidebar main";
    min-height: 100vh;
}

.dashboard-header {
    grid-area: header;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
}

.dashboard-menu-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
}

.dashboard-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
}

.dashboard-subtitle {
    font-size: 0.875rem;
    color: #64748b;
}

.dashboard-user-info {
    display: flex;
    align-items: center;
}

.dashboard-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
}

.dashboard-user-details {
    line-height: 1.4;
}

.dashboard-user-name {
    font-weight: 600;
    color: #1e293b;
}

.dashboard-user-role {
    font-size: 0.75rem;
    color: #64748b;
}

.dashboard-sidebar {
    grid-area: sidebar;
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    color: white;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    position: sticky;
    top: 0;
    height: 100vh;
}

.dashboard-logo {
    padding: 24px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.dashboard-logo-img {
    max-height: 40px;
    margin-right: 12px;
}

.dashboard-logo-text {
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
}

.dashboard-nav {
    flex: 1;
    padding: 20px 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.dashboard-nav-section {
    margin-bottom: 20px;
}

.dashboard-nav-title {
    padding: 0 20px 8px;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #94a3b8;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.dashboard-nav-item {
    padding: 12px 20px;
    color: #cbd5e1;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s;
    border-left: 3px solid transparent;
    cursor: pointer;
}

.dashboard-nav-item:hover,
.dashboard-nav-item.active {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-left-color: #3b82f6;
}

.dashboard-nav-item.logout {
    color: #f87171;
    margin-top: auto;
}

.dashboard-nav-item.logout:hover {
    background: rgba(248, 113, 113, 0.1);
}

.dashboard-nav-item i {
    width: 20px;
    text-align: center;
}

.dashboard-badge {
    background: #ef4444;
    color: white;
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 12px;
    margin-left: 8px;
}

.with-submenu {
    flex-direction: column;
    padding: 0;
}

.submenu-toggle {
    transition: transform 0.3s;
}

.submenu-toggle.open {
    transform: rotate(180deg);
}

.dashboard-submenu {
    display: none;
    background: rgba(0, 0, 0, 0.2);
    border-left: 3px solid #3b82f6;
}

.dashboard-submenu-item {
    padding: 10px 20px 10px 45px;
    color: #cbd5e1;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s;
    font-size: 0.875rem;
}

.dashboard-submenu-item:hover {
    background: rgba(255, 255, 255, 0.05);
    color: white;
}

.dashboard-main {
    grid-area: main;
    padding: 24px;
    overflow-y: auto;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}

.dashboard-alert {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.dashboard-alert i {
    margin-top: 2px;
}

.dashboard-alert-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: inherit;
    cursor: pointer;
    margin-left: auto;
    opacity: 0.7;
}

.dashboard-alert-close:hover {
    opacity: 1;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    overflow: hidden;
}

.dashboard-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dashboard-card-body {
    padding: 24px;
}

.dashboard-card-footer {
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
}

.dashboard-form-group {
    margin-bottom: 1.5rem;
}

.dashboard-form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.dashboard-form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.dashboard-form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.dashboard-form-control.is-invalid {
    border-color: #ef4444;
}

.dashboard-form-control.is-valid {
    border-color: #10b981;
}

.dashboard-form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.dashboard-invalid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #ef4444;
}

.dashboard-valid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #10b981;
}

.dashboard-form-control.is-invalid ~ .dashboard-invalid-feedback {
    display: block;
}

.dashboard-form-control.is-valid ~ .dashboard-valid-feedback {
    display: block;
}

.dashboard-row {
    display: flex;
    flex-wrap: wrap;
    margin-left: -0.75rem;
    margin-right: -0.75rem;
}

.dashboard-col {
    flex: 1 0 0%;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

.dashboard-col-6 {
    flex: 0 0 auto;
    width: 50%;
}

.dashboard-col-12 {
    flex: 0 0 auto;
    width: 100%;
}

.dashboard-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 24px;
}

.dashboard-btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    font-size: 0.875rem;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-outline {
    background: white;
    color: #64748b;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f8fafc;
    border-color: #9ca3af;
}

.dashboard-quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 24px;
}

.dashboard-quick-action {
    background: #f8fafc;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    text-decoration: none;
    color: #1e293b;
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.dashboard-quick-action:hover {
    border-color: #3b82f6;
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.dashboard-quick-action-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.dashboard-quick-action-text {
    font-weight: 500;
}

.password-card {
    border-left: 4px solid #3b82f6;
    background: #f0f9ff;
}

.security-alert {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    color: #92400e;
}

@media (max-width: 1024px) {
    .dashboard-container {
        grid-template-columns: 200px 1fr;
    }
    
    .dashboard-sidebar {
        width: 200px;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        grid-template-columns: 1fr;
        grid-template-areas:
            "header"
            "main";
    }
    
    .dashboard-sidebar {
        position: fixed;
        left: -250px;
        top: 0;
        bottom: 0;
        z-index: 1000;
        width: 250px;
        transition: left 0.3s;
    }
    
    .dashboard-sidebar.active {
        left: 0;
    }
    
    .dashboard-menu-toggle {
        display: block;
    }
    
    .dashboard-col-6 {
        width: 100%;
    }
    
    .dashboard-main {
        padding: 16px;
    }
    
    .dashboard-card-body {
        padding: 16px;
    }
    
    .dashboard-actions {
        flex-direction: column;
    }
    
    .dashboard-btn {
        width: 100%;
    }
}

@media (max-width: 640px) {
    .dashboard-header {
        padding: 0 16px;
    }
    
    .dashboard-quick-actions {
        grid-template-columns: 1fr;
    }
}

.text-danger {
    color: #ef4444 !important;
}

.text-success {
    color: #10b981 !important;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

.gap-2 {
    gap: 0.5rem;
}

.d-flex {
    display: flex;
}

.justify-content-between {
    justify-content: space-between;
}

.align-items-center {
    align-items: center;
}
    </style>
</head>
<body class="dashboard-page">

    <div class="dashboard-container">
        <header class="dashboard-header">
            <button class="dashboard-menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="d-flex align-items-center gap-3">
                <h1 class="dashboard-title mb-0">Création d'Utilisateur</h1>
                <div class="dashboard-subtitle">Administration Medsense</div>
            </div>
            <div class="dashboard-user-info">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                        <div class="dashboard-avatar">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="dashboard-user-details ms-2">
                            <div class="dashboard-user-name">Admin</div>
                            <div class="dashboard-user-role">Administrateur</div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../frontoffice/auth/profile.php"><i class="fas fa-user me-2"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="../../../controllers/logout.php" 
                               onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <aside class="dashboard-sidebar" id="sidebar">
            <div class="dashboard-logo">
                <a href="../home/index.php" class="text-white text-decoration-none">
                    <img src="../assets/img/logo.png" alt="logo" class="dashboard-logo-img">
                    <span class="dashboard-logo-text">Medsense Medical</span>
                </a>
            </div>
            
            <nav class="dashboard-nav">
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Tableau de Bord</div>
                    <a class="dashboard-nav-item" href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Gestion Médicale</div>
                    
                    <div class="dashboard-nav-item with-submenu">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-calendar-check"></i>
                                <span>Rendez-vous</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle"></i>
                        </div>
                        <div class="dashboard-submenu">
                            <a class="dashboard-submenu-item" href="admin-appointments.php">
                                <i class="fas fa-list"></i>
                                <span>Tous les rendez-vous</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-patient-appointments.php">
                                <i class="fas fa-user-injured"></i>
                                <span>Rendez-vous patients</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-new-appointment.php">
                                <i class="fas fa-plus-circle"></i>
                                <span>Nouveau rendez-vous</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-calendar.php">
                                <i class="fas fa-calendar"></i>
                                <span>Calendrier</span>
                            </a>
                        </div>
                    </div>
                    
                    <a class="dashboard-nav-item" href="admin-patients.php">
                        <i class="fas fa-user-injured"></i>
                        <span>Patients</span>
                    </a>
                    
                    <div class="dashboard-nav-item with-submenu">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-user-md"></i>
                                <span>Médecins</span>
                                <?php if (isset($pendingDoctors['count']) && $pendingDoctors['count'] > 0): ?>
                                    <span class="dashboard-badge"><?= $pendingDoctors['count'] ?></span>
                                <?php endif; ?>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle"></i>
                        </div>
                        <div class="dashboard-submenu">
                            <a class="dashboard-submenu-item" href="admin-doctors.php">
                                <i class="fas fa-list"></i>
                                <span>Tous les médecins</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-doctor-availability.php">
                                <i class="fas fa-clock"></i>
                                <span>Disponibilité</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-doctor-ratings.php">
                                <i class="fas fa-star"></i>
                                <span>Évaluations</span>
                            </a>
                        </div>
                    </div>
                    
                    <a class="dashboard-nav-item active" href="admin-users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                    
                    <a class="dashboard-nav-item" href="admin-complaints.php">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Réclamations</span>
                    </a>
                </div>
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Rapports</div>
                    
                    <div class="dashboard-nav-item with-submenu">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-chart-bar"></i>
                                <span>Rapports</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle"></i>
                        </div>
                        <div class="dashboard-submenu">
                            <a class="dashboard-submenu-item" href="admin-reports-statistics.php">
                                <i class="fas fa-chart-pie"></i>
                                <span>Statistiques</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-nav-section mt-auto">
                    <a class="dashboard-nav-item logout" href="../../../controllers/logout.php" 
                       onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </nav>
        </aside>

        <main class="dashboard-main">
            <?php if ($success_message): ?>
                <div class="dashboard-alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?= htmlspecialchars($success_message) ?></div>
                    <button type="button" class="dashboard-alert-close">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="dashboard-alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?= htmlspecialchars($error_message) ?></div>
                    <button type="button" class="dashboard-alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">
                        <i class="fas fa-user-plus me-2"></i>Ajouter un Nouvel Utilisateur
                    </h3>
                    <a href="admin-users.php" class="dashboard-btn btn-outline">
                        <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
                <div class="dashboard-card-body">
                    <div class="alert alert-warning security-alert mb-4">
                        <h6><i class="fas fa-shield-alt me-2"></i>Sécurité des données</h6>
                        <small>
                            Toutes les données saisies sont automatiquement protégées contre les injections HTML et les scripts malveillants.
                            Les caractères spéciaux sont échappés pour garantir la sécurité du système.
                        </small>
                    </div>

                    <form method="POST" id="createUserForm" novalidate>
                        <div class="dashboard-row">
                            <div class="dashboard-col dashboard-col-6">
                                <div class="dashboard-form-group">
                                    <label for="nom" class="dashboard-form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="dashboard-form-control" id="nom" name="nom" 
                                           value="<?= escape_data($_POST['nom'] ?? '') ?>" 
                                           pattern="[A-Za-zÀ-ÿ\s\-']{2,50}" 
                                           title="Le nom doit contenir entre 2 et 50 caractères alphabétiques" 
                                           required>
                                    <small class="dashboard-form-text">2 à 50 caractères alphabétiques uniquement</small>
                                    <div class="dashboard-invalid-feedback" id="nom-feedback"></div>
                                    <div class="dashboard-valid-feedback" id="nom-valid-feedback"></div>
                                </div>
                            </div>
                            <div class="dashboard-col dashboard-col-6">
                                <div class="dashboard-form-group">
                                    <label for="prenom" class="dashboard-form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="dashboard-form-control" id="prenom" name="prenom" 
                                           value="<?= escape_data($_POST['prenom'] ?? '') ?>" 
                                           pattern="[A-Za-zÀ-ÿ\s\-']{2,50}" 
                                           title="Le prénom doit contenir entre 2 et 50 caractères alphabétiques" 
                                           required>
                                    <small class="dashboard-form-text">2 à 50 caractères alphabétiques uniquement</small>
                                    <div class="dashboard-invalid-feedback" id="prenom-feedback"></div>
                                    <div class="dashboard-valid-feedback" id="prenom-valid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-form-group">
                            <label for="email" class="dashboard-form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="dashboard-form-control" id="email" name="email" 
                                   value="<?= escape_data($_POST['email'] ?? '') ?>" 
                                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" 
                                   title="Veuillez entrer une adresse email valide" 
                                   required>
                            <small class="dashboard-form-text">L'adresse email sera utilisée pour la connexion</small>
                            <div class="dashboard-invalid-feedback" id="email-feedback"></div>
                            <div class="dashboard-valid-feedback" id="email-valid-feedback"></div>
                        </div>

                        <div class="dashboard-row">
                            <div class="dashboard-col dashboard-col-6">
                                <div class="dashboard-form-group">
                                    <label for="dateNaissance" class="dashboard-form-label">Date de Naissance</label>
                                    <input type="date" class="dashboard-form-control" id="dateNaissance" name="dateNaissance" 
                                           value="<?= escape_data($_POST['dateNaissance'] ?? '') ?>"
                                           max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                                           title="L'utilisateur doit être majeur (18 ans minimum)">
                                    <small class="dashboard-form-text">Âge minimum : 18 ans</small>
                                </div>
                            </div>
                            <div class="dashboard-col dashboard-col-6">
                                <div class="dashboard-form-group">
                                    <label for="adresse" class="dashboard-form-label">Adresse</label>
                                    <input type="text" class="dashboard-form-control" id="adresse" name="adresse" 
                                           value="<?= escape_data($_POST['adresse'] ?? '') ?>"
                                           maxlength="255"
                                           title="Maximum 255 caractères">
                                    <small class="dashboard-form-text">Maximum 255 caractères</small>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-row">
                            <div class="dashboard-col dashboard-col-6">
                                <div class="dashboard-form-group">
                                    <label for="role" class="dashboard-form-label">Rôle <span class="text-danger">*</span></label>
                                    <select class="dashboard-form-control" id="role" name="role" required>
                                        <option value="">Sélectionner un rôle</option>
                                        <option value="user" <?= (($_POST['role'] ?? '') === 'user') ? 'selected' : '' ?>>Utilisateur</option>
                                        <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrateur</option>
                                        <option value="moderator" <?= (($_POST['role'] ?? '') === 'moderator') ? 'selected' : '' ?>>Modérateur</option>
                                    </select>
                                    <div class="dashboard-invalid-feedback" id="role-feedback"></div>
                                    <div class="dashboard-valid-feedback" id="role-valid-feedback"></div>
                                </div>
                            </div>
                            <div class="dashboard-col dashboard-col-6">
                                <div class="dashboard-form-group">
                                    <label for="statut" class="dashboard-form-label">Statut <span class="text-danger">*</span></label>
                                    <select class="dashboard-form-control" id="statut" name="statut" required>
                                        <option value="">Sélectionner un statut</option>
                                        <option value="actif" <?= (($_POST['statut'] ?? '') === 'actif') ? 'selected' : '' ?>>Actif</option>
                                        <option value="inactif" <?= (($_POST['statut'] ?? '') === 'inactif') ? 'selected' : '' ?>>Inactif</option>
                                    </select>
                                    <div class="dashboard-invalid-feedback" id="statut-feedback"></div>
                                    <div class="dashboard-valid-feedback" id="statut-valid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-card password-card mt-4">
                            <div class="dashboard-card-header">
                                <h4 class="dashboard-card-title">
                                    <i class="fas fa-lock me-2"></i>Mot de passe <span class="text-danger">*</span>
                                </h4>
                            </div>
                            <div class="dashboard-card-body">
                                <div class="dashboard-alert alert-warning">
                                    <i class="fas fa-info-circle"></i>
                                    <small>Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial</small>
                                </div>
                                <div class="dashboard-row">
                                    <div class="dashboard-col dashboard-col-6">
                                        <div class="dashboard-form-group">
                                            <label for="mot_de_passe" class="dashboard-form-label">Mot de passe</label>
                                            <input type="password" class="dashboard-form-control" id="mot_de_passe" name="mot_de_passe" 
                                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                                   title="Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial"
                                                   required>
                                            <small class="dashboard-form-text">8 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial</small>
                                            <div class="dashboard-invalid-feedback" id="password-feedback"></div>
                                            <div class="dashboard-valid-feedback" id="password-valid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="dashboard-col dashboard-col-6">
                                        <div class="dashboard-form-group">
                                            <label for="confirm_mot_de_passe" class="dashboard-form-label">Confirmer le mot de passe</label>
                                            <input type="password" class="dashboard-form-control" id="confirm_mot_de_passe" 
                                                   name="confirm_mot_de_passe" required>
                                            <div class="dashboard-invalid-feedback" id="confirm-password-feedback"></div>
                                            <div class="dashboard-valid-feedback" id="confirm-password-valid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-actions mt-4">
                            <button type="submit" class="dashboard-btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i> Créer l'utilisateur
                            </button>
                            <button type="reset" class="dashboard-btn btn-outline" id="resetBtn">Réinitialiser</button>
                            <a href="admin-users.php" class="dashboard-btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="dashboard-quick-actions">
                <a href="admin-dashboard.php" class="dashboard-quick-action">
                    <div class="dashboard-quick-action-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="dashboard-quick-action-text">Retour au Dashboard</div>
                </a>
                <a href="admin-users.php" class="dashboard-quick-action">
                    <div class="dashboard-quick-action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="dashboard-quick-action-text">Liste des Utilisateurs</div>
                </a>
                <a href="../frontoffice/home/index.php" class="dashboard-quick-action">
                    <div class="dashboard-quick-action-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="dashboard-quick-action-text">Retour au site</div>
                </a>
            </div>
        </main>
    </div>

  
    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    
    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        });

        document.querySelectorAll('.with-submenu').forEach(item => {
            const toggle = item.querySelector('.submenu-toggle');
            const submenu = item.querySelector('.dashboard-submenu');
            
            if (toggle && submenu) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    document.querySelectorAll('.with-submenu').forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.querySelector('.dashboard-submenu').style.display = 'none';
                            otherItem.querySelector('.submenu-toggle').classList.remove('open');
                        }
                    });
                    
                    if (submenu.style.display === 'block') {
                        submenu.style.display = 'none';
                        toggle.classList.remove('open');
                    } else {
                        submenu.style.display = 'block';
                        toggle.classList.add('open');
                    }
                });
            }
        });

        document.querySelectorAll('.dashboard-alert-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.dashboard-alert').style.display = 'none';
            });
        });
        setTimeout(() => {
            const alerts = document.querySelectorAll('.dashboard-alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);

        const form = document.getElementById('createUserForm');
        const patterns = {
            nom: /^[A-Za-zÀ-ÿ\s\-']{2,50}$/,
            prenom: /^[A-Za-zÀ-ÿ\s\-']{2,50}$/,
            email: /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i,
            mot_de_passe: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
        };

        function validateField(field) {
            const value = field.value.trim();
            const fieldName = field.name || field.id;
            const isRequired = field.hasAttribute('required');
            const invalidFeedback = document.getElementById(`${field.id}-feedback`);
            const validFeedback = document.getElementById(`${field.id}-valid-feedback`);
            field.classList.remove('is-valid', 'is-invalid');
            
            if (invalidFeedback) invalidFeedback.style.display = 'none';
            if (validFeedback) validFeedback.style.display = 'none';
            if (isRequired && !value) {
                field.classList.add('is-invalid');
                if (invalidFeedback) {
                    invalidFeedback.textContent = 'Ce champ est obligatoire';
                    invalidFeedback.style.display = 'block';
                }
                return false;
            }
            if (value && patterns[fieldName]) {
                if (!patterns[fieldName].test(value)) {
                    field.classList.add('is-invalid');
                    if (invalidFeedback) {
                        invalidFeedback.textContent = field.getAttribute('title');
                        invalidFeedback.style.display = 'block';
                    }
                    return false;
                }
            }
            
         
            if (field.id === 'confirm_mot_de_passe' && value) {
                const password = document.getElementById('mot_de_passe').value;
                if (value !== password) {
                    field.classList.add('is-invalid');
                    if (invalidFeedback) {
                        invalidFeedback.textContent = 'Les mots de passe ne correspondent pas';
                        invalidFeedback.style.display = 'block';
                    }
                    return false;
                }
            }
            
            
            if (value) {
                field.classList.add('is-valid');
                if (validFeedback) {
                    validFeedback.textContent = '✓ Champ valide';
                    validFeedback.style.display = 'block';
                }
                return true;
            }
            
            return true; 
        }

        
        form.querySelectorAll('input, select').forEach(field => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) {
                    field.classList.remove('is-invalid');
                    const invalidFeedback = document.getElementById(`${field.id}-feedback`);
                    if (invalidFeedback) invalidFeedback.style.display = 'none';
                }
            });
        });

       
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            form.querySelectorAll('input[required], select[required]').forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
          
            const password = document.getElementById('mot_de_passe');
            const confirmPassword = document.getElementById('confirm_mot_de_passe');
            
            if (password.value && confirmPassword.value && password.value !== confirmPassword.value) {
                isValid = false;
                confirmPassword.classList.add('is-invalid');
                const feedback = document.getElementById('confirm-password-feedback');
                if (feedback) {
                    feedback.textContent = 'Les mots de passe ne correspondent pas';
                    feedback.style.display = 'block';
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Veuillez corriger les erreurs dans le formulaire avant de soumettre.');
            }
        });

       
        document.getElementById('resetBtn').addEventListener('click', function() {
            form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
                field.classList.remove('is-valid', 'is-invalid');
            });
            document.querySelectorAll('.dashboard-invalid-feedback, .dashboard-valid-feedback').forEach(feedback => {
                feedback.style.display = 'none';
            });
        });

  
        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>