<?php
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

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mot de passe oublié - Medsense</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="../../assets/css/forgot-password.css">
    <link rel="stylesheet" href="../../assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 

#submitBtn {
    width: 100% !important;
    height: auto !important;
    min-height: 50px !important;
    padding: 15px 20px !important;
    margin: 15px 0 0 0 !important;
    background: linear-gradient(135deg, #1a56db 0%, #3b82f6 100%) !important;
    background-color: #1a56db !important;
    border: none !important;
    border-radius: 12px !important;
    color: white !important;
    font-weight: 600 !important;
    font-size: 1.1rem !important;
    text-align: center !important;
    line-height: normal !important;
    cursor: pointer !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: center !important;
    position: relative !important;
    overflow: visible !important;
    visibility: visible !important;
    opacity: 1 !important;
    box-shadow: 0 5px 15px rgba(26, 86, 219, 0.3) !important;
    transition: all 0.3s ease !important;
    z-index: 1000 !important;
    transform: none !important;
}

#submitBtn:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 10px 25px rgba(26, 86, 219, 0.4) !important;
}

#submitBtn:active {
    transform: translateY(-1px) !important;
}

#submitBtn:focus {
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5) !important;
}

#submitBtn i {
    margin-right: 10px !important;
    font-size: 1.2rem !important;
    color: white !important;
}


button.btn,
input[type="submit"],
input[type="button"],
button[type="submit"] {
    box-sizing: border-box !important;
}


.btn-primary,
.btn {
    background-image: none !important;
    text-shadow: none !important;
}

#forgotPasswordForm button[type="submit"] {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
}


#submitBtn:disabled {
    opacity: 0.7 !important;
    cursor: not-allowed !important;
    transform: none !important;
}


@keyframes pulse-attention {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.auth-container:not(.success-shown) #submitBtn {
    animation: pulse-attention 2s ease-in-out 3s 3;
}

@media (max-width: 768px) {
    #submitBtn {
        padding: 12px 15px !important;
        font-size: 1rem !important;
        min-height: 45px !important;
    }
    
    #submitBtn i {
        font-size: 1rem !important;
        margin-right: 8px !important;
    }
}

@media (max-width: 480px) {
    #submitBtn {
        padding: 12px !important;
        font-size: 1rem !important;
        min-height: 45px !important;
    }
}
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px 0;
}


.auth-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
    padding: 40px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.18);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.auth-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(50, 50, 93, 0.15), 0 10px 20px rgba(0, 0, 0, 0.1);
}


.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-header h3 {
    color: #2d3436;
    font-weight: 700;
    margin: 20px 0 10px;
    font-size: 1.8rem;
}

.auth-header p {
    color: #636e72;
    font-size: 1rem;
    margin-bottom: 0;
}


.auth-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}


.auth-instructions {
    background: #f8f9fa;
    border-left: 4px solid #667eea;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-size: 0.95rem;
    color: #495057;
}

.auth-instructions i {
    color: #667eea;
}

#forgotPasswordForm {
    margin-top: 30px;
}

.auth-form-group {
    margin-bottom: 25px;
    position: relative;
}

.auth-form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #495057;
    font-size: 0.95rem;
}

.auth-form-label i {
    color: #667eea;
    width: 20px;
}

.auth-form-control {
    width: 100%;
    padding: 12px 16px 12px 45px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    color: #495057;
}

.auth-form-control:focus {
    border-color: #667eea;
    background-color: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.auth-form-control:focus + .realtime-validation {
    opacity: 1;
    transform: translateY(0);
}


.position-relative {
    position: relative;
}

.auth-input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    z-index: 2;
    transition: color 0.3s ease;
}

.auth-form-control:focus ~ .auth-input-icon {
    color: #667eea;
}


.realtime-validation {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0;
    transition: all 0.3s ease;
    color: #28a745;
    font-size: 1.1rem;
}


.auth-error-message {
    display: none;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 5px;
    padding: 5px 0;
}

