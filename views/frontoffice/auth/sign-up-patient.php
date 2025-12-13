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
    
    $data = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
        'dateNaissance' => trim($_POST['dateNaissance'] ?? ''),
        'adresse' => trim($_POST['adresse'] ?? '')
    ];

    
    $errors = [];
    
    
    $required = ['nom', 'prenom', 'email', 'mot_de_passe'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Le champ '$field' est obligatoire";
        }
    }

  
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }

    if (strlen($data['mot_de_passe']) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    }

    
    if (!empty($data['dateNaissance'])) {
        $birthDate = new DateTime($data['dateNaissance']);
        $today = new DateTime();
        $minDate = new DateTime();
        $minDate->modify('-120 years');
        
        if ($birthDate > $today) {
            $errors[] = "La date de naissance ne peut pas être dans le futur";
        } elseif ($birthDate < $minDate) {
            $errors[] = "L'âge maximum est de 120 ans";
        }
    }

    
    if (empty($errors)) {
        $data['role'] = 'patient';
        $data['statut'] = 'actif';
     
        $result = $authController->register($data);
        
        if ($result['success']) {
            header('Location: sign-in.php?success=1');
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
    <title>Inscription Patient - Medcare</title>
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/sign-up-patient.css">
    <style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f0f7ff 0%, #e1f0ff 100%);
    min-height: 100vh;
    color: #333;
    line-height: 1.6;
    padding: 20px;
}

.container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(42, 116, 255, 0.15);
    position: relative;
    z-index: 1;
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e8f4ff;
}

.logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
}

