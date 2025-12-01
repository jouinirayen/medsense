<?php
// views/frontoffice/auth/reset-password.php
session_start();
require_once __DIR__ . '/../../../controllers/PasswordController.php';

$passwordController = new PasswordController();
$error_message = null;
$success_message = null;
$valid_token = false;

// Vérifier le token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error_message = "Token de réinitialisation manquant";
} else {
    $valid_token = $passwordController->validateToken($token);
    if (!$valid_token) {
        $error_message = "Le lien de réinitialisation est invalide ou a expiré";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // VALIDATION RENFORCÉE
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Veuillez remplir tous les champs";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Le mot de passe doit contenir au moins 8 caractères";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error_message = "Le mot de passe doit contenir au moins une majuscule";
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error_message = "Le mot de passe doit contenir au moins une minuscule";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error_message = "Le mot de passe doit contenir au moins un chiffre";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        $error_message = "Le mot de passe doit contenir au moins un caractère spécial";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Les mots de passe ne correspondent pas";
    } else {
        $result = $passwordController->resetPassword($token, $new_password);
        if ($result['success']) {
            $success_message = $result['message'];
            $valid_token = false;
        } else {
            $error_message = $result['message'];
        }
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../../assets/img/favicon.png" type="image/png">
    <title>Réinitialisation du mot de passe - Medcare Medical</title>
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
        
        .login-header p {
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
        
        .main_btn:disabled {
            background: #a0aec0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 5px;
            transition: all 0.3s;
            width: 0%;
        }
        
        .password-requirements {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 2px;
        }
        
        .requirement.valid {
            color: #38a169;
        }
        
        .requirement.invalid {
            color: #e53e3e;
        }
        
        .requirement-icon {
            margin-right: 5px;
            font-size: 10px;
        }
        
        /* COULEURS POUR LA FORCE DU MOT DE PASSE */
        .bg-danger { background-color: #e53e3e !important; }
        .bg-warning { background-color: #dd6b20 !important; }
        .bg-info { background-color: #3182ce !important; }
        .bg-success { background-color: #38a169 !important; }
        
        .text-danger { color: #e53e3e !important; }
        .text-warning { color: #dd6b20 !important; }
        .text-info { color: #3182ce !important; }
        .text-success { color: #38a169 !important; }
        
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
                        <img src="../../assets/img/logo.png" alt="Medcare Medical">
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
                        <h2>Nouveau mot de passe</h2>
                        <p>Choisissez un nouveau mot de passe sécurisé</p>
                    </div>
                    <div class="page_link">
                        <a href="../home/index.php">Home</a>
                        <a href="reset-password.php">Nouveau mot de passe</a>
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
                            <h3>Nouveau mot de passe</h3>
                            <p>Choisissez un nouveau mot de passe sécurisé pour votre compte</p>
                        </div>

                        <?php if ($error_message): ?>
                            <div class="alert-error">
                                <i class="fas fa-exclamation-circle mr-2"></i><?= $error_message ?>
                            </div>
                            
                            <?php if (strpos($error_message, 'invalide') !== false || strpos($error_message, 'expiré') !== false): ?>
                                <div class="text-center mt-3">
                                    <a href="forgot-password.php" class="btn btn-outline-primary">
                                        <i class="fas fa-redo mr-2"></i>Demander un nouveau lien
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($success_message): ?>
                            <div class="alert-success">
                                <i class="fas fa-check-circle mr-2"></i><?= $success_message ?>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="sign-in.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
                                </a>
                            </div>
                        <?php elseif ($valid_token): ?>
                            <form method="POST" action="" id="resetForm">
                                <div class="form-group">
                                    <label for="new_password">Nouveau mot de passe</label>
                                    <input type="password" 
                                           id="new_password" 
                                           name="new_password" 
                                           class="form-control" 
                                           required
                                           minlength="8"
                                           placeholder="Minimum 8 caractères avec majuscule, minuscule, chiffre et caractère spécial">
                                    <div class="password-strength" id="passwordStrength"></div>
                                    <small class="form-text text-muted" id="passwordFeedback"></small>
                                    
                                    <div class="password-requirements" id="passwordRequirements">
                                        <div class="requirement invalid" id="reqLength">
                                            <i class="requirement-icon fas fa-times"></i>
                                            Au moins 8 caractères
                                        </div>
                                        <div class="requirement invalid" id="reqUpper">
                                            <i class="requirement-icon fas fa-times"></i>
                                            Au moins une majuscule
                                        </div>
                                        <div class="requirement invalid" id="reqLower">
                                            <i class="requirement-icon fas fa-times"></i>
                                            Au moins une minuscule
                                        </div>
                                        <div class="requirement invalid" id="reqNumber">
                                            <i class="requirement-icon fas fa-times"></i>
                                            Au moins un chiffre
                                        </div>
                                        <div class="requirement invalid" id="reqSpecial">
                                            <i class="requirement-icon fas fa-times"></i>
                                            Au moins un caractère spécial
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirmer le mot de passe</label>
                                    <input type="password" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           class="form-control" 
                                           required
                                           minlength="8"
                                           placeholder="Retapez votre mot de passe">
                                    <small class="form-text text-muted" id="confirmFeedback"></small>
                                </div>

                                <button type="submit" class="main_btn" id="submitBtn" disabled>
                                    <i class="fas fa-save mr-2"></i>Réinitialiser le mot de passe
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="login-links">
                            <a href="sign-in.php" class="back-home">
                                <i class="ti-arrow-left mr-2"></i> Retour à la connexion
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

            // Validation avancée du mot de passe
            function validatePassword(password) {
                const requirements = {
                    length: password.length >= 8,
                    upper: /[A-Z]/.test(password),
                    lower: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[^A-Za-z0-9]/.test(password)
                };
                
                return requirements;
            }
            
            function updatePasswordRequirements(requirements) {
                const reqElements = {
                    length: document.getElementById('reqLength'),
                    upper: document.getElementById('reqUpper'),
                    lower: document.getElementById('reqLower'),
                    number: document.getElementById('reqNumber'),
                    special: document.getElementById('reqSpecial')
                };
                
                let allValid = true;
                
                for (const [key, element] of Object.entries(reqElements)) {
                    if (requirements[key]) {
                        element.classList.remove('invalid');
                        element.classList.add('valid');
                        element.querySelector('i').className = 'requirement-icon fas fa-check';
                    } else {
                        element.classList.remove('valid');
                        element.classList.add('invalid');
                        element.querySelector('i').className = 'requirement-icon fas fa-times';
                        allValid = false;
                    }
                }
                
                return allValid;
            }
            
            function updateSubmitButton(requirementsValid, passwordsMatch) {
                const submitBtn = document.getElementById('submitBtn');
                const password = document.getElementById('new_password').value;
                
                if (requirementsValid && passwordsMatch && password.length > 0) {
                    submitBtn.disabled = false;
                    submitBtn.title = '';
                } else {
                    submitBtn.disabled = true;
                    submitBtn.title = 'Veuillez remplir tous les critères de sécurité';
                }
            }

            // Validation du mot de passe en temps réel
            const newPasswordInput = document.getElementById('new_password');
            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', function() {
                    const password = this.value;
                    const strengthBar = document.getElementById('passwordStrength');
                    const feedback = document.getElementById('passwordFeedback');
                    
                    if (!strengthBar || !feedback) return;
                    
                    const requirements = validatePassword(password);
                    const requirementsValid = updatePasswordRequirements(requirements);
                    
                    let strength = 0;
                    let message = '';
                    let colorClass = '';
                    
                    // Calcul de la force basé sur les critères remplis
                    if (requirements.length) strength++;
                    if (requirements.upper && requirements.lower) strength++;
                    if (requirements.number) strength++;
                    if (requirements.special) strength++;
                    
                    switch(strength) {
                        case 0:
                        case 1:
                            message = 'Très faible';
                            colorClass = 'bg-danger';
                            break;
                        case 2:
                            message = 'Faible';
                            colorClass = 'bg-danger';
                            break;
                        case 3:
                            message = 'Moyen';
                            colorClass = 'bg-warning';
                            break;
                        case 4:
                            message = 'Bon';
                            colorClass = 'bg-info';
                            break;
                        case 5:
                            message = 'Fort';
                            colorClass = 'bg-success';
                            break;
                    }
                    
                    strengthBar.style.width = (strength * 20) + '%';
                    strengthBar.className = `password-strength ${colorClass}`;
                    feedback.textContent = message;
                    feedback.className = `form-text text-${colorClass.replace('bg-', '')}`;
                    
                    // Vérifier la correspondance des mots de passe
                    const confirm = document.getElementById('confirm_password').value;
                    const passwordsMatch = password === confirm && password.length > 0;
                    
                    updateSubmitButton(requirementsValid, passwordsMatch);
                });
            }
            
            // Validation de la confirmation
            const confirmPasswordInput = document.getElementById('confirm_password');
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    const confirm = this.value;
                    const password = document.getElementById('new_password').value;
                    const feedback = document.getElementById('confirmFeedback');
                    
                    if (!feedback) return;
                    
                    const requirements = validatePassword(password);
                    const requirementsValid = updatePasswordRequirements(requirements);
                    
                    if (confirm === password && password.length >= 8) {
                        feedback.textContent = 'Les mots de passe correspondent';
                        feedback.className = 'form-text text-success';
                        updateSubmitButton(requirementsValid, true);
                    } else if (password.length > 0) {
                        feedback.textContent = 'Les mots de passe ne correspondent pas';
                        feedback.className = 'form-text text-danger';
                        updateSubmitButton(requirementsValid, false);
                    } else {
                        feedback.textContent = '';
                        updateSubmitButton(requirementsValid, false);
                    }
                });
            }
            
            // Validation finale du formulaire
            const resetForm = document.getElementById('resetForm');
            if (resetForm) {
                resetForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('new_password').value;
                    const confirm = document.getElementById('confirm_password').value;
                    const requirements = validatePassword(password);
                    
                    let errorMessages = [];
                    
                    if (password !== confirm) {
                        errorMessages.push('Les mots de passe ne correspondent pas');
                    }
                    
                    if (!requirements.length) {
                        errorMessages.push('Le mot de passe doit contenir au moins 8 caractères');
                    }
                    if (!requirements.upper) {
                        errorMessages.push('Le mot de passe doit contenir au moins une majuscule');
                    }
                    if (!requirements.lower) {
                        errorMessages.push('Le mot de passe doit contenir au moins une minuscule');
                    }
                    if (!requirements.number) {
                        errorMessages.push('Le mot de passe doit contenir au moins un chiffre');
                    }
                    if (!requirements.special) {
                        errorMessages.push('Le mot de passe doit contenir au moins un caractère spécial');
                    }
                    
                    if (errorMessages.length > 0) {
                        e.preventDefault();
                        alert('Erreurs de validation :\n• ' + errorMessages.join('\n• '));
                        return false;
                    }
                    
                    console.log('Form submission allowed');
                    return true;
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