<?php
session_start();
include_once '../../../controllers/AuthController.php'; 
include_once '../../../controllers/PasswordController.php';
include_once '../../../controllers/ProfileController.php';

$authController = new AuthController();
$passwordController = new PasswordController();
$profileController = new ProfileController();

// Vérification de la connexion et récupération de l'utilisateur
if (!$authController->isLoggedIn()) {
    header('Location: sign-in.php');
    exit;
}

// Récupérer l'utilisateur actuel avec vérification
$user = $authController->getCurrentUser();

// Vérifier si l'utilisateur a été correctement récupéré
if (!$user || !is_object($user)) {
    // Si l'utilisateur n'est pas trouvé, déconnecter et rediriger
    session_destroy();
    $_SESSION['error_message'] = "Session invalide. Veuillez vous reconnecter.";
    header('Location: sign-in.php');
    exit;
}

$isAdmin = $user->estAdmin();

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

// Fonction modifiée pour gérer le cas où $user est null
function getProfilePhotoUrl($user) {
    global $profileController;
    
    // Vérifier si l'utilisateur existe et a une photo
    if ($user && method_exists($user, 'getPhotoProfil') && $user->getPhotoProfil()) {
        $url = $profileController->getProfilePhotoUrl($user->getPhotoProfil());
        if ($url) {
            return $url;
        }
    }
    return null;
}

// Récupérer l'URL de la photo avec sécurité
$photo_url = $user ? getProfilePhotoUrl($user) : null;

// Fonction utilitaire pour afficher les informations utilisateur en toute sécurité
function safeUserInfo($user, $method, $default = '') {
    if ($user && method_exists($user, $method)) {
        $value = $user->$method();
        return $value ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $default;
    }
    return $default;
}

