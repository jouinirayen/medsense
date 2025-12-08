<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? "Admin Panel"); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem 1.5rem 1.5rem;
            border-bottom: 1px solid #334155;
            text-align: center;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: white;
        }

        .sidebar-logo img {
            height: 40px;
            width: auto;
        }

        .logo-text {
            text-align: left;
        }

        .logo-text h1 {
            font-size: 1.3rem;
            margin-bottom: 0.2rem;
            color: #60a5fa;
        }

        .logo-text p {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 0 1.5rem 0.5rem;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 0.2rem;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1.5rem;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border-left-color: #60a5fa;
        }

        .nav-links a.active {
            background: rgba(96, 165, 250, 0.1);
            color: #60a5fa;
            border-left-color: #60a5fa;
        }

        .nav-links a i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-links a span {
            flex: 1;
        }

        .badge {
            background: #ef4444;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid #334155;
            margin-top: auto;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #60a5fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .user-details h4 {
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
        }

        .user-details p {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        /* Main Content Area */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            background: #f8fafc;
            min-height: 100vh;
        }

        .admin-header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title h1 {
            color: #1e293b;
            font-size: 1.5rem;
            margin-bottom: 0.2rem;
        }

        .header-title p {
            color: #64748b;
            font-size: 0.9rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .admin-content {
            padding: 2rem;
        }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: #1e293b;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: 250px;
            }
            
            .admin-main {
                margin-left: 250px;
            }
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .admin-header {
                padding: 1rem;
            }
            
            .admin-content {
                padding: 1rem;
            }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="" class="sidebar-logo">
                    <img src="../../../images/logo.svg" alt="MedSense Logo">
                    <div class="logo-text">
                        <h1>MedSense</h1>
                        <p>Admin Panel</p>
                    </div>
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h3 class="nav-title">Tableau de Bord</h3>
                    <ul class="nav-links">
                        <li>
                            <a href=" " class="<?= ($current_page == 'front.php') ? 'active' : '' ?>">
                                <i class="fas fa-home"></i>
                                <span>Accueil</span>
                            </a>
                        </li>
                        <li>
                            <a href=" " class="<?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard Admin</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3 class="nav-title">Gestion des Réclamations</h3>
                    <ul class="nav-links">
                        <li>
                            <a href="admin_reclamations.php" class="<?= ($current_page == 'admin_reclamations.php') ? 'active' : '' ?>">
                                <i class="fas fa-list-alt"></i>
                                <span>Toutes les Réclamations</span>
                            </a>
                        </li>
                        <li>
                            <a href="admin_reclamations.php?filter=urgence" class="<?= ($_GET['filter'] ?? '') == 'urgence' ? 'active' : '' ?>">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Réclamations Urgentes</span>
                                <span class="badge">🚨</span>
                            </a>
                        </li>
                        <li>
                            <a href="admin_reclamations.php?filter=normal" class="<?= ($_GET['filter'] ?? '') == 'normal' ? 'active' : '' ?>">
                                <i class="fas fa-file-alt"></i>
                                <span>Réclamations Normales</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3 class="nav-title">Statistiques & Rapports</h3>
                    <ul class="nav-links">
                        <li>
                            <a href="admin_statistics.php" class="<?= ($current_page == 'admin_statistics.php') ? 'active' : '' ?>">
                                <i class="fas fa-chart-bar"></i>
                                <span>Statistiques</span>
                            </a>
                        </li>
                        <li>
                            <a href=" " class="<?= ($current_page == 'admin_reports.php') ? 'active' : '' ?>">
                                <i class="fas fa-chart-pie"></i>
                                <span>Rapports</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3 class="nav-title">Gestion Utilisateurs</h3>
                    <ul class="nav-links">
                        <li>
                            <a href=" " class="<?= ($current_page == 'admin_users.php') ? 'active' : '' ?>">
                                <i class="fas fa-users"></i>
                                <span>Utilisateurs</span>
                            </a>
                        </li>
                        <li>
                            <a href=" " class="<?= ($current_page == 'admin_roles.php') ? 'active' : '' ?>">
                                <i class="fas fa-user-shield"></i>
                                <span>Rôles & Permissions</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3 class="nav-title">Paramètres</h3>
                    <ul class="nav-links">
                        <li>
                            <a href=" " class="<?= ($current_page == 'admin_settings.php') ? 'active' : '' ?>">
                                <i class="fas fa-cog"></i>
                                <span>Paramètres Système</span>
                            </a>
                        </li>
                        <li>
                            <a href=" " class="<?= ($current_page == 'admin_logs.php') ? 'active' : '' ?>">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Journaux d'activité</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">A</div>
                    <div class="user-details">
                        <h4>Administrateur</h4>
                        <p>Super Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <button class="mobile-toggle" id="mobileToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="header-title">
                        <h1><?= htmlspecialchars($pageTitle ?? "Tableau de Bord Admin"); ?></h1>
                        <p>Interface d'administration MedSense</p>
                    </div>
                </div>
                <div class="header-actions">
                    
                    <a href="../../frontoffice/reclamation/index.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </div>
            </header>

            <div class="admin-content">
                <!-- Your page content will be inserted here -->