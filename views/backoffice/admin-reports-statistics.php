<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();

// Récupérer les filtres
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // Début du mois par défaut
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Aujourd'hui par défaut
$reportType = $_GET['report_type'] ?? 'overview';

// Obtenir les statistiques d'approbation
$approvalStats = $adminController->getApprovalStats();
$approvalStatsData = $approvalStats['success'] ? $approvalStats : [];

// Obtenir tous les utilisateurs pour certaines statistiques
$usersResult = $adminController->getAllUsers();
$allUsers = $usersResult['success'] ? $usersResult['users'] : [];

// Obtenir tous les médecins
$doctorsResult = $adminController->getAllDoctors();
$allDoctors = $doctorsResult['success'] ? $doctorsResult['doctors'] : [];

// Calculer les statistiques
$stats = [
    'total_users' => count($allUsers),
    'total_doctors' => count($allDoctors),
    'active_users' => count(array_filter($allUsers, fn($u) => $u['statut'] === 'actif')),
    'pending_doctors' => count(array_filter($allDoctors, fn($d) => $d['statut'] === 'en_attente')),
    'rejected_doctors' => count(array_filter($allDoctors, fn($d) => $d['statut'] === 'rejeté')),
    'suspended_doctors' => count(array_filter($allDoctors, fn($d) => $d['statut'] === 'suspendu')),
];

// Statistiques par rôle
$roles = ['admin', 'medecin', 'user', 'patient'];
$roleStats = [];
foreach ($roles as $role) {
    $roleStats[$role] = count(array_filter($allUsers, fn($u) => $u['role'] === $role));
}

// Statistiques par statut
$statuts = ['actif', 'inactif', 'en_attente', 'rejeté', 'suspendu'];
$statutStats = [];
foreach ($statuts as $statut) {
    $statutStats[$statut] = count(array_filter($allUsers, fn($u) => $u['statut'] === $statut));
}

// Messages d'alerte
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fonction pour formater les nombres
function formatNumber($number) {
    return number_format($number, 0, ',', ' ');
}

// Fonction pour obtenir la couleur selon le rôle
function getRoleColor($role) {
    $colors = [
        'admin' => '#dc3545', // rouge
        'medecin' => '#17a2b8', // bleu clair
        'user' => '#28a745', // vert
        'patient' => '#ffc107', // jaune
        'moderator' => '#6c757d' // gris
    ];
    return $colors[$role] ?? '#007bff'; // bleu par défaut
}

