<?php
/**
 * Page d'audit médical - Admin
 * admin-audit.php
 */

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();

// Récupération des paramètres de filtre
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$action_type = $_GET['action_type'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$entity_type = $_GET['entity_type'] ?? '';
$export = $_GET['export'] ?? '';

// Récupération des données d'audit
$auditResult = $adminController->getAuditLogs([
    'date_from' => $date_from,
    'date_to' => $date_to,
    'action_type' => $action_type,
    'user_id' => $user_id,
    'entity_type' => $entity_type
]);

$auditLogs = $auditResult['success'] ? $auditResult['logs'] : [];
$stats = $auditResult['stats'] ?? [];

// Export PDF si demandé
if ($export === 'pdf') {
    $adminController->exportAuditToPDF([
        'date_from' => $date_from,
        'date_to' => $date_to,
        'action_type' => $action_type,
        'user_id' => $user_id,
        'entity_type' => $entity_type
    ]);
    exit;
}

// Export Excel si demandé
if ($export === 'excel') {
    $adminController->exportAuditToExcel([
        'date_from' => $date_from,
        'date_to' => $date_to,
        'action_type' => $action_type,
        'user_id' => $user_id,
        'entity_type' => $entity_type
    ]);
    exit;
}

// Récupération des utilisateurs pour le filtre
$usersResult = $adminController->manageUsers('list');
$allUsers = $usersResult['success'] ? $usersResult['users'] : [];

// Messages de session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Médical - Medsense Medical</title>
    
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
        
        .btn-danger {
            background: var(--danger-color);
            color: white;
        }
        
        .btn-warning {
            background: var(--warning-color);
            color: white;
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
        
        .audit-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            gap: 5px;
        }
        
        .audit-badge.create {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .audit-badge.update {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
            border: 1px solid rgba(33, 150, 243, 0.3);
        }
        
        .audit-badge.delete {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
        
        .audit-badge.login {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }
        
        .audit-badge.view {
            background: rgba(158, 158, 158, 0.1);
            color: #757575;
            border: 1px solid rgba(158, 158, 158, 0.3);
        }
        
        .entity-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #f0f0f0;
            color: #666;
        }
        
        .filter-active {
            background-color: rgba(26, 115, 232, 0.05);
            border-left: 4px solid var(--medical-blue);
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
            border-top: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }
        
        .stat-card.total {
            border-top-color: var(--medical-blue);
        }
        
        .stat-card.create {
            border-top-color: var(--success-color);
        }
        
        .stat-card.update {
            border-top-color: var(--info-color);
        }
        
        .stat-card.delete {
            border-top-color: var(--danger-color);
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
        
        .stat-icon.total {
            background: rgba(26, 115, 232, 0.1);
            color: var(--medical-blue);
        }
        
        .stat-icon.create {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }
        
        .stat-icon.update {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
        }
        
        .stat-icon.delete {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
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
            
            .mobile-menu-btn {
                display: block;
            }
        }
        
        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 20px;
            border-left: 2px solid var(--medical-blue);
        }
        
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -7px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--medical-blue);
        }
        
        .timeline-item.create:before {
            background: var(--success-color);
            border-color: var(--success-color);
        }
        
        .timeline-item.update:before {
            background: var(--info-color);
            border-color: var(--info-color);
        }
        
        .timeline-item.delete:before {
            background: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .timeline-item.login:before {
            background: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .pre-formatted {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.85rem;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        
        .data-diff {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        
        .data-diff .added {
            background: #d4edda;
            color: #155724;
            padding: 2px 4px;
            border-radius: 2px;
        }
        
        .data-diff .removed {
            background: #f8d7da;
            color: #721c24;
            padding: 2px 4px;
            border-radius: 2px;
        }
    </style>
</head>
<body>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar (identique à admin-users.php) -->
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
                    <a class="nav-link" href="admin-appointments.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Rendez-vous</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="admin-patients.php">
                        <i class="fas fa-user-injured"></i>
                        <span>Patients</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="admin-doctors.php">
                        <i class="fas fa-user-md"></i>
                        <span>Médecins</span>
                    </a>
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
                                <a class="nav-link active" href="admin-audit.php">
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
                <h4 class="mb-0"><i class="fas fa-clipboard-list medical-icon me-2"></i>Audit Médical</h4>
                <p class="text-muted mb-0">Suivi des activités et conformité</p>
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

            <!-- Statistiques -->
            <div class="stats-container">
                <div class="stat-card total">
                    <div class="stat-icon total">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                    <div class="stat-label">Total Activités</div>
                </div>
                <div class="stat-card create">
                    <div class="stat-icon create">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="stat-value"><?= $stats['creations'] ?? 0 ?></div>
                    <div class="stat-label">Créations</div>
                </div>
                <div class="stat-card update">
                    <div class="stat-icon update">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-value"><?= $stats['updates'] ?? 0 ?></div>
                    <div class="stat-label">Modifications</div>
                </div>
                <div class="stat-card delete">
                    <div class="stat-icon delete">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <div class="stat-value"><?= $stats['deletions'] ?? 0 ?></div>
                    <div class="stat-label">Suppressions</div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card mb-4 <?= ($date_from || $date_to || $action_type || $user_id || $entity_type) ? 'filter-active' : '' ?>">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres d'Audit
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="auditFilterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?= htmlspecialchars($date_from) ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="<?= htmlspecialchars($date_to) ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="action_type" class="form-label">Type d'action</label>
                                <select class="form-select" id="action_type" name="action_type">
                                    <option value="">Toutes les actions</option>
                                    <option value="create" <?= $action_type === 'create' ? 'selected' : '' ?>>Création</option>
                                    <option value="update" <?= $action_type === 'update' ? 'selected' : '' ?>>Modification</option>
                                    <option value="delete" <?= $action_type === 'delete' ? 'selected' : '' ?>>Suppression</option>
                                    <option value="login" <?= $action_type === 'login' ? 'selected' : '' ?>>Connexion</option>
                                    <option value="view" <?= $action_type === 'view' ? 'selected' : '' ?>>Consultation</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="entity_type" class="form-label">Type d'entité</label>
                                <select class="form-select" id="entity_type" name="entity_type">
                                    <option value="">Toutes les entités</option>
                                    <option value="user" <?= $entity_type === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                    <option value="patient" <?= $entity_type === 'patient' ? 'selected' : '' ?>>Patient</option>
                                    <option value="doctor" <?= $entity_type === 'doctor' ? 'selected' : '' ?>>Médecin</option>
                                    <option value="appointment" <?= $entity_type === 'appointment' ? 'selected' : '' ?>>Rendez-vous</option>
                                    <option value="prescription" <?= $entity_type === 'prescription' ? 'selected' : '' ?>>Ordonnance</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="user_id" class="form-label">Utilisateur</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">Tous les utilisateurs</option>
                                    <?php foreach ($allUsers as $user): ?>
                                        <?php $userId = $user['id_utilisateur'] ?? $user['id']; ?>
                                        <option value="<?= $userId ?>" <?= $user_id == $userId ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i> Appliquer les filtres
                                        </button>
                                        <?php if ($date_from || $date_to || $action_type || $user_id || $entity_type): ?>
                                            <a href="admin-audit.php" class="btn btn-outline-secondary ms-2">
                                                <i class="fas fa-times me-1"></i> Réinitialiser
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="admin-audit.php?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" 
                                           class="btn btn-success">
                                            <i class="fas fa-file-excel me-1"></i> Excel
                                        </a>
                                        <a href="admin-audit.php?<?= http_build_query(array_merge($_GET, ['export' => 'pdf'])) ?>" 
                                           class="btn btn-danger">
                                            <i class="fas fa-file-pdf me-1"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des logs d'audit -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Journal d'Audit
                        <span class="badge bg-primary ms-2"><?= count($auditLogs) ?></span>
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <?php if (!empty($auditLogs)): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleDetails">
                                <i class="fas fa-eye me-1"></i> Afficher les détails
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($auditLogs)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun log d'audit trouvé</h5>
                            <p class="text-muted">Modifiez vos critères de recherche ou revenez plus tard</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="150">Date & Heure</th>
                                        <th width="100">Type</th>
                                        <th width="100">Entité</th>
                                        <th>Utilisateur</th>
                                        <th>Description</th>
                                        <th width="150">Adresse IP</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($auditLogs as $log): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d/m/Y', strtotime($log['created_at'])) ?><br>
                                                <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="audit-badge <?= $log['action_type'] ?>">
                                                <i class="fas fa-<?= $this->getActionIcon($log['action_type']) ?>"></i>
                                                <?= ucfirst($log['action_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="entity-badge">
                                                <?= ucfirst($log['entity_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar">
                                                    <?= strtoupper(substr($log['user_prenom'], 0, 1) . substr($log['user_nom'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($log['user_prenom'] . ' ' . $log['user_nom']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($log['user_email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($log['description']) ?>
                                            <?php if ($log['entity_id']): ?>
                                                <br><small class="text-muted">ID: <?= $log['entity_id'] ?></small>
                                            <?php endif; ?>
                                            
                                            <!-- Détails cachés -->
                                            <div class="audit-details mt-2" style="display: none;">
                                                <?php if (!empty($log['old_values'])): ?>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Anciennes valeurs:</small>
                                                        <div class="pre-formatted"><?= htmlspecialchars(json_encode(json_decode($log['old_values']), JSON_PRETTY_PRINT)) ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($log['new_values'])): ?>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Nouvelles valeurs:</small>
                                                        <div class="pre-formatted"><?= htmlspecialchars(json_encode(json_decode($log['new_values']), JSON_PRETTY_PRINT)) ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($log['changes'])): ?>
                                                    <div>
                                                        <small class="text-muted d-block">Modifications:</small>
                                                        <div class="data-diff">
                                                            <?= $this->formatChanges($log['changes']) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($log['ip_address']) ?></small>
                                            <?php if (!empty($log['user_agent'])): ?>
                                                <br><small class="text-muted"><?= $this->getBrowserIcon($log['user_agent']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary toggle-details-btn" 
                                                    data-log-id="<?= $log['id'] ?>">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                            <?php if (!empty($log['old_values']) || !empty($log['new_values'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning compare-btn" 
                                                        data-log-id="<?= $log['id'] ?>"
                                                        data-old-values='<?= htmlspecialchars($log['old_values'] ?? '{}') ?>'
                                                        data-new-values='<?= htmlspecialchars($log['new_values'] ?? '{}') ?>'>
                                                    <i class="fas fa-code-compare"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Affichage des logs du <?= date('d/m/Y', strtotime($date_from)) ?> au <?= date('d/m/Y', strtotime($date_to)) ?>
                        </small>
                        <div>
                            <?php if (count($auditLogs) > 50): ?>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> Charger plus
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vue timeline alternative -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-stream me-2"></i>Vue Chronologique
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($auditLogs)): ?>
                        <p class="text-muted text-center">Aucune activité à afficher</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach (array_slice($auditLogs, 0, 10) as $log): ?>
                                <div class="timeline-item <?= $log['action_type'] ?>">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?= htmlspecialchars($log['user_prenom'] . ' ' . $log['user_nom']) ?></strong>
                                            <span class="audit-badge <?= $log['action_type'] ?> ms-2">
                                                <?= ucfirst($log['action_type']) ?>
                                            </span>
                                            <span class="entity-badge ms-2">
                                                <?= ucfirst($log['entity_type']) ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($log['description']) ?></p>
                                    <?php if ($log['entity_id']): ?>
                                        <small class="text-muted">ID: <?= $log['entity_id'] ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Navigation Actions -->
            <div class="dashboard-actions mt-4">
                <a href="admin-dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-tachometer-alt me-2"></i> Retour au Dashboard
                </a>
                <a href="admin-reports-statistics.php" class="btn btn-outline-primary">
                    <i class="fas fa-chart-pie me-2"></i> Statistiques
                </a>
                <a href="../frontoffice/home/index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i> Retour au site
                </a>
            </div>
        </div>
    </div>

    <!-- Modal pour comparaison détaillée -->
    <div class="modal fade" id="compareModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Comparaison des modifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Anciennes valeurs</h6>
                            <div id="oldValues" class="pre-formatted" style="height: 300px;"></div>
                        </div>
                        <div class="col-md-6">
                            <h6>Nouvelles valeurs</h6>
                            <div id="newValues" class="pre-formatted" style="height: 300px;"></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6>Différences</h6>
                        <div id="diffValues" class="data-diff" style="height: 200px;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="printComparison">
                        <i class="fas fa-print me-1"></i> Imprimer
                    </button>
                </div>
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

        // Toggle détails des logs
        document.querySelectorAll('.toggle-details-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const logId = this.dataset.logId;
                const details = this.closest('tr').querySelector('.audit-details');
                details.style.display = details.style.display === 'none' ? 'block' : 'none';
                
                if (details.style.display === 'block') {
                    this.innerHTML = '<i class="fas fa-times"></i>';
                    this.classList.add('btn-danger');
                    this.classList.remove('btn-outline-primary');
                } else {
                    this.innerHTML = '<i class="fas fa-info-circle"></i>';
                    this.classList.add('btn-outline-primary');
                    this.classList.remove('btn-danger');
                }
            });
        });

        // Toggle tous les détails
        document.getElementById('toggleDetails').addEventListener('click', function() {
            const allDetails = document.querySelectorAll('.audit-details');
            const isVisible = allDetails[0].style.display === 'block';
            
            allDetails.forEach(details => {
                details.style.display = isVisible ? 'none' : 'block';
            });
            
            document.querySelectorAll('.toggle-details-btn').forEach(btn => {
                if (isVisible) {
                    btn.innerHTML = '<i class="fas fa-info-circle"></i>';
                    btn.classList.add('btn-outline-primary');
                    btn.classList.remove('btn-danger');
                } else {
                    btn.innerHTML = '<i class="fas fa-times"></i>';
                    btn.classList.add('btn-danger');
                    btn.classList.remove('btn-outline-primary');
                }
            });
            
            this.innerHTML = isVisible ? 
                '<i class="fas fa-eye me-1"></i> Afficher les détails' :
                '<i class="fas fa-eye-slash me-1"></i> Masquer les détails';
        });

        // Modal de comparaison
        document.querySelectorAll('.compare-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const oldValues = JSON.parse(this.dataset.oldValues);
                const newValues = JSON.parse(this.dataset.newValues);
                
                document.getElementById('oldValues').textContent = 
                    JSON.stringify(oldValues, null, 2);
                document.getElementById('newValues').textContent = 
                    JSON.stringify(newValues, null, 2);
                
                // Calculer et afficher les différences
                const diff = calculateDiff(oldValues, newValues);
                document.getElementById('diffValues').innerHTML = diff;
                
                const modal = new bootstrap.Modal(document.getElementById('compareModal'));
                modal.show();
            });
        });

        // Calculer les différences
        function calculateDiff(oldObj, newObj) {
            let diffHtml = '';
            
            // Comparer les clés
            const allKeys = new Set([...Object.keys(oldObj || {}), ...Object.keys(newObj || {})]);
            
            allKeys.forEach(key => {
                const oldVal = oldObj?.[key];
                const newVal = newObj?.[key];
                
                if (JSON.stringify(oldVal) !== JSON.stringify(newVal)) {
                    diffHtml += `<div class="mb-2">
                        <strong>${key}:</strong><br>
                        ${oldVal !== undefined ? 
                            `<span class="removed">${JSON.stringify(oldVal)}</span> → ` : 
                            'Création: '}
                        ${newVal !== undefined ? 
                            `<span class="added">${JSON.stringify(newVal)}</span>` : 
                            '<span class="removed">Supprimé</span>'}
                    </div>`;
                }
            });
            
            return diffHtml || '<p class="text-muted">Aucune différence</p>';
        }

        // Imprimer la comparaison
        document.getElementById('printComparison').addEventListener('click', function() {
            window.print();
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
        document.getElementById('auditFilterForm').addEventListener('submit', function(e) {
            const dateFrom = document.getElementById('date_from').value;
            const dateTo = document.getElementById('date_to').value;
            
            if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
                e.preventDefault();
                alert('La date de début doit être antérieure à la date de fin');
                return false;
            }
        });

        // Exporter les filtres actuels
        window.exportFilters = function(format) {
            const form = document.getElementById('auditFilterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            
            window.location.href = `admin-audit.php?${params.toString()}&export=${format}`;
        };

        // Initialiser les tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>