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
            // Refresh user data
            $user = $authController->getCurrentUser();
        } else {
            $profile_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
        }
    }
    
    if (isset($_POST['update_photo']) && isset($_FILES['photo_profil'])) {
        $result = $profileController->updateProfilePhoto($user->getId(), $_FILES['photo_profil']);
        
        if ($result['success']) {
            $photo_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            // Refresh user data
            $user = $authController->getCurrentUser();
        } else {
            $photo_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
        }
    }
   
    if (isset($_POST['delete_photo'])) {
        $result = $profileController->deleteProfilePhoto($user->getId());
        
        if ($result['success']) {
            $photo_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            // Refresh user data
            $user = $authController->getCurrentUser();
        } else {
            $photo_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
        }
    }
    
    // Changement de mot de passe
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
                // Réinitialiser le formulaire
                $_POST = [];
            } else {
                $password_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

function getProfilePhotoUrl($user) {
    global $profileController;
    if ($user->getPhotoProfil()) {
        $url = $profileController->getProfilePhotoUrl($user->getPhotoProfil());
        if ($url) {
            return $url;
        }
    }
    return null;
}
$photo_url = getProfilePhotoUrl($user);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Medsense Medical</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    
    <!-- main css -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    
    <style>
        :root {
            --medical-blue: #1a73e8;
            --medical-light-blue: #e8f0fe;
            --medical-dark-blue: #0d47a1;
            --medical-teal: #007c91;
            --medical-cyan: #00bcd4;
            --medical-light-cyan: #b2ebf2;
            --secondary-color: #5f6368;
            --success-color: #4caf50;
            --info-color: #2196f3;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --light-color: #f8f9fa;
            --dark-color: #202124;
            --sidebar-width: 280px;
            --card-radius: 12px;
            --shadow-light: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 6px 15px rgba(0, 0, 0, 0.07);
            --shadow-strong: 0 10px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--medical-blue) 0%, var(--medical-dark-blue) 100%);
            color: white;
            padding: 0;
            z-index: 1000;
            box-shadow: var(--shadow-medium);
            transition: var(--transition);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 14px 20px;
            border-left: 3px solid transparent;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .sidebar-menu .nav-link:hover, 
        .sidebar-menu .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid white;
        }
        
        .sidebar-menu .nav-link i {
            width: 24px;
            margin-right: 12px;
            font-size: 18px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            min-height: 100vh;
            background-color: #f8f9fa;
            transition: var(--transition);
        }
        
        .top-bar {
            background-color: white;
            border-radius: var(--card-radius);
            padding: 18px 25px;
            box-shadow: var(--shadow-light);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid var(--medical-blue);
        }
        
        .card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-light);
            margin-bottom: 25px;
            transition: var(--transition);
            overflow: hidden;
        }
        
        .card:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-teal) 100%);
            color: white;
            border-bottom: none;
            padding: 18px 25px;
            border-radius: var(--card-radius) var(--card-radius) 0 0 !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 2.5rem;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
        }
        
        .profile-avatar-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1.5rem;
        }
        
        .profile-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: var(--shadow-strong);
            background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-cyan) 100%);
            position: relative;
            z-index: 2;
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
            font-size: 3rem;
            font-weight: bold;
            color: white;
        }
        
        .avatar-ring {
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-cyan), var(--medical-teal));
            z-index: 1;
            animation: rotate 10s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .photo-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            padding: 12px;
            display: flex;
            justify-content: center;
            gap: 10px;
            opacity: 0;
            transition: var(--transition);
            z-index: 3;
            border-radius: 0 0 50% 50%;
        }
        
        .profile-avatar-container:hover .photo-actions {
            opacity: 1;
        }
        
        .photo-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .photo-upload-btn {
            background: var(--medical-cyan);
            color: white;
        }
        
        .photo-upload-btn:hover {
            background: #00a5bb;
            transform: scale(1.1);
        }
        
        .photo-delete-btn {
            background: var(--danger-color);
            color: white;
        }
        
        .photo-delete-btn:hover {
            background: #e31b1b;
            transform: scale(1.1);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--medical-dark-blue);
            margin-bottom: 0.75rem;
        }
        
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.85rem;
            transition: var(--transition);
            font-size: 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 0.2rem rgba(26, 115, 232, 0.25);
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--medical-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--medical-dark-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background: #3d8b40;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }
        
        .btn-info {
            background: var(--medical-cyan);
            color: white;
        }
        
        .btn-info:hover {
            background: #00a5bb;
            transform: translateY(-2px);
        }
        
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            gap: 5px;
        }
        
        .role-badge.user {
            background: rgba(26, 115, 232, 0.1);
            color: var(--medical-blue);
            border: 1px solid rgba(26, 115, 232, 0.3);
        }
        
        .role-badge.admin {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            gap: 5px;
        }
        
        .status-badge.actif {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .status-badge.inactif {
            background: rgba(108, 117, 125, 0.1);
            color: var(--secondary-color);
            border: 1px solid rgba(108, 117, 125, 0.3);
        }
        
        .info-group {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background: var(--medical-light-blue);
            border-radius: 10px;
            border-left: 4px solid var(--medical-blue);
            transition: var(--transition);
        }
        
        .info-group:hover {
            background: white;
            box-shadow: var(--shadow-light);
        }
        
        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--medical-teal);
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-value {
            font-size: 16px;
            color: #2d3748;
            font-weight: 500;
        }
        
        .admin-section {
            background: #fff5f5;
            border-left: 4px solid var(--danger-color);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #f0fff4;
            color: #2d7740;
            border-left-color: var(--success-color);
        }
        
        .alert-danger {
            background: #fff5f5;
            color: #c53030;
            border-left-color: var(--danger-color);
        }
        
        .alert-info {
            background: var(--medical-light-blue);
            color: var(--medical-dark-blue);
            border-left-color: var(--medical-blue);
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--medical-dark-blue);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .dashboard-actions {
            margin-top: 2.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 20px;
            box-shadow: var(--shadow-light);
            text-align: center;
            transition: var(--transition);
            border-top: 4px solid var(--medical-blue);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
        }
        
        .stat-icon.primary {
            background: rgba(26, 115, 232, 0.1);
            color: var(--medical-blue);
        }
        
        .stat-icon.success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }
        
        .stat-icon.info {
            background: rgba(0, 188, 212, 0.1);
            color: var(--medical-cyan);
        }
        
        .stat-icon.warning {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--medical-dark-blue);
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .tab-container {
            margin-top: 30px;
        }
        
        .nav-tabs {
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 25px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            padding: 12px 24px;
            color: var(--secondary-color);
            font-weight: 500;
            border-radius: 8px 8px 0 0;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--medical-blue);
            background: rgba(26, 115, 232, 0.05);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--medical-blue);
            background: white;
            border-bottom: 3px solid var(--medical-blue);
        }
        
        .tab-content {
            background: white;
            border-radius: 0 0 var(--card-radius) var(--card-radius);
            padding: 25px;
            box-shadow: var(--shadow-light);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .profile-completion {
            margin-bottom: 30px;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
            background: #e9ecef;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-bar {
            background: linear-gradient(to right, var(--medical-blue), var(--medical-cyan));
            transition: width 0.6s ease;
        }
        
        .completion-text {
            font-size: 14px;
            color: var(--secondary-color);
            text-align: center;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                overflow: hidden;
            }
            
            .sidebar .nav-link span {
                display: none;
            }
            
            .sidebar .sidebar-header h3 {
                display: none;
            }
            
            .sidebar .nav-link i {
                margin-right: 0;
                font-size: 20px;
            }
            
            .main-content {
                margin-left: 80px;
                padding: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
                width: 280px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .dashboard-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .mobile-menu-btn {
                display: block !important;
            }
        }
        
        .mobile-menu-btn {
            display: none;
            background: var(--medical-blue);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            font-size: 18px;
            cursor: pointer;
        }
        
        .form-text {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        .text-danger {
            color: var(--danger-color) !important;
        }
        
        .photo-form {
            display: none;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--secondary-color);
            cursor: pointer;
        }
        
        .password-input-container {
            position: relative;
        }
        
        .medical-icon {
            color: var(--medical-blue);
        }
    </style>
</head>
<body>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a class="navbar-brand logo_h" href="../home/index.php">
                <img src="../../assets/img/logo.png" alt="logo" style="height: 200px;">
            </a>
        </div>
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="../home/index.php">
                        <i class="fas fa-home"></i>
                        <span>Accueil</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Mon Profil</span>
                    </a>
                </li>
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="../../backoffice/admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../backoffice/admin-users.php">
                        <i class="fas fa-users"></i>
                        <span>Gestion Utilisateurs</span>
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="../appointments/">
                        <i class="fas fa-calendar-check"></i>
                        <span>Mes Rendez-vous</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item mt-3">
                    <small class="text-uppercase text-muted ms-3">Compte</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../../controllers/logout.php" 
                       onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h4 class="mb-0"><i class="fas fa-user-md medical-icon me-2"></i>Mon Profil</h4>
                <p class="text-muted mb-0">Gérez vos informations personnelles et votre compte</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 45px; height: 45px;">
                            <?php if ($photo_url): ?>
                                <img src="<?php echo $photo_url; ?>" alt="Photo de profil" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <span style="font-size: 16px;">
                                    <?php echo strtoupper(substr($user->getPrenom(), 0, 1) . substr($user->getNom(), 0, 1)); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="ms-2"><?php echo htmlspecialchars($user->getPrenom()); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="../appointments/"><i class="fas fa-calendar me-2"></i> Mes rendez-vous</a></li>
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
        </div>

        <!-- Content -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Stats Overview -->
                    <div class="stats-container">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-value"><?php echo $user->estActif() ? 'Actif' : 'Inactif'; ?></div>
                            <div class="stat-label">Statut du compte</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div class="stat-value"><?php echo ucfirst($user->getRole()); ?></div>
                            <div class="stat-label">Rôle</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stat-value">
                                <?php 
                                $date = new DateTime($user->getDateInscription());
                                echo $date->format('d/m/Y');
                                ?>
                            </div>
                            <div class="stat-label">Membre depuis</div>
                        </div>
                        <?php if ($user->getDateNaissance()): ?>
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-birthday-cake"></i>
                            </div>
                            <div class="stat-value"><?php echo $user->getAge(); ?> ans</div>
                            <div class="stat-label">Âge</div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Profile Completion -->
                    <div class="profile-completion">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Complétion du profil</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $completion = 25; // Base
                                if ($user->getDateNaissance()) $completion += 25;
                                if ($user->getAdresse()) $completion += 25;
                                if ($photo_url) $completion += 25;
                                ?>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $completion; ?>%" 
                                         aria-valuenow="<?php echo $completion; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="completion-text">Votre profil est complété à <?php echo $completion; ?>%</div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Profile Card -->
                    <div class="card animate-fade-in-up">
                        <div class="card-header">
                            <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>Gestion du Profil</h4>
                            <span class="role-badge <?php echo $isAdmin ? 'admin' : 'user'; ?>">
                                <i class="fas fa-<?php echo $isAdmin ? 'crown' : 'user'; ?>"></i>
                                <?php echo ucfirst($user->getRole()); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <!-- Profile Header -->
                            <div class="profile-header">
                                <div class="profile-avatar-container">
                                    <div class="avatar-ring"></div>
                                    <div class="profile-avatar">
                                        <?php if ($photo_url): ?>
                                            <img src="<?php echo $photo_url; ?>" alt="Photo de profil" 
                                                 onerror="this.style.display='none'; document.getElementById('avatar-initials').style.display='flex';">
                                            <div class="avatar-initials" id="avatar-initials" style="display: none;">
                                                <?php echo strtoupper(substr($user->getPrenom(), 0, 1) . substr($user->getNom(), 0, 1)); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-initials">
                                                <?php echo strtoupper(substr($user->getPrenom(), 0, 1) . substr($user->getNom(), 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="photo-actions">
                                            <!-- Formulaire pour uploader une photo -->
                                            <form method="POST" action="" enctype="multipart/form-data" id="photoUploadForm" class="photo-form">
                                                <input type="file" name="photo_profil" id="photoInput" accept="image/jpeg,image/png,image/gif,image/webp" required>
                                                <input type="hidden" name="update_photo" value="1">
                                            </form>
                                            
                                            <button type="button" class="photo-btn photo-upload-btn" onclick="document.getElementById('photoInput').click()" title="Changer la photo">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                            
                                            <?php if ($photo_url): ?>
                                            <form method="POST" action="" id="deletePhotoForm" class="photo-form">
                                                <input type="hidden" name="delete_photo" value="1">
                                            </form>
                                            <button type="button" class="photo-btn photo-delete-btn" onclick="if(confirm('Supprimer la photo de profil ?')) { document.getElementById('deletePhotoForm').submit(); }" title="Supprimer la photo">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <h2 class="mb-2"><?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?></h2>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($user->getEmail()); ?></p>
                                <div class="status-badge <?php echo $user->estActif() ? 'actif' : 'inactif'; ?>">
                                    <i class="fas fa-<?php echo $user->estActif() ? 'check-circle' : 'pause-circle'; ?>"></i>
                                    <?php echo ucfirst($user->getStatut()); ?>
                                </div>
                            </div>

                            <!-- Messages d'alerte -->
                            <?php if ($profile_success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle"></i>
                                    <div><?php echo $profile_success; ?></div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($profile_error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <div><?php echo $profile_error; ?></div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($photo_success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle"></i>
                                    <div><?php echo $photo_success; ?></div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($photo_error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <div><?php echo $photo_error; ?></div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Tabs Navigation -->
                            <div class="tab-container">
                                <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab">
                                            <i class="fas fa-edit"></i> Modifier le profil
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                            <i class="fas fa-lock"></i> Mot de passe
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                                            <i class="fas fa-info-circle"></i> Informations
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="profileTabsContent">
                                    <!-- Edit Profile Tab -->
                                    <div class="tab-pane fade show active" id="edit" role="tabpanel">
                                        <form method="POST" action="" onsubmit="return validateProfileForm()">
                                            <input type="hidden" name="update_profile" value="1">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="prenom" class="form-label">Prénom *</label>
                                                        <input type="text" class="form-control" id="prenom" name="prenom" 
                                                               value="<?php echo htmlspecialchars($user->getPrenom()); ?>" 
                                                               required 
                                                               minlength="2" 
                                                               maxlength="50"
                                                               pattern="[A-Za-zÀ-ÿ\s\-']+"
                                                               title="Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes">
                                                        <div class="form-text">Minimum 2 caractères, lettres uniquement</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="nom" class="form-label">Nom *</label>
                                                        <input type="text" class="form-control" id="nom" name="nom" 
                                                               value="<?php echo htmlspecialchars($user->getNom()); ?>" 
                                                               required 
                                                               minlength="2" 
                                                               maxlength="50"
                                                               pattern="[A-Za-zÀ-ÿ\s\-']+"
                                                               title="Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes">
                                                        <div class="form-text">Minimum 2 caractères, lettres uniquement</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($user->getEmail()); ?>" 
                                                       required
                                                       maxlength="255"
                                                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                                       title="Format d'email valide requis (exemple@domaine.com)">
                                                <div class="form-text">Format: exemple@domaine.com</div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="dateNaissance" class="form-label">Date de naissance</label>
                                                        <input type="date" class="form-control" id="dateNaissance" name="dateNaissance" 
                                                               value="<?php echo $user->getDateNaissance() ? htmlspecialchars($user->getDateNaissance()) : ''; ?>"
                                                               max="<?php echo date('Y-m-d'); ?>"
                                                               title="La date ne peut pas être dans le futur">
                                                        <div class="form-text">Doit être dans le passé</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="adresse" class="form-label">Adresse</label>
                                                        <textarea class="form-control" id="adresse" name="adresse" rows="3" maxlength="500" placeholder="Votre adresse complète"><?php echo $user->getAdresse() ? htmlspecialchars($user->getAdresse()) : ''; ?></textarea>
                                                        <div class="form-text">Maximum 500 caractères</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Password Tab -->
                                    <div class="tab-pane fade" id="password" role="tabpanel">
                                        <?php if ($password_success): ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="fas fa-check-circle"></i>
                                                <div><?php echo $password_success; ?></div>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($password_error): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <div><?php echo $password_error; ?></div>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="POST" action="" onsubmit="return validatePasswordForm()">
                                            <input type="hidden" name="change_password" value="1">
                                            
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                                <div class="password-input-container">
                                                    <input type="password" class="form-control" id="current_password" name="current_password" 
                                                           required
                                                           minlength="6"
                                                           maxlength="255">
                                                    <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text">Minimum 6 caractères</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                                <div class="password-input-container">
                                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                                           required
                                                           minlength="6"
                                                           maxlength="255"
                                                           pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$"
                                                           title="Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre">
                                                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text">Minimum 6 caractères, avec majuscule, minuscule et chiffre</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                                <div class="password-input-container">
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                           required
                                                           minlength="6"
                                                           maxlength="255">
                                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text">Doit correspondre au nouveau mot de passe</div>
                                            </div>
                                            
                                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-key me-2"></i>Changer le mot de passe
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Info Tab -->
                                    <div class="tab-pane fade" id="info" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">
                                                        <i class="fas fa-id-card"></i> Nom complet
                                                    </div>
                                                    <div class="info-value"><?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?></div>
                                                </div>
                                                
                                                <div class="info-group">
                                                    <div class="info-label">
                                                        <i class="fas fa-envelope"></i> Email
                                                    </div>
                                                    <div class="info-value"><?php echo htmlspecialchars($user->getEmail()); ?></div>
                                                </div>
                                                
                                                <div class="info-group">
                                                    <div class="info-label">
                                                        <i class="fas fa-user-tag"></i> Rôle
                                                    </div>
                                                    <div class="info-value">
                                                        <span class="role-badge <?php echo $isAdmin ? 'admin' : 'user'; ?>">
                                                            <i class="fas fa-<?php echo $isAdmin ? 'crown' : 'user'; ?>"></i>
                                                            <?php echo ucfirst($user->getRole()); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">
                                                        <i class="fas fa-toggle-on"></i> Statut
                                                    </div>
                                                    <div class="info-value">
                                                        <span class="status-badge <?php echo $user->estActif() ? 'actif' : 'inactif'; ?>">
                                                            <i class="fas fa-<?php echo $user->estActif() ? 'check-circle' : 'pause-circle'; ?>"></i>
                                                            <?php echo ucfirst($user->getStatut()); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="info-group">
                                                    <div class="info-label">
                                                        <i class="fas fa-calendar-plus"></i> Date d'inscription
                                                    </div>
                                                    <div class="info-value">
                                                        <?php 
                                                        $date = new DateTime($user->getDateInscription());
                                                        echo $date->format('d/m/Y à H:i');
                                                        ?>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($user->getDateNaissance()): ?>
                                                <div class="info-group">
                                                    <div class="info-label">
                                                        <i class="fas fa-birthday-cake"></i> Date de naissance
                                                    </div>
                                                    <div class="info-value">
                                                        <?php 
                                                        $dateNaissance = new DateTime($user->getDateNaissance());
                                                        echo $dateNaissance->format('d/m/Y');
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($user->getAdresse()): ?>
                                        <div class="info-group">
                                            <div class="info-label">
                                                <i class="fas fa-map-marker-alt"></i> Adresse
                                            </div>
                                            <div class="info-value"><?php echo htmlspecialchars($user->getAdresse()); ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($isAdmin): ?>
                            <div class="admin-section">
                                <h5><i class="fas fa-shield-alt me-2"></i>Zone Administrateur</h5>
                                <p class="mb-3">Vous avez accès aux fonctionnalités d'administration du système.</p>
                                <div>
                                    <a href="../../backoffice/admin-dashboard.php" class="btn btn-primary me-2">
                                        <i class="fas fa-tachometer-alt me-1"></i>Tableau de bord
                                    </a>
                                    <a href="../../backoffice/admin-users.php" class="btn btn-secondary">
                                        <i class="fas fa-users me-1"></i>Gestion utilisateurs
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Actions du dashboard -->
                            <div class="dashboard-actions">
                                <a href="../home/index.php" class="btn btn-secondary">
                                    <i class="fas fa-home me-2"></i> Retour à l'accueil
                                </a>
                                
                                <?php if ($isAdmin): ?>
                                    <a href="../../backoffice/admin-dashboard.php" class="btn btn-primary">
                                        <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
                                    </a>
                                <?php else: ?>
                                    <a href="../appointments/" class="btn btn-primary">
                                        <i class="fas fa-calendar me-2"></i> Mes rendez-vous
                                    </a>
                                <?php endif; ?>
                                
                                <a href="../../../controllers/logout.php" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../../assets/js/popper.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script src="../../assets/js/stellar.js"></script>
    <script src="../../assets/js/theme.js"></script>
    
    <script>
        // Fonction de validation pour la photo
        document.getElementById('photoInput').addEventListener('change', function() {
            validatePhoto(this);
        });

        function validatePhoto(input) {
            const file = input.files[0];
            if (file) {
                // Vérifier la taille (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('La taille de la photo ne doit pas dépasser 2MB');
                    input.value = '';
                    return false;
                }
                
                // Vérifier le type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Type de fichier non autorisé. Formats acceptés: JPEG, PNG, GIF, WebP');
                    input.value = '';
                    return false;
                }
                
                // Si tout est valide, soumettre le formulaire
                document.getElementById('photoUploadForm').submit();
            }
            return true;
        }

        // Fonction de validation pour le formulaire de profil
        function validateProfileForm() {
            const prenom = document.getElementById('prenom');
            const nom = document.getElementById('nom');
            const email = document.getElementById('email');
            const dateNaissance = document.getElementById('dateNaissance');
            
            let isValid = true;
            
            // Validation des champs requis
            if (!prenom.checkValidity()) {
                prenom.reportValidity();
                isValid = false;
            }
            
            if (!nom.checkValidity()) {
                nom.reportValidity();
                isValid = false;
            }
            
            if (!email.checkValidity()) {
                email.reportValidity();
                isValid = false;
            }
            
            // Validation de la date
            if (dateNaissance.value) {
                const selectedDate = new Date(dateNaissance.value);
                const today = new Date();
                if (selectedDate > today) {
                    alert('La date de naissance ne peut pas être dans le futur');
                    isValid = false;
                }
            }
            
            return isValid;
        }

        // Fonction de validation pour le formulaire de mot de passe
        function validatePasswordForm() {
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            let isValid = true;
            
            // Validation des champs requis
            if (!currentPassword.checkValidity()) {
                currentPassword.reportValidity();
                isValid = false;
            }
            
            if (!newPassword.checkValidity()) {
                newPassword.reportValidity();
                isValid = false;
            }
            
            if (!confirmPassword.checkValidity()) {
                confirmPassword.reportValidity();
                isValid = false;
            }
            
            // Validation de la correspondance des mots de passe
            if (newPassword.value !== confirmPassword.value) {
                alert('Les mots de passe ne correspondent pas');
                confirmPassword.focus();
                isValid = false;
            }
            
            return isValid;
        }

        // Toggle password visibility
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

        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('mobile-open');
        });

        // Activer les dropdowns de Bootstrap
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl)
        });
        
        // Activer les tabs de Bootstrap
        var tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]')
        var tabList = tabEls.map(function (tabEl) {
            return new bootstrap.Tab(tabEl)
        });
        
        // Confirmation de déconnexion
        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });

        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 300 + (index * 100));
            });
            
            // Animate progress bar
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                setTimeout(() => {
                    progressBar.style.width = progressBar.getAttribute('aria-valuenow') + '%';
                }, 500);
            }
        });
    </script>
</body>
</html>