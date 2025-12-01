<?php
session_start();
include_once '../../../controllers/AuthController.php';

$authController = new AuthController();

if ($authController->isLoggedIn()) {
    header('Location: ../home/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $dateNaissance = trim($_POST['dateNaissance'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    
    // Validation côté serveur
    $errors = [];
    
    // Validation prénom
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire";
    } elseif (strlen($prenom) < 2) {
        $errors[] = "Le prénom doit contenir au moins 2 caractères";
    } elseif (strlen($prenom) > 50) {
        $errors[] = "Le prénom est trop long (max 50 caractères)";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/', $prenom)) {
        $errors[] = "Le prénom contient des caractères non autorisés";
    }
    
    // Validation nom
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    } elseif (strlen($nom) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractères";
    } elseif (strlen($nom) > 50) {
        $errors[] = "Le nom est trop long (max 50 caractères)";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/', $nom)) {
        $errors[] = "Le nom contient des caractères non autorisés";
    }
    
    // Validation email
    if (empty($email)) {
        $errors[] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    } elseif (strlen($email) > 255) {
        $errors[] = "L'email est trop long (max 255 caractères)";
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
        $errors[] = "Le mot de passe est trop long (max 255 caractères)";
    }
    
    // Validation date de naissance
    if (!empty($dateNaissance)) {
        $today = new DateTime();
        $birthdate = new DateTime($dateNaissance);
        $minDate = new DateTime();
        $minDate->modify('-120 years');
        
        if ($birthdate > $today) {
            $errors[] = "La date de naissance ne peut pas être dans le futur";
        } elseif ($birthdate < $minDate) {
            $errors[] = "L'âge maximum est de 120 ans";
        }
    }
    
    // Validation adresse
    if (!empty($adresse) && strlen($adresse) > 500) {
        $errors[] = "L'adresse est trop longue (max 500 caractères)";
    }
    
    // Si pas d'erreurs de validation, procéder à l'inscription
    if (empty($errors)) {
        $result = $authController->register($_POST);
        
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
    <title>Inscription - Medcare Medical</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    <!-- main css -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <style>
        .register-area {
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
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header h3 {
            color: #2d3748;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .register-header p {
            color: #718096;
            margin-bottom: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2d3748;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
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
        
        .form-control.error {
            border-color: #e53e3e;
            box-shadow: 0 0 0 2px rgba(229, 62, 62, 0.2);
        }
        
        .form-control.success {
            border-color: #38a169;
            box-shadow: 0 0 0 2px rgba(56, 161, 105, 0.2);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
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
        
        .main_btn:hover:not(:disabled) {
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
        
        .register-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .register-links a {
            color: #3f51b5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .register-links a:hover {
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
            display: flex;
            align-items: flex-start;
        }
        
        .alert-error i {
            margin-top: 2px;
            margin-right: 10px;
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
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .field-error {
            color: #e53e3e;
            font-size: 12px;
            margin-top: 5px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .field-error i {
            margin-right: 5px;
            font-size: 11px;
        }
        
        .global-error-alert {
            background: #fff5f5;
            color: #c53030;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #c53030;
            font-size: 14px;
        }
        
        .global-error-alert ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }
        
        .global-error-alert li {
            margin-bottom: 3px;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
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
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
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
                            <li class="nav-item"><a class="nav-link" href="sign-in.php">Login</a></li>
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
                        <h2>Inscription</h2>
                        <p>Créez votre compte patient</p>
                    </div>
                    <div class="page_link">
                        <a href="../home/index.php">Home</a>
                        <a href="sign-up.php">Inscription</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--================End Banner Area =================-->

    <!--================Register Area =================-->
    <section class="register-area">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="appointment-form animate-fade-in-up">
                        <div class="register-header">
                            <h3>Créez votre compte</h3>
                            <p>Rejoignez Medcare Medical et accédez à nos services en ligne</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="registerForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="prenom">Prénom *</label>
                                    <input type="text" 
                                           id="prenom" 
                                           name="prenom" 
                                           class="form-control" 
                                           placeholder="Votre prénom">
                                </div>

                                <div class="form-group">
                                    <label for="nom">Nom *</label>
                                    <input type="text" 
                                           id="nom" 
                                           name="nom" 
                                           class="form-control" 
                                           placeholder="Votre nom">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">Adresse Email *</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control" 
                                       placeholder="votre@email.com">
                            </div>

                            <div class="form-group">
                                <label for="mot_de_passe">Mot de passe *</label>
                                <input type="password" 
                                       id="mot_de_passe" 
                                       name="mot_de_passe" 
                                       class="form-control" 
                                       placeholder="Choisissez un mot de passe sécurisé">
                                <small style="color: #718096; font-size: 12px; margin-top: 5px; display: block;">
                                    Le mot de passe doit contenir au moins 6 caractères
                                </small>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="dateNaissance">Date de naissance</label>
                                    <input type="date" 
                                           id="dateNaissance" 
                                           name="dateNaissance" 
                                           class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="adresse">Adresse</label>
                                <textarea id="adresse" 
                                          name="adresse" 
                                          class="form-control" 
                                          placeholder="Votre adresse complète"></textarea>
                            </div>

                            <button type="submit" class="main_btn" id="submitBtn">
                                Créer mon compte <i class="ti-arrow-right ml-2"></i>
                            </button>
                        </form>

                        <div class="register-links">
                            <p>Vous avez déjà un compte ? <a href="sign-in.php">Connectez-vous</a></p>
                            <a href="../home/index.php" class="back-home">
                                <i class="ti-arrow-left mr-2"></i> Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--================End Register Area =================-->

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
    <script src="../../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../../assets/js/popper.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script src="../../assets/js/stellar.js"></script>
    <script src="../../assets/js/theme.js"></script>
    
    <script>
        // Validation du formulaire d'inscription
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const prenomInput = document.getElementById('prenom');
            const nomInput = document.getElementById('nom');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('mot_de_passe');
            const dateNaissanceInput = document.getElementById('dateNaissance');
            const adresseInput = document.getElementById('adresse');
            const submitBtn = document.getElementById('submitBtn');

            // Fonctions de validation
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            function isValidName(name) {
                const nameRegex = /^[a-zA-ZÀ-ÿ\s\-']+$/;
                return nameRegex.test(name);
            }

            function validateNameField(field, fieldName) {
                const value = field.value.trim();
                const parent = field.parentNode;
                
                const existingError = parent.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }

                // Supprimer les classes existantes
                field.classList.remove('error', 'success');

                if (!value) {
                    showFieldError(field, `Le ${fieldName} est obligatoire`);
                    return false;
                } else if (value.length < 2) {
                    showFieldError(field, `Le ${fieldName} doit contenir au moins 2 caractères`);
                    return false;
                } else if (value.length > 50) {
                    showFieldError(field, `Le ${fieldName} est trop long (max 50 caractères)`);
                    return false;
                } else if (!isValidName(value)) {
                    showFieldError(field, `Le ${fieldName} contient des caractères non autorisés`);
                    return false;
                } else {
                    showFieldSuccess(field);
                    return true;
                }
            }

            function validateEmailField(field) {
                const value = field.value.trim();
                const parent = field.parentNode;
                
                const existingError = parent.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }

                // Supprimer les classes existantes
                field.classList.remove('error', 'success');

                if (!value) {
                    showFieldError(field, 'L\'email est obligatoire');
                    return false;
                } else if (!isValidEmail(value)) {
                    showFieldError(field, 'Format d\'email invalide (ex: user@example.com)');
                    return false;
                } else if (value.length > 255) {
                    showFieldError(field, 'L\'email est trop long (max 255 caractères)');
                    return false;
                } else {
                    showFieldSuccess(field);
                    return true;
                }
            }

            function validatePasswordField(field) {
                const value = field.value;
                const parent = field.parentNode;
                
                const existingError = parent.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }

                // Supprimer les classes existantes
                field.classList.remove('error', 'success');

                if (!value) {
                    showFieldError(field, 'Le mot de passe est obligatoire');
                    return false;
                } else if (value.length < 6) {
                    showFieldError(field, 'Le mot de passe doit contenir au moins 6 caractères');
                    return false;
                } else if (value.length > 255) {
                    showFieldError(field, 'Le mot de passe est trop long (max 255 caractères)');
                    return false;
                } else {
                    showFieldSuccess(field);
                    return true;
                }
            }

            function validateDateField(field) {
                const value = field.value;
                const parent = field.parentNode;
                
                const existingError = parent.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }

                // Supprimer les classes existantes
                field.classList.remove('error', 'success');

                if (!value) {
                    // Date de naissance n'est pas obligatoire
                    clearFieldStatus(field);
                    return true;
                }

                const selectedDate = new Date(value);
                const today = new Date();
                const minDate = new Date();
                minDate.setFullYear(today.getFullYear() - 120);

                if (selectedDate > today) {
                    showFieldError(field, 'La date ne peut pas être dans le futur');
                    return false;
                } else if (selectedDate < minDate) {
                    showFieldError(field, 'Âge maximum 120 ans');
                    return false;
                } else {
                    showFieldSuccess(field);
                    return true;
                }
            }

            function validateAddressField(field) {
                const value = field.value.trim();
                const parent = field.parentNode;
                
                const existingError = parent.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }

                // Supprimer les classes existantes
                field.classList.remove('error', 'success');

                if (!value) {
                    // Adresse n'est pas obligatoire
                    clearFieldStatus(field);
                    return true;
                }

                if (value.length > 500) {
                    showFieldError(field, 'L\'adresse est trop longue (max 500 caractères)');
                    return false;
                } else {
                    showFieldSuccess(field);
                    return true;
                }
            }

            function showFieldError(field, message) {
                field.classList.add('error');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i>' + message;
                field.parentNode.appendChild(errorDiv);
            }

            function showFieldSuccess(field) {
                field.classList.add('success');
            }

            function clearFieldStatus(field) {
                field.classList.remove('error', 'success');
                
                const existingError = field.parentNode.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }
            }

            // Validation en temps réel
            if (prenomInput) {
                prenomInput.addEventListener('blur', function() {
                    validateNameField(this, 'prénom');
                });
            }

            if (nomInput) {
                nomInput.addEventListener('blur', function() {
                    validateNameField(this, 'nom');
                });
            }

            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    validateEmailField(this);
                });
            }

            if (passwordInput) {
                passwordInput.addEventListener('blur', function() {
                    validatePasswordField(this);
                });
            }

            if (dateNaissanceInput) {
                dateNaissanceInput.addEventListener('change', function() {
                    validateDateField(this);
                });
            }

            if (adresseInput) {
                adresseInput.addEventListener('blur', function() {
                    validateAddressField(this);
                });
            }

            // Validation à la soumission
            if (form) {
                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    const errors = [];

                    // Valider tous les champs
                    if (!validateNameField(prenomInput, 'prénom')) {
                        isValid = false;
                        errors.push('Prénom invalide');
                    }

                    if (!validateNameField(nomInput, 'nom')) {
                        isValid = false;
                        errors.push('Nom invalide');
                    }

                    if (!validateEmailField(emailInput)) {
                        isValid = false;
                        errors.push('Email invalide');
                    }

                    if (!validatePasswordField(passwordInput)) {
                        isValid = false;
                        errors.push('Mot de passe invalide');
                    }

                    if (!validateDateField(dateNaissanceInput)) {
                        isValid = false;
                        errors.push('Date de naissance invalide');
                    }

                    if (!validateAddressField(adresseInput)) {
                        isValid = false;
                        errors.push('Adresse invalide');
                    }

                    if (!isValid) {
                        e.preventDefault();
                        showGlobalErrors(errors);
                        return false;
                    }

                    // Désactiver le bouton pour éviter les doubles soumissions
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="loading-spinner"></span>Création du compte...';
                    }
                });
            }

            function showGlobalErrors(errors) {
                // Supprimer les anciennes erreurs globales
                const existingGlobalError = document.querySelector('.global-error-alert');
                if (existingGlobalError) {
                    existingGlobalError.remove();
                }

                const errorDiv = document.createElement('div');
                errorDiv.className = 'global-error-alert alert-error';
                
                let errorHtml = '<div class="d-flex align-items-center"><i class="fas fa-exclamation-circle mr-2"></i>';
                errorHtml += '<strong>Veuillez corriger les erreurs suivantes :</strong></div>';
                errorHtml += '<ul style="margin: 0.5rem 0 0 1.5rem;">';
                errors.forEach(error => {
                    errorHtml += '<li>' + error + '</li>';
                });
                errorHtml += '</ul>';
                
                errorDiv.innerHTML = errorHtml;
                
                const form = document.querySelector('form');
                if (form) {
                    form.parentNode.insertBefore(errorDiv, form);
                    
                    // Faire défiler jusqu'aux erreurs
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                // Auto-suppression après 8 secondes
                setTimeout(() => {
                    if (errorDiv.parentNode) {
                        errorDiv.style.opacity = '0';
                        errorDiv.style.transition = 'opacity 0.5s ease';
                        setTimeout(() => {
                            if (errorDiv.parentNode) {
                                errorDiv.parentNode.removeChild(errorDiv);
                            }
                        }, 500);
                    }
                }, 8000);
            }

            // Calcul de l'âge maximum pour la date de naissance (120 ans)
            if (dateNaissanceInput) {
                const today = new Date();
                const maxDate = new Date();
                maxDate.setFullYear(today.getFullYear() - 120);
                dateNaissanceInput.max = today.toISOString().split('T')[0];
                dateNaissanceInput.min = maxDate.toISOString().split('T')[0];
            }

            // Focus automatique sur le prénom
            if (prenomInput) {
                setTimeout(() => {
                    prenomInput.focus();
                }, 500);
            }

            // Auto-suppression des messages d'erreur existants après 5 secondes
            setTimeout(() => {
                const alertError = document.querySelector('.alert-error');
                if (alertError && !alertError.classList.contains('global-error-alert')) {
                    alertError.style.opacity = '0';
                    alertError.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        if (alertError.parentNode) {
                            alertError.parentNode.removeChild(alertError);
                        }
                    }, 500);
                }
            }, 5000);
        });
    </script>
</body>
</html>