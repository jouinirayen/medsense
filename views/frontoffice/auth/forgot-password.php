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
        // Appel de la fonction forgotPassword
        $result = $passwordController->forgotPassword($email);

        if ($result['success']) {
            $success_message = $result['message'];

            // Affichage du lien de débogage si nécessaire
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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="../../assets/css/forgot-password.css">
    <link rel="stylesheet" href="../../assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Ripple effect */
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Email fly animation */
        @keyframes emailFly {
            0% {
                transform: translateX(0) rotate(0deg);
            }
            25% {
                transform: translateX(10px) rotate(10deg);
            }
            50% {
                transform: translateX(0) rotate(0deg);
            }
            75% {
                transform: translateX(-10px) rotate(-10deg);
            }
            100% {
                transform: translateX(0) rotate(0deg);
            }
        }
        
        /* Security badge pulse */
        @keyframes securityPulse {
            0%, 100% {
                box-shadow: 0 0 5px rgba(40, 167, 69, 0.3);
            }
            50% {
                box-shadow: 0 0 20px rgba(40, 167, 69, 0.6);
            }
        }
    </style>
</head>
<body class="auth-page">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-logo-container animate-fade-in-down">
                <a href="../../index.php" class="auth-logo">
                    <i class="fas fa-heartbeat"></i> Medsense
                </a>
            </div>
            
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
                        
                        <!-- Étapes du processus -->
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