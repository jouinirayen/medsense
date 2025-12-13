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

$stats = $dashboardData['stats'] ?? [];
$recentUsers = $dashboardData['recentUsers'] ?? [];
$pendingDoctors = $dashboardData['pendingDoctors'] ?? [];

$totalUsers = $stats['total'] ?? 0;
$newThisMonth = $stats['new_this_month'] ?? 0;
$roleStats = $stats['by_role'] ?? [];

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
   
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
   
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
  
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
</head>
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

.dashboard-widgets {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}

@media (max-width: 1200px) {
    .dashboard-widgets {
        grid-template-columns: 1fr;
    }
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

.dashboard-card-body {
    padding: 24px;
}

.dashboard-card-footer {
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
}

.dashboard-table-responsive {
    overflow-x: auto;
}

.dashboard-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

.dashboard-table th {
    background: #f8fafc;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.dashboard-table td {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.dashboard-table tbody tr:hover {
    background: #f8fafc;
}


.dashboard-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.dashboard-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    flex-shrink: 0;
}

.dashboard-user-name {
    font-weight: 600;
    color: #1e293b;
}

.dashboard-user-email {
    font-size: 0.875rem;
    color: #64748b;
}

.dashboard-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.role-admin {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.role-medecin {
    background: rgba(6, 182, 212, 0.1);
    color: #0891b2;
}

.role-patient {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}

.dashboard-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-actif {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}

.status-inactif {
    background: rgba(148, 163, 184, 0.1);
    color: #64748b;
}

.status-en_attente {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
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

.btn-sm {
    padding: 6px 12px;
    font-size: 0.75rem;
}

.dashboard-quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
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

.dashboard-empty {
    text-align: center;
    padding: 48px 24px;
    color: #64748b;
}

.dashboard-empty i {
    opacity: 0.5;
    margin-bottom: 16px;
}

.dashboard-empty h5 {
    color: #64748b;
    margin-bottom: 8px;
}

.dashboard-empty p {
    font-size: 0.875rem;
    color: #94a3b8;
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
    
    .dashboard-widgets {
        grid-template-columns: 1fr;
    }
    
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .dashboard-quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
    
    .dashboard-quick-actions {
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
    
    .dashboard-actions {
        flex-direction: column;
    }
    
    .dashboard-btn {
        width: 100%;
    }
}</style>
<body class="dashboard-page">

    <div class="dashboard-container">
     
        <header class="dashboard-header">
            <button class="dashboard-menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="d-flex align-items-center gap-3">
                <h1 class="dashboard-title mb-0">Tableau de Bord Administrateur</h1>
                <div class="dashboard-subtitle">Vue d'ensemble du système</div>
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
                          <div class="dashboard-nav-section mt-auto">
                       <li><a class="dashboard-nav-item logout" href="../../../controllers/logout.php" 
                       onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a></li>
                </div>
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
                    <a class="dashboard-nav-item active" href="admin-dashboard.php">
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
                            <a class="dashboard-submenu-item" href="admin-doctor-complaints.php">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Réclamations</span>
                            </a>
                        </div>
                    </div>
                    
                    <a class="dashboard-nav-item" href="admin-users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                    
                   
                    <a class="dashboard-nav-item" href="admin-complaints.php">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Réclamations</span>
                    </a>
                    
                  
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Gestion du Blog</div>
                    
                    
                    <div class="dashboard-nav-item with-submenu">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-blog"></i>
                                <span>Blog</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle"></i>
                </div>
                
               
                    
                    
                
                
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Rapports</div>
                    
                    <!-- Rapports -->
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
                    <a class="dashboard-nav-item logout" href="../../controllers/logout.php" 
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
            <?php if (isset($pendingDoctors['count']) && $pendingDoctors['count'] > 0): ?>
                <div class="dashboard-alert alert-warning">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong><?= $pendingDoctors['count'] ?> médecin(s)</strong> en attente d'approbation.
                        <a href="admin-doctors.php?statut=en_attente" class="alert-link">Voir la liste</a>
                    </div>
                    <button type="button" class="dashboard-alert-close">&times;</button>
                </div>
            <?php endif; ?>
            <div class="dashboard-stats">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-value"><?= $totalUsers ?></div>
                        <div class="dashboard-stat-label">Total Utilisateurs</div>
                    </div>
                </div>
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon success">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-value"><?= $newThisMonth ?></div>
                        <div class="dashboard-stat-label">Nouveaux ce mois</div>
                    </div>
                </div>
                <?php 

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
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon <?= $color ?>">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-value"><?= $role['count'] ?></div>
                        <div class="dashboard-stat-label"><?= ucfirst($role['role']) ?>s</div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (isset($pendingDoctors['count'])): ?>
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-value"><?= $pendingDoctors['count'] ?></div>
                        <div class="dashboard-stat-label">Médecins en attente</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="dashboard-widgets">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">
                            <i class="fas fa-user-clock me-2"></i>Utilisateurs Récents
                            <span class="dashboard-card-badge"><?= count($recentUsers) ?></span>
                        </h3>
                    </div>
                    <div class="dashboard-card-body">
                        <?php if (empty($recentUsers)): ?>
                            <div class="dashboard-empty">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <h5>Aucun utilisateur récent</h5>
                            </div>
                        <?php else: ?>
                            <div class="dashboard-table-responsive">
                                <table class="dashboard-table">
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
                                                <div class="dashboard-user-info">
                                                    <div class="dashboard-user-avatar">
                                                        <?= $initials ?>
                                                    </div>
                                                    <div>
                                                        <div class="dashboard-user-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
                                                        <div class="dashboard-user-email"><?= htmlspecialchars($user['email']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="dashboard-badge role-<?= $user['role'] ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td><?= formatDate($user['date_inscription']) ?></td>
                                            <td>
                                                <span class="dashboard-status status-<?= $user['statut'] ?>">
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
                    <div class="dashboard-card-footer">
                        <a href="admin-users.php" class="dashboard-btn btn-outline">
                            <i class="fas fa-list me-1"></i> Voir tous les utilisateurs
                        </a>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">
                            <i class="fas fa-user-md me-2"></i>Médecins en Attente
                            <?php if (isset($pendingDoctors['count']) && $pendingDoctors['count'] > 0): ?>
                                <span class="dashboard-card-badge danger"><?= $pendingDoctors['count'] ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="dashboard-card-body">
                        <?php if (empty($pendingDoctors) || (isset($pendingDoctors['success']) && !$pendingDoctors['success']) || (isset($pendingDoctors['count']) && $pendingDoctors['count'] == 0)): ?>
                            <div class="dashboard-empty">
                                <i class="fas fa-user-md fa-3x mb-3"></i>
                                <h5>Aucun médecin en attente</h5>
                                <p>Tous les médecins ont été approuvés</p>
                            </div>
                        <?php else: 
                            $doctors = isset($pendingDoctors['doctors']) ? $pendingDoctors['doctors'] : $pendingDoctors;
                            if (!is_array($doctors) || count($doctors) == 0): ?>
                                <div class="dashboard-empty">
                                    <i class="fas fa-user-md fa-3x mb-3"></i>
                                    <h5>Aucun médecin en attente</h5>
                                    <p>Tous les médecins ont été approuvés</p>
                                </div>
                            <?php else: ?>
                            <div class="dashboard-table-responsive">
                                <table class="dashboard-table">
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
                                                <div class="dashboard-user-info">
                                                    <div class="dashboard-user-avatar">
                                                        <?= $initials ?>
                                                    </div>
                                                    <div>
                                                        <div class="dashboard-user-name"><?= $doctorName ?></div>
                                                        <div class="dashboard-user-email"><?= $doctorEmail ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= $specialite ?></td>
                                            <td><?= $date ?></td>
                                            <td>
                                                <div class="dashboard-actions">
                                                    <a href="admin-approve-doctor.php?id=<?= $doctorId ?>" 
                                                       class="dashboard-btn btn-success btn-sm" 
                                                       title="Approuver">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="admin-reject-doctor.php?id=<?= $doctorId ?>" 
                                                       class="dashboard-btn btn-danger btn-sm" 
                                                       title="Rejeter">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                    <a href="admin-view-doctor.php?id=<?= $doctorId ?>" 
                                                       class="dashboard-btn btn-info btn-sm" 
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
                    <div class="dashboard-card-footer">
                        <a href="admin-medecins.php?statut=en_attente" class="dashboard-btn btn-outline">
                            <i class="fas fa-list me-1"></i> Gérer tous les médecins en attente
                        </a>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">
                        <i class="fas fa-bolt me-2"></i>Actions Rapides
                    </h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="dashboard-quick-actions">
                        <a href="admin-create-user.php" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="dashboard-quick-action-text">Nouvel Utilisateur</div>
                        </a>
                        <a href="admin-medecins.php" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="dashboard-quick-action-text">Gérer Médecins</div>
                        </a>
                        <a href="admin-users.php" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="dashboard-quick-action-text">Gérer Utilisateurs</div>
                        </a>
                        <a href="admin-reports-statistics.php" class="dashboard-quick-action">
                            <div class="dashboard-quick-action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="dashboard-quick-action-text">Rapports</div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="dashboard-actions">
                <a href="admin-users.php" class="dashboard-btn btn-primary">
                    <i class="fas fa-users me-2"></i> Gestion Utilisateurs
                </a>
                <a href="admin-medecins.php" class="dashboard-btn btn-success">
                    <i class="fas fa-user-md me-2"></i> Gestion Médecins
                </a>
                <a href="admin-complaints.php" class="dashboard-btn btn-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Gestion Réclamations
                </a>
                <a href="../frontoffice/home/index.php" class="dashboard-btn btn-outline">
                    <i class="fas fa-home me-2"></i> Retour au site
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