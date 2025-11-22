<?php
session_start();
include_once '../../../controllers/AuthController.php'; 

$authController = new AuthController();

if (!$authController->isLoggedIn()) {
    header('Location: sign-in.php');
    exit;
}

$user = $authController->getCurrentUser();
$isAdmin = $user && $user['role'] === 'admin';
?>

<!doctype html>
<html lang="fr">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../../../assets/img/favicon.png" type="image/png">
    <title>Mon Profil - Medcare Medical</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    <!-- main css -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <style>
        .profile-area {
            padding: 120px 0 80px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .profile-card {
            background: white;
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0 auto 1.5rem;
            color: white;
        }
        
        .profile-info {
            margin-bottom: 2rem;
        }
        
        .info-group {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3f51b5;
        }
        
        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #718096;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-size: 16px;
            color: #2d3748;
            font-weight: 500;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #3f51b5;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .role-badge.admin {
            background: #e53e3e;
        }
        
        .profile-actions {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-profile {
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            text-align: center;
            flex: 1;
            min-width: 150px;
        }
        
        .btn-primary {
            background: #3f51b5;
            color: white;
        }
        
        .btn-primary:hover {
            background: #303f9f;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #e53e3e;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c53030;
            transform: translateY(-2px);
        }
        
        .admin-section {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
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
                        <?php if ($user): ?>
                            <span style="color: #fff; margin-right: 15px;">
                                <i class="ti-user"></i> 
                                <?php echo $isAdmin ? "Admin: " . htmlspecialchars($user['prenom']) : "Bonjour, " . htmlspecialchars($user['prenom']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="main_menu">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <a class="navbar-brand logo_h" href="../home/index.php">
                        <img src="../../../assets/img/logo.png" alt="Medcare Medical">
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse offset" id="navbarSupportedContent">
                        <ul class="nav navbar-nav menu_nav ml-auto">
                            <li class="nav-item"><a class="nav-link" href="../home/index.php">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="../templete/about-us.html">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="../templete/department.html">Department</a></li>
                            <li class="nav-item"><a class="nav-link" href="../templete/doctors.html">Doctors</a></li>
                            <?php if ($isAdmin): ?>
                                <li class="nav-item"><a class="nav-link" href="../../backoffice/admin-dashboard.php">Administration</a></li>
                            <?php else: ?>
                                <li class="nav-item"><a class="nav-link" href="../appointments/">Mes Rendez-vous</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a class="nav-link" href="../templete/contact.html">Contact</a></li>
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
                        <h2>Mon Profil</h2>
                        <p>Gérez vos informations personnelles</p>
                    </div>
                    <div class="page_link">
                        <a href="../home/index.php">Home</a>
                        <a href="profile.php">Mon Profil</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--================End Banner Area =================-->

    <!--================Profile Area =================-->
    <section class="profile-area">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="profile-card animate-fade-in-up">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <?php 
                                if ($isAdmin) {
                                    echo '👑';
                                } else {
                                    echo strtoupper(substr($user['prenom'], 0, 1));
                                }
                                ?>
                            </div>
                            <h3><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h3>
                            <span class="role-badge <?php echo $isAdmin ? 'admin' : ''; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </div>

                        <div class="profile-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <div class="info-label">Nom complet</div>
                                        <div class="info-value"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <div class="info-label">Adresse email</div>
                                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <div class="info-label">Rôle</div>
                                        <div class="info-value">
                                            <span class="role-badge <?php echo $isAdmin ? 'admin' : ''; ?>">
                                                <?php echo $user['role']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <div class="info-label">Date d'inscription</div>
                                        <div class="info-value">
                                            <?php 
                                            $date = new DateTime($user['date_inscription']);
                                            echo $date->format('d/m/Y');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($user['dateNaissance']): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <div class="info-label">Date de naissance</div>
                                        <div class="info-value">
                                            <?php 
                                            $dateNaissance = new DateTime($user['dateNaissance']);
                                            echo $dateNaissance->format('d/m/Y');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <div class="info-label">Âge</div>
                                        <div class="info-value">
                                            <?php 
                                            $today = new DateTime();
                                            $age = $today->diff($dateNaissance)->y;
                                            echo $age . ' ans';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($user['adresse']): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-group">
                                        <div class="info-label">Adresse</div>
                                        <div class="info-value"><?php echo htmlspecialchars($user['adresse']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($isAdmin): ?>
                        <div class="admin-section">
                            <h4><i class="fas fa-shield-alt"></i> Zone Administrateur</h4>
                            <p>Vous avez accès aux fonctionnalités d'administration du système.</p>
                        </div>
                        <?php endif; ?>

                        <div class="profile-actions">
                            <a href="../home/index.php" class="btn-profile btn-secondary">
                                <i class="ti-arrow-left"></i> Retour à l'accueil
                            </a>
                            
                            <?php if ($isAdmin): ?>
                                <a href="../../backoffice/admin-dashboard.php" class="btn-profile btn-primary">
                                    <i class="ti-dashboard"></i> Tableau de bord
                                </a>
                            <?php else: ?>
                                <a href="../appointments/" class="btn-profile btn-primary">
                                    <i class="ti-calendar"></i> Mes rendez-vous
                                </a>
                            <?php endif; ?>
                            
                            <a href="../../../controllers/logout.php" 
                               class="btn-profile btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                                <i class="ti-power-off"></i> Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--================End Profile Area =================-->

    <!--================ Footer Area =================-->
    <footer class="footer-area area-padding-top">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Medcare Medical</h4>
                    <ul>
                        <li><a href="../home/index.php">Accueil</a></li>
                        <li><a href="../templete/about-us.html">À propos</a></li>
                        <li><a href="../templete/department.html">Services</a></li>
                        <li><a href="../templete/contact.html">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="../templete/department.html">Consultation</a></li>
                        <li><a href="../templete/department.html">Urgence</a></li>
                        <li><a href="../templete/department.html">Analyse</a></li>
                        <li><a href="../templete/department.html">Radiologie</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-sm-6 single-footer-widget">
                    <h4>Compte</h4>
                    <ul>
                        <li><a href="profile.php">Mon Profil</a></li>
                        <?php if (!$isAdmin): ?>
                            <li><a href="../appointments/">Mes Rendez-vous</a></li>
                        <?php endif; ?>
                        <li><a href="sign-in.php">Connexion</a></li>
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
    <script src="../../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../../assets/js/popper.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script src="../../assets/js/stellar.js"></script>
    <script src="../../assets/js/theme.js"></script>
    
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
            const profileCard = document.querySelector('.profile-card');
            if (profileCard) {
                profileCard.style.opacity = '0';
                profileCard.style.transform = 'translateY(30px)';
                profileCard.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    profileCard.style.opacity = '1';
                    profileCard.style.transform = 'translateY(0)';
                }, 300);
            }
        });
    </script>
</body>
</html>