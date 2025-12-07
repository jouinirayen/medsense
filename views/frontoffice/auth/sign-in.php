<?php
session_start();
include_once '../../../controllers/AuthController.php';
$authController = new AuthController();

if ($authController->isLoggedIn()) {
    header('Location: ../home/index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    // Validation côté serveur
    $errors = [];
    
    // Validation email
    if (empty($email)) {
        $errors[] = "L'adresse email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    } elseif (strlen($email) > 255) {
        $errors[] = "L'adresse email est trop longue";
    } else {
        // Nettoyage supplémentaire
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    // Validation mot de passe
    if (empty($mot_de_passe)) {
        $errors[] = "Le mot de passe est obligatoire";
    } elseif (strlen($mot_de_passe) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    } elseif (strlen($mot_de_passe) > 255) {
        $errors[] = "Le mot de passe est trop long";
    }
    
    // Si pas d'erreurs de validation, tenter la connexion
    if (empty($errors)) {
        $result = $authController->login($email, $mot_de_passe);
        
        if ($result['success']) {
            header('Location: ../home/index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../../assets/img/favicon.png" type="image/png">
    <title>Connexion - Medcare Medical</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    <!-- main css -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="../../assets/css/login.css">
    <link rel="stylesheet" href="../../assets/css/animations.css">
    <style>
        /* Styles d'animation spécifiques */
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .animate-shake {
            animation: loginShake 0.5s ease-out;
        }
        
        .animate-pulse {
            animation: loginPulse 0.5s ease-out;
        }
        
        .btn-loading-active {
            animation: buttonPulse 1.5s infinite;
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
                </div>
            </div>
        </div>
        <div class="main_menu">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <a class="navbar-brand logo_h" href="../home/index.php">
                    <img src="../../assets/img/logo.png" alt="logo" style="height: 120px;">
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
                            <li class="nav-item"><a class="nav-link" href="../templete/contact.html">Contact</a></li>
                            <li class="nav-item"><a class="nav-link" href="sign-up.php">Register</a></li>
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
                        <h2>Connexion</h2>
                        <p>Accédez à votre compte patient</p>
                    </div>
                    <div class="page_link">
                        <a href="../home/index.php">Home</a>
                        <a href="sign-in.php">Connexion</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--================End Banner Area =================-->

    <!--================Login Area =================-->
    <section class="login-area">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="appointment-form animate-fade-in-up">
                        <div class="login-header">
                            <h3>Connectez-vous à votre compte</h3>
                            <p>Accédez à vos rendez-vous et informations médicales</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert-error animate-fade-in animate-shake">
                                <i class="fas fa-exclamation-circle"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="loginForm">
                            <div class="form-group">
                                <label for="email">Adresse Email</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       placeholder="Entrez votre adresse email">
                            </div>

                            <div class="form-group">
                                <label for="mot_de_passe">Mot de passe</label>
                                <input type="password" 
                                       id="mot_de_passe" 
                                       name="mot_de_passe" 
                                       class="form-control" 
                                       placeholder="Entrez votre mot de passe">
                                <div class="forgot-password-link">
                                    <a href="forgot-password.php">
                                        <i class="fas fa-key mr-1"></i>Mot de passe oublié ?
                                    </a>
                                </div>
                            </div>

                            <button type="submit" class="main_btn" id="submitBtn">
                                Se connecter <i class="ti-arrow-right ml-2"></i>
                            </button>
                        </form>

                        <div class="login-links">
                            <p>Vous n'avez pas de compte ? <a href="select-role.php">Créer un compte</a></p>
                            <a href="../home/index.php" class="back-home">
                                <i class="ti-arrow-left mr-2"></i> Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--================End Login Area =================-->

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
                        <li><a href="sign-in.php">Connexion</a></li>
                        <li><a href="sign-up.php">Inscription</a></li>
                        <li><a href="forgot-password.php">Mot de passe oublié</a></li>
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
    <script src="../../../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../../../assets/js/popper.js"></script>
    <script src="../../../assets/js/bootstrap.min.js"></script>
    <script src="../../../assets/js/stellar.js"></script>
    <script src="../../../assets/js/theme.js"></script>
    <!-- Scripts d'authentification -->
    <script src="js/auth.js"></script>
    <script src="js/login.js"></script>
    
    <script>
        // Script d'initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser les animations
            if (window.loginAnimations) {
                window.loginAnimations.init();
            }
            
            // Validation avancée
            const form = document.getElementById('loginForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value.trim();
                    const password = document.getElementById('mot_de_passe').value;
                    const errors = [];
                    
                    // Validation email
                    if (!email) {
                        errors.push('L\'email est obligatoire');
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        errors.push('Format d\'email invalide (ex: user@example.com)');
                    } else if (email.length > 255) {
                        errors.push('L\'email est trop long (max 255 caractères)');
                    }
                    
                    // Validation mot de passe
                    if (!password) {
                        errors.push('Le mot de passe est obligatoire');
                    } else if (password.length < 6) {
                        errors.push('Le mot de passe doit contenir au moins 6 caractères');
                    } else if (password.length > 255) {
                        errors.push('Le mot de passe est trop long (max 255 caractères)');
                    }
                    
                    // Si erreurs, afficher et empêcher la soumission
                    if (errors.length > 0) {
                        e.preventDefault();
                        if (window.loginAnimations) {
                            window.loginAnimations.showGlobalError(errors);
                        }
                        return false;
                    }
                    
                    return true;
                });
            }
            
            // Navigation rapide avec la touche Entrée
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.type !== 'submit') {
                    const submitBtn = document.getElementById('submitBtn');
                    if (submitBtn && document.activeElement.type !== 'textarea') {
                        e.preventDefault();
                        submitBtn.click();
                    }
                }
            });
            
            // Animation pour les liens du footer
            const footerLinks = document.querySelectorAll('.footer-area a');
            footerLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.transition = 'transform 0.3s ease';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>