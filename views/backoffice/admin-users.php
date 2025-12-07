<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();
$usersResult = $adminController->manageUsers('list');
$allUsers = $usersResult['success'] ? $usersResult['users'] : [];

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Récupération des paramètres de filtre
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$statut_filter = $_GET['statut'] ?? '';

// Application des filtres
$users = $allUsers;

if ($search || $role_filter || $statut_filter) {
    $users = array_filter($allUsers, function($user) use ($search, $role_filter, $statut_filter) {
        $match_search = true;
        $match_role = true;
        $match_statut = true;
        
        // Filtre de recherche
        if ($search) {
            $search_term = strtolower(trim($search));
            $nom = strtolower($user['nom'] ?? '');
            $prenom = strtolower($user['prenom'] ?? '');
            $email = strtolower($user['email'] ?? '');
            
            $match_search = strpos($nom, $search_term) !== false ||
                           strpos($prenom, $search_term) !== false ||
                           strpos($email, $search_term) !== false;
        }
        
        // Filtre par rôle
        if ($role_filter) {
            $match_role = ($user['role'] ?? '') === $role_filter;
        }
        
        // Filtre par statut
        if ($statut_filter) {
            $match_statut = ($user['statut'] ?? '') === $statut_filter;
        }
        
        return $match_search && $match_role && $match_statut;
    });
    
    // Réindexer le tableau après filtrage
    $users = array_values($users);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Medsense Medical</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    
    <!-- main css -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    
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
        
        .btn-success {
            background: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background: #3d8b40;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background: #e68900;
            transform: translateY(-2px);
        }
        
        .btn-outline-primary {
            background: transparent;
            color: var(--medical-blue);
            border: 1px solid var(--medical-blue);
        }
        
        .btn-outline-primary:hover {
            background: var(--medical-blue);
            color: white;
        }
        
        .btn-outline-warning {
            background: transparent;
            color: var(--warning-color);
            border: 1px solid var(--warning-color);
        }
        
        .btn-outline-warning:hover {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.875rem;
        }
        
        .table-responsive {
            max-height: 600px;
            border-radius: var(--card-radius);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        
        .table th {
            background: var(--medical-light-blue);
            color: var(--medical-dark-blue);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 12px;
            padding: 1rem;
            border-bottom: 2px solid var(--medical-blue);
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
            vertical-align: middle;
        }
        
        .table tr:hover {
            background: var(--medical-light-blue);
        }
        
        .actions-column {
            min-width: 200px;
        }
        
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            gap: 5px;
        }
        
        .role-badge.admin {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
        
        .role-badge.user {
            background: rgba(26, 115, 232, 0.1);
            color: var(--medical-blue);
            border: 1px solid rgba(26, 115, 232, 0.3);
        }
        
        .role-badge.moderator {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
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
        
        .section-title {
            color: var(--medical-dark-blue);
            margin-bottom: 2rem;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 1rem;
        }
        
        .filter-active {
            background-color: rgba(26, 115, 232, 0.05);
            border-left: 4px solid var(--medical-blue);
        }
        
        .filter-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: var(--medical-blue);
            border-radius: 50%;
            margin-right: 5px;
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
        
        .dashboard-actions {
            margin-top: 2.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
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
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
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
                display: block;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .actions-column {
                min-width: 150px;
            }
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
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--medical-blue) 0%, var(--medical-cyan) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            margin-right: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
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
            <img src="../assets/img/logo.png" alt="logo" style="height: 200px;">
        </a>
    </div>
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin-dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-uppercase text-muted ms-3">Gestion Médicale</small>
            </li>
            
            <!-- Menu Rendez-vous avec sous-menu -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#appointmentsMenu">
                    <i class="fas fa-calendar-check"></i>
                    <span>Rendez-vous</span>
                    <i class="fas fa-chevron-down float-end mt-1"></i>
                </a>
                <div class="collapse" id="appointmentsMenu">
                    <ul class="nav flex-column sidebar-submenu">
                        <li class="nav-item">
                            <a class="nav-link" href="admin-appointments.php">
                                <i class="fas fa-list"></i>
                                <span>Tous les rendez-vous</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-patient-appointments.php">
                                <i class="fas fa-user-injured"></i>
                                <span>Rendez-vous patients</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-new-appointment.php">
                                <i class="fas fa-plus-circle"></i>
                                <span>Nouveau rendez-vous</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-calendar.php">
                                <i class="fas fa-calendar"></i>
                                <span>Calendrier</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="admin-patients.php">
                    <i class="fas fa-user-injured"></i>
                    <span>Patients</span>
                </a>
            </li>
            
            <!-- Menu Médecins avec sous-menu COMPLET -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#doctorsMenu">
                    <i class="fas fa-user-md"></i>
                    <span>Médecins</span>
                    <i class="fas fa-chevron-down float-end mt-1"></i>
                </a>
                <div class="collapse" id="doctorsMenu">
                    <ul class="nav flex-column sidebar-submenu">
                        <li class="nav-item">
                            <a class="nav-link" href="admin-doctors.php">
                                <i class="fas fa-list"></i>
                                <span>Tous les médecins</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-doctor-availability.php">
                                <i class="fas fa-clock"></i>
                                <span>Disponibilité</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-doctor-reviews.php">
                                <i class="fas fa-star"></i>
                                <span>Notes & Avis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-doctor-ratings.php">
                                <i class="fas fa-chart-line"></i>
                                <span>Évaluations</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link active" href="admin-users.php">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            
            <!-- Menu Réclamations -->
            <li class="nav-item">
                <a class="nav-link" href="admin-complaints.php">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Réclamations</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="admin-prescriptions.php">
                    <i class="fas fa-prescription"></i>
                    <span>Ordonnances</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="admin-billing.php">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Facturation</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-uppercase text-muted ms-3">Gestion du Blog</small>
            </li>
            
            <!-- Menu Blog avec sous-menu -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#blogMenu">
                    <i class="fas fa-blog"></i>
                    <span>Blog</span>
                    <i class="fas fa-chevron-down float-end mt-1"></i>
                </a>
                <div class="collapse" id="blogMenu">
                    <ul class="nav flex-column sidebar-submenu">
                        <li class="nav-item">
                            <a class="nav-link" href="admin-blog-categories.php">
                                <i class="fas fa-tags"></i>
                                <span>Catégories</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-blog-articles.php">
                                <i class="fas fa-file-alt"></i>
                                <span>Articles</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-blog-comments.php">
                                <i class="fas fa-comments"></i>
                                <span>Commentaires</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-blog-activity.php">
                                <i class="fas fa-chart-line"></i>
                                <span>Activité du blog</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-uppercase text-muted ms-3">Avis & Évaluations</small>
            </li>
            
            <!-- Menu Reviews avec sous-menu -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#reviewsMenu">
                    <i class="fas fa-star"></i>
                    <span>Reviews & Avis</span>
                    <i class="fas fa-chevron-down float-end mt-1"></i>
                </a>
                <div class="collapse" id="reviewsMenu">
                    <ul class="nav flex-column sidebar-submenu">
                        <li class="nav-item">
                            <a class="nav-link" href="admin-reviews.php">
                                <i class="fas fa-star-half-alt"></i>
                                <span>Tous les avis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-doctor-reviews.php">
                                <i class="fas fa-user-md"></i>
                                <span>Avis médecins</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-patient-reviews.php">
                                <i class="fas fa-user-injured"></i>
                                <span>Avis patients</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="admin-feedback.php">
                    <i class="fas fa-comment-medical"></i>
                    <span>Feedback</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-uppercase text-muted ms-3">Configuration</small>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="admin-settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-uppercase text-muted ms-3">Rapports</small>
            </li>
            
            <!-- Menu Rapports avec sous-menu -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#reportsMenu">
                    <i class="fas fa-chart-bar"></i>
                    <span>Rapports</span>
                    <i class="fas fa-chevron-down float-end mt-1"></i>
                </a>
                <div class="collapse" id="reportsMenu">
                    <ul class="nav flex-column sidebar-submenu">
                        <li class="nav-item">
                            <a class="nav-link" href="admin-reports-statistics.php">
                                <i class="fas fa-chart-pie"></i>
                                <span>Statistiques</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-reports-financial.php">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Financiers</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-reports-medical.php">
                                <i class="fas fa-stethoscope"></i>
                                <span>Médicaux</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-audit.php">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Audit médical</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
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
                <h4 class="mb-0"><i class="fas fa-users medical-icon me-2"></i>Gestion des Utilisateurs</h4>
                <p class="text-muted mb-0">Administrez les comptes utilisateurs du système</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 45px; height: 45px;">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <span class="ms-2"> Admin</span>
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
        </div>

        <!-- Content -->
        <div class="container-fluid">
            <!-- Stats Overview -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?= count($allUsers) ?></div>
                    <div class="stat-label">Total Utilisateurs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value">
                        <?= count(array_filter($allUsers, function($user) { return $user['statut'] === 'actif'; })) ?>
                    </div>
                    <div class="stat-label">Utilisateurs Actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div class="stat-value">
                        <?= count(array_filter($allUsers, function($user) { return $user['statut'] === 'inactif'; })) ?>
                    </div>
                    <div class="stat-label">Utilisateurs Inactifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-value">
                        <?= count(array_filter($allUsers, function($user) { return $user['role'] === 'admin'; })) ?>
                    </div>
                    <div class="stat-label">Administrateurs</div>
                </div>
            </div>

            <!-- En-tête avec titre et bouton d'ajout -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="fas fa-users me-2"></i>Gestion des Utilisateurs
                </h1>
                <a href="admin-create-user.php" class="btn btn-success">
                    <i class="fas fa-user-plus me-1"></i> Nouvel Utilisateur
                </a>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-users me-2"></i>Gestion des Utilisateurs
    </h1>
    <div class="d-flex gap-2">
        <a href="admin-export-excel.php?<?= http_build_query([
            'search' => $search,
            'role' => $role_filter,
            'statut' => $statut_filter
        ]) ?>" class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i> Exporter Excel
        </a>
        <a href="admin-create-user.php" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> Nouvel Utilisateur
        </a>
    </div>
</div>

            <!-- Messages d'alerte -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div><?= htmlspecialchars($success_message) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?= htmlspecialchars($error_message) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filtres et recherche -->
            <div class="card mb-4 <?= ($search || $role_filter || $statut_filter) ? 'filter-active' : '' ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres et Recherche
                        <?php if ($search || $role_filter || $statut_filter): ?>
                            <span class="filter-indicator"></span>
                        <?php endif; ?>
                    </h5>
                    <?php if ($search || $role_filter || $statut_filter): ?>
                        <span class="badge bg-primary">
                            <?= count($users) ?> résultat(s)
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" placeholder="Nom, prénom ou email...">
                            </div>
                            <div class="col-md-3">
                                <label for="role" class="form-label">Rôle</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">Tous les rôles</option>
                                    <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                    <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                    <option value="moderator" <?= $role_filter === 'moderator' ? 'selected' : '' ?>>Modérateur</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" <?= $statut_filter === 'actif' ? 'selected' : '' ?>>Actif</option>
                                    <option value="inactif" <?= $statut_filter === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-search me-1"></i> Appliquer
                                    </button>
                                    <?php if ($search || $role_filter || $statut_filter): ?>
                                        <a href="admin-users.php" class="btn btn-outline-secondary" title="Réinitialiser">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des utilisateurs -->
            <div class="card animate-fade-in-up">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Liste des Utilisateurs
                        <span class="badge bg-primary ms-2"><?= count($users) ?></span>
                    </h5>
                    <div class="d-flex align-items-center">
                        <small class="text-muted me-2">Total: <?= count($allUsers) ?> utilisateur(s)</small>
                        <?php if ($search || $role_filter || $statut_filter): ?>
                            <a href="admin-users.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Tout afficher
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun utilisateur trouvé</h5>
                            <?php if ($search || $role_filter || $statut_filter): ?>
                                <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                                <a href="admin-users.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-redo me-1"></i> Réinitialiser les filtres
                                </a>
                            <?php else: ?>
                                <p class="text-muted">Commencez par ajouter un nouvel utilisateur</p>
                                <a href="admin-create-user.php" class="btn btn-success mt-2">
                                    <i class="fas fa-user-plus me-1"></i> Ajouter un utilisateur
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilisateur</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Date d'inscription</th>
                                        <th class="actions-column">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): 
                                        $user_id = $user['id_utilisateur'] ?? $user['id'];
                                        $is_current_user = $user_id == $_SESSION['user_id'];
                                        $initials = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
                                    ?>
                                    <tr>
                                        <td><strong>#<?= $user_id ?></strong></td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?= $initials ?>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="role-badge <?= $user['role'] ?>">
                                                <i class="fas fa-<?= $user['role'] === 'admin' ? 'crown' : 'user' ?>"></i>
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $user['statut'] ?>">
                                                <i class="fas fa-<?= $user['statut'] === 'actif' ? 'check-circle' : 'pause-circle' ?>"></i>
                                                <?= ucfirst($user['statut']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="admin-edit.php?id=<?= $user_id ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </a>
                                                
                                                <?php if (!$is_current_user): ?>
                                                    <?php if ($user['statut'] === 'actif'): ?>
                                                        <form method="POST" action="admin-deactivate.php" 
                                                              onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?');"
                                                              style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                                            <button type="submit" class="btn btn-outline-warning" title="Désactiver">
                                                                <i class="fas fa-user-slash"></i> Désactiver
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" action="admin-activate.php" 
                                                              onsubmit="return confirm('Êtes-vous sûr de vouloir activer cet utilisateur ?');"
                                                              style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                                            <button type="submit" class="btn btn-outline-success" title="Activer">
                                                                <i class="fas fa-user-check"></i> Activer
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-secondary" disabled title="Vous ne pouvez pas modifier votre propre statut">
                                                        <i class="fas fa-user-lock"></i> Statut
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Navigation Actions -->
            <div class="dashboard-actions mt-4">
                <a href="admin-dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-tachometer-alt me-2"></i> Retour au Dashboard
                </a>
                <a href="admin-create-user.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i> Nouvel Utilisateur
                </a>
                <a href="../frontoffice/home/index.php" class="btn btn-outline-primary">
                    <i class="fas fa-home me-2"></i> Retour au site
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/stellar.js"></script>
    <script src="../assets/js/theme.js"></script>
    
    <script>
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
        
        // Gérer le menu déroulant des rendez-vous
        document.getElementById('appointmentsMenu').addEventListener('show.bs.collapse', function () {
            this.previousElementSibling.querySelector('.fa-chevron-down').classList.add('fa-chevron-up');
            this.previousElementSibling.querySelector('.fa-chevron-down').classList.remove('fa-chevron-down');
        });
        
        document.getElementById('appointmentsMenu').addEventListener('hide.bs.collapse', function () {
            this.previousElementSibling.querySelector('.fa-chevron-up').classList.add('fa-chevron-down');
            this.previousElementSibling.querySelector('.fa-chevron-up').classList.remove('fa-chevron-up');
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

        // Amélioration de l'UX des filtres
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const inputs = filterForm.querySelectorAll('input, select');
            
            // Réinitialiser les filtres avec la touche Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    window.location.href = 'admin-users.php';
                }
            });
            
            // Indicateur visuel pour les champs de filtre actifs
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value) {
                        this.classList.add('border-primary');
                    } else {
                        this.classList.remove('border-primary');
                    }
                });
                
                // Appliquer le style initial
                if (input.value) {
                    input.classList.add('border-primary');
                }
            });
            
            // Animation au chargement
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
        });
    </script>
</body>
</html>