.auth-form-control:invalid:not(:focus):not(:placeholder-shown) ~ .auth-error-message {
    display: block;
    animation: slideInDown 0.3s ease;
}


.auth-btn-primary {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 10px;
}

.auth-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.auth-btn-primary:active {
    transform: translateY(0);
}

.auth-btn-primary i {
    margin-right: 8px;
}

.btn-outline-primary {
    padding: 10px 20px;
    border: 2px solid #667eea;
    border-radius: 10px;
    color: #667eea;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
}


.auth-back-to-login {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.auth-back-to-login a {
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.auth-back-to-login a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.auth-back-to-login i {
    color: #667eea;
    transition: transform 0.3s ease;
}

.auth-back-to-login:hover i {
    transform: translateX(-5px);
}


.auth-logo-container {
    text-align: center;
    margin-bottom: 30px;
}

.auth-logo {
    font-size: 2.5rem;
    font-weight: 800;
    color: #2d3436;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.auth-logo:hover {
    color: #667eea;
    transform: scale(1.05);
}

.auth-logo i {
    color: #667eea;
    font-size: 2.8rem;
}

.auth-alert {
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 25px;
    border: none;
    display: flex;
    align-items: center;
    animation-duration: 0.5s;
}

.auth-alert-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
    color: white;
}

.auth-alert-success {
    background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
    color: white;
}

.auth-alert i {
    font-size: 1.2rem;
    margin-right: 10px;
}

.security-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 50px;
    color: #6c757d;
    font-size: 0.9rem;
    margin: 20px 0;
    border: 1px solid #e9ecef;
}

.security-badge i {
    color: #28a745;
}


.auth-form-footer {
    text-align: center;
    margin-top: 30px;
    padding: 20px;
    color: #6c757d;
    font-size: 0.9rem;
    border-top: 1px solid #e9ecef;
}


.forgot-password-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 0;
    position: relative;
    padding: 0 20px;
}

.forgot-password-steps::before {
    content: '';
    position: absolute;
    top: 25px;
    left: 20%;
    right: 20%;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.forgot-password-step {
    text-align: center;
    z-index: 2;
    position: relative;
    flex: 1;
}

.forgot-password-step-icon {
    width: 50px;
    height: 50px;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    color: #6c757d;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    border: 3px solid white;
}

.forgot-password-step.active .forgot-password-step-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.forgot-password-step-title {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
}

.forgot-password-step.active .forgot-password-step-title {
    color: #667eea;
}


.auth-loading-spinner {
    display: none;
    text-align: center;
    padding: 20px;
}

.loading-dots {
    display: inline-flex;
    gap: 8px;
}

.loading-dots div {
    width: 12px;
    height: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    animation: loading-dots 1.4s infinite ease-in-out both;
}

.loading-dots div:nth-child(1) { animation-delay: -0.32s; }
.loading-dots div:nth-child(2) { animation-delay: -0.16s; }
.loading-dots div:nth-child(3) { animation-delay: 0s; }
.loading-dots div:nth-child(4) { animation-delay: 0.16s; }

@keyframes loading-dots {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

.email-icon-animation {
    animation: email-float 3s ease-in-out infinite;
}

@keyframes email-float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(5deg); }
}