.logo i {
    font-size: 3rem;
    color: #2a74ff;
    animation: pulse 2s infinite ease-in-out;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.logo span {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #2a74ff 0%, #1d5fe0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.header h1 {
    font-size: 2.2rem;
    color: #0c1b45;
    margin-bottom: 10px;
}

.header p {
    color: #6c7a96;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.alert {
    padding: 18px 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert-error {
    background: linear-gradient(135deg, #ffeded 0%, #ffeaea 100%);
    border-left: 4px solid #e74c3c;
    color: #c0392b;
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-left: 4px solid #2ecc71;
    color: #27ae60;
}

.alert-patient {
    background: linear-gradient(135deg, #e8f4ff 0%, #d4e7ff 100%);
    border-left: 4px solid #3498db;
    color: #2980b9;
}

.alert i {
    font-size: 22px;
    margin-top: 2px;
}

.alert div {
    flex: 1;
    line-height: 1.5;
}

.info-box {
    background: #e8f4ff;
    border: 2px solid #2a74ff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.info-box i {
    color: #2a74ff;
    font-size: 24px;
    margin-top: 2px;
}

.info-box p {
    margin: 0;
    color: #2c3e50;
}

.form-section {
    margin-bottom: 35px;
    animation: slideUp 0.5s ease-out;
    animation-fill-mode: both;
}

.form-section:nth-child(1) { animation-delay: 0.1s; }
.form-section:nth-child(2) { animation-delay: 0.2s; }
.form-section:nth-child(3) { animation-delay: 0.3s; }
.form-section:nth-child(4) { animation-delay: 0.4s; }

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0c1b45;
    margin-bottom: 25px;
    font-size: 1.5rem;
    font-weight: 600;
}

.section-title i {
    color: #2a74ff;
    font-size: 1.3rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    color: #0c1b45;
    font-weight: 600;
    font-size: 1rem;
}

.form-group label .required {
    color: #e74c3c;
    margin-left: 4px;
}

.form-control {
    width: 100%;
    padding: 15px 18px;
    border: 2px solid #e0e6ef;
    border-radius: 12px;
    font-size: 16px;
    background: #f8fafc;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #2a74ff;
    background: white;
    box-shadow: 0 0 0 4px rgba(42, 116, 255, 0.1);
}

.form-control.valid {
    border-color: #2ecc71;
    background: #f0fdf4;
}

.form-control.invalid {
    border-color: #e74c3c;
    background: #ffeded;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
    line-height: 1.5;
}

.password-input-wrapper {
    position: relative;
    display: flex;
    gap: 10px;
}

.password-generator-btn {
    background: #2a74ff;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-generator-btn:hover {
    background: #1d5fe0;
    transform: scale(1.05);
}

.password-requirements {
    font-size: 14px;
    color: #6c7a96;
    margin-top: 8px;
    font-style: italic;
}

.suggested-password {
    background: #f8fafc;
    border: 2px solid #e0e6ef;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.suggested-password span {
    flex: 1;
    font-family: 'Courier New', monospace;
    font-size: 15px;
    padding: 10px;
    background: white;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    word-break: break-all;
    min-width: 200px;
}

.btn-small {
    padding: 8px 16px;
    background: #2a74ff;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.btn-small:hover {
    background: #1d5fe0;
    transform: translateY(-2px);
}

.password-strength {
    margin-top: 12px;
}

.strength-text {
    font-size: 14px;
    color: #6c7a96;
    margin-bottom: 5px;
}

.strength-bar {
    height: 8px;
    background: #e0e6ef;
    border-radius: 4px;
    overflow: hidden;
}

.strength-fill {
    height: 100%;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.strength-fill.weak { width: 25%; background: #e74c3c; }
.strength-fill.fair { width: 50%; background: #f39c12; }
.strength-fill.good { width: 75%; background: #3498db; }
.strength-fill.strong { width: 100%; background: #2ecc71; }

.validation-message {
    font-size: 14px;
    margin-top: 8px;
    min-height: 20px;
    line-height: 1.4;
}

.validation-message.error {
    color: #e74c3c;
    font-weight: 500;
}

.validation-message.success {
    color: #2ecc71;
}


.ai-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    border: none;
    border-radius: 14px;
    padding: 16px 32px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 20px auto;
    width: 100%;
    max-width: 350px;
}

.ai-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

.ai-button.loading {
    opacity: 0.8;
    cursor: not-allowed;
}

.ia-suggestions {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    padding: 25px;
    margin: 25px 0;
    border-left: 5px solid #8b5cf6;
    animation: slideIn 0.5s ease-out;
}

.ia-suggestions h4 {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0c1b45;
    margin-bottom: 15px;
    font-size: 1.3rem;
}

.ia-suggestions ul {
    list-style: none;
    margin: 15px 0;
    padding-left: 20px;
}

.ia-suggestions li {
    margin-bottom: 10px;
    padding-left: 12px;
    position: relative;
    color: #2c3e50;
}

.ia-suggestions li:before {
    content: "✨";
    position: absolute;
    left: -15px;
    font-size: 14px;
}

.confidence-score {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-top: 25px;
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;
    gap: 20px;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.score-bar {
    height: 10px;
    background: #e0e6ef;
    border-radius: 5px;
    overflow: hidden;
}

.score-fill {
    height: 100%;
    border-radius: 5px;
    transition: width 0.5s ease;
}
.btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: linear-gradient(135deg, #2a74ff 0%, #1d5fe0 100%);
    color: white;
    border: none;
    border-radius: 14px;
    padding: 18px 40px;
    font-size: 1.2rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    width: 100%;
    max-width: 400px;
    margin: 30px auto;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(42, 116, 255, 0.3);
}

.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none !important;
}

.loading {
    text-align: center;
    padding: 20px;
    color: #2a74ff;
    font-weight: 600;
    display: none;
}

.loading i {
    margin-right: 10px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.footer-links {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #e8f4ff;
}

.footer-links a {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #2a74ff;
    text-decoration: none;
    font-weight: 500;
    padding: 12px 25px;
    border-radius: 12px;
    background: #f8fafc;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.footer-links a:hover {
    background: #2a74ff;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(42, 116, 255, 0.2);
}

.footer-links a i {
    font-size: 16px;
}

.notification {
    position: fixed;
    top: 25px;
    right: 25px;
    background: white;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 15px;
    z-index: 1000;
    animation: slideInRight 0.3s ease-out;
    max-width: 450px;
}

.notification.success {
    border-left: 5px solid #2ecc71;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #27ae60;
}

.notification.error {
    border-left: 5px solid #e74c3c;
    background: linear-gradient(135deg, #ffeded 0%, #ffeaea 100%);
    color: #c0392b;
}

.notification.info {
    border-left: 5px solid #3498db;
    background: linear-gradient(135deg, #e8f4ff 0%, #d4e7ff 100%);
    color: #2980b9;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100px);
    }
}
@media (max-width: 768px) {
    .container {
        padding: 25px 20px;
        margin: 10px;
    }
    
    .header h1 {
        font-size: 1.8rem;
    }
    
    .header p {
        font-size: 1rem;
    }
    
    .logo i {
        font-size: 2.2rem;
    }
    
    .logo span {
        font-size: 1.8rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-control {
        padding: 14px 16px;
    }
    
    .ai-button {
        padding: 14px 25px;
        font-size: 1rem;
        max-width: 300px;
    }
    
    .btn {
        padding: 16px 30px;
        font-size: 1.1rem;
        max-width: 350px;
    }
    
    .footer-links {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
    
    .footer-links a {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .confidence-score {
        grid-template-columns: 1fr;
        gap: 15px;
        text-align: center;
    }
    
    .ia-suggestions {
        padding: 20px;
    }
    
    .notification {
        left: 20px;
        right: 20px;
        max-width: none;
    }
}

@media (max-width: 480px) {
    body {
        padding: 10px;
    }
    
    .container {
        padding: 20px 15px;
    }
    
    .header h1 {
        font-size: 1.6rem;
    }
    
    .logo {
        flex-direction: column;
        gap: 10px;
    }
    
    .logo i {
        font-size: 2rem;
    }
    
    .logo span {
        font-size: 1.6rem;
    }
    
    .form-control {
        padding: 12px 14px;
    }
    
    .suggested-password {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    
    .suggested-password span {
        min-width: 0;
    }
    
    .btn-small {
        width: 100%;
        justify-content: center;
    }
    
    .btn {
        padding: 15px 25px;
        font-size: 1rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}


::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #2a74ff;
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: #1d5fe0;
}


:focus-visible {
    outline: 3px solid #2a74ff;
    outline-offset: 2px;
    border-radius: 4px;
}


@media print {
    body {
        background: white !important;
    }
    
    .container {
        box-shadow: none !important;
        padding: 0 !important;
    }
    
    .ai-button,
    .footer-links,
    .notification {
        display: none !important;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-user-injured"></i>
                <span>Medsense</span>
            </div>
            <h1>Inscription Patient</h1>
            <p>Créez votre compte pour accéder à nos services médicaux en ligne</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <div>Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.</div>
            </div>
            <div class="footer-links">
                <a href="sign-in.php"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
                <a href="../home/index.php"><i class="fas fa-home"></i> Retour à l'accueil</a>
            </div>
            <?php exit(); ?>
        <?php endif; ?>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <p>Créez votre compte patient pour prendre rendez-vous, consulter votre dossier médical et gérer votre santé en ligne.</p>
        </div>
        
        
        <form method="POST" action="" id="patientForm" novalidate>
          
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Informations personnelles
                </h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom <span class="required">*</span></label>
                        <input type="text" id="prenom" name="prenom" class="form-control" 
                               placeholder="Votre prénom" 
                               value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>"
                               pattern="[A-Za-zÀ-ÿ\s\-']{2,}"
                               title="Le prénom doit contenir au moins 2 lettres"
                               required>
                        <div class="validation-message" id="prenom-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nom">Nom <span class="required">*</span></label>
                        <input type="text" id="nom" name="nom" class="form-control" 
                               placeholder="Votre nom"
                               value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
                               pattern="[A-Za-zÀ-ÿ\s\-']{2,}"
                               title="Le nom doit contenir au moins 2 lettres"
                               required>
                        <div class="validation-message" id="nom-error"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Adresse email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="exemple@email.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               required>
                        <div class="validation-message" id="email-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="mot_de_passe">Mot de passe <span class="required">*</span></label>
                        <div class="password-input-wrapper">
                            <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" 
                                   placeholder="Minimum 6 caractères"
                                   minlength="6"
                                   required>
                            <button type="button" id="generatePasswordBtn" class="password-generator-btn">
                                <i class="fas fa-key"></i>
                            </button>
                        </div>
                        <div class="password-requirements">Doit contenir au moins 6 caractères</div>
                        <div class="validation-message" id="mot_de_passe-error"></div>
                        
                      
                        <div id="generatedPasswordDisplay" style="display: none; margin-top: 10px;">
                            <div class="suggested-password">
                                <span id="suggestedPasswordText"></span>
                                <button type="button" id="useSuggestedPassword" class="btn-small">
                                    <i class="fas fa-check"></i> Utiliser
                                </button>
                                <button type="button" id="regeneratePassword" class="btn-small">
                                    <i class="fas fa-redo"></i> Régénérer
                                </button>
                                <button type="button" id="copyPassword" class="btn-small">
                                    <i class="fas fa-copy"></i> Copier
                                </button>
                            </div>
                            <div id="passwordStrength" style="margin-top: 5px;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="dateNaissance">Date de naissance</label>
                        <input type="date" id="dateNaissance" name="dateNaissance" class="form-control"
                               value="<?php echo isset($_POST['dateNaissance']) ? htmlspecialchars($_POST['dateNaissance']) : ''; ?>">
                        <div class="validation-message" id="dateNaissance-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <textarea id="adresse" name="adresse" class="form-control" 
                                  placeholder="Votre adresse complète" 
                                  rows="3"><?php echo isset($_POST['adresse']) ? htmlspecialchars($_POST['adresse']) : ''; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section" style="text-align: center;">
                <button type="button" id="verifyAiBtn" class="ai-button">
                    <i class="fas fa-robot"></i> ✨ Vérifier avec l'IA
                </button>
                <p style="color: #666; font-size: 14px; margin-top: 8px;">
                    <i class="fas fa-lightbulb"></i> L'IA va vérifier et corriger automatiquement vos informations
                </p>
            </div>
            
        
            <div id="iaSuggestionsContainer" style="display: none;"></div>
            
           
            <div class="form-section">
                <div class="alert alert-patient">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>Confidentialité garantie :</strong> Vos données médicales sont protégées par le secret médical et notre politique de confidentialité.
                    </div>
                </div>
            </div>
            
            
            <div class="form-section">
                <button type="submit" class="btn" id="submitBtn">
                    <i class="fas fa-user-plus"></i>
                    Créer mon compte patient
                </button>
                
                <div class="loading" id="loading">
                    <i class="fas fa-spinner fa-spin"></i> Création du compte en cours...
                </div>
            </div>
        </form>
        
        
        <div class="footer-links">
            <a href="select-role.php"><i class="fas fa-arrow-left"></i> Changer de type de compte</a>
            <a href="sign-in.php"><i class="fas fa-sign-in-alt"></i> Déjà un compte ? Connectez-vous</a>
            <a href="../home/index.php"><i class="fas fa-home"></i> Retour à l'accueil</a>
        </div>
    </div>

    <script>
      
        function generateSecurePassword() {
            const length = 12;
            const charset = {
                lowercase: 'abcdefghijklmnopqrstuvwxyz',
                uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                numbers: '0123456789',
                special: '!@#$%^&*()_+-=[]{}|;:,.<>?'
            };
            
           
            let password = '';
            password += getRandomChar(charset.lowercase);
            password += getRandomChar(charset.uppercase);
            password += getRandomChar(charset.numbers);
            password += getRandomChar(charset.special);
            
           
            const allChars = charset.lowercase + charset.uppercase + charset.numbers + charset.special;
            for (let i = password.length; i < length; i++) {
                password += getRandomChar(allChars);
            }
            
           
            password = shuffleString(password);
            
            return password;
        }

        function getRandomChar(charset) {
            return charset.charAt(Math.floor(Math.random() * charset.length));
        }

        function shuffleString(string) {
            const array = string.split('');
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array.join('');
        }

       
        function checkPasswordStrength(password) {
            let score = 0;
            
          
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            
            
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            if (score <= 2) return { level: 'weak', text: 'Faible' };
            if (score <= 4) return { level: 'fair', text: 'Moyen' };
            if (score <= 5) return { level: 'good', text: 'Bon' };
            return { level: 'strong', text: 'Fort' };
        }

     
        function updatePasswordStrength(password) {
            const strengthElement = document.getElementById('passwordStrength');
            if (!strengthElement) return;
            
            if (!password || password.length === 0) {
                strengthElement.innerHTML = '';
                return;
            }
            
            const strength = checkPasswordStrength(password);
            strengthElement.innerHTML = `
                <div class="password-strength">
                    <div class="strength-text">Force : ${strength.text}</div>
                    <div class="strength-bar">
                        <div class="strength-fill ${strength.level}"></div>
                    </div>
                </div>
            `;
        }

       
        function showNotification(message, type = 'info') {
           
            document.querySelectorAll('.notification').forEach(n => n.remove());
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                               type === 'error' ? 'fa-exclamation-circle' : 
                               'fa-info-circle'}"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
        
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        async function verifyForm() {
            const button = document.getElementById('verifyAiBtn');
            const originalText = button.innerHTML;
            
            try {
                button.classList.add('loading');
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyse en cours...';
                button.disabled = true;
             
                const formData = {
                    nom: document.getElementById("nom").value,
                    prenom: document.getElementById("prenom").value,
                    email: document.getElementById("email").value,
                    adresse: document.getElementById("adresse").value,
                    dateNaissance: document.getElementById("dateNaissance").value
                };
                
                console.log("Données envoyées à l'IA :", formData);
             
                const possiblePaths = [
                    '../../../controllers/verify_ai.php',          
                    window.location.origin + '/verify_ai.php' 
                ];
                
                let response = null;
                let successfulPath = null;
                
                for (let path of possiblePaths) {
                    try {
                        console.log("Test du chemin :", path);
                        const testResponse = await fetch(path, {
                            method: "POST",
                            headers: {"Content-Type": "application/json"},
                            body: JSON.stringify(formData)
                        });
                        
                        if (testResponse.ok) {
                            response = testResponse;
                            successfulPath = path;
                            console.log("✓ Chemin valide :", path);
                            break;
                        }
                    } catch (err) {
                        console.log("✗ Échec avec", path, ":", err.message);
                        continue;
                    }
                }
                
                if (!response) {
                    throw new Error("Impossible de trouver le fichier verify_ai.php. Vérifiez qu'il existe dans le dossier.");
                }
                
                const data = await response.json();
                console.log("Réponse IA reçue :", data);
                
                if (!data.success) {
                    throw new Error(data.error || 'Erreur lors de la vérification IA');
                }
                
                let correctionsApplied = 0;
                
                if (data.correct.nom && data.correct.nom !== formData.nom) {
                    document.getElementById("nom").value = data.correct.nom;
                    correctionsApplied++;
                }
                
                if (data.correct.prenom && data.correct.prenom !== formData.prenom) {
                    document.getElementById("prenom").value = data.correct.prenom;
                    correctionsApplied++;
                }
                
                if (data.correct.email && data.correct.email !== formData.email) {
                    document.getElementById("email").value = data.correct.email;
                    correctionsApplied++;
                }
                
                if (data.correct.adresse && data.correct.adresse !== formData.adresse) {
                    document.getElementById("adresse").value = data.correct.adresse;
                    correctionsApplied++;
                }
                
                if (data.correct.dateNaissance && data.correct.dateNaissance !== formData.dateNaissance) {
                    document.getElementById("dateNaissance").value = data.correct.dateNaissance;
                    correctionsApplied++;
                }
                
            
                showIASuggestions(data.suggestions, data.confidence_score, correctionsApplied);
                
           
                if (correctionsApplied > 0) {
                    showNotification(`✅ ${correctionsApplied} correction(s) appliquée(s) par l'IA`, 'success');
                } else {
                    showNotification('✅ Tous les champs sont déjà corrects', 'info');
                }
                
            } catch (error) {
                console.error('Erreur IA détaillée :', error);
                showNotification(`❌ ${error.message}`, 'error');
            } finally {
                
                button.classList.remove('loading');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
        
        
        function showIASuggestions(suggestions, confidenceScore, correctionsApplied) {
            const container = document.getElementById('iaSuggestionsContainer');
            
            if (!suggestions || suggestions.length === 0) {
                container.style.display = 'none';
                return;
            }
            
          
            let scoreColor = '#10b981'; 
            let scoreText = 'Excellent';
            
            if (confidenceScore < 70) {
                scoreColor = '#f59e0b'; 
                scoreText = 'Bon';
            }
            if (confidenceScore < 50) {
                scoreColor = '#ef4444'; 
                scoreText = 'À améliorer';
            }
            
            let html = `
                <div class="ia-suggestions">
                    <h4><i class="fas fa-robot"></i> Suggestions de l'IA</h4>
                    <p>L'intelligence artificielle a analysé vos informations :</p>
                    
                    ${correctionsApplied > 0 ? 
                        `<div style="background: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin: 10px 0;">
                            <i class="fas fa-check-circle"></i> ${correctionsApplied} correction(s) ont été appliquée(s) automatiquement
                        </div>` : ''
                    }
                    
                    <ul>
                        ${suggestions.map(suggestion => `<li>${suggestion}</li>`).join('')}
                    </ul>
                    
                    <div class="confidence-score">
                        <div>
                            <strong>Score de confiance :</strong>
                            <div style="font-size: 12px; color: #6b7280;">Qualité des données</div>
                        </div>
                        <div class="score-bar">
                            <div class="score-fill" style="width: ${confidenceScore}%; background: ${scoreColor};"></div>
                        </div>
                        <div style="font-weight: bold; color: ${scoreColor};">
                            ${scoreText} (${confidenceScore}%)
                        </div>
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
            container.style.display = 'block';
            
         
            setTimeout(() => {
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 500);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('patientForm');
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            const verifyAiBtn = document.getElementById('verifyAiBtn');
           
            verifyAiBtn.addEventListener('click', verifyForm);
            
            const generateBtn = document.getElementById('generatePasswordBtn');
            const suggestedPasswordText = document.getElementById('suggestedPasswordText');
            const generatedPasswordDisplay = document.getElementById('generatedPasswordDisplay');
            const useSuggestedBtn = document.getElementById('useSuggestedPassword');
            const regenerateBtn = document.getElementById('regeneratePassword');
            const copyBtn = document.getElementById('copyPassword');
            const passwordInput = document.getElementById('mot_de_passe');
            
            console.log("Générateur bouton:", generateBtn);
            console.log("Champ mot de passe:", passwordInput);
            
            if (generateBtn) {
                generateBtn.addEventListener('click', function() {
                    console.log("Bouton générateur cliqué");
                    const newPassword = generateSecurePassword();
                    if (suggestedPasswordText) {
                        suggestedPasswordText.textContent = newPassword;
                    }
                    if (generatedPasswordDisplay) {
                        generatedPasswordDisplay.style.display = 'block';
                    }
                    updatePasswordStrength(newPassword);
                });
            }
            
            if (useSuggestedBtn) {
                useSuggestedBtn.addEventListener('click', function() {
                    if (passwordInput && suggestedPasswordText) {
                        passwordInput.value = suggestedPasswordText.textContent;
                        passwordInput.type = 'text';
                        updatePasswordStrength(passwordInput.value);
                     
                        setTimeout(() => {
                            passwordInput.type = 'password';
                        }, 3000);
                    }
                });
            }
            
            if (regenerateBtn) {
                regenerateBtn.addEventListener('click', function() {
                    const newPassword = generateSecurePassword();
                    if (suggestedPasswordText) {
                        suggestedPasswordText.textContent = newPassword;
                    }
                    updatePasswordStrength(newPassword);
                });
            }
            
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    if (suggestedPasswordText && navigator.clipboard) {
                        navigator.clipboard.writeText(suggestedPasswordText.textContent)
                            .then(() => {
                            
                                const originalText = copyBtn.innerHTML;
                                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copié!';
                                setTimeout(() => {
                                    copyBtn.innerHTML = originalText;
                                }, 2000);
                            })
                            .catch(err => {
                                console.error('Erreur lors de la copie: ', err);
                            });
                    }
                });
            }
            
         
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    updatePasswordStrength(this.value);
                });
            }
            
          
            const fields = {
                'prenom': {
                    required: true,
                    pattern: /^[A-Za-zÀ-ÿ\s\-']{2,}$/,
                    message: 'Le prénom doit contenir au moins 2 lettres'
                },
                'nom': {
                    required: true,
                    pattern: /^[A-Za-zÀ-ÿ\s\-']{2,}$/,
                    message: 'Le nom doit contenir au moins 2 lettres'
                },
                'email': {
                    required: true,
                    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    message: 'Veuillez entrer une adresse email valide'
                },
                'mot_de_passe': {
                    required: true,
                    minLength: 6,
                    message: 'Le mot de passe doit contenir au moins 6 caractères'
                }
            };
   
            Object.keys(fields).forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const config = fields[fieldName];
                
                if (field) {
                    field.addEventListener('blur', function() {
                        validateField(field, config);
                    });
                    
                    field.addEventListener('input', function() {
                        if (field.classList.contains('invalid')) {
                            validateField(field, config);
                        }
                    });
                }
            });
            
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                Object.keys(fields).forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    const config = fields[fieldName];
                    
                    if (field && !validateField(field, config)) {
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    const firstError = document.querySelector('.validation-message.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    submitBtn.disabled = true;
                    if (loading) loading.style.display = 'block';
                }
            });
            
            function validateField(field, config) {
                const value = field.value.trim();
                const errorElement = document.getElementById(field.id + '-error');
                
                if (config.required && !value) {
                    showError(field.id, 'Ce champ est obligatoire');
                    return false;
                }
                
                if (config.pattern && value && !config.pattern.test(value)) {
                    showError(field.id, config.message);
                    return false;
                }
                
                if (config.minLength && value.length < config.minLength) {
                    showError(field.id, config.message);
                    return false;
                }
                
                clearError(field.id);
                return true;
            }
            
            function showError(fieldId, message) {
                const field = document.getElementById(fieldId);
                const errorElement = document.getElementById(fieldId + '-error');
                
                if (field) {
                    field.classList.add('invalid');
                    field.classList.remove('valid');
                }
                
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.className = 'validation-message error';
                }
            }
            
            function clearError(fieldId) {
                const field = document.getElementById(fieldId);
                const errorElement = document.getElementById(fieldId + '-error');
                
                if (field) {
                    field.classList.remove('invalid');
                    field.classList.add('valid');
                }
                
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.className = 'validation-message';
                }
            }
        });
    </script>
</body>
</html>