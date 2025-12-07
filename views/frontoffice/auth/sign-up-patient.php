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
    // Récupération des données
    $data = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
        'dateNaissance' => trim($_POST['dateNaissance'] ?? ''),
        'adresse' => trim($_POST['adresse'] ?? '')
    ];

    // Validation
    $errors = [];
    
    // Validation des champs obligatoires
    $required = ['nom', 'prenom', 'email', 'mot_de_passe'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Le champ '$field' est obligatoire";
        }
    }

    // Validation email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }

    // Validation mot de passe
    if (strlen($data['mot_de_passe']) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    }

    // Validation date de naissance
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

    // Si pas d'erreurs
    if (empty($errors)) {
        // Ajouter le rôle
        $data['role'] = 'patient';
        $data['statut'] = 'actif';
        
        // Appeler le contrôleur d'inscription
        $result = $authController->register($data);
        
        if ($result['success']) {
            // Redirection vers la page de connexion
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
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-user-injured"></i>
                <span>Medcare</span>
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
        
        <!-- Formulaire d'inscription -->
        <form method="POST" action="" id="patientForm" novalidate>
            <!-- Informations personnelles -->
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
                        <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" 
                               placeholder="Minimum 6 caractères"
                               minlength="6"
                               required>
                        <div class="password-requirements">Doit contenir au moins 6 caractères</div>
                        <div class="validation-message" id="mot_de_passe-error"></div>
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
            
            <!-- Conditions d'utilisation -->
            <div class="form-section">
                <div class="alert alert-patient">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>Confidentialité garantie :</strong> Vos données médicales sont protégées par le secret médical et notre politique de confidentialité.
                    </div>
                </div>
            </div>
            
            <!-- Bouton de soumission -->
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
        
        <!-- Liens de navigation -->
        <div class="footer-links">
            <a href="select-role.php"><i class="fas fa-arrow-left"></i> Changer de type de compte</a>
            <a href="sign-in.php"><i class="fas fa-sign-in-alt"></i> Déjà un compte ? Connectez-vous</a>
            <a href="../home/index.php"><i class="fas fa-home"></i> Retour à l'accueil</a>
        </div>
    </div>

    <script src="../../assets/js/sign-up-patient.js"></script>
</body>
</html>