<?php
session_start();
include_once '../../../controllers/AuthController.php';

$authController = new AuthController();

if ($authController->isLoggedIn()) {
    header('Location: ../home/index.php');
    exit;
}


$error = '';
$success = '';
$uploadedDiplomePath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
        'dateNaissance' => trim($_POST['dateNaissance'] ?? ''),
        'adresse' => trim($_POST['adresse'] ?? ''),
        'langues' => trim($_POST['langues'] ?? ''),
        'experience' => trim($_POST['experience'] ?? '')
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

    if (!empty($data['experience']) && (!is_numeric($data['experience']) || $data['experience'] < 0 || $data['experience'] > 60)) {
        $errors[] = "L'expérience doit être un nombre entre 0 et 60 ans";
    }

    if (isset($_FILES['diplome']) && $_FILES['diplome']['error'] === UPLOAD_ERR_OK) {
        $diplome = $_FILES['diplome'];
      
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($diplome['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors[] = "Le format du fichier n'est pas accepté. Formats acceptés: PDF, JPG, JPEG, PNG";
        }
     
        $maxSize = 2 * 1024 * 1024; 
        if ($diplome['size'] > $maxSize) {
            $errors[] = "Le fichier est trop volumineux. Taille maximum: 2MB";
        }
        
        if (empty($errors)) {
         
            $uploadDir = __DIR__ . '/../../../uploads/diplomes/';

            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $errors[] = "Impossible de créer le dossier de stockage des diplômes.";
                }
            }
            
            if (empty($errors)) {
                $fileName = uniqid('diplome_', true) . '_' . time() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;
                if (move_uploaded_file($diplome['tmp_name'], $uploadPath)) {
                    $uploadedDiplomePath = 'uploads/diplomes/' . $fileName;
             
                    if (!file_exists($uploadPath)) {
                        $errors[] = "Erreur lors de l'enregistrement du fichier.";
                    }
                } else {
                    $errors[] = "Erreur lors du téléchargement du fichier. Veuillez réessayer.";
                }
            }
        }
    } else {
        $errors[] = "Veuillez télécharger votre diplôme";
    }

    if (empty($errors)) {
        $data['role'] = 'medecin';
        $data['statut'] = 'en_attente';
        $data['diplome_path'] = $uploadedDiplomePath;
        $result = $authController->register($data);
        
        if ($result['success']) {
            $success = "Votre compte médecin a été créé avec succès ! Votre compte sera activé après vérification de votre diplôme par l'administrateur.";
        } else {
            $error = $result['message'];
            if ($uploadedDiplomePath && file_exists(__DIR__ . '/../../../' . $uploadedDiplomePath)) {
                @unlink(__DIR__ . '/../../../' . $uploadedDiplomePath);
            }
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
    <title>Inscription Médecin - Medcare</title>
    <link rel="stylesheet" href="../../assets/vendors/fontawesome/css/all.min.css">
    
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
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.header {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeInDown 0.8s ease-out;
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
    font-size: 2.5rem;
    color: #0c1b45;
    margin-bottom: 15px;
}

.header-subtitle {
    color: #6c7a96;
    font-size: 1.2rem;
    max-width: 600px;
    margin: 0 auto;
}
.alert {
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    animation: slideIn 0.5s ease-out;
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-left: 4px solid #10b981;
    color: #065f46;
}

.alert-error {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-left: 4px solid #ef4444;
    color: #7f1d1d;
}

.alert-medical {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #f59e0b;
    color: #92400e;
}

.alert i {
    font-size: 24px;
    margin-top: 2px;
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
    animation: fadeIn 0.5s ease-out;
}

.info-box i {
    color: #2a74ff;
    font-size: 24px;
    margin-top: 2px;
}

.form-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    animation: slideUp 0.5s ease-out;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0c1b45;
    margin-bottom: 30px;
    font-size: 1.5rem;
}

.section-title i {
    color: #2a74ff;
    font-size: 1.3rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

.required {
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 15px;
    border: 2px solid #e2e8f0;
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
    border-color: #10b981;
    background: #f0fdf4;
}

.form-control.invalid {
    border-color: #ef4444;
    background: #fef2f2;
}

.validation-message {
    font-size: 14px;
    margin-top: 8px;
    min-height: 20px;
}

.validation-message.error {
    color: #ef4444;
}

.validation-message.success {
    color: #10b981;
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
}

.password-generator-btn:hover {
    background: #1d5fe0;
    transform: scale(1.05);
}

.password-requirements {
    font-size: 14px;
    color: #6c7a96;
    margin-top: 8px;
}

.experience-input-wrapper {
    position: relative;
}

.experience-unit {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c7a96;
    font-size: 14px;
}

.langues-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.langue-tag {
    background: #e8f4ff;
    color: #2a74ff;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.langue-tag-remove {
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    padding: 0 4px;
    transition: color 0.3s ease;
}

.langue-tag-remove:hover {
    color: #ef4444;
}

.form-help {
    font-size: 14px;
    color: #6c7a96;
    margin-top: 5px;
    display: block;
}
.ai-button {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 15px 30px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.ai-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

.ai-button.loading {
    opacity: 0.7;
    cursor: not-allowed;
}

.ia-suggestions {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    padding: 25px;
    margin-top: 20px;
    border-left: 4px solid #8b5cf6;
}

.ia-suggestions h4 {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #0c1b45;
    margin-bottom: 15px;
}

.ia-suggestions ul {
    list-style: none;
    margin: 15px 0;
    padding-left: 20px;
}

.ia-suggestions li {
    margin-bottom: 8px;
    padding-left: 10px;
    position: relative;
}

.ia-suggestions li:before {
    content: "•";
    color: #8b5cf6;
    font-weight: bold;
    position: absolute;
    left: -15px;
}

.confidence-score {
    background: white;
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;
    gap: 15px;
    align-items: center;
}

.score-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.score-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

/* ====================
   DOCUMENTS SECTION
   ==================== */
.documents-section {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.document-upload {
    border: 2px dashed #2a74ff;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(42, 116, 255, 0.05);
}

.document-upload:hover {
    background: rgba(42, 116, 255, 0.1);
    border-color: #1d5fe0;
}

.document-upload i {
    font-size: 48px;
    color: #2a74ff;
    margin-bottom: 20px;
}

.document-upload input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    top: 0;
    left: 0;
}

.document-requirements {
    margin-top: 20px;
    text-align: left;
    background: white;
    padding: 15px;
    border-radius: 8px;
    font-size: 14px;
}

.document-requirements ul {
    list-style: none;
    padding-left: 0;
    margin-top: 10px;
}

.document-requirements li {
    padding: 5px 0;
    padding-left: 25px;
    position: relative;
}

.document-requirements li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: linear-gradient(135deg, #2a74ff 0%, #1d5fe0 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 18px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(42, 116, 255, 0.3);
}

.btn-small {
    padding: 8px 16px;
    font-size: 14px;
    border-radius: 6px;
    background: #2a74ff;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-small:hover {
    background: #1d5fe0;
}

.suggested-password {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.password-strength {
    margin-top: 15px;
}

.strength-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 5px;
}

.strength-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.strength-fill.weak {
    background: #ef4444;
    width: 25%;
}

.strength-fill.fair {
    background: #f59e0b;
    width: 50%;
}

.strength-fill.good {
    background: #10b981;
    width: 75%;
}

.strength-fill.strong {
    background: #10b981;
    width: 100%;
}

.loading {
    display: none;
    text-align: center;
    padding: 20px;
    color: #2a74ff;
    font-weight: 600;
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
    gap: 25px;
    margin-top: 40px;
    padding-top: 40px;
    border-top: 1px solid #e2e8f0;
}

.footer-links a {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #2a74ff;
    text-decoration: none;
    font-weight: 500;
    padding: 12px 20px;
    border-radius: 12px;
    background: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.footer-links a:hover {
    background: #2a74ff;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(42, 116, 255, 0.2);
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 15px;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
    max-width: 400px;
}

.notification.success {
    border-left: 4px solid #10b981;
}

.notification.error {
    border-left: 4px solid #ef4444;
}

.notification.info {
    border-left: 4px solid #2a74ff;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100px);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .container {
        padding: 20px 15px;
    }
    
    .header h1 {
        font-size: 2rem;
    }
    
    .header-subtitle {
        font-size: 1rem;
        padding: 0 10px;
    }
    
    .form-section {
        padding: 20px 15px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
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
    
    .ai-button {
        width: 100%;
        justify-content: center;
    }
    
    .confidence-score {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .notification {
        left: 20px;
        right: 20px;
        max-width: none;
    }
}

@media (max-width: 480px) {
    .logo i {
        font-size: 2rem;
    }
    
    .logo span {
        font-size: 1.8rem;
    }
    
    .header h1 {
        font-size: 1.6rem;
    }
    
    .form-control {
        padding: 12px;
    }
    
    .btn {
        padding: 16px 25px;
        font-size: 1rem;
    }
    
    .document-upload {
        padding: 25px 15px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-user-md"></i>
                <span>Medsense</span>
            </div>
            <h1>Inscription Médecin</h1>
            <p class="header-subtitle">Créez votre compte professionnel pour accéder à la plateforme médicale</p>
        </div>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div><?php echo htmlspecialchars($success); ?></div>
            </div>
            <div class="footer-links">
                <a href="../home/index.php"><i class="fas fa-home"></i> Accueil</a>
                <a href="sign-in.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
            </div>
            <?php exit(); ?>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <p>Votre compte médecin nécessitera une validation par l'administration. Tous les champs marqués d'un astérisque (*) sont obligatoires.</p>
        </div>
        <div class="alert alert-medical">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Important :</strong> Votre compte sera activé après vérification de votre diplôme. Cette vérification peut prendre 24 à 48 heures.
            </div>
        </div>
        <form method="POST" action="" id="doctorForm" enctype="multipart/form-data" novalidate>
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Informations personnelles
                </h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom <span class="required">*</span></label>
                        <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Votre prénom" value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>" required>
                        <div class="validation-message" id="prenom-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom <span class="required">*</span></label>
                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Votre nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
                        <div class="validation-message" id="nom-error"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="exemple@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        <div class="validation-message" id="email-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="mot_de_passe">Mot de passe <span class="required">*</span></label>
                        <div class="password-input-wrapper">
                            <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" placeholder="Minimum 6 caractères" required>
                            <button type="button" id="generatePasswordBtn" class="password-generator-btn">
                                <i class="fas fa-key"></i>
                            </button>
                        </div>
                        <div class="password-requirements">Doit contenir au moins 6 caractères, dont une lettre et un chiffre</div>
                        <div class="validation-message" id="password-error"></div>
        
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
                        <input type="date" id="dateNaissance" name="dateNaissance" class="form-control" value="<?php echo isset($_POST['dateNaissance']) ? htmlspecialchars($_POST['dateNaissance']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Expérience professionnelle</label>
                        <div class="experience-input-wrapper">
                            <input type="number" id="experience" name="experience" class="form-control" 
                                   placeholder="0" min="0" max="60" step="1"
                                   value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?>">
                            <span class="experience-unit">années</span>
                        </div>
                        <div class="validation-message" id="experience-error"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <textarea id="adresse" name="adresse" class="form-control" placeholder="Votre adresse complète" rows="3"><?php echo isset($_POST['adresse']) ? htmlspecialchars($_POST['adresse']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="langues">Langues parlées</label>
                    <input type="text" id="langues" name="langues" class="form-control" 
                           placeholder="Ex: Français, Anglais, Arabe (séparez par des virgules)"
                           value="<?php echo isset($_POST['langues']) ? htmlspecialchars($_POST['langues']) : ''; ?>">
                    <div class="langues-tags" id="languesTags"></div>
                    <small class="form-help">Séparez les langues par des virgules</small>
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

        
            <div class="form-section documents-section">
                <h2 class="section-title">
                    <i class="fas fa-file-medical"></i>
                    Documents professionnels
                </h2>

                <div class="form-group">
                    <label for="diplome">Diplôme de médecine <span class="required">*</span></label>
                    <div class="document-upload">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Glissez-déposez votre diplôme ou cliquez pour sélectionner</p>
                        <input type="file" id="diplome" name="diplome" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="document-requirements">
                            <p><strong>Exigences :</strong></p>
                            <ul>
                                <li>Formats acceptés : PDF, JPG, JPEG, PNG</li>
                                <li>Taille maximum : 2 Mo</li>
                                <li>Le document doit être lisible et en couleur</li>
                            </ul>
                        </div>
                    </div>
                    <div class="validation-message" id="diplome-error"></div>
                    <div id="filePreview" style="display: none; margin-top: 1rem;">
                        <img id="previewImage" src="" alt="Aperçu du document" style="max-width: 100%; max-height: 200px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                </div>
            </div>
            <div class="form-section">
                <button type="submit" class="btn" id="submitBtn">
                    <i class="fas fa-user-md"></i>
                    Créer mon compte médecin
                </button>

                <div class="loading" id="loading">
                    <i class="fas fa-spinner fa-spin"></i> Traitement en cours...
                </div>
            </div>
        </form>
        <div class="footer-links">
            <a href="select-role.php"><i class="fas fa-arrow-left"></i> Retour aux autres inscriptions</a>
            <a href="sign-in.php"><i class="fas fa-sign-in-alt"></i> Déjà un compte ? Se connecter</a>
            <a href="../home/index.php"><i class="fas fa-home"></i> Page d'accueil</a>
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
        function updateLanguesTags() {
            const languesInput = document.getElementById('langues');
            const languesTagsContainer = document.getElementById('languesTags');
            const value = languesInput.value.trim();
            languesTagsContainer.innerHTML = '';
            
            if (!value) return;
            const langues = value.split(',').map(lang => lang.trim()).filter(lang => lang.length > 0);
            langues.forEach((langue, index) => {
                const tag = document.createElement('div');
                tag.className = 'langue-tag';
                tag.innerHTML = `
                    ${langue}
                    <span class="langue-tag-remove" data-index="${index}">&times;</span>
                `;
                languesTagsContainer.appendChild(tag);
            });
            document.querySelectorAll('.langue-tag-remove').forEach(button => {
                button.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    removeLangue(index);
                });
            });
        }
        function removeLangue(index) {
            const languesInput = document.getElementById('langues');
            let langues = languesInput.value.split(',').map(lang => lang.trim()).filter(lang => lang.length > 0);
            if (index >= 0 && index < langues.length) {
                langues.splice(index, 1);
                languesInput.value = langues.join(', ');
                updateLanguesTags();
            }
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
                    langues: document.getElementById("langues").value,
                    experience: document.getElementById("experience").value
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
                
                if (data.correct.langues && data.correct.langues !== formData.langues) {
                    document.getElementById("langues").value = data.correct.langues;
                    updateLanguesTags();
                    correctionsApplied++;
                }
                
                if (data.correct.experience !== undefined && data.correct.experience !== formData.experience) {
                    document.getElementById("experience").value = data.correct.experience;
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
            const form = document.getElementById('doctorForm');
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            const diplomeInput = document.getElementById('diplome');
            const filePreview = document.getElementById('filePreview');
            const previewImage = document.getElementById('previewImage');
            const languesInput = document.getElementById('langues');
            const experienceInput = document.getElementById('experience');
            const verifyAiBtn = document.getElementById('verifyAiBtn');
            
          
            updateLanguesTags();
            
            
            verifyAiBtn.addEventListener('click', verifyForm);
            
          
            languesInput.addEventListener('input', updateLanguesTags);
            
           
            experienceInput.addEventListener('input', function() {
                const value = parseInt(this.value);
                if (isNaN(value)) return;
                
                if (value < 0) this.value = 0;
                if (value > 60) this.value = 60;
            });
            
          
            const generateBtn = document.getElementById('generatePasswordBtn');
            const suggestedPasswordText = document.getElementById('suggestedPasswordText');
            const generatedPasswordDisplay = document.getElementById('generatedPasswordDisplay');
            const useSuggestedBtn = document.getElementById('useSuggestedPassword');
            const regenerateBtn = document.getElementById('regeneratePassword');
            const copyBtn = document.getElementById('copyPassword');
            const passwordInput = document.getElementById('mot_de_passe');
            
          
            if (generateBtn) {
                generateBtn.addEventListener('click', function() {
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
            
            diplomeInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const fileType = file.type;
                        
                        if (fileType.startsWith('image/')) {
                            previewImage.src = e.target.result;
                            filePreview.style.display = 'block';
                        } else if (fileType === 'application/pdf') {
                            previewImage.src = '../../assets/images/pdf-icon.png';
                            previewImage.alt = 'Document PDF';
                            filePreview.style.display = 'block';
                        }
                       
                        const maxSize = 2 * 1024 * 1024; // 2MB
                        if (file.size > maxSize) {
                            showError('diplome', 'Le fichier est trop volumineux. Maximum : 2 Mo');
                            diplomeInput.value = '';
                            filePreview.style.display = 'none';
                        } else {
                            clearError('diplome');
                        }
                    }
                    reader.readAsDataURL(file);
                } else {
                    filePreview.style.display = 'none';
                }
            });
         
            const fields = {
                'prenom': {
                    required: true,
                    pattern: /^[A-Za-zÀ-ÿ\s\-']+$/,
                    message: 'Le prénom ne doit contenir que des lettres'
                },
                'nom': {
                    required: true,
                    pattern: /^[A-Za-zÀ-ÿ\s\-']+$/,
                    message: 'Le nom ne doit contenir que des lettres'
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
                },
                'experience': {
                    required: false,
                    pattern: /^[0-9]*$/,
                    message: 'L\'expérience doit être un nombre valide'
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
                e.preventDefault();
                
                let isValid = true;

                Object.keys(fields).forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    const config = fields[fieldName];
                    
                    if (field && !validateField(field, config)) {
                        isValid = false;
                    }
                });
     
                if (experienceInput.value) {
                    const expValue = parseInt(experienceInput.value);
                    if (isNaN(expValue) || expValue < 0 || expValue > 60) {
                        showError('experience', 'L\'expérience doit être un nombre entre 0 et 60 ans');
                        isValid = false;
                    }
                }
       
                if (!diplomeInput.files || !diplomeInput.files[0]) {
                    showError('diplome', 'Veuillez télécharger votre diplôme');
                    isValid = false;
                }
                
                if (isValid) {
              
                    submitBtn.disabled = true;
                    loading.style.display = 'block';
                    form.submit();
                } else {
 
                    const firstError = document.querySelector('.validation-message.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            
            function validateField(field, config) {
                const value = field.value.trim();
                const errorElement = document.getElementById(field.id + '-error');
                
                if (!config.required && !value) {
                    clearError(field.id);
                    return true;
                }
                
              
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
 
                if (field.id === 'email' && value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        showError(field.id, 'Veuillez entrer une adresse email valide');
                        return false;
                    }
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
            
            const uploadArea = document.querySelector('.document-upload');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight(e) {
                uploadArea.style.borderColor = 'var(--medical-teal)';
                uploadArea.style.backgroundColor = 'rgba(13, 148, 136, 0.1)';
            }
            
            function unhighlight(e) {
                uploadArea.style.borderColor = '';
                uploadArea.style.backgroundColor = '';
            }
            
            uploadArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length > 0) {
                    diplomeInput.files = files;
                    diplomeInput.dispatchEvent(new Event('change'));
                }
            }
        });
    </script>
</body>
</html>