// Fonction pour obtenir la couleur selon le statut
function getStatusColor($status) {
    $colors = [
        'actif' => '#28a745', // vert
        'inactif' => '#6c757d', // gris
        'en_attente' => '#ffc107', // jaune
        'rejeté' => '#dc3545', // rouge
        'suspendu' => '#343a40' // noir
    ];
    return $colors[$status] ?? '#007bff'; // bleu par défaut
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports Statistiques - Medsense Medical</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        
        .chart-container {
            height: 300px;
            margin-top: 20px;
            position: relative;
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
        
        .export-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin-right: 15px;
            margin-bottom: 5px;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            margin-top: 15px;
            justify-content: center;
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
                                <a class="nav-link" href="admin-doctor-ratings.php">
                                    <i class="fas fa-star"></i>
                                    <span>Évaluations</span>
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
                
                <li class="nav-item">
                    <a class="nav-link" href="admin-complaints.php">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Réclamations</span>
                    </a>
                </li>
                
                <li class="nav-item mt-3">
                    <small class="text-uppercase text-muted ms-3">Gestion du Blog</small>
                </li>
                
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
                
                <li class="nav-item">
                    <a class="nav-link" href="admin-reviews.php">
                        <i class="fas fa-star"></i>
                        <span>Reviews & Avis</span>
                    </a>
                </li>
                
                <li class="nav-item mt-3">
                    <small class="text-uppercase text-muted ms-3">Rapports</small>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link active" href="admin-reports-statistics.php">
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
                <h4 class="mb-0"><i class="fas fa-chart-pie medical-icon me-2"></i>Rapports Statistiques</h4>
                <p class="text-muted mb-0">Analyse et visualisation des données du système</p>
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

            <!-- Filtres de rapport -->
            <div class="card mb-4 <?= ($startDate != date('Y-m-01') || $endDate != date('Y-m-d')) ? 'filter-active' : '' ?>">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres de Rapport
                        <?php if ($startDate != date('Y-m-01') || $endDate != date('Y-m-d')): ?>
                            <span class="filter-indicator"></span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?= htmlspecialchars($startDate) ?>" max="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?= htmlspecialchars($endDate) ?>" max="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="report_type" class="form-label">Type de rapport</label>
                                <select class="form-select" id="report_type" name="report_type">
                                    <option value="overview" <?= $reportType === 'overview' ? 'selected' : '' ?>>Vue d'ensemble</option>
                                    <option value="users" <?= $reportType === 'users' ? 'selected' : '' ?>>Utilisateurs</option>
                                    <option value="doctors" <?= $reportType === 'doctors' ? 'selected' : '' ?>>Médecins</option>
                                    <option value="appointments" <?= $reportType === 'appointments' ? 'selected' : '' ?>>Rendez-vous</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-chart-bar me-1"></i> Générer
                                    </button>
                                    <?php if ($startDate != date('Y-m-01') || $endDate != date('Y-m-d')): ?>
                                        <a href="admin-reports-statistics.php" class="btn btn-outline-secondary" title="Réinitialiser">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistiques principales -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?= formatNumber($stats['total_users']) ?></div>
                    <div class="stat-label">Total Utilisateurs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-value"><?= formatNumber($stats['total_doctors']) ?></div>
                    <div class="stat-label">Médecins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value"><?= formatNumber($stats['active_users']) ?></div>
                    <div class="stat-label">Utilisateurs Actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?= formatNumber($stats['pending_doctors']) ?></div>
                    <div class="stat-label">Médecins en Attente</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value"><?= formatNumber($stats['rejected_doctors']) ?></div>
                    <div class="stat-label">Médecins Rejetés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon secondary">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div class="stat-value"><?= formatNumber($stats['suspended_doctors']) ?></div>
                    <div class="stat-label">Médecins Suspendus</div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row mb-4">
                <!-- Répartition par rôle -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 animate-fade-in-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie me-2"></i>Répartition par Rôle
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="roleChart"></canvas>
                            </div>
                            <div class="chart-legend">
                                <?php foreach ($roleStats as $role => $count): ?>
                                    <?php if ($count > 0): ?>
                                        <div class="legend-item">
                                            <div class="legend-color" style="background-color: <?= getRoleColor($role) ?>"></div>
                                            <span class="small"><?= ucfirst($role) ?>: <?= formatNumber($count) ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Répartition par statut -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 animate-fade-in-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Répartition par Statut
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                            <div class="chart-legend">
                                <?php foreach ($statutStats as $statut => $count): ?>
                                    <?php if ($count > 0): ?>
                                        <div class="legend-item">
                                            <div class="legend-color" style="background-color: <?= getStatusColor($statut) ?>"></div>
                                            <span class="small"><?= ucfirst($statut) ?>: <?= formatNumber($count) ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableaux détaillés -->
            <div class="row">
                <!-- Statistiques par rôle -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 animate-fade-in-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>Statistiques par Rôle
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Rôle</th>
                                            <th>Nombre</th>
                                            <th>Pourcentage</th>
                                            <th>Couleur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalRoles = array_sum($roleStats);
                                        foreach ($roleStats as $role => $count):
                                            if ($count > 0):
                                                $percentage = $totalRoles > 0 ? round(($count / $totalRoles) * 100, 1) : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?= ucfirst($role) ?></strong>
                                            </td>
                                            <td><?= formatNumber($count) ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $percentage ?>%; background-color: <?= getRoleColor($role) ?>"
                                                         aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= $percentage ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="legend-color" style="background-color: <?= getRoleColor($role) ?>; margin: 0 auto;"></div>
                                            </td>
                                        </tr>
                                        <?php endif; endforeach; ?>
                                        <tr class="table-secondary">
                                            <td><strong>Total</strong></td>
                                            <td><strong><?= formatNumber($totalRoles) ?></strong></td>
                                            <td><strong>100%</strong></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques par statut -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 animate-fade-in-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line me-2"></i>Statistiques par Statut
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Statut</th>
                                            <th>Nombre</th>
                                            <th>Pourcentage</th>
                                            <th>Couleur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalStatuts = array_sum($statutStats);
                                        foreach ($statutStats as $statut => $count):
                                            if ($count > 0):
                                                $percentage = $totalStatuts > 0 ? round(($count / $totalStatuts) * 100, 1) : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?= ucfirst($statut) ?></strong>
                                            </td>
                                            <td><?= formatNumber($count) ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $percentage ?>%; background-color: <?= getStatusColor($statut) ?>"
                                                         aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= $percentage ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="legend-color" style="background-color: <?= getStatusColor($statut) ?>; margin: 0 auto;"></div>
                                            </td>
                                        </tr>
                                        <?php endif; endforeach; ?>
                                        <tr class="table-secondary">
                                            <td><strong>Total</strong></td>
                                            <td><strong><?= formatNumber($totalStatuts) ?></strong></td>
                                            <td><strong>100%</strong></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Options d'export -->
            <div class="card animate-fade-in-up">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-export me-2"></i>Export des Données
                    </h5>
                </div>
                <div class="card-body">
                    <div class="export-options">
                        <a href="admin-export-excel.php?type=statistics&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" 
                           class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i> Exporter en Excel
                        </a>
                        <a href="admin-export-pdf.php?type=statistics&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" 
                           class="btn btn-danger">
                            <i class="fas fa-file-pdf me-1"></i> Exporter en PDF
                        </a>
                        <a href="javascript:void(0);" onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print me-1"></i> Imprimer le Rapport
                        </a>
                        <a href="admin-reports-statistics.php?start_date=<?= date('Y-m-01') ?>&end_date=<?= date('Y-m-d') ?>&report_type=overview" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i> Ce mois
                        </a>
                        <a href="admin-reports-statistics.php?start_date=<?= date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= date('Y-m-d') ?>&report_type=overview" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-calendar me-1"></i> 30 derniers jours
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navigation Actions -->
            <div class="dashboard-actions mt-4">
                <a href="admin-dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-tachometer-alt me-2"></i> Retour au Dashboard
                </a>
                <a href="admin-reports-financial.php" class="btn btn-success">
                    <i class="fas fa-money-bill-wave me-2"></i> Rapports Financiers
                </a>
                <a href="admin-reports-medical.php" class="btn btn-primary">
                    <i class="fas fa-stethoscope me-2"></i> Rapports Médicaux
                </a>
                <a href="admin-audit.php" class="btn btn-warning">
                    <i class="fas fa-clipboard-list me-2"></i> Audit Médical
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
        
        // Données pour les graphiques
        const roleData = {
            labels: [<?php echo '"' . implode('","', array_map('ucfirst', array_keys(array_filter($roleStats)))) . '"'; ?>],
            datasets: [{
                data: [<?php echo implode(',', array_values(array_filter($roleStats))); ?>],
                backgroundColor: [
                    <?php foreach ($roleStats as $role => $count): ?>
                        <?php if ($count > 0): ?>
                            '<?= getRoleColor($role) ?>',
                        <?php endif; ?>
                    <?php endforeach; ?>
                ],
                borderWidth: 1
            }]
        };

        const statusData = {
            labels: [<?php echo '"' . implode('","', array_map('ucfirst', array_keys(array_filter($statutStats)))) . '"'; ?>],
            datasets: [{
                data: [<?php echo implode(',', array_values(array_filter($statutStats))); ?>],
                backgroundColor: [
                    <?php foreach ($statutStats as $statut => $count): ?>
                        <?php if ($count > 0): ?>
                            '<?= getStatusColor($statut) ?>',
                        <?php endif; ?>
                    <?php endforeach; ?>
                ],
                borderWidth: 1
            }]
        };

        // Configuration des graphiques
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.formattedValue + ' (' + ((context.parsed / context.dataset.data.reduce((a, b) => a + b, 0)) * 100).toFixed(1) + '%)';
                            return label;
                        }
                    }
                }
            }
        };

        // Initialiser le graphique des rôles
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        const roleChart = new Chart(roleCtx, {
            type: 'pie',
            data: roleData,
            options: chartOptions
        });

        // Initialiser le graphique des statuts
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: statusData,
            options: chartOptions
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

        // Validation des dates
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate && startDate > endDate) {
                e.preventDefault();
                alert('La date de début ne peut pas être postérieure à la date de fin.');
                return false;
            }
        });

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

        // Fonction pour télécharger les graphiques
        function downloadChart(chartId, filename) {
            const link = document.createElement('a');
            link.download = filename;
            link.href = document.getElementById(chartId).toDataURL('image/png');
            link.click();
        }
    </script>
</body>
</html>