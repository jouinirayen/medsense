<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();
$dashboardData = $adminController->dashboard();

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Extraire les données du dashboard
$stats = $dashboardData['stats'] ?? [];
$recentUsers = $dashboardData['recentUsers'] ?? [];
$pendingDoctors = $dashboardData['pendingDoctors'] ?? [];

// Calculer les statistiques
$totalUsers = $stats['total'] ?? 0;
$newThisMonth = $stats['new_this_month'] ?? 0;
$roleStats = $stats['by_role'] ?? [];

// Fonction pour formater la date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    $now = new DateTime();
    $interval = $now->diff($date);
    
    if ($interval->days == 0) {
        return "Aujourd'hui à " . $date->format('H:i');
    } elseif ($interval->days == 1) {
        return "Hier à " . $date->format('H:i');
    } elseif ($interval->days < 7) {
        return "Il y a " . $interval->days . " jours";
    } else {
        return $date->format('d/m/Y');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Medsense Medical</title>
    
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
        
        .role-badge.medecin {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
            border: 1px solid rgba(33, 150, 243, 0.3);
        }
        
        .role-badge.patient {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.3);
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
        
        .status-badge.en_attente {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }
        
        .section-title {
            color: var(--medical-dark-blue);
            margin-bottom: 2rem;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 1rem;
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
        
        .alert-warning {
            background: #fff8e1;
            color: #856404;
            border-left-color: var(--warning-color);
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
        
        .stat-icon.danger {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
        }
        
        .stat-icon.info {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
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
        
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }
        
        .pending-count {
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-left: 8px;
        }
        
        .sidebar-submenu {
            background-color: rgba(255, 255, 255, 0.05);
            margin-left: 20px;
            border-left: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-submenu .nav-link {
            padding: 10px 20px;
            font-size: 0.9rem;
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
                    <a class="nav-link active" href="admin-dashboard.php">
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
                        <?php if (isset($pendingDoctors['count']) && $pendingDoctors['count'] > 0): ?>
                            <span class="pending-count"><?= $pendingDoctors['count'] ?></span>
                        <?php endif; ?>
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
                                <a class="nav-link" href="admin-doctor-ratings.php">
                                    <i class="fas fa-star"></i>
                                    <span>Évaluations</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admin-doctor-complaints.php">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Réclamations</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="admin-users.php">
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
                <h4 class="mb-0"><i class="fas fa-tachometer-alt medical-icon me-2"></i>Tableau de Bord Administrateur</h4>
                <p class="text-muted mb-0">Vue d'ensemble du système et statistiques</p>
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

            <!-- Alertes importantes -->
            <?php if (isset($pendingDoctors['count']) && $pendingDoctors['count'] > 0): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong><?= $pendingDoctors['count'] ?> médecin(s)</strong> en attente d'approbation.
                        <a href="admin-doctors.php?statut=en_attente" class="alert-link">Voir la liste</a>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistiques principales -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?= $totalUsers ?></div>
                    <div class="stat-label">Total Utilisateurs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-value"><?= $newThisMonth ?></div>
                    <div class="stat-label">Nouveaux ce mois</div>
                </div>
                <?php 
                // Afficher les statistiques par rôle
                foreach ($roleStats as $role): 
                    $icon = '';
                    $color = '';
                    
                    switch($role['role']) {
                        case 'admin':
                            $icon = 'fa-user-shield';
                            $color = 'danger';
                            break;
                        case 'medecin':
                            $icon = 'fa-user-md';
                            $color = 'info';
                            break;
                        case 'patient':
                            $icon = 'fa-user-injured';
                            $color = 'success';
                            break;
                        default:
                            $icon = 'fa-user';
                            $color = 'primary';
                    }
                ?>
                <div class="stat-card">
                    <div class="stat-icon <?= $color ?>">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                    <div class="stat-value"><?= $role['count'] ?></div>
                    <div class="stat-label"><?= ucfirst($role['role']) ?>s</div>
                </div>
                <?php endforeach; ?>
                
                <?php if (isset($pendingDoctors['count'])): ?>
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?= $pendingDoctors['count'] ?></div>
                    <div class="stat-label">Médecins en attente</div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Deux colonnes : Utilisateurs récents et Médecins en attente -->
            <div class="row">
                <!-- Utilisateurs récents -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 animate-fade-in-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-clock me-2"></i>Utilisateurs Récents
                                <span class="badge bg-primary ms-2"><?= count($recentUsers) ?></span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recentUsers)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucun utilisateur récent</h5>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Utilisateur</th>
                                                <th>Rôle</th>
                                                <th>Date d'inscription</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentUsers as $user): 
                                                $initials = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="user-info">
                                                        <div class="user-avatar">
                                                            <?= $initials ?>
                                                        </div>
                                                        <div>
                                                            <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong>
                                                            <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="role-badge <?= $user['role'] ?>">
                                                        <i class="fas fa-<?= $user['role'] === 'admin' ? 'crown' : ($user['role'] === 'medecin' ? 'user-md' : 'user') ?>"></i>
                                                        <?= ucfirst($user['role']) ?>
                                                    </span>
                                                </td>
                                                <td><?= formatDate($user['date_inscription']) ?></td>
                                                <td>
                                                    <span class="status-badge <?= $user['statut'] ?>">
                                                        <i class="fas fa-<?= $user['statut'] === 'actif' ? 'check-circle' : 'pause-circle' ?>"></i>
                                                        <?= ucfirst($user['statut']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-end">
                            <a href="admin-users.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> Voir tous les utilisateurs
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Médecins en attente -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 animate-fade-in-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-md me-2"></i>Médecins en Attente
                                <?php if (isset($pendingDoctors['count']) && $pendingDoctors['count'] > 0): ?>
                                    <span class="badge bg-danger ms-2"><?= $pendingDoctors['count'] ?></span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($pendingDoctors) || (isset($pendingDoctors['success']) && !$pendingDoctors['success']) || (isset($pendingDoctors['count']) && $pendingDoctors['count'] == 0)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucun médecin en attente</h5>
                                    <p class="text-muted small">Tous les médecins ont été approuvés</p>
                                </div>
                            <?php else: 
                                $doctors = isset($pendingDoctors['doctors']) ? $pendingDoctors['doctors'] : $pendingDoctors;
                                if (!is_array($doctors) || count($doctors) == 0): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Aucun médecin en attente</h5>
                                        <p class="text-muted small">Tous les médecins ont été approuvés</p>
                                    </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Médecin</th>
                                                <th>Spécialité</th>
                                                <th>Date d'inscription</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($doctors as $doctor): 
                                                if (is_array($doctor) && isset($doctor['id_utilisateur'])) {
                                                    $doctorId = $doctor['id_utilisateur'];
                                                    $doctorName = htmlspecialchars($doctor['prenom'] . ' ' . $doctor['nom']);
                                                    $doctorEmail = htmlspecialchars($doctor['email']);
                                                    $specialite = isset($doctor['specialite']) ? htmlspecialchars($doctor['specialite']) : 'Non spécifiée';
                                                    $date = formatDate($doctor['date_inscription']);
                                                    $initials = strtoupper(substr($doctor['prenom'], 0, 1) . substr($doctor['nom'], 0, 1));
                                                } else {
                                                    continue;
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="user-info">
                                                        <div class="user-avatar">
                                                            <?= $initials ?>
                                                        </div>
                                                        <div>
                                                            <strong><?= $doctorName ?></strong>
                                                            <div class="text-muted small"><?= $doctorEmail ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= $specialite ?></td>
                                                <td><?= $date ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="admin-approve-doctor.php?id=<?= $doctorId ?>" 
                                                           class="btn btn-outline-success" 
                                                           title="Approuver">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="admin-reject-doctor.php?id=<?= $doctorId ?>" 
                                                           class="btn btn-outline-danger" 
                                                           title="Rejeter">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                        <a href="admin-view-doctor.php?id=<?= $doctorId ?>" 
                                                           class="btn btn-outline-info" 
                                                           title="Voir détails">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-end">
                            <a href="admin-doctors.php?statut=en_attente" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> Gérer tous les médecins en attente
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card animate-fade-in-up">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="admin-create-user.php" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                <span>Nouvel Utilisateur</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="admin-medecins.php" class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-user-md fa-2x mb-2"></i>
                                <span>Gérer Médecins</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="admin-users.php" class="btn btn-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <span>Gérer Utilisateurs</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="admin-reports-statistics.php" class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <span>Rapports</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Actions -->
            <div class="dashboard-actions">
                <a href="admin-users.php" class="btn btn-primary">
                    <i class="fas fa-users me-2"></i> Gestion Utilisateurs
                </a>
                <a href="admin-medecins.php" class="btn btn-success">
                    <i class="fas fa-user-md me-2"></i> Gestion Médecins
                </a>
                <a href="admin-complaints.php" class="btn btn-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Gestion Réclamations
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
        
        // Gérer tous les menus déroulants
        document.querySelectorAll('.collapse').forEach(collapse => {
            collapse.addEventListener('show.bs.collapse', function () {
                this.previousElementSibling.querySelector('.fa-chevron-down').classList.add('fa-chevron-up');
                this.previousElementSibling.querySelector('.fa-chevron-down').classList.remove('fa-chevron-down');
            });
            
            collapse.addEventListener('hide.bs.collapse', function () {
                this.previousElementSibling.querySelector('.fa-chevron-up').classList.add('fa-chevron-down');
                this.previousElementSibling.querySelector('.fa-chevron-up').classList.remove('fa-chevron-up');
            });
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
        });
    </script>
</body>
</html>