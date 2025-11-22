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
    </style>
</head>
<body>

    <!--================Header Menu Area =================-->
    <header class="header_area">
        <div class="top_menu row m0">
            <div class="container">
                <div class="float-left">
                    <a class="dn_btn" href="mailto:medical@example.com"><i class="ti-email"></i>medical@example.com</a>
                    <span class="dn_btn"> <i class="ti-location-pin"></i>Find our Location</span>
                </div>
                <div class="float-right">
                    <ul class="list header_social">
                        <li><a href="#"><i class="ti-facebook"></i></a></li>
                        <li><a href="#"><i class="ti-twitter-alt"></i></a></li>
                        <li><a href="#"><i class="ti-linkedin"></i></a></li>
                    </ul>
                    <!-- Section authentification -->
                    <div class="auth-top" style="display: inline-block; margin-left: 20px;">
                        <span style="color: #fff; margin-right: 15px;">
                            <i class="ti-shield"></i> Admin Dashboard
                        </span>
                        <a href="../../../controllers/logout.php" style="color: #fff; text-decoration: underline;">
                            <i class="ti-power-off"></i> Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="main_menu">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <a class="navbar-brand logo_h" href="../frontoffice/home/index.php">
                        <img src="../../assets/img/logo.png" alt="Medcare Medical">
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse offset" id="navbarSupportedContent">
                        <ul class="nav navbar-nav menu_nav ml-auto">
                            <li class="nav-item"><a class="nav-link" href="../frontoffice/home/index.php">Home</a></li>
                            <li class="nav-item active"><a class="nav-link" href="admin-dashboard.php">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="gestion-utilisateurs.php">Utilisateurs</a></li>
                            <li class="nav-item"><a class="nav-link" href="gestion-rendezvous.php">Rendez-vous</a></li>
                            <li class="nav-item"><a class="nav-link" href="../frontoffice/templete/contact.html">Contact</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    <!--================Header Menu Area =================-->

    <!--================Banner Area =================-->
    <section class="banner_area">
        <div class="banner_inner d-flex align-items-center">
            <div class="container">
                <div class="banner_content d-md-flex justify-content-between align-items-center">
                    <div class="mb-3 mb-md-0">
                        <h2>Dashboard Administrateur</h2>
                        <p>Gestion du système Medcare Medical</p>
                    </div>
                    <div class="page_link">
                        <a href="../frontoffice/home/index.php">Home</a>
                        <a href="admin-dashboard.php">Dashboard Admin</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--================End Banner Area =================-->

    <!--================Dashboard Area =================-->
    <section class="dashboard-area">
        <div class="container">
            <div class="animate-fade-in-up">
                <!-- Statistiques -->
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

                <!-- Utilisateurs récents -->
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

                <!-- Actions rapides -->
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
    <!--================End Dashboard Area =================-->

    <!--================ Footer Area =================-->
    <footer class="footer-area area-padding-top">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Medcare Medical</h4>
                    <ul>
                        <li><a href="../frontoffice/home/index.php">Accueil</a></li>
                        <li><a href="../frontoffice/templete/about-us.html">À propos</a></li>
                        <li><a href="../frontoffice/templete/department.html">Services</a></li>
                        <li><a href="../frontoffice/templete/contact.html">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Administration</h4>
                    <ul>
                        <li><a href="admin-dashboard.php">Dashboard</a></li>
                        <li><a href="admin-list.php">Utilisateurs</a></li>
                        <li><a href="gestion-rendezvous.php">Rendez-vous</a></li>
                        <li><a href="#">Rapports</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Aide</a></li>
                        <li><a href="#">Contact Support</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="ti-email"></i> medical@example.com</li>
                        <li><i class="ti-mobile"></i> +33 1 23 45 67 89</li>
                        <li><i class="ti-location-pin"></i> Paris, France</li>
                    </ul>
                </div>
            </div>
            <div class="row footer-bottom d-flex justify-content-between">
                <p class="col-lg-8 col-sm-12 footer-text m-0">
                    &copy; <?php echo date('Y'); ?> Medcare Medical. Tous droits réservés.
                </p>
                <div class="col-lg-4 col-sm-12 footer-social">
                    <a href="#"><i class="ti-facebook"></i></a>
                    <a href="#"><i class="ti-twitter"></i></a>
                    <a href="#"><i class="ti-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <!--================ End Footer Area =================-->

    <!-- Optional JavaScript -->
    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/stellar.js"></script>
    <script src="../assets/js/theme.js"></script>
    
    <script>
        // Confirmation de déconnexion
        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });

        // Animation au chargement
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