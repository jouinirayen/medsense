<?php
session_start();
include_once '../../../controllers/AuthController.php';

$authController = new AuthController();

if ($authController->isLoggedIn()) {
    header('Location: ../home/index.php');
    exit;
}

// Initialisation des variables
$error = '';
$success = '';
$uploadedDiplomePath = null;

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

    // Gestion de l'upload du diplôme
    if (isset($_FILES['diplome']) && $_FILES['diplome']['error'] === UPLOAD_ERR_OK) {
        $diplome = $_FILES['diplome'];
        
        // Vérification du type de fichier
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($diplome['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors[] = "Le format du fichier n'est pas accepté. Formats acceptés: PDF, JPG, JPEG, PNG";
        }
        
        // Vérification de la taille (max 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($diplome['size'] > $maxSize) {
            $errors[] = "Le fichier est trop volumineux. Taille maximum: 2MB";
        }
        
        // Si pas d'erreurs, procéder à l'upload
        if (empty($errors)) {
            // Chemin vers le dossier d'uploads depuis la racine du projet
            $uploadDir = __DIR__ . '/../../../uploads/diplomes/';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $errors[] = "Impossible de créer le dossier de stockage des diplômes.";
                }
            }
            
            if (empty($errors)) {
                // Générer un nom unique pour le fichier
                $fileName = uniqid('diplome_', true) . '_' . time() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;
                
                // Déplacer le fichier uploadé
                if (move_uploaded_file($diplome['tmp_name'], $uploadPath)) {
                    // Enregistrer le chemin pour la base de données
                    // Format: uploads/diplomes/fichier.ext
                    $uploadedDiplomePath = 'uploads/diplomes/' . $fileName;
                    
                    // Vérification que le fichier a bien été créé
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

    // Si pas d'erreurs
    if (empty($errors)) {
        // Ajouter le rôle, le statut et le chemin du diplôme
        $data['role'] = 'medecin';
        $data['statut'] = 'en_attente';
        $data['diplome_path'] = $uploadedDiplomePath;
        
        // Appeler le contrôleur d'inscription
        $result = $authController->register($data);
        
        if ($result['success']) {
            $success = "Votre compte médecin a été créé avec succès ! Votre compte sera activé après vérification de votre diplôme par l'administrateur.";
        } else {
            $error = $result['message'];
            // Supprimer le fichier uploadé en cas d'erreur d'inscription
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
    <link rel="stylesheet" href="../../assets/css/doctor-signup.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-user-md"></i>
                <span>Medcare Pro</span>
            </div>
            <h1>Inscription Médecin</h1>
            <p class="header-subtitle">Créez votre compte professionnel pour accéder à la plateforme médicale</p>
        </div>

        <!-- Contenu -->
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

        <!-- Info Box -->
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <p>Votre compte médecin nécessitera une validation par l'administration. Tous les champs marqués d'un astérisque (*) sont obligatoires.</p>
        </div>

        <!-- Warning Box -->
        <div class="alert alert-medical">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Important :</strong> Votre compte sera activé après vérification de votre diplôme. Cette vérification peut prendre 24 à 48 heures.
            </div>
        </div>

        <!-- Formulaire -->
        <form method="POST" action="" id="doctorForm" enctype="multipart/form-data" novalidate>
            <!-- Informations personnelles -->
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
                        <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" placeholder="Minimum 6 caractères" required>
                        <div class="password-requirements">Doit contenir au moins 6 caractères, dont une lettre et un chiffre</div>
                        <div class="validation-message" id="password-error"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dateNaissance">Date de naissance</label>
                        <input type="date" id="dateNaissance" name="dateNaissance" class="form-control" value="<?php echo isset($_POST['dateNaissance']) ? htmlspecialchars($_POST['dateNaissance']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <textarea id="adresse" name="adresse" class="form-control" placeholder="Votre adresse complète" rows="3"><?php echo isset($_POST['adresse']) ? htmlspecialchars($_POST['adresse']) : ''; ?></textarea>
                </div>
            </div>

            <!-- Diplôme -->
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

            <!-- Bouton d'inscription -->
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

        <!-- Liens de navigation -->
        <div class="footer-links">
            <a href="select-role.php"><i class="fas fa-arrow-left"></i> Retour aux autres inscriptions</a>
            <a href="sign-in.php"><i class="fas fa-sign-in-alt"></i> Déjà un compte ? Se connecter</a>
            <a href="../home/index.php"><i class="fas fa-home"></i> Page d'accueil</a>
        </div>
    </div>

    <script>
        // Validation en temps réel
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('doctorForm');
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            const diplomeInput = document.getElementById('diplome');
            const filePreview = document.getElementById('filePreview');
            const previewImage = document.getElementById('previewImage');
            
            // Gestion de l'aperçu du fichier
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
                        
                        // Validation de la taille
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
            
            // Validation des champs
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
                }
            };
            
            // Validation en temps réel
            Object.keys(fields).forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const config = fields[fieldName];
                
                field.addEventListener('blur', function() {
                    validateField(field, config);
                });
                
                field.addEventListener('input', function() {
                    if (field.classList.contains('invalid')) {
                        validateField(field, config);
                    }
                });
            });
            
            // Validation du formulaire
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                let isValid = true;
                
                // Valider tous les champs
                Object.keys(fields).forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    const config = fields[fieldName];
                    
                    if (!validateField(field, config)) {
                        isValid = false;
                    }
                });
                
                // Validation du diplôme
                if (!diplomeInput.files || !diplomeInput.files[0]) {
                    showError('diplome', 'Veuillez télécharger votre diplôme');
                    isValid = false;
                }
                
                if (isValid) {
                    // Afficher le loading
                    submitBtn.disabled = true;
                    loading.style.display = 'block';
                    
                    // Soumettre le formulaire
                    form.submit();
                } else {
                    // Scroll vers la première erreur
                    const firstError = document.querySelector('.validation-message.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            // Fonctions de validation
            function validateField(field, config) {
                const value = field.value.trim();
                const errorElement = document.getElementById(field.id + '-error');
                
                // Validation requise
                if (config.required && !value) {
                    showError(field.id, 'Ce champ est obligatoire');
                    return false;
                }
                
                // Validation pattern
                if (config.pattern && value && !config.pattern.test(value)) {
                    showError(field.id, config.message);
                    return false;
                }
                
                // Validation longueur minimale
                if (config.minLength && value.length < config.minLength) {
                    showError(field.id, config.message);
                    return false;
                }
                
                // Validation email spécifique
                if (field.id === 'email' && value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        showError(field.id, 'Veuillez entrer une adresse email valide');
                        return false;
                    }
                }
                
                // Validation réussie
                clearError(field.id);
                return true;
            }
            
            function showError(fieldId, message) {
                const field = document.getElementById(fieldId);
                const errorElement = document.getElementById(fieldId + '-error');
                
                field.classList.add('invalid');
                field.classList.remove('valid');
                
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.className = 'validation-message error';
                }
            }
            
            function clearError(fieldId) {
                const field = document.getElementById(fieldId);
                const errorElement = document.getElementById(fieldId + '-error');
                
                field.classList.remove('invalid');
                field.classList.add('valid');
                
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.className = 'validation-message';
                }
            }
            
            // Gestion du drag and drop
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