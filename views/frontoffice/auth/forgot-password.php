<?php
// views/frontoffice/auth/forgot-password.php
session_start();
require_once __DIR__ . '/../../../controllers/PasswordController.php';

$passwordController = new PasswordController();
$error_message = null;
$success_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error_message = "Veuillez entrer votre adresse email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Adresse email invalide";
    } else {
        $result = $passwordController->forgotPassword($email);
        
        if ($result['success']) {
            $success_message = $result['message'];
            
            // En mode développement, afficher le lien de débogage
            if (isset($result['reset_link'])) {
                $success_message .= "<br><br><small class='text-muted'>[DEBUG] Lien de réinitialisation :</small><br>";
                $success_message .= "<a href='" . htmlspecialchars($result['reset_link']) . "' class='btn btn-sm btn-outline-primary mt-2' target='_blank'>" . htmlspecialchars($result['reset_link']) . "</a>";
            }
        } else {
            $error_message = $result['message'];
        }
    }
}
?>

!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../../assets/img/favicon.png" type="image/png">
    <title>Mot de passe oublié - Medcare Medical</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <style>
        .login-area {
            padding: 120px 0 80px;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .appointment-form {
            background: white;
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 0;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h3 {
            color: #2d3748;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
     <   .login-header p {
            color: #718096;
            margin-bottom: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        /* CORRECTION CRITIQUE : Empêcher les labels d'être cliquables */
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2d3748;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: default !important;
            pointer-events: none !important;
            user-select: none !important;
        }
        
        /* S'assurer que les labels ne sont PAS des liens */
        label[for] {
            cursor: default !important;
            pointer-events: none !important;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: #3f51b5;
            box-shadow: 0 0 0 2px rgba(63, 81, 181, 0.2);
            outline: none;
            background: white;
        }
        
        .main_btn {
            width: 100%;
            padding: 14px;
            background: #3f51b5;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .main_btn:hover {
            background: #303f9f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(63, 81, 181, 0.3);
        }
        
        .login-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .login-links a {
            color: #3f51b5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .login-links a:hover {
            color: #303f9f;
            text-decoration: underline;
        }
        
        .alert-error {
            background: #fff5f5;
            color: #c53030;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #c53030;
            font-size: 14px;
        }
        
        .alert-success {
            background: #f0fff4;
            color: #2d7740;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #48bb78;
            font-size: 14px;
        }
        
        .back-home {
            display: inline-flex;
            align-items: center;
            color: #3f51b5;
            text-decoration: none;
            margin-top: 1rem;
            font-weight: 500;
        }
        
        .back-home:hover {
            color: #303f9f;
            text-decoration: underline;
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
<body style="cursor: default;">

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
                    <a class="navbar-brand logo_h" href="../home/index.php">
                        <img src="../../assets/img/logo.png" alt="Medsense Medical">
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <div class="collapse navbar-collapse offset" id="navbarSupportedContent">
                        <ul class="nav navbar-nav menu_nav ml-auto">
                            <li class="nav-item"><a class="nav-link" href="../home/index.php">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="../templete/about-us.html">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="../templete/department.html">Department</a></li>
                            <li class="nav-item"><a class="nav-link" href="../templete/doctors.html">Doctors</a></li>
                            <li class="nav-item"><a class="nav-link" href="../templete/contact.html">Contact</a></li>
                            <li class="nav-item"><a class="nav-link" href="sign-in.php">Connexion</a></li>
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
                        <h2>Mot de passe oublié</h2>
                        <p>Réinitialisez votre mot de passe en quelques étapes</p>
                    </div>
                    <div class="page_link">
                        <a href="../home/index.php">Home</a>
                        <a href="forgot-password.php">Mot de passe oublié</a>
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
                            <h3>Réinitialisation du mot de passe</h3>
                            <p>Entrez votre email pour recevoir un lien de réinitialisation</p>
                        </div>

                        <div id="messageContainer">
                            <?php if ($error_message): ?>
                                <div class="alert-error">
                                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error_message) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success_message): ?>
                                <div class="alert-success">
                                    <?= $success_message ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!$success_message): ?>
                            <form method="POST" action="" id="emailForm" novalidate> <!-- novalidate désactive la validation HTML -->
                                <div class="form-group">
                                    <label for="email">Adresse Email</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                           placeholder="Entrez votre adresse email"
                                           autocomplete="email">
                                    <div class="error-message" id="emailError"></div>
                                </div>

                                <button type="submit" class="main_btn" id="submitBtn">
                                    <span class="btn-text">
                                        <i class="fas fa-paper-plane mr-2"></i>Envoyer le lien de réinitialisation
                                    </span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="login-links">
                            <p><a href="sign-in.php"><i class="ti-arrow-left mr-2"></i>Retour à la connexion</a></p>
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
                    &copy; <?= date('Y') ?> Medcare Medical. Tous droits réservés.
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

    <script src="../../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../../assets/js/popper.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    
    <script>
        // Solution radicale pour empêcher la navigation non désirée
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - applying navigation fixes');
            
            // Bloquer tous les clics sur les labels
            const allLabels = document.querySelectorAll('label');
            allLabels.forEach(label => {
                // Réinitialiser complètement le label
                label.style.cursor = 'default';
                label.style.pointerEvents = 'none';
                label.style.userSelect = 'none';
                
                // Supprimer tous les événements existants
                const newLabel = label.cloneNode(true);
                label.parentNode.replaceChild(newLabel, label);
                
                // Bloquer tous les types de clics
                ['click', 'mousedown', 'mouseup', 'dblclick'].forEach(eventType => {
                    newLabel.addEventListener(eventType, function(e) {
                        console.log('Label event blocked:', eventType);
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }, true);
                });
            });

            // Focus sur le champ email
            document.getElementById('email')?.focus();
            
            // Empêcher la navigation via le formulaire sauf pour le bouton submit
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('click', function(e) {
                    if (e.target.tagName === 'LABEL') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                });
            }
        });

        // Solution de secours globale
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'LABEL') {
                console.log('Global label click prevented');
                e.preventDefault();
                e.stopPropagation();
                window.location.href = 'javascript:void(0)';
                return false;
            }
        }, true);

        // Empêcher la navigation via le clic droit sur les labels
        document.addEventListener('contextmenu', function(e) {
            if (e.target.tagName === 'LABEL') {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>