<?php
session_start();
require_once __DIR__ . '/../../../controllers/PasswordController.php';

$passwordController = new PasswordController();
$error_message = null;
$success_message = null;
$token = $_GET['token'] ?? '';
if (empty($token) && !isset($_POST['new_password'])) {
    $error_message = "Lien de réinitialisation invalide ou expiré.";
} elseif (!empty($token)) {
    $tokenCheck = $passwordController->validateToken($token);
    if (!$tokenCheck['success']) {
        $error_message = $tokenCheck['message'];
        $token = ''; 
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        $error_message = "Veuillez remplir tous les champs.";
    } elseif ($newPassword !== $confirmPassword) {
        $error_message = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($newPassword) < 8) {
        $error_message = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        $result = $passwordController->resetPassword($token, $newPassword);
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Réinitialiser le mot de passe - Medsense</title>
       <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="../../assets/css/reset-password.css">
    <link rel="stylesheet" href="../../assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="auth-logo-container">
                <a href="../../index.php" class="auth-logo">
                    <i class="fas fa-heartbeat"></i> Medsense
                </a>
            </div>
            
            <div class="auth-container">
                <div class="auth-header">
                    <div class="auth-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3>Réinitialisation du mot de passe</h3>
                    <p>Créez un nouveau mot de passe sécurisé</p>
                </div>
                
                <?php if ($error_message): ?>
                    <div class="auth-alert auth-alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="auth-alert auth-alert-success" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?= htmlspecialchars($success_message) ?>
                        <div class="mt-3 text-center">
                            <a href="sign-in.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt mr-2"></i> Retour à la connexion
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$success_message && !empty($token)): ?>
                    <div class="auth-instructions">
                        <i class="fas fa-info-circle mr-2"></i>
                        Votre nouveau mot de passe doit contenir au moins 8 caractères avec des lettres et des chiffres.
                    </div>
                    
                    <form method="POST" action="" id="resetPasswordForm" novalidate>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <div class="auth-form-group">
                            <label for="new_password" class="auth-form-label">
                                <i class="fas fa-lock mr-2"></i> Nouveau mot de passe
                            </label>
                            <div class="position-relative">
                                <span class="auth-input-icon">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" 
                                       name="new_password" 
                                       id="new_password" 
                                       class="auth-form-control" 
                                       placeholder="Saisissez votre nouveau mot de passe" 
                                       required
                                       minlength="8"
                                       pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$"
                                       title="Le mot de passe doit contenir au moins 8 caractères avec des lettres et des chiffres"
                                       autocomplete="new-password"
                                       aria-describedby="passwordHelp passwordError">
                                <button type="button" class="auth-password-toggle" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="auth-password-strength" id="passwordStrength">
                                <div class="auth-strength-bar" id="strengthBar"></div>
                            </div>
                            <small id="passwordHelp" class="form-text text-muted">
                                Minimum 8 caractères avec des lettres et des chiffres
                            </small>
                            <div id="passwordError" class="auth-error-message">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Le mot de passe doit contenir au moins 8 caractères avec des lettres et des chiffres
                            </div>
                        </div>
                        
                        <div class="auth-form-group">
                            <label for="confirm_password" class="auth-form-label">
                                <i class="fas fa-lock mr-2"></i> Confirmer le mot de passe
                            </label>
                            <div class="position-relative">
                                <span class="auth-input-icon">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" 
                                       name="confirm_password" 
                                       id="confirm_password" 
                                       class="auth-form-control" 
                                       placeholder="Confirmez votre nouveau mot de passe" 
                                       required
                                       minlength="8"
                                       autocomplete="new-password"
                                       aria-describedby="confirmHelp confirmError">
                                <button type="button" class="auth-password-toggle" data-target="confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small id="confirmHelp" class="form-text text-muted">
                                Ressaisissez votre mot de passe pour confirmation
                            </small>
                            <div id="confirmError" class="auth-error-message">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Les mots de passe ne correspondent pas
                            </div>
                            <div id="passwordMatchCheck" class="password-match-check"></div>
                        </div>
                        
                        <div class="auth-password-requirements">
                            <p class="font-weight-bold mb-2">Exigences de sécurité :</p>
                            <div class="auth-requirement" id="reqLength">
                                <i class="fas fa-circle" id="iconLength"></i>
                                <span>Au moins 8 caractères</span>
                            </div>
                            <div class="auth-requirement" id="reqLetter">
                                <i class="fas fa-circle" id="iconLetter"></i>
                                <span>Au moins une lettre</span>
                            </div>
                            <div class="auth-requirement" id="reqNumber">
                                <i class="fas fa-circle" id="iconNumber"></i>
                                <span>Au moins un chiffre</span>
                            </div>
                            <div class="auth-requirement" id="reqMatch">
                                <i class="fas fa-circle" id="iconMatch"></i>
                                <span>Les mots de passe correspondent</span>
                            </div>
                        </div>
                        
                        <div class="auth-loading-spinner" id="loadingSpinner">
                            <div class="auth-spinner"></div>
                            <p class="mt-2">Réinitialisation en cours...</p>
                        </div>
                        
                        <button type="submit" class="auth-btn-success" id="submitBtn" aria-label="Réinitialiser le mot de passe">
                            <i class="fas fa-sync-alt mr-2"></i> Réinitialiser le mot de passe
                        </button>
                    </form>
                    
                    <div class="auth-back-to-login">
                        <p>
                            <i class="fas fa-arrow-left mr-2"></i>
                            <a href="sign-in.php">Retour à la page de connexion</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="auth-form-footer">
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
<script src="js/auth.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const passwordInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('confirm_password');
    
    if (form) {
        function checkPasswordMatch() {
            const matchCheck = document.getElementById('passwordMatchCheck');
            if (passwordInput.value && confirmInput.value) {
                if (passwordInput.value === confirmInput.value) {
                    matchCheck.textContent = '✓ Les mots de passe correspondent';
                    matchCheck.className = 'password-match-check valid';
                    return true;
                } else {
                    matchCheck.textContent = '✗ Les mots de passe ne correspondent pas';
                    matchCheck.className = 'password-match-check invalid';
                    return false;
                }
            }
            return false;
        }
        
        function updateRequirements() {
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;
            
            
            const reqLength = document.getElementById('reqLength');
            const iconLength = document.getElementById('iconLength');
            const hasLength = password.length >= 8;
            reqLength.className = hasLength ? 'auth-requirement met' : 'auth-requirement unmet';
            iconLength.className = hasLength ? 'fas fa-check-circle text-success' : 'fas fa-circle';
            
        
            const reqLetter = document.getElementById('reqLetter');
            const iconLetter = document.getElementById('iconLetter');
            const hasLetter = /[A-Za-z]/.test(password);
            reqLetter.className = hasLetter ? 'auth-requirement met' : 'auth-requirement unmet';
            iconLetter.className = hasLetter ? 'fas fa-check-circle text-success' : 'fas fa-circle';
            
        
            const reqNumber = document.getElementById('reqNumber');
            const iconNumber = document.getElementById('iconNumber');
            const hasNumber = /\d/.test(password);
            reqNumber.className = hasNumber ? 'auth-requirement met' : 'auth-requirement unmet';
            iconNumber.className = hasNumber ? 'fas fa-check-circle text-success' : 'fas fa-circle';
            
         
            const reqMatch = document.getElementById('reqMatch');
            const iconMatch = document.getElementById('iconMatch');
            const hasMatch = password === confirmPassword && password !== '';
            reqMatch.className = hasMatch ? 'auth-requirement met' : 'auth-requirement unmet';
            iconMatch.className = hasMatch ? 'fas fa-check-circle text-success' : 'fas fa-circle';
            
            return hasLength && hasLetter && hasNumber && hasMatch;
        }
        
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = AuthUtils.checkPasswordStrength(password);
            AuthUtils.updateStrengthBar(strength);
            
            updateRequirements();
            checkPasswordMatch();
        });
        
        confirmInput.addEventListener('input', function() {
            updateRequirements();
            checkPasswordMatch();
        });
        
        
        form.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;
            const passwordError = document.getElementById('passwordError');
            const confirmError = document.getElementById('confirmError');
            
            let isValid = true;
            
           
            if (password.length < 8 || !/[A-Za-z]/.test(password) || !/\d/.test(password)) {
                e.preventDefault();
                passwordError.style.display = 'block';
                passwordInput.classList.add('is-invalid');
                isValid = false;
            }
            
         
            if (password !== confirmPassword) {
                e.preventDefault();
                confirmError.style.display = 'block';
                confirmInput.classList.add('is-invalid');
                isValid = false;
            }
            
            
            if (isValid) {
                AuthUtils.toggleSpinner(true, 'resetPasswordForm');
            }
            
            return isValid;
        });
        
       
        if (passwordInput) passwordInput.focus();
    }
});
</script>

</body>
</html>