// Fonction pour obtenir les initiales en toute sécurité
function getInitials($user) {
    if ($user && method_exists($user, 'getPrenom') && method_exists($user, 'getNom')) {
        $prenom = $user->getPrenom();
        $nom = $user->getNom();
        return strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
    }
    return '??';
}
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
        min-height: 100vh;
    }
    
    /* Sidebar améliorée */
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
        overflow-y: auto;
    }
    
    .sidebar-header {
        padding: 30px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .sidebar-header img {
        height: 60px;
        width: auto;
        object-fit: contain;
    }
    
    .sidebar-menu {
        padding: 20px 0;
    }
    
    .sidebar-menu .nav-link {
        color: rgba(255, 255, 255, 0.85);
        padding: 14px 25px;
        border-left: 4px solid transparent;
        transition: var(--transition);
        font-weight: 500;
        display: flex;
        align-items: center;
        margin: 2px 0;
    }
    
    .sidebar-menu .nav-link:hover, 
    .sidebar-menu .nav-link.active {
        color: white;
        background-color: rgba(255, 255, 255, 0.15);
        border-left: 4px solid white;
        transform: translateX(5px);
    }
    
    .sidebar-menu .nav-link i {
        width: 24px;
        margin-right: 15px;
        font-size: 18px;
        text-align: center;
    }
    
    .sidebar-menu .nav-item small {
        display: block;
        padding: 15px 25px 8px;
        font-size: 11px;
        letter-spacing: 1px;
        opacity: 0.7;
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        margin-top: 10px;
    }
    
    .sidebar-menu .nav-item:not(:first-child) small {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 20px;
        padding-top: 25px;
    }
    
    /* Main Content amélioré */
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 30px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transition: var(--transition);
    }
    
    /* Top Bar modernisé */
    .top-bar {
        background: white;
        border-radius: var(--card-radius);
        padding: 20px 30px;
        box-shadow: var(--shadow-medium);
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 4px solid var(--medical-blue);
        position: relative;
        overflow: hidden;
    }
    
    .top-bar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--medical-blue), var(--medical-cyan));
    }
    
    .top-bar h4 {
        color: var(--medical-dark-blue);
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .top-bar .text-muted {
        color: var(--secondary-color) !important;
    }
    
    /* User dropdown amélioré */
    .user-dropdown .dropdown-toggle {
        padding: 8px 15px;
        border-radius: 50px;
        background: var(--medical-light-blue);
        border: 2px solid rgba(26, 115, 232, 0.1);
        transition: var(--transition);
    }
    
    .user-dropdown .dropdown-toggle:hover {
        background: white;
        border-color: var(--medical-blue);
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
    }
    
    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--medical-blue), var(--medical-cyan));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .user-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    
    /* Stats Container modernisé */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 25px;
        margin-bottom: 35px;
    }
    
    .stat-card {
        background: white;
        border-radius: var(--card-radius);
        padding: 25px;
        box-shadow: var(--shadow-light);
        text-align: center;
        transition: var(--transition);
        border-top: 4px solid;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100%;
        background: linear-gradient(135deg, rgba(26, 115, 232, 0.03), rgba(0, 188, 212, 0.03));
        opacity: 0;
        transition: var(--transition);
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-strong);
    }
    
    .stat-card:hover::before {
        opacity: 1;
    }
    
    .stat-card:nth-child(1) { border-top-color: var(--medical-blue); }
    .stat-card:nth-child(2) { border-top-color: var(--success-color); }
    .stat-card:nth-child(3) { border-top-color: var(--info-color); }
    .stat-card:nth-child(4) { border-top-color: var(--medical-cyan); }
    
    .stat-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 28px;
        background: linear-gradient(135deg, rgba(26, 115, 232, 0.1), rgba(0, 188, 212, 0.1));
        color: var(--medical-blue);
        position: relative;
        z-index: 1;
    }
    
    .stat-value {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--medical-dark-blue);
        position: relative;
        z-index: 1;
    }
    
    .stat-label {
        font-size: 14px;
        color: var(--secondary-color);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }
    
    /* Profile Completion amélioré */
    .profile-completion {
        margin-bottom: 35px;
    }
    
    .profile-completion .card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--shadow-medium);
        overflow: hidden;
        background: linear-gradient(135deg, white, #f8f9fa);
    }
    
    .profile-completion .card-header {
        background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-dark-blue) 100%);
        color: white;
        border-bottom: none;
        padding: 20px 30px;
    }
    
    .profile-completion .card-body {
        padding: 30px;
    }
    
    .progress {
        height: 12px;
        border-radius: 6px;
        background: #e9ecef;
        overflow: hidden;
        margin-bottom: 15px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .progress-bar {
        background: linear-gradient(90deg, var(--medical-blue), var(--medical-cyan));
        position: relative;
        overflow: hidden;
    }
    
    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, 
            transparent 0%, 
            rgba(255, 255, 255, 0.3) 50%, 
            transparent 100%);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .completion-text {
        font-size: 15px;
        color: var(--secondary-color);
        text-align: center;
        font-weight: 500;
    }
    
    /* Main Profile Card modernisé */
    .profile-main-card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--shadow-strong);
        margin-bottom: 30px;
        overflow: hidden;
        background: white;
    }
    
    .profile-main-card .card-header {
        background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-teal) 100%);
        color: white;
        border-bottom: none;
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .profile-main-card .card-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1.5rem;
    }
    
    .profile-main-card .card-body {
        padding: 35px;
    }
    
    /* Profile Header amélioré */
    .profile-header {
        text-align: center;
        margin-bottom: 40px;
        padding-bottom: 40px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        position: relative;
    }
    
    .profile-avatar-container {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto 25px;
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
        transition: var(--transition);
    }
    
    .profile-avatar:hover img {
        transform: scale(1.05);
    }
    
    .avatar-initials {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        font-weight: bold;
        color: white;
    }
    
    .avatar-ring {
        position: absolute;
        top: -6px;
        left: -6px;
        right: -6px;
        bottom: -6px;
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
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        padding: 12px 20px;
        display: flex;
        justify-content: center;
        gap: 15px;
        border-radius: 50px;
        opacity: 0;
        transition: var(--transition);
        z-index: 3;
        width: auto;
    }
    
    .profile-avatar-container:hover .photo-actions {
        opacity: 1;
        bottom: 15px;
    }
    
    .photo-btn {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        transition: var(--transition);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .photo-upload-btn {
        background: linear-gradient(135deg, var(--medical-cyan), #00a5bb);
        color: white;
    }
    
    .photo-upload-btn:hover {
        background: linear-gradient(135deg, #00a5bb, #008b9e);
        transform: scale(1.1) rotate(5deg);
    }
    
    .photo-delete-btn {
        background: linear-gradient(135deg, var(--danger-color), #d32f2f);
        color: white;
    }
    
    .photo-delete-btn:hover {
        background: linear-gradient(135deg, #d32f2f, #b71c1c);
        transform: scale(1.1) rotate(-5deg);
    }
    
    .profile-header h2 {
        font-size: 2.2rem;
        font-weight: 600;
        color: var(--medical-dark-blue);
        margin-bottom: 8px;
    }
    
    .profile-header .text-muted {
        font-size: 1.1rem;
        color: var(--secondary-color) !important;
        margin-bottom: 20px;
    }
    
    /* Badges améliorés */
    .role-badge {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 0.95rem;
        font-weight: 600;
        gap: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .role-badge.user {
        background: linear-gradient(135deg, rgba(26, 115, 232, 0.15), rgba(26, 115, 232, 0.05));
        color: var(--medical-blue);
        border: 1px solid rgba(26, 115, 232, 0.2);
    }
    
    .role-badge.admin {
        background: linear-gradient(135deg, rgba(244, 67, 54, 0.15), rgba(244, 67, 54, 0.05));
        color: var(--danger-color);
        border: 1px solid rgba(244, 67, 54, 0.2);
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 0.95rem;
        font-weight: 600;
        gap: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .status-badge.actif {
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(76, 175, 80, 0.05));
        color: var(--success-color);
        border: 1px solid rgba(76, 175, 80, 0.2);
    }
    
    .status-badge.inactif {
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.15), rgba(108, 117, 125, 0.05));
        color: var(--secondary-color);
        border: 1px solid rgba(108, 117, 125, 0.2);
    }
    
    /* Tabs modernisés */
    .tab-container {
        margin-top: 40px;
    }
    
    .nav-tabs {
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 30px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        padding: 15px 30px;
        color: var(--secondary-color);
        font-weight: 600;
        border-radius: 8px 8px 0 0;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: var(--transition);
        background: transparent;
        position: relative;
        font-size: 1rem;
    }
    
    .nav-tabs .nav-link:hover {
        color: var(--medical-blue);
        background: rgba(26, 115, 232, 0.08);
        transform: translateY(-2px);
    }
    
    .nav-tabs .nav-link.active {
        color: var(--medical-blue);
        background: white;
        border-bottom: 3px solid var(--medical-blue);
        box-shadow: 0 -4px 12px rgba(26, 115, 232, 0.1);
    }
    
    .nav-tabs .nav-link.active i {
        color: var(--medical-blue);
    }
    
    .tab-content {
        background: white;
        border-radius: 0 0 var(--card-radius) var(--card-radius);
        padding: 35px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
    }
    
    /* Formulaires améliorés */
    .form-label {
        font-weight: 600;
        color: var(--medical-dark-blue);
        margin-bottom: 10px;
        font-size: 1rem;
    }
    
    .form-control, .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px;
        transition: var(--transition);
        font-size: 16px;
        background: #fff;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--medical-blue);
        box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.15);
        background: #fff;
    }
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    .form-text {
        font-size: 0.875rem;
        margin-top: 8px;
        color: var(--secondary-color);
    }
    
    /* Boutons améliorés */
    .btn {
        padding: 14px 28px;
        border-radius: 8px;
        font-weight: 600;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 16px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--medical-blue), #0d62d9);
        color: white;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #0d62d9, var(--medical-dark-blue));
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(26, 115, 232, 0.3);
    }
    
    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #3d8b40);
        color: white;
    }
    
    .btn-success:hover {
        background: linear-gradient(135deg, #3d8b40, #2d7730);
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(76, 175, 80, 0.3);
    }
    
    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #d32f2f);
        color: white;
    }
    
    .btn-danger:hover {
        background: linear-gradient(135deg, #d32f2f, #b71c1c);
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(244, 67, 54, 0.3);
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #e2e8f0, #cbd5e0);
        color: #4a5568;
    }
    
    .btn-secondary:hover {
        background: linear-gradient(135deg, #cbd5e0, #a0aec0);
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(108, 117, 125, 0.2);
    }
    
    /* Password input container */
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
        color: var(--secondary-color);
        cursor: pointer;
        font-size: 18px;
        padding: 5px;
        transition: var(--transition);
    }
    
    .password-toggle:hover {
        color: var(--medical-blue);
    }
    
    /* Alerts améliorés */
    .alert {
        padding: 20px 25px;
        border-radius: 10px;
        margin-bottom: 25px;
        border-left: 5px solid;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: var(--shadow-light);
    }
    
    .alert-success {
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(76, 175, 80, 0.05));
        color: #2d7740;
        border-left-color: var(--success-color);
    }
    
    .alert-danger {
        background: linear-gradient(135deg, rgba(244, 67, 54, 0.1), rgba(244, 67, 54, 0.05));
        color: #c53030;
        border-left-color: var(--danger-color);
    }
    
    .alert-info {
        background: linear-gradient(135deg, rgba(26, 115, 232, 0.1), rgba(26, 115, 232, 0.05));
        color: var(--medical-dark-blue);
        border-left-color: var(--medical-blue);
    }
    
    .alert i {
        font-size: 24px;
        flex-shrink: 0;
    }
    
    /* Admin Section améliorée */
    .admin-section {
        background: linear-gradient(135deg, rgba(255, 245, 245, 0.8), rgba(255, 235, 235, 0.9));
        border-left: 5px solid var(--danger-color);
        padding: 25px 30px;
        border-radius: 12px;
        margin-top: 30px;
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.1);
    }
    
    .admin-section h5 {
        color: var(--danger-color);
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    /* Dashboard Actions améliorées */
    .dashboard-actions {
        margin-top: 40px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    /* Info Groups améliorés */
    .info-group {
        margin-bottom: 20px;
        padding: 20px;
        background: linear-gradient(135deg, var(--medical-light-blue), rgba(232, 240, 254, 0.5));
        border-radius: 10px;
        border-left: 5px solid var(--medical-blue);
        transition: var(--transition);
    }
    
    .info-group:hover {
        background: linear-gradient(135deg, white, #f8f9fa);
        box-shadow: var(--shadow-light);
        transform: translateX(5px);
    }
    
    .info-label {
        font-size: 12px;
        text-transform: uppercase;
        color: var(--medical-teal);
        font-weight: 600;
        letter-spacing: 1px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-value {
        font-size: 17px;
        color: #2d3748;
        font-weight: 500;
    }
    
    /* Mobile Menu Button amélioré */
    .mobile-menu-btn {
        display: none;
        background: linear-gradient(135deg, var(--medical-blue), var(--medical-dark-blue));
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 18px;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3);
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1001;
        transition: var(--transition);
    }
    
    .mobile-menu-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(26, 115, 232, 0.4);
    }
    
    /* Animation pour les cartes */
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
    
    /* Responsive Design amélioré */
    @media (max-width: 992px) {
        .sidebar {
            width: 80px;
        }
        
        .sidebar .nav-link span,
        .sidebar .nav-item small {
            display: none;
        }
        
        .sidebar .sidebar-header img {
            height: 40px;
        }
        
        .sidebar .nav-link {
            padding: 15px;
            justify-content: center;
        }
        
        .sidebar .nav-link i {
            margin-right: 0;
            font-size: 20px;
        }
        
        .main-content {
            margin-left: 80px;
            padding: 20px;
        }
        
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            width: 280px;
        }
        
        .sidebar.mobile-open {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0;
            padding: 20px 15px;
        }
        
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .mobile-menu-btn {
            display: block;
        }
        
        .top-bar {
            flex-direction: column;
            gap: 15px;
            text-align: center;
            padding: 20px;
        }
        
        .user-dropdown {
            width: 100%;
        }
        
        .user-dropdown .dropdown-toggle {
            width: 100%;
            justify-content: center;
        }
        
        .dashboard-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
        
        .nav-tabs .nav-link {
            padding: 12px 20px;
            font-size: 0.9rem;
        }
        
        .profile-main-card .card-body {
            padding: 25px 20px;
        }
    }
    
    @media (max-width: 480px) {
        .profile-avatar-container {
            width: 140px;
            height: 140px;
        }
        
        .avatar-initials {
            font-size: 3rem;
        }
        
        .nav-tabs .nav-link {
            padding: 10px 15px;
            font-size: 0.85rem;
        }
        
        .profile-header h2 {
            font-size: 1.8rem;
        }
    }
    
    /* Scrollbar personnalisée */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
    }
    
    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
    
    /* Loading animation */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .loading {
        animation: pulse 1.5s infinite;
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
            
            <!-- Section pour les médecins -->
            <?php if ($user->estMedecin()): ?>
            <li class="nav-item">
                <small class="text-uppercase text-muted ms-3 mt-3">Médecin</small>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../consultations/">
                    <i class="fas fa-stethoscope"></i>
                    <span>Mes Consultations</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../patients/">
                    <i class="fas fa-user-injured"></i>
                    <span>Mes Patients</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../calendrier/">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendrier</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../reclamations/">
                    <i class="fas fa-comment-medical"></i>
                    <span>Réclamations</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../blog/">
                    <i class="fas fa-blog"></i>
                    <span>Blog</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../activity/">
                    <i class="fas fa-chart-line"></i>
                    <span>Activité</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Section pour les administrateurs -->
            <?php if ($isAdmin): ?>
            <li class="nav-item">
                <small class="text-uppercase text-muted ms-3 mt-3">Administration</small>
            </li>
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
            <li class="nav-item">
                <a class="nav-link" href="../../backoffice/admin-medecins.php">
                    <i class="fas fa-user-md"></i>
                    <span>Gestion Médecins</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Section pour les patients (si non médecin et non admin) -->
            <?php if ($user->estPatient()): ?>
            <li class="nav-item">
                <small class="text-uppercase text-muted ms-3 mt-3">Patient</small>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../appointments/">
                    <i class="fas fa-calendar-check"></i>
                    <span>Mes Rendez-vous</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../historique/">
                    <i class="fas fa-history"></i>
                    <span>Historique Médical</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../ordonnances/">
                    <i class="fas fa-prescription"></i>
                    <span>Ordonnances</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item mt-3">
                <small class="text-uppercase text-muted ms-3">Compte</small>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="rate_site.php">
                    <i class="fas fa-star"></i>
                    <span>Évaluer le site</span>
                </a>
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
                                    <?php echo getInitials($user); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="ms-2"><?php echo safeUserInfo($user, 'getPrenom', 'Utilisateur'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="../appointments/"><i class="fas fa-calendar me-2"></i> Mes rendez-vous</a></li>
                    
                         <li><a class="dropdown-item" href="rate_site.php"><i class="fas fa-star me-2"></i> Évaluer le site</a></li>
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
                            <div class="stat-value"><?php echo $user && $user->estActif() ? 'Actif' : 'Inactif'; ?></div>
                            <div class="stat-label">Statut du compte</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div class="stat-value"><?php echo $user ? ucfirst($user->getRole()) : 'Non défini'; ?></div>
                            <div class="stat-label">Rôle</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stat-value">
                                <?php 
                                if ($user && $user->getDateInscription()) {
                                    $date = new DateTime($user->getDateInscription());
                                    echo $date->format('d/m/Y');
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                            <div class="stat-label">Membre depuis</div>
                        </div>
                        <?php if ($user && $user->getDateNaissance()): ?>
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
                                $completion = 0;
                                if ($user) {
                                    $completion = 25; // Base
                                    if ($user->getDateNaissance()) $completion += 25;
                                    if ($user->getAdresse()) $completion += 25;
                                    if ($photo_url) $completion += 25;
                                }
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
                            <?php if ($user): ?>
                            <span class="role-badge <?php echo $isAdmin ? 'admin' : 'user'; ?>">
                                <i class="fas fa-<?php echo $isAdmin ? 'crown' : 'user'; ?>"></i>
                                <?php echo ucfirst($user->getRole()); ?>
                            </span>
                            <?php endif; ?>
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
                                                <?php echo getInitials($user); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-initials">
                                                <?php echo getInitials($user); ?>
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
                                <?php if ($user): ?>
                                <h2 class="mb-2"><?php echo safeUserInfo($user, 'getPrenom') . ' ' . safeUserInfo($user, 'getNom'); ?></h2>
                                <p class="text-muted mb-2"><?php echo safeUserInfo($user, 'getEmail'); ?></p>
                                <div class="status-badge <?php echo $user->estActif() ? 'actif' : 'inactif'; ?>">
                                    <i class="fas fa-<?php echo $user->estActif() ? 'check-circle' : 'pause-circle'; ?>"></i>
                                    <?php echo ucfirst($user->getStatut()); ?>
                                </div>
                                <?php else: ?>
                                <h2 class="mb-2">Utilisateur non trouvé</h2>
                                <p class="text-muted mb-2">Veuillez vous reconnecter</p>
                                <?php endif; ?>
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

                            <?php if (!$user): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <div>Votre session a expiré ou est invalide. Veuillez vous reconnecter.</div>
                                    <a href="sign-in.php" class="btn btn-danger mt-3">Se reconnecter</a>
                                </div>
                            <?php else: ?>
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
                                                               value="<?php echo safeUserInfo($user, 'getPrenom'); ?>" 
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
                                                               value="<?php echo safeUserInfo($user, 'getNom'); ?>" 
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
                                                       value="<?php echo safeUserInfo($user, 'getEmail'); ?>" 
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
                                                    <div class="info-value"><?php echo safeUserInfo($user, 'getPrenom') . ' ' . safeUserInfo($user, 'getNom'); ?></div>
                                                </div>
                                                
                                                <div class="info-group">
                                                    <div class="info-label">
                                                        <i class="fas fa-envelope"></i> Email
                                                    </div>
                                                    <div class="info-value"><?php echo safeUserInfo($user, 'getEmail'); ?></div>
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
                            <?php endif; ?>
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
        tabEls.forEach(function (tabEl) {
            var tab = new bootstrap.Tab(tabEl);
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