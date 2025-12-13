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
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "L'adresse email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    } elseif (strlen($email) > 255) {
        $errors[] = "L'adresse email est trop longue";
    } else { 
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    if (empty($mot_de_passe)) {
        $errors[] = "Le mot de passe est obligatoire";
    } elseif (strlen($mot_de_passe) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    } elseif (strlen($mot_de_passe) > 255) {
        $errors[] = "Le mot de passe est trop long";
    }
    
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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Medcare Medical</title>
    <link rel="icon" href="../../assets/img/favicon.png" type="image/png">

    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ====================
           RESET & BASE STYLES
        ==================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.login-page {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f9ff 0%, #e8f0ff 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* ====================
           BACKGROUND DECORATION
        ==================== */
        .medical-decoration {
            position: absolute;
            font-size: 120px;
            color: rgba(42, 116, 255, 0.08);
            z-index: 0;
            animation: float 6s ease-in-out infinite;
        }

        .medical-decoration:nth-child(1) {
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .medical-decoration:nth-child(2) {
            bottom: 15%;
            right: 7%;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* ====================
           LOGIN CONTAINER
        ==================== */
        .login-area {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .appointment-form {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(42, 116, 255, 0.15);
            transition: all 0.4s ease;
            border: 1px solid rgba(42, 116, 255, 0.1);
        }

        .appointment-form:hover {
            box-shadow: 0 25px 70px rgba(42, 116, 255, 0.2);
            transform: translateY(-5px);
        }

        /* ====================
           LOGO SECTION
        ==================== */
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-img {
            height: 100px;
            transition: all 0.3s ease;
            filter: drop-shadow(0 5px 15px rgba(42, 116, 255, 0.2));
        }

        .logo-container:hover .logo-img {
            transform: scale(1.05) rotate(2deg);
        }

        /* ====================
           HEADER SECTION
        ==================== */
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #0c1b45;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #2a74ff 0%, #1d5fe0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-header p {
            color: #6c7a96;
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ====================
           ALERT MESSAGES
        ==================== */
        .alert-error {
            background: linear-gradient(135deg, #ffeded 0%, #ffeaea 100%);
            border-left: 4px solid #e74c3c;
            padding: 18px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            animation: fadeIn 0.5s ease-out;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.1);
        }

        .alert-error i {
            color: #e74c3c;
            font-size: 20px;
            margin-top: 2px;
        }

        .alert-error div {
            color: #c0392b;
            font-weight: 500;
            line-height: 1.5;
            flex: 1;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ====================
           FORM STYLES
        ==================== */
        .login-form {
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 28px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #0c1b45;
            font-weight: 600;
            font-size: 1rem;
        }

        .input-with-icon {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-with-icon i {
            position: absolute;
            left: 20px;
            color: #2a74ff;
            font-size: 18px;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px 16px 55px;
            border: 2px solid #e0e6ef;
            border-radius: 12px;
            font-size: 16px;
            color: #333;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #2a74ff;
            background: white;
            box-shadow: 0 0 0 4px rgba(42, 116, 255, 0.1);
        }

        .form-control.error {
            border-color: #e74c3c;
            background: #fff5f5;
        }

        .input-with-icon.focused i {
            color: #1d5fe0;
            transform: scale(1.1);
        }

        /* ====================
           FORGOT PASSWORD LINK
        ==================== */
        .forgot-password-link {
            text-align: right;
            margin-top: 12px;
        }

        .forgot-password-link a {
            color: #2a74ff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .forgot-password-link a:hover {
            color: #1d5fe0;
            text-decoration: underline;
        }

        /* ====================
           SUBMIT BUTTON
        ==================== */
        .main_btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 18px 30px;
            background: linear-gradient(135deg, #2a74ff 0%, #1d5fe0 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
            text-decoration: none;
        }

        .main_btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(42, 116, 255, 0.3);
        }

        .main_btn:active {
            transform: translateY(-1px);
        }

        .main_btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .main_btn i {
            transition: transform 0.3s ease;
        }

        .main_btn:hover i {
            transform: translateX(5px);
        }

        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ====================
           LOGIN LINKS
        ==================== */
        .login-links {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #e0e6ef;
            margin-top: 30px;
        }

        .login-links p {
            color: #6c7a96;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .login-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 28px;
            margin: 8px;
            background: #f8fafc;
            color: #2a74ff;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .login-link:hover {
            background: #2a74ff;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(42, 116, 255, 0.2);
        }

        .login-link.back-home {
            background: transparent;
            border-color: #e0e6ef;
            color: #6c7a96;
        }

        .login-link.back-home:hover {
            border-color: #2a74ff;
            color: #2a74ff;
            background: transparent;
        }

        .login-link i {
            margin-right: 8px;
            font-size: 16px;
        }

        /* ====================
           RESPONSIVE DESIGN
        ==================== */
        @media (max-width: 768px) {
            .appointment-form {
                padding: 30px 25px;
                margin: 0 10px;
            }

            .login-header h3 {
                font-size: 1.75rem;
            }

            .login-header p {
                font-size: 1rem;
            }

            .logo-img {
                height: 80px;
            }

            .medical-decoration {
                font-size: 80px;
            }

            .login-link {
                padding: 12px 20px;
                margin: 5px;
                font-size: 14px;
                width: 100%;
                max-width: 300px;
            }

            .login-links {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }

        @media (max-width: 480px) {
            .appointment-form {
                padding: 25px 20px;
            }

            .login-header h3 {
                font-size: 1.5rem;
            }

            .form-control {
                padding: 14px 15px 14px 45px;
            }

            .input-with-icon i {
                left: 15px;
                font-size: 16px;
            }

            .main_btn {
                padding: 16px 20px;
                font-size: 16px;
            }

            .medical-decoration {
                font-size: 60px;
            }

            .medical-decoration:nth-child(1) {
                top: 5%;
                left: 2%;
            }

            .medical-decoration:nth-child(2) {
                bottom: 5%;
                right: 2%;
            }
        }

        /* ====================
           EXTRA ANIMATIONS
        ==================== */
        .form-group {
            animation: slideUp 0.5s ease-out forwards;
            opacity: 0;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .main_btn { animation-delay: 0.3s; }
        .login-links { animation-delay: 0.4s; }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="login-page">
    <div class="medical-decoration"><i class="fas fa-heartbeat"></i></div>
    <div class="medical-decoration"><i class="fas fa-user-md"></i></div>
    
    <div class="login-area">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 col-sm-10">
                    <div class="appointment-form">
                        <div class="logo-container">
                            <img src="../../assets/img/logo.png" alt="Medcare Medical Logo" class="logo-img">
                        </div>
                        
                        <div class="login-header">
                            <h3>Connectez-vous à votre compte</h3>
                            <p>Accédez à vos rendez-vous et informations médicales</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="loginForm" class="login-form">
                            <div class="form-group">
                                <label for="email">Adresse Email</label>
                                <div class="input-with-icon">
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($email); ?>" 
                                           placeholder="Entrez votre adresse email"
                                           required>
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="mot_de_passe">Mot de passe</label>
                                <div class="input-with-icon">
                                    <input type="password" 
                                           id="mot_de_passe" 
                                           name="mot_de_passe" 
                                           class="form-control" 
                                           placeholder="Entrez votre mot de passe"
                                           required>
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div class="forgot-password-link">
                                    <a href="forgot-password.php">
                                        <i class="fas fa-key mr-1"></i>Mot de passe oublié ?
                                    </a>
                                </div>
                            </div>
                            <button type="submit" class="main_btn" id="submitBtn">
                                <span class="btn-text">Se connecter</span>
                                <i class="ti-arrow-right ml-2"></i>
                            </button>
                        </form>

                        <div class="login-links">
                            <p>Vous n'avez pas de compte ?</p>
                            <a href="select-role.php" class="login-link">
                                <i class="fas fa-user-plus"></i>
                                Créer un compte
                            </a>
                            
                            <a href="../home/index.php" class="login-link back-home">
                                <i class="ti-arrow-left mr-2"></i> Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../../assets/js/popper.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script src="../../assets/js/stellar.js"></script>
    <script src="../../assets/js/theme.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ripple effect for buttons
            const buttons = document.querySelectorAll('.main_btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple-effect');
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Form validation
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value.trim();
                    const password = document.getElementById('mot_de_passe').value;
                    const errors = [];
                    
                    // Email validation
                    if (!email) {
                        errors.push('L\'email est obligatoire');
                        document.getElementById('email').classList.add('error');
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        errors.push('Format d\'email invalide');
                        document.getElementById('email').classList.add('error');
                    } else {
                        document.getElementById('email').classList.remove('error');
                    }
                    
                    // Password validation
                    if (!password) {
                        errors.push('Le mot de passe est obligatoire');
                        document.getElementById('mot_de_passe').classList.add('error');
                    } else if (password.length < 6) {
                        errors.push('Le mot de passe doit contenir au moins 6 caractères');
                        document.getElementById('mot_de_passe').classList.add('error');
                    } else {
                        document.getElementById('mot_de_passe').classList.remove('error');
                    }
                    
                    // If errors exist, prevent submission and show them
                    if (errors.length > 0) {
                        e.preventDefault();
                        
                        let errorDiv = document.querySelector('.alert-error');
                        if (!errorDiv) {
                            errorDiv = document.createElement('div');
                            errorDiv.className = 'alert-error';
                            loginForm.prepend(errorDiv);
                        }
                        
                        errorDiv.innerHTML = `
                            <i class="fas fa-exclamation-circle"></i>
                            <div>${errors.join('<br>')}</div>
                        `;
                        
                        // Trigger animation
                        errorDiv.style.animation = 'none';
                        setTimeout(() => {
                            errorDiv.style.animation = 'fadeIn 0.5s ease-out';
                        }, 10);
                        
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = document.getElementById('submitBtn');
                    if (submitBtn) {
                        submitBtn.innerHTML = `
                            <span class="loading-spinner"></span>
                            <span class="btn-text">Connexion en cours...</span>
                        `;
                        submitBtn.disabled = true;
                    }
                    
                    return true;
                });
            }
            
            // Input focus effects
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
            
            // Logo hover effect
            const logo = document.querySelector('.logo-container');
            if (logo) {
                logo.addEventListener('mouseenter', function() {
                    const img = this.querySelector('img');
                    if (img) {
                        img.style.transform = 'scale(1.05) rotate(2deg)';
                    }
                });
                
                logo.addEventListener('mouseleave', function() {
                    const img = this.querySelector('img');
                    if (img) {
                        img.style.transform = 'scale(1) rotate(0deg)';
                    }
                });
            }
            
            // Auto-remove error message when user starts typing
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    const errorDiv = document.querySelector('.alert-error');
                    if (errorDiv && this.value.trim() !== '') {
                        errorDiv.remove();
                    }
                });
            });
        });
    </script>
</body>
</html>