<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}
require_once __DIR__ .'/../../controllers/AdminController.php';
$adminController = new AdminController();
$dashboardData = $adminController->dashboard();
?>

<!doctype html>
<html lang="fr">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../assets/img/favicon.png" type="image/png">
    <title>Dashboard Admin - Medcare Medical</title>
    
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
            --primary-color: #2c7be5;
            --secondary-color: #6c757d;
            --success-color: #00d97e;
            --info-color: #39afd1;
            --warning-color: #f6c343;
            --danger-color: #e63757;
            --light-color: #f9fafd;
            --dark-color: #12263f;
            --sidebar-width: 260px;
            --medical-color: #17a2b8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            color: #333;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, #1a5bb8 100%);
            color: white;
            padding: 0;
            z-index: 1000;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar-menu .nav-link:hover, 
        .sidebar-menu .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid white;
        }
        
        .sidebar-menu .nav-link i {
            width: 24px;
            margin-right: 10px;
        }
        
        .sidebar-submenu {
            padding-left: 40px;
        }
        
        .sidebar-submenu .nav-link {
            padding: 8px 20px;
            font-size: 0.9rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        
        .top-bar {
            background-color: white;
            border-radius: 10px;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3ebf6;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .doctor-card {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e3ebf6;
        }
        
        .doctor-card:last-child {
            border-bottom: none;
        }
        
        .doctor-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .doctor-info h6 {
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .doctor-stats {
            display: flex;
            font-size: 0.85rem;
            color: var(--secondary-color);
        }
        
        .doctor-stats span {
            margin-right: 15px;
        }
        
        .appointment-item {
            padding: 15px 0;
            border-bottom: 1px solid #e3ebf6;
        }
        
        .appointment-item:last-child {
            border-bottom: none;
        }
        
        .appointment-patient {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .appointment-details {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .appointment-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-terminé {
            background-color: rgba(0, 217, 126, 0.1);
            color: var(--success-color);
        }
        
        .status-en-cours {
            background-color: rgba(57, 175, 209, 0.1);
            color: var(--info-color);
        }
        
        .status-en-attente {
            background-color: rgba(246, 195, 67, 0.1);
            color: var(--warning-color);
        }
        
        .specialty-item {
            padding: 10px 0;
            border-bottom: 1px solid #e3ebf6;
            display: flex;
            align-items: center;
        }
        
        .specialty-item:last-child {
            border-bottom: none;
        }
        
        .specialty-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .color-primary { background-color: var(--primary-color); }
        .color-success { background-color: var(--success-color); }
        .color-warning { background-color: var(--warning-color); }
        .color-danger { background-color: var(--danger-color); }
        .color-info { background-color: var(--info-color); }
        
        .btn-view-all {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
        }
        
        .btn-view-all:hover {
            text-decoration: underline;
        }
        
        .footer-card {
            background-color: rgba(44, 123, 229, 0.05);
            border-radius: 0 0 10px 10px;
            padding: 15px 20px;
            text-align: center;
        }
        
        /* Dashboard Stats Styles */
        .dashboard-area {
            padding: 120px 0 80px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
            border-left: 4px solid #3f51b5;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.admin {
            border-left-color: #e53e3e;
        }
        
        .stat-card.user {
            border-left-color: #38a169;
        }
        
        .stat-card.new {
            border-left-color: #d69e2e;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 14px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #3f51b5;
        }
        
        .stat-card.admin .stat-icon {
            color: #e53e3e;
        }
        
        .stat-card.user .stat-icon {
            color: #38a169;
        }
        
        .stat-card.new .stat-icon {
            color: #d69e2e;
        }
        
        .recent-users {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: #f8f9fa;
            color: #2d3748;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 12px;
            padding: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #3f51b5;
            color: white;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .role-badge.admin {
            background: #e53e3e;
        }
        
        .role-badge.user {
            background: #38a169;
        }
        
        .dashboard-actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-dashboard {
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        
        .btn-primary {
            background: #3f51b5;
            color: white;
        }
        
        .btn-primary:hover {
            background: #303f9f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(63, 81, 181, 0.3);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }
        
        .section-title {
            color: #2d3748;
            margin-bottom: 2rem;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 1rem;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
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
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
           <<a class="navbar-brand logo_h" href="../home/index.php">
                    <img src="../assets/img/logo.png" alt="logo" style="height: 200px;">
                </a>
        </div>
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#">
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
                    <div class="collapse show" id="appointmentsMenu">
                        <ul class="nav flex-column sidebar-submenu">
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span>Tous les rendez-vous</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span>Nouveau rendez-vous</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span>Calendrier</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-injured"></i>
                        <span>Patients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-md"></i>
                        <span>Médecins</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="admin-users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-prescription"></i>
                        <span>Ordonnances</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Facturation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <small class="text-uppercase text-muted ms-3">Rapports</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-chart-bar"></i>
                        <span>Rapports statistiques</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Audit médical</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Tableau de Bord Administrateur</h4>
            <div class="d-flex align-items-center">
                <div class="input-group me-3" style="width: 300px;">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-0 bg-light" placeholder="Rechercher...">
                </div>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <span class="ms-2">Admin</span>
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

        <!-- Dashboard Content -->
        <section class="dashboard-area">
            <div class="container">
                <div class="animate-fade-in-up">
                    <!-- Stats Grid -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number"><?php echo $dashboardData['stats']['total']; ?></div>
                            <div class="stat-label">Utilisateurs Total</div>
                        </div>
                        
                        <div class="stat-card new">
                            <div class="stat-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="stat-number"><?php echo $dashboardData['stats']['new_this_month']; ?></div>
                            <div class="stat-label">Nouveaux ce mois</div>
                        </div>
                        
                        <?php foreach ($dashboardData['stats']['by_role'] as $role): ?>
                        <div class="stat-card <?php echo $role['role'] === 'admin' ? 'admin' : 'user'; ?>">
                            <div class="stat-icon">
                                <i class="fas <?php echo $role['role'] === 'admin' ? 'fa-shield-alt' : 'fa-user'; ?>"></i>
                            </div>
                            <div class="stat-number"><?php echo $role['count']; ?></div>
                            <div class="stat-label"><?php echo ucfirst($role['role']); ?>s</div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Médecins les plus actifs -->
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Médecins les plus actifs</h5>
                                    <a href="#" class="btn-view-all">Voir tout</a>
                                </div>
                                <div class="card-body">
                                    <div class="doctor-card">
                                        <div class="doctor-avatar">LM</div>
                                        <div class="doctor-info flex-grow-1">
                                            <h6>Dr. Laurent Martin</h6>
                                            <div class="doctor-stats">
                                                <span><i class="fas fa-user-injured me-1"></i> 42 patients</span>
                                                <span><i class="fas fa-star me-1 text-warning"></i> 4.8/5.0</span>
                                            </div>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-outline-primary">Profil</a>
                                    </div>
                                    
                                    <div class="doctor-card">
                                        <div class="doctor-avatar">SB</div>
                                        <div class="doctor-info flex-grow-1">
                                            <h6>Dr. Sophie Bernard</h6>
                                            <div class="doctor-stats">
                                                <span><i class="fas fa-user-injured me-1"></i> 38 patients</span>
                                                <span><i class="fas fa-star me-1 text-warning"></i> 4.9/5.0</span>
                                            </div>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-outline-primary">Profil</a>
                                    </div>
                                    
                                    <div class="doctor-card">
                                        <div class="doctor-avatar">PD</div>
                                        <div class="doctor-info flex-grow-1">
                                            <h6>Dr. Pierre Dubois</h6>
                                            <div class="doctor-stats">
                                                <span><i class="fas fa-user-injured me-1"></i> 35 patients</span>
                                                <span><i class="fas fa-star me-1 text-warning"></i> 4.7/5.0</span>
                                            </div>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-outline-primary">Profil</a>
                                    </div>
                                    
                                    <div class="doctor-card">
                                        <div class="doctor-avatar">ML</div>
                                        <div class="doctor-info flex-grow-1">
                                            <h6>Dr. Marie Leroy</h6>
                                            <div class="doctor-stats">
                                                <span><i class="fas fa-user-injured me-1"></i> 31 patients</span>
                                                <span><i class="fas fa-star me-1 text-warning"></i> 4.8/5.0</span>
                                            </div>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-outline-primary">Profil</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rendez-vous récents -->
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Rendez-vous récents</h5>
                                </div>
                                <div class="card-body">
                                    <div class="appointment-item">
                                        <div class="appointment-patient">M. Jean Dupont</div>
                                        <div class="appointment-details">Consultation générale - Dr. Martin</div>
                                        <div>
                                            <span class="appointment-status status-terminé">Terminé</span>
                                            <span class="text-muted ms-2">10:30</span>
                                        </div>
                                    </div>
                                    
                                    <div class="appointment-item">
                                        <div class="appointment-patient">Mme. Sophie Laurent</div>
                                        <div class="appointment-details">Suivi cardiologie - Dr. Bernard</div>
                                        <div>
                                            <span class="appointment-status status-en-cours">En cours</span>
                                            <span class="text-muted ms-2">11:15</span>
                                        </div>
                                    </div>
                                    
                                    <div class="appointment-item">
                                        <div class="appointment-patient">M. Robert Petit</div>
                                        <div class="appointment-details">Consultation dermatologie - Dr. Leroy</div>
                                        <div>
                                            <span class="appointment-status status-en-attente">En attente</span>
                                            <span class="text-muted ms-2">14:00</span>
                                        </div>
                                    </div>
                                    
                                    <div class="appointment-item">
                                        <div class="appointment-patient">Mme. Claire Moreau</div>
                                        <div class="appointment-details">Bilan annuel - Dr. Dubois</div>
                                        <div>
                                            <span class="appointment-status status-en-attente">Programmé</span>
                                            <span class="text-muted ms-2">15:30</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="footer-card">
                                    <span class="text-muted">12 autres rendez-vous aujourd'hui</span>
                                    <a href="#" class="btn btn-sm btn-primary ms-2">Voir calendrier</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Répartition par spécialité -->
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Répartition par spécialité</h5>
                                    <a href="#" class="btn-view-all">Voir tout</a>
                                </div>
                                <div class="card-body">
                                    <div class="specialty-item">
                                        <div class="specialty-color color-primary"></div>
                                        <div>Médecine générale</div>
                                    </div>
                                    
                                    <div class="specialty-item">
                                        <div class="specialty-color color-success"></div>
                                        <div>Cardiologie</div>
                                    </div>
                                    
                                    <div class="specialty-item">
                                        <div class="specialty-color color-warning"></div>
                                        <div>Dermatologie</div>
                                    </div>
                                    
                                    <div class="specialty-item">
                                        <div class="specialty-color color-danger"></div>
                                        <div>Pédiatrie</div>
                                    </div>
                                    
                                    <div class="specialty-item">
                                        <div class="specialty-color color-info"></div>
                                        <div>Gynécologie</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Users Table -->
                    <div class="recent-users">
                        <h3 class="section-title">Utilisateurs Récents</h3>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Date d'inscription</th>
                                        <th>Rôle</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dashboardData['recentUsers'] as $user): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></td>
                                        <td>
                                            <span class="role-badge <?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn-dashboard btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                                <i class="ti-pencil"></i> Modifier
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Dashboard Actions -->
                    <div class="dashboard-actions">
                        <a href="admin-edit.php" class="btn-dashboard btn-primary">
                            <i class="ti-user"></i> Gérer les utilisateurs
                        </a>
                        <a href="gestion-rendezvous.php" class="btn-dashboard btn-primary">
                            <i class="ti-calendar"></i> Gérer les rendez-vous
                        </a>
                        <a href="../frontoffice/home/index.php" class="btn-dashboard btn-secondary">
                            <i class="ti-home"></i> Retour au site
                        </a>
                        <a href="../../../controllers/logout.php" 
                           class="btn-dashboard btn-secondary" 
                           onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                            <i class="ti-power-off"></i> Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    
   

    <!-- Scripts -->
    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/stellar.js"></script>
    <script src="../assets/js/theme.js"></script>
    
    <script>
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

        // Animation des cartes de statistiques
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
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