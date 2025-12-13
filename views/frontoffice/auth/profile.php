<?php
session_start();
include_once '../../../controllers/AuthController.php'; 
include_once '../../../controllers/PasswordController.php';
include_once '../../../controllers/ProfileController.php';

$authController = new AuthController();
$passwordController = new PasswordController();
$profileController = new ProfileController();

if (!$authController->isLoggedIn()) {
    header('Location: sign-in.php');
    exit;
}

$user = $authController->getCurrentUser();
$isAdmin = $user && $user->estAdmin();
$isMedecin = $user && $user->estMedecin();

$profile_error = null;
$profile_success = null;
$photo_error = null;
$photo_success = null;
$password_error = null;
$password_success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $result = $profileController->updateProfile($user->getId(), $_POST);
        
        if ($result['success']) {
            $profile_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            $user = $authController->getCurrentUser();
        } else {
            $profile_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
        }
    }
    
    if (isset($_POST['update_photo']) && isset($_FILES['photo_profil'])) {
        $result = $profileController->updateProfilePhoto($user->getId(), $_FILES['photo_profil']);
        
        if ($result['success']) {
            $photo_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            $user = $authController->getCurrentUser();
        } else {
            $photo_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
        }
    }
   
    if (isset($_POST['delete_photo'])) {
        $result = $profileController->deleteProfilePhoto($user->getId());
        
        if ($result['success']) {
            $photo_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            $user = $authController->getCurrentUser();
        } else {
            $photo_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $password_error = "Veuillez remplir tous les champs";
        } elseif ($new_password !== $confirm_password) {
            $password_error = "Les nouveaux mots de passe ne correspondent pas";
        } elseif (strlen($new_password) < 6) {
            $password_error = "Le nouveau mot de passe doit contenir au moins 6 caractères";
        } else {
            $result = $passwordController->changePassword($user->getId(), $current_password, $new_password);
            
            if ($result['success']) {
                $password_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
                $_POST = [];
            } else {
                $password_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

function getProfilePhotoUrl($user) {
    return $user->getPhotoProfilUrl();
}
$photo_url = getProfilePhotoUrl($user);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Medsense Medical</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
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
        font-weight: 600;
    }

    .dashboard-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
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

    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .dashboard-stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .dashboard-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .dashboard-stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .dashboard-stat-icon.primary {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .dashboard-stat-icon.success {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }

    .dashboard-stat-icon.info {
        background: rgba(6, 182, 212, 0.1);
        color: #06b6d4;
    }

    .dashboard-stat-icon.warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .dashboard-stat-icon.danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .dashboard-stat-value {
        font-size: 1.875rem;
        font-weight: bold;
        color: #1e293b;
        line-height: 1;
        margin-bottom: 4px;
    }

    .dashboard-stat-label {
        color: #64748b;
        font-size: 0.875rem;
    }

    .dashboard-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        overflow: hidden;
        animation: slideUp 0.6s ease;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
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

    .dashboard-card-badge {
        background: #3b82f6;
        color: white;
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .dashboard-card-badge.danger {
        background: #ef4444;
    }

    .dashboard-card-badge.success {
        background: #22c55e;
    }

    .dashboard-card-badge.warning {
        background: #f59e0b;
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

    .profile-section {
        padding: 40px 0;
        border-bottom: 1px solid #e2e8f0;
        text-align: center;
    }

    .profile-avatar-container {
        position: relative;
        display: inline-block;
        margin-bottom: 25px;
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        border: 5px solid white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        position: relative;
        margin: 0 auto;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-initials {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        font-weight: 800;
        color: white;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    }

    .avatar-actions {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        color: #3b82f6;
        transition: all 0.3s;
        z-index: 2;
    }

    .avatar-actions:hover {
        background: #3b82f6;
        color: white;
        transform: scale(1.1) rotate(15deg);
    }

    .profile-info h3 {
        font-size: 28px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .profile-info p {
        color: #64748b;
        font-size: 16px;
        margin-bottom: 20px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 14px;
    }

    .status-badge.actif {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }

    .status-badge.inactif {
        background: rgba(148, 163, 184, 0.1);
        color: #64748b;
        border: 1px solid rgba(148, 163, 184, 0.2);
    }

    .status-badge.en_attente {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .nav-tabs {
        border: none;
        margin: 20px 0;
        display: flex;
        gap: 5px;
        background: #f8f9fa;
        padding: 10px;
        border-radius: 12px;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 15px 25px;
        border-radius: 8px;
        background: transparent;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }

    .nav-tabs .nav-link:hover {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .nav-tabs .nav-link.active {
        background: #3b82f6;
        color: white;
        box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
    }

    .tab-content {
        padding: 20px 0;
    }

    .form-label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label i {
        color: #3b82f6;
        font-size: 16px;
    }

    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .password-input-container {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .info-card {
        background: #f8fafc;
        border-radius: 8px;
        padding: 20px;
        border-left: 4px solid #3b82f6;
    }

    .info-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .info-value {
        font-size: 16px;
        color: #1e293b;
        font-weight: 500;
    }

    .quick-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #e2e8f0;
    }

    .dashboard-btn {
        padding: 12px 24px;
        border-radius: 8px;
        border: none;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        font-size: 14px;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .btn-success {
        background: #22c55e;
        color: white;
    }

    .btn-success:hover {
        background: #16a34a;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
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

    .admin-section {
        background: #fef2f2;
        border-radius: 8px;
        padding: 20px;
        margin-top: 30px;
        border-left: 4px solid #ef4444;
    }

    .admin-section h5 {
        color: #ef4444;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
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
        
        .dashboard-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
        }
        
        .avatar-initials {
            font-size: 2.5rem;
        }
        
        .nav-tabs {
            flex-direction: column;
        }
    }

    @media (max-width: 640px) {
        .dashboard-stats {
            grid-template-columns: 1fr;
        }
        
        .dashboard-header {
            padding: 0 16px;
        }
        
        .dashboard-main {
            padding: 16px;
        }
        
        .dashboard-card-body {
            padding: 16px;
        }
        
        .quick-actions {
            flex-direction: column;
        }
        
        .dashboard-btn {
            width: 100%;
        }
    }

    .is-valid {
        border-color: #22c55e !important;
    }

    .is-invalid {
        border-color: #ef4444 !important;
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }

    .error-message {
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
    }
    </style>
</head>
<body class="dashboard-page">

    <div class="dashboard-container">
       
        <aside class="dashboard-sidebar" id="sidebar">
            <div class="dashboard-logo">
                <a href="../home/index.php" class="text-white text-decoration-none">
                    <img src="../assets/img/logo.png" alt="logo" class="dashboard-logo-img">
                    <span class="dashboard-logo-text">Medsense Medical</span>
                </a>
            </div>
            
            <nav class="dashboard-nav">
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Navigation</div>
                    <a class="dashboard-nav-item" href="../home/index.php">
                        <i class="fas fa-home"></i>
                        <span>Accueil</span>
                    </a>
                    <a class="dashboard-nav-item active" href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Mon Profil</span>
                    </a>
                </div>
                
                <?php if ($isAdmin): ?>
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Administration</div>
                    <a class="dashboard-nav-item" href="../../backoffice/admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
                    </a>
                    <a class="dashboard-nav-item" href="../../backoffice/admin-users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </div>
                <?php elseif ($isMedecin): ?>
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Médecin</div>
                    <a class="dashboard-nav-item" href="../appointments/">
                        <i class="fas fa-calendar-check"></i>
                        <span>Mes Rendez-vous</span>
                    </a>
                    <a class="dashboard-nav-item" href="../consultations/">
                        <i class="fas fa-stethoscope"></i>
                        <span>Consultations</span>
                    </a>
                </div>
                <?php else: ?>
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Patient</div>
                    <a class="dashboard-nav-item" href="../appointments/">
                        <i class="fas fa-calendar-check"></i>
                        <span>Mes Rendez-vous</span>
                    </a>
                    <a class="dashboard-nav-item" href="../doctors/">
                        <i class="fas fa-user-md"></i>
                        <span>Trouver un Médecin</span>
                    </a>
                </div>
                <?php endif; ?>
                
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
            
            <?php if ($profile_success): ?>
                <div class="dashboard-alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo $profile_success; ?></div>
                    <button type="button" class="dashboard-alert-close">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if ($profile_error): ?>
                <div class="dashboard-alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo $profile_error; ?></div>
                    <button type="button" class="dashboard-alert-close">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if ($photo_success): ?>
                <div class="dashboard-alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo $photo_success; ?></div>
                    <button type="button" class="dashboard-alert-close">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if ($photo_error): ?>
                <div class="dashboard-alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo $photo_error; ?></div>
                    <button type="button" class="dashboard-alert-close">&times;</button>
                </div>
            <?php endif; ?>

            
            <div class="dashboard-stats">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon primary">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-value"><?php echo $user->estActif() ? 'Actif' : 'Inactif'; ?></div>
                        <div class="dashboard-stat-label">Statut</div>
                    </div>
                </div>
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon <?php echo $isAdmin ? 'danger' : ($isMedecin ? 'info' : 'success'); ?>">
                        <i class="fas <?php echo $isAdmin ? 'fa-user-shield' : ($isMedecin ? 'fa-user-md' : 'fa-user-injured'); ?>"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-value"><?php echo ucfirst($user->getRole()); ?></div>
                        <div class="dashboard-stat-label">Rôle</div>
                    </div>
                </div>
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon warning">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-value">
                            <?php 
                            $date = new DateTime($user->getDateInscription());
                            echo $date->format('d/m/Y');
                            ?>
                        </div>
                        <div class="dashboard-stat-label">Membre depuis</div>
                    </div>
                </div>
                <?php if ($user->getDateNaissance()): ?>
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon success">
                        <i class="fas fa-birthday-cake"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-value"><?php echo $user->getAge(); ?> ans</div>
                        <div class="dashboard-stat-label">Âge</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">
                        <i class="fas fa-user-cog me-2"></i>Gestion du Profil
                    </h3>
                    <span class="dashboard-card-badge <?php echo $isAdmin ? 'danger' : ($isMedecin ? 'info' : 'success'); ?>">
                        <i class="fas fa-<?php echo $isAdmin ? 'crown' : ($isMedecin ? 'user-md' : 'user'); ?> me-1"></i>
                        <?php echo ucfirst($user->getRole()); ?>
                    </span>
                </div>
                
                <div class="dashboard-card-body">
                   
                    <div class="profile-section">
                        <div class="profile-avatar-container">
                            <div class="profile-avatar">
                                <?php if ($photo_url && $photo_url != '/assets/images/default-avatar.png'): ?>
                                    <img src="<?php echo $photo_url; ?>" alt="Photo de profil" id="currentPhoto">
                                <?php else: ?>
                                    <div class="avatar-initials" id="currentInitials">
                                        <?php echo strtoupper(substr($user->getPrenom(), 0, 1) . substr($user->getNom(), 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="avatar-actions" data-bs-toggle="dropdown">
                                    <i class="fas fa-camera"></i>
                                </div>
                                <ul class="dropdown-menu">
                                    <li>
                                        <form method="POST" action="" enctype="multipart/form-data" id="photoUploadForm">
                                            <input type="file" name="photo_profil" id="photoInput" accept="image/*">
                                            <input type="hidden" name="update_photo" value="1">
                                        </form>
                                        <a class="dropdown-item" href="#" onclick="document.getElementById('photoInput').click()">
                                            <i class="fas fa-upload me-2"></i>Changer la photo
                                        </a>
                                    </li>
                                    <?php if ($photo_url && $photo_url != '/assets/images/default-avatar.png'): ?>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" onclick="deleteProfilePhoto()">
                                            <i class="fas fa-trash me-2"></i>Supprimer la photo
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?></h3>
                            <p><?php echo htmlspecialchars($user->getEmail()); ?></p>
                            <span class="status-badge <?php echo $user->estActif() ? 'actif' : 'inactif'; ?>">
                                <i class="fas fa-<?php echo $user->estActif() ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                <?php echo ucfirst($user->getStatut()); ?>
                            </span>
                        </div>
                    </div>

                    
                    <?php if ($password_success): ?>
                        <div class="dashboard-alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <div><?php echo $password_success; ?></div>
                            <button type="button" class="dashboard-alert-close">&times;</button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($password_error): ?>
                        <div class="dashboard-alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <div><?php echo $password_error; ?></div>
                            <button type="button" class="dashboard-alert-close">&times;</button>
                        </div>
                    <?php endif; ?>

                    
                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab">
                                <i class="fas fa-edit me-1"></i> Modifier le profil
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                <i class="fas fa-lock me-1"></i> Mot de passe
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                                <i class="fas fa-info-circle me-1"></i> Informations
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="profileTabsContent">
                        
                        <div class="tab-pane fade show active" id="edit" role="tabpanel">
                            <form method="POST" action="" id="profileForm" onsubmit="return validateProfileForm()">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="prenom" class="form-label"><i class="fas fa-user"></i>Prénom *</label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" 
                                               value="<?php echo htmlspecialchars($user->getPrenom()); ?>" 
                                               required>
                                        <div class="error-message" id="prenom-error"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nom" class="form-label"><i class="fas fa-user"></i>Nom *</label>
                                        <input type="text" class="form-control" id="nom" name="nom" 
                                               value="<?php echo htmlspecialchars($user->getNom()); ?>" 
                                               required>
                                        <div class="error-message" id="nom-error"></div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="email" class="form-label"><i class="fas fa-envelope"></i>Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user->getEmail()); ?>" 
                                               required>
                                        <div class="error-message" id="email-error"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dateNaissance" class="form-label"><i class="fas fa-calendar"></i>Date de naissance</label>
                                        <input type="date" class="form-control" id="dateNaissance" name="dateNaissance" 
                                               value="<?php echo $user->getDateNaissance() ? htmlspecialchars($user->getDateNaissance()) : ''; ?>">
                                        <div class="error-message" id="dateNaissance-error"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="adresse" class="form-label"><i class="fas fa-map-marker-alt"></i>Adresse</label>
                                        <input type="text" class="form-control" id="adresse" name="adresse" 
                                               value="<?php echo $user->getAdresse() ? htmlspecialchars($user->getAdresse()) : ''; ?>">
                                        <div class="error-message" id="adresse-error"></div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="dashboard-btn btn-success">
                                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="password" role="tabpanel">
    <form method="POST" action="" id="passwordForm" onsubmit="return validatePasswordForm()">
        <input type="hidden" name="change_password" value="1">
        
        <div class="row g-3">
            <div class="col-md-12">
                <label for="current_password" class="form-label">
                    <i class="fas fa-key"></i> Mot de passe actuel
                </label>
                <div class="password-input-container">
                    <input type="password" class="form-control" 
                           id="current_password" 
                           name="current_password">
                    <button type="button" class="password-toggle" 
                            onclick="togglePassword('current_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="error-message" id="current_password-error"></div>
            </div>
            
            <div class="col-md-6">
                <label for="new_password" class="form-label">
                    <i class="fas fa-key"></i> Nouveau mot de passe
                </label>
                <div class="password-input-container">
                    <input type="password" class="form-control" 
                           id="new_password" 
                           name="new_password">
                    <button type="button" class="password-toggle" 
                            onclick="togglePassword('new_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="error-message" id="new_password-error"></div>
            </div>
            
            <div class="col-md-6">
                <label for="confirm_password" class="form-label">
                    <i class="fas fa-key"></i> Confirmer le mot de passe
                </label>
                <div class="password-input-container">
                    <input type="password" class="form-control" 
                           id="confirm_password" 
                           name="confirm_password">
                    <button type="button" class="password-toggle" 
                            onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="error-message" id="confirm_password-error"></div>
            </div>
            
            <div class="col-12 mt-3">
                <button type="submit" class="dashboard-btn btn-primary">
                    <i class="fas fa-key me-2"></i> Changer le mot de passe
                </button>
            </div>
        </div>
    </form>
</div>
                     
                       
                        <div class="tab-pane fade" id="info" role="tabpanel">
                            <div class="info-grid">
                                <div class="info-card">
                                    <div class="info-label">Nom complet</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?></div>
                                </div>
                                <div class="info-card">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user->getEmail()); ?></div>
                                </div>
                                <div class="info-card">
                                    <div class="info-label">Rôle</div>
                                    <div class="info-value">
                                        <span class="dashboard-card-badge <?php echo $isAdmin ? 'danger' : ($isMedecin ? 'info' : 'success'); ?>">
                                            <?php echo ucfirst($user->getRole()); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-card">
                                    <div class="info-label">Statut</div>
                                    <div class="info-value">
                                        <span class="status-badge <?php echo $user->estActif() ? 'actif' : 'inactif'; ?>">
                                            <?php echo ucfirst($user->getStatut()); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-card">
                                    <div class="info-label">Date d'inscription</div>
                                    <div class="info-value">
                                        <?php 
                                        $date = new DateTime($user->getDateInscription());
                                        echo $date->format('d/m/Y à H:i');
                                        ?>
                                    </div>
                                </div>
                                <?php if ($user->getDateNaissance()): ?>
                                <div class="info-card">
                                    <div class="info-label">Date de naissance</div>
                                    <div class="info-value">
                                        <?php 
                                        $dateNaissance = new DateTime($user->getDateNaissance());
                                        echo $dateNaissance->format('d/m/Y');
                                        ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($user->getAdresse()): ?>
                            <div class="info-card mt-3">
                                <div class="info-label">Adresse</div>
                                <div class="info-value"><?php echo htmlspecialchars($user->getAdresse()); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($isAdmin): ?>
                            <div class="admin-section">
                                <h5><i class="fas fa-shield-alt"></i>Zone Administrateur</h5>
                                <p>Vous avez accès aux fonctionnalités d'administration du système.</p>
                                <div class="quick-actions">
                                    <a href="../../backoffice/admin-dashboard.php" class="dashboard-btn btn-primary">
                                        <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                                    </a>
                                    <a href="../../backoffice/admin-users.php" class="dashboard-btn btn-outline">
                                        <i class="fas fa-users me-2"></i>Gestion utilisateurs
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="quick-actions">
                        <a href="../home/index.php" class="dashboard-btn btn-outline">
                            <i class="fas fa-home me-2"></i> Accueil
                        </a>
                        
                        <?php if ($isAdmin): ?>
                            <a href="../../backoffice/admin-dashboard.php" class="dashboard-btn btn-primary">
                                <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord Admin
                            </a>
                        <?php elseif ($isMedecin): ?>
                            <a href="../appointments/" class="dashboard-btn btn-primary">
                                <i class="fas fa-calendar me-2"></i> Mes rendez-vous
                            </a>
                        <?php else: ?>
                            <a href="../appointments/" class="dashboard-btn btn-primary">
                                <i class="fas fa-calendar me-2"></i> Mes rendez-vous
                            </a>
                        <?php endif; ?>
                        
                        <a href="../../../controllers/logout.php" 
                           class="dashboard-btn btn-danger" 
                           onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </a>
                    </div>
                </div>
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

        document.getElementById('photoInput')?.addEventListener('change', function() {
            validatePhoto(this);
        });

        function validatePhoto(input) {
            const file = input.files[0];
            if (file) {
                
                if (file.size > 2 * 1024 * 1024) {
                    alert('La taille de la photo ne doit pas dépasser 2MB');
                    input.value = '';
                    return false;
                }
                
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Type de fichier non autorisé. Formats acceptés: JPEG, PNG, GIF, WebP');
                    input.value = '';
                    return false;
                }
                
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const currentPhoto = document.getElementById('currentPhoto');
                    const currentInitials = document.getElementById('currentInitials');
                    
                    if (currentPhoto) {
                        currentPhoto.src = e.target.result;
                    } else if (currentInitials) {
                        currentInitials.style.display = 'none';
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Photo de profil';
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        currentInitials.parentNode.appendChild(img);
                        currentInitials.parentNode.removeChild(currentInitials);
                    }
                };
                reader.readAsDataURL(file);
                
                
                document.getElementById('photoUploadForm').submit();
            }
            return true;
        }

        function deleteProfilePhoto() {
            if (confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil ?')) {
              
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_photo';
                input.value = '1';
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        
        function validateProfileForm() {
            let isValid = true;
            
            clearErrors();
            
            
            const prenom = document.getElementById('prenom');
            if (!prenom.value.trim()) {
                showError('prenom', 'Le prénom est requis');
                isValid = false;
            } else if (prenom.value.trim().length < 2) {
                showError('prenom', 'Le prénom doit contenir au moins 2 caractères');
                isValid = false;
            } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(prenom.value.trim())) {
                showError('prenom', 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes');
                isValid = false;
            }
            
           
            const nom = document.getElementById('nom');
            if (!nom.value.trim()) {
                showError('nom', 'Le nom est requis');
                isValid = false;
            } else if (nom.value.trim().length < 2) {
                showError('nom', 'Le nom doit contenir au moins 2 caractères');
                isValid = false;
            } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(nom.value.trim())) {
                showError('nom', 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes');
                isValid = false;
            }
            
        
            const email = document.getElementById('email');
            if (!email.value.trim()) {
                showError('email', 'L\'email est requis');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                showError('email', 'Format d\'email invalide');
                isValid = false;
            }
           
            const dateNaissance = document.getElementById('dateNaissance');
            if (dateNaissance.value) {
                const selectedDate = new Date(dateNaissance.value);
                const today = new Date();
                if (selectedDate > today) {
                    showError('dateNaissance', 'La date de naissance ne peut pas être dans le futur');
                    isValid = false;
                }
            }
            
          
            const adresse = document.getElementById('adresse');
            if (adresse.value && adresse.value.length > 500) {
                showError('adresse', 'L\'adresse ne peut pas dépasser 500 caractères');
                isValid = false;
            }
            
            return isValid;
        }

        function validatePasswordForm() {
            let isValid = true;
            
            
            clearErrors();
            
          
            const currentPassword = document.getElementById('current_password');
            if (!currentPassword.value) {
                showError('current_password', 'Le mot de passe actuel est requis');
                isValid = false;
            }
            
          
            const newPassword = document.getElementById('new_password');
            if (!newPassword.value) {
                showError('new_password', 'Le nouveau mot de passe est requis');
                isValid = false;
            } else if (newPassword.value.length < 6) {
                showError('new_password', 'Le mot de passe doit contenir au moins 6 caractères');
                isValid = false;
            } else if (!/(?=.*[A-Za-z])(?=.*\d)/.test(newPassword.value)) {
                showError('new_password', 'Le mot de passe doit contenir au moins une lettre et un chiffre');
                isValid = false;
            }
            
           
            const confirmPassword = document.getElementById('confirm_password');
            if (!confirmPassword.value) {
                showError('confirm_password', 'La confirmation du mot de passe est requise');
                isValid = false;
            } else if (newPassword.value !== confirmPassword.value) {
                showError('confirm_password', 'Les mots de passe ne correspondent pas');
                isValid = false;
            }
            
            return isValid;
        }

    
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorElement = document.getElementById(fieldId + '-error');
            
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }
        }

        function clearErrors() {
           
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
                el.style.display = 'none';
            });
            
            
            document.querySelectorAll('.form-control').forEach(el => {
                el.classList.remove('is-valid', 'is-invalid');
            });
        }

      
        document.querySelectorAll('#profileForm .form-control').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
               
                const errorElement = document.getElementById(this.id + '-error');
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                }
                this.classList.remove('is-invalid');
            });
        });

        function validateField(field) {
            const value = field.value.trim();
            const fieldId = field.id;
            
            switch(fieldId) {
                case 'prenom':
                    if (!value) {
                        showError(fieldId, 'Le prénom est requis');
                    } else if (value.length < 2) {
                        showError(fieldId, 'Le prénom doit contenir au moins 2 caractères');
                    } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(value)) {
                        showError(fieldId, 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes');
                    } else {
                        field.classList.remove('is-invalid');
                        field.classList.add('is-valid');
                    }
                    break;
                    
                case 'nom':
                    if (!value) {
                        showError(fieldId, 'Le nom est requis');
                    } else if (value.length < 2) {
                        showError(fieldId, 'Le nom doit contenir au moins 2 caractères');
                    } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(value)) {
                        showError(fieldId, 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes');
                    } else {
                        field.classList.remove('is-invalid');
                        field.classList.add('is-valid');
                    }
                    break;
                    
                case 'email':
                    if (!value) {
                        showError(fieldId, 'L\'email est requis');
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        showError(fieldId, 'Format d\'email invalide');
                    } else {
                        field.classList.remove('is-invalid');
                        field.classList.add('is-valid');
                    }
                    break;
                    
                case 'dateNaissance':
                    if (value) {
                        const selectedDate = new Date(value);
                        const today = new Date();
                        if (selectedDate > today) {
                            showError(fieldId, 'La date de naissance ne peut pas être dans le futur');
                        } else {
                            field.classList.remove('is-invalid');
                            field.classList.add('is-valid');
                        }
                    }
                    break;
                    
                case 'adresse':
                    if (value && value.length > 500) {
                        showError(fieldId, 'L\'adresse ne peut pas dépasser 500 caractères');
                    } else if (value) {
                        field.classList.remove('is-invalid');
                        field.classList.add('is-valid');
                    }
                    break;
            }
        }

        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
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

        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 300 + (index * 100));
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