@media (max-width: 768px) {
    .auth-container {
        padding: 30px 20px;
    }
    
    .auth-logo {
        font-size: 2rem;
    }
    
    .auth-logo i {
        font-size: 2.2rem;
    }
    
    .forgot-password-steps::before {
        left: 15%;
        right: 15%;
    }
    
    .forgot-password-step-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .auth-header h3 {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .auth-page {
        padding: 10px;
    }
    
    .auth-container {
        padding: 25px 15px;
    }
    
    .forgot-password-steps {
        padding: 0 10px;
    }
    
    .forgot-password-steps::before {
        left: 10%;
        right: 10%;
    }
    
    .forgot-password-step-title {
        font-size: 0.75rem;
    }
}
    </style>
</head>
<body class="auth-page">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            
            <div class="auth-container animate-fade-in-up">
                <div class="auth-header">
                    <div class="auth-icon animate-bounce-in">
                        <i class="fas fa-key email-icon-animation"></i>
                    </div>
                    <h3 class="animate-fade-in">Mot de passe oublié ?</h3>
                    <p class="animate-fade-in animate-delay-1">Entrez votre email pour réinitialiser votre mot de passe</p>
                </div>
                
                <div class="auth-instructions animate-fade-in-left animate-delay-2">
                    <i class="fas fa-info-circle mr-2"></i>
                    Vous recevrez un lien par email pour créer un nouveau mot de passe.
                </div>

                <?php if ($error_message): ?>
                    <div class="auth-alert auth-alert-danger alert-dismissible fade show animate-fade-in animate-shake" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="auth-alert auth-alert-success animate-fade-in animate-pulse" role="alert">
                        <i class="fas fa-check-circle mr-2 animate-float"></i>
                        <?= $success_message ?>
                        <div class="mt-3 animate-fade-in animate-delay-1">
                            <a href="sign-in.php" class="btn btn-outline-primary animate-fade-in animate-delay-2">
                                <i class="fas fa-sign-in-alt mr-2"></i> Retour à la connexion
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$success_message): ?>
                    <form method="POST" action="" id="forgotPasswordForm" novalidate>
                        <div class="auth-form-group animate-fade-in-up animate-delay-3">
                            <label for="email" class="auth-form-label">
                                <i class="fas fa-envelope mr-2"></i> Adresse Email
                            </label>
                            <div class="position-relative">
                                <span class="auth-input-icon">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       class="auth-form-control forgot-password-email-input" 
                                       placeholder="exemple@email.com" 
                                       required
                                       pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                       title="Veuillez entrer une adresse email valide (ex: nom@domaine.com)"
                                       maxlength="100"
                                       autocomplete="email"
                                       aria-describedby="emailHelp emailError"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                <div class="realtime-validation" id="emailValidationIcon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <small id="emailHelp" class="form-text text-muted">
                                Saisissez l'adresse email associée à votre compte
                            </small>
                            <div id="emailError" class="auth-error-message">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Veuillez entrer une adresse email valide
                            </div>
                        </div>
                        
                       
                        <div class="forgot-password-steps animate-fade-in animate-delay-4">
                            <div class="forgot-password-step active">
                                <div class="forgot-password-step-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="forgot-password-step-title">Entrez votre email</div>
                            </div>
                            <div class="forgot-password-step">
                                <div class="forgot-password-step-icon">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div class="forgot-password-step-title">Recevez le lien</div>
                            </div>
                            <div class="forgot-password-step">
                                <div class="forgot-password-step-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div class="forgot-password-step-title">Réinitialisez</div>
                            </div>
                        </div>
                        
                        <div class="auth-loading-spinner" id="loadingSpinner">
                            <div class="loading-dots">
                                <div></div><div></div><div></div><div></div>
                            </div>
                            <p class="mt-2">Envoi en cours...</p>
                        </div>
                        
                        <div class="security-badge animate-fade-in animate-delay-5">
                            <i class="fas fa-shield-alt"></i>
                            <span>Vos données sont sécurisées</span>
                        </div>
                        
                        <button type="submit" class="auth-btn-primary forgot-password-btn-submit" id="submitBtn" aria-label="Envoyer le lien de réinitialisation">
                            <i class="fas fa-paper-plane mr-2"></i> Envoyer le lien de réinitialisation
                        </button>
                    </form>
                    
                    <div class="auth-back-to-login animate-fade-in animate-delay-6">
                        <p>
                            <i class="fas fa-arrow-left mr-2"></i>
                            <a href="sign-in.php">Retour à la page de connexion</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="auth-form-footer animate-fade-in animate-delay-7">
                <small class="text-muted">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Vos informations sont sécurisées et confidentielles
                </small>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/forgot-password.js"></script>

</body>
</html>