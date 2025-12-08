<?php
// At the VERY TOP - handle form submission
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $defaultUserId = 1; // Hardcoded user ID
    
    // FONCTIONNALITÉ 2: Détection de mauvais mots
    $reclamation = new Reclamation();
    $badWords = $reclamation->detectBadWords($titre, $description);
    if ($badWords !== null) {
        $_SESSION['errors'] = [
            "⚠️ ATTENTION : Votre réclamation contient des mots inappropriés.",
            "Veuillez reformuler votre message de manière respectueuse.",
            "Mots détectés : " . implode(', ', $badWords)
        ];
        $_SESSION['old_titre'] = $titre;
        $_SESSION['old_description'] = $description;
        // Redirect to stay on the same page to show errors
        header('Location: create.php');
        exit;
    }
    
    // Validation
    $errors = [];
    if (empty($titre)) {
        $errors[] = "Le titre est requis.";
    } elseif (strlen($titre) < 3) {
        $errors[] = "Le titre doit contenir au moins 3 caractères.";
    } elseif (strlen($titre) > 255) {
        $errors[] = "Le titre ne doit pas dépasser 255 caractères.";
    }
    
    if (empty($description)) {
        $errors[] = "La description est requise.";
    } elseif (strlen($description) < 10) {
        $errors[] = "La description doit contenir au moins 10 caractères.";
    } elseif (strlen($description) > 5000) {
        $errors[] = "La description ne doit pas dépasser 5000 caractères.";
    }
    
    if (empty($errors)) {
        // FONCTIONNALITÉ 1: Génération automatique de description détaillée
        $detailedDescription = $reclamation->generateDetailedDescription($titre, $description);
        
        // Create reclamation using the new model with setters
        $reclamation->setTitre($titre)
                    ->setDescription($detailedDescription)
                    ->setDate(date('Y-m-d H:i:s'))
                    ->setUserId($defaultUserId)
                    ->setType(Reclamation::TYPE_NORMAL)
                    ->setStatut(Reclamation::STATUS_OPEN);
        
        if ($reclamation->create()) {
            // Set success notification in session
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => "Réclamation créée avec succès ! Description enrichie automatiquement.",
                'show' => true
            ];
            
            // Redirect to index page
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Erreur lors de la création de la réclamation.";
        }
    } else {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_titre'] = $titre;
        $_SESSION['old_description'] = $description;
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = "Créer une Réclamation - MedSense";

// Retrieve errors and previously submitted values from session
$errors = $_SESSION['errors'] ?? [];
$titreValue = $_SESSION['old_titre'] ?? '';
$descriptionValue = $_SESSION['old_description'] ?? '';

// Clear session data
unset($_SESSION['errors'], $_SESSION['old_titre'], $_SESSION['old_description']);

// Check for success notification from redirect
$successNotification = $_SESSION['notification'] ?? null;
if ($successNotification && $successNotification['type'] === 'success') {
    unset($_SESSION['notification']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../../../css/style.css">
    <script src="../../../js/reclamation-utils.js"></script>
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .form-header {
            background: linear-gradient(135deg, #4f46e5 0%, #3aed52ff 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .form-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .form-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .form-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .char-counter {
            text-align: right;
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-cancel {
            background: #6b7280;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-error ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 1rem;
            }
            
            .form-header {
                padding: 1.5rem;
            }
            
            .form-body {
                padding: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="form-page">
    <!-- Header Section -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <a href="../../../index.php" class="logo-link">
                    <img src="../../../images/logo.svg" alt="MedSense Logo" class="logo">
                    <div class="site-branding">
                        <h1 class="site-title">MedSense</h1>
                        <p class="tagline">Réclamations et Urgences</p>
                    </div>
                </a>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="index.php" class="<?= ($currentPage == 'index.php') ? 'active' : '' ?>">📋 Mes Réclamations</a></li>
                    <li><a href="create.php" class="<?= ($currentPage == 'create.php') ? 'active' : '' ?>">✏️ Nouvelle Réclamation</a></li>
                    <li><a href="urgence.php" class="<?= ($currentPage == 'urgence.php') ? 'active' : '' ?>">🚨 Urgence</a></li>
                    <li><a href="../../backoffice/reponse/admin_reclamations.php" class="<?= ($currentPage == 'admin_reclamations.php') ? 'active' : '' ?>">⚙️ Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="form-container">
            <div class="form-card">
                <!-- Form Header -->
                <div class="form-header">
                    <h1>Nouvelle Réclamation</h1>
                    <p>Décrivez votre problème en détail pour une prise en charge rapide</p>
                </div>

                <!-- Form Body -->
                <div class="form-body">
                    <?php if (!empty($errors)) : ?>
                        <div class="alert-error">
                            <ul>
                                <?php foreach ($errors as $error) : ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" id="reclamationForm" onsubmit="return handleFormSubmit(event)">
                        <!-- Title Field -->
                        <div class="form-group">
                            <label for="titre">Titre de la réclamation *</label>
                            <input type="text" id="titre" name="titre" 
                                   placeholder="Ex: Problème avec mon corps, Mal aux genoux..."
                                   value="<?= htmlspecialchars($titreValue) ?>">
                            <span id="titreError" class="error-message"></span>
                        </div>

                        <!-- Description Field -->
                        <div class="form-group">
                            <label for="description">Description détaillée *</label>
                            <textarea id="description" name="description"  rows="6"
                                      placeholder="Veuillez décrire votre problème de manière précise. Incluez toutes les informations qui pourraient nous aider à résoudre votre situation rapidement..."><?= htmlspecialchars($descriptionValue) ?></textarea>
                            <div class="char-counter">
                                <span id="charCount"><?= mb_strlen($descriptionValue) ?> / 5000 caractères</span>
                            </div>
                            <span id="descriptionError" class="error-message"></span>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" name="Save" class="btn btn-success">
                                📨 Soumettre la réclamation
                            </button>
                            <a href="index.php" class="btn btn-cancel">
                                ❌ Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../../../footer.php'; ?>

    <script>
        // Liste de mots inappropriés (côté client pour validation en temps réel)
        const badWords = ['merde', 'putain', 'con', 'connard', 'salope', 'enculé', 'enculer',
            'foutre', 'bordel', 'chier', 'chié', 'nique', 'niquer',
            'bite', 'couilles', 'cul', 'pute', 'putes', 'salaud', 'salauds',
            'crétin', 'idiot', 'imbécile', 'débile', 'stupide', 'conne', 'connasse',
            'fdp', 'pd', 'tg', 'ntm', 'ptn'];

        // Form validation functions
        const titre = document.getElementById('titre');
        const description = document.getElementById('description');
        const titreError = document.getElementById('titreError');
        const descriptionError = document.getElementById('descriptionError');
        const charCount = document.getElementById('charCount');

        titre.addEventListener('input', validateTitre);
        description.addEventListener('input', validateDescription);
        
        // Détection de mauvais mots en temps réel
        titre.addEventListener('input', checkBadWords);
        description.addEventListener('input', checkBadWords);

        function validateTitre() {
            const value = titre.value.trim();
            if (!value) {
                titreError.textContent = "Le titre est requis";
                titreError.style.display = "block";
                return false;
            } else if (value.length < 3) {
                titreError.textContent = "Le titre doit contenir au moins 3 caractères";
                titreError.style.display = "block";
                return false;
            } else if (value.length > 255) {
                titreError.textContent = "Le titre ne doit pas dépasser 255 caractères";
                titreError.style.display = "block";
                return false;
            }
            titreError.style.display = "none";
            return true;
        }

        function validateDescription() {
            const value = description.value.trim();
            charCount.textContent = description.value.length + " / 5000 caractères";
            
            if (description.value.length > 4500) {
                charCount.style.color = "#ef4444";
                charCount.style.fontWeight = "bold";
            } else if (description.value.length > 4000) {
                charCount.style.color = "#f59e0b";
            } else {
                charCount.style.color = "#6b7280";
            }
            
            if (!value) {
                descriptionError.textContent = "La description est requise";
                descriptionError.style.display = "block";
                return false;
            } else if (value.length < 10) {
                descriptionError.textContent = "La description doit contenir au moins 10 caractères";
                descriptionError.style.display = "block";
                return false;
            } else if (value.length > 5000) {
                descriptionError.textContent = "La description ne doit pas dépasser 5000 caractères";
                descriptionError.style.display = "block";
                return false;
            }
            descriptionError.style.display = "none";
            return true;
        }

        // Fonction de détection de mauvais mots
        function checkBadWords() {
            const text = (titre.value + ' ' + description.value).toLowerCase();
            const detectedWords = [];
            
            badWords.forEach(word => {
                if (text.includes(word)) {
                    detectedWords.push(word);
                }
            });
            
            if (detectedWords.length > 0) {
                // Afficher un avertissement
                const warningDiv = document.getElementById('badWordsWarning');
                if (!warningDiv) {
                    const warning = document.createElement('div');
                    warning.id = 'badWordsWarning';
                    warning.className = 'alert-error';
                    warning.style.marginTop = '1rem';
                    warning.style.backgroundColor = '#fef2f2';
                    warning.style.border = '2px solid #ef4444';
                    warning.innerHTML = `
                        <strong>⚠️ ATTENTION :</strong> Votre message contient des mots inappropriés.
                        Veuillez reformuler votre message de manière respectueuse.
                        <br><small>Mots détectés : ${detectedWords.join(', ')}</small>
                    `;
                    description.parentElement.appendChild(warning);
                } else {
                    warningDiv.innerHTML = `
                        <strong>⚠️ ATTENTION :</strong> Votre message contient des mots inappropriés.
                        Veuillez reformuler votre message de manière respectueuse.
                        <br><small>Mots détectés : ${detectedWords.join(', ')}</small>
                    `;
                }
                return false;
            } else {
                // Supprimer l'avertissement s'il existe
                const warningDiv = document.getElementById('badWordsWarning');
                if (warningDiv) {
                    warningDiv.remove();
                }
                return true;
            }
        }

        function validateForm() {
            const isTitreValid = validateTitre();
            const isDescriptionValid = validateDescription();
            const hasNoBadWords = checkBadWords();
            
            if (!isTitreValid) {
                titre.focus();
            } else if (!isDescriptionValid) {
                description.focus();
            } else if (!hasNoBadWords) {
                showAlert('⚠️ Votre réclamation contient des mots inappropriés. Veuillez reformuler votre message de manière respectueuse.', 'error');
                return false;
            }
            
            return isTitreValid && isDescriptionValid && hasNoBadWords;
        }

        // Gestionnaire de soumission du formulaire avec validation
        function handleFormSubmit(event) {
            if (!validateForm()) {
                event.preventDefault();
                return false;
            }
            // Si validation OK, le formulaire sera soumis normalement
            return true;
        }

        // Initialize validation on page load
        document.addEventListener('DOMContentLoaded', function() {
            validateTitre();
            validateDescription();
            checkBadWords();
            // Initialiser la validation en temps réel
            const form = document.getElementById('reclamationForm');
            if (form) {
                initRealtimeValidation(form);
            }
        });

        // Afficher l'alerte de succès si la réclamation a été créée
        <?php if ($successNotification && $successNotification['type'] === 'success'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('Réclamation a été créée avec succès !', 'success');
            });
        <?php endif; ?>
    </script>
</body>
</html>