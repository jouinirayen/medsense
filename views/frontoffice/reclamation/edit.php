<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = 1; // Hardcoded user ID
$errors = [];
$reclamation = null;

// Process form submission (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validation
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
        // Fetch the reclamation first
        $reclamationModel = new Reclamation();
        $reclamation = $reclamationModel->findForUser($id, $userId);
        
        if (!$reclamation) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => "Réclamation introuvable !",
                'show' => true
            ];
            header('Location: index.php');
            exit;
        }
        
        // Update reclamation using setters
        $reclamation->setTitre($titre)
                    ->setDescription($description);
        
        if ($reclamation->update()) {
            // Set success notification
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => "Réclamation mise à jour avec succès !",
                'show' => true
            ];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Erreur lors de la mise à jour de la réclamation.";
        }
    } else {
        // If validation errors, create a temporary reclamation object
        $reclamation = new Reclamation();
        $reclamation->setId($id)
                    ->setTitre($titre)
                    ->setDescription($description);
    }
} else {
    // GET request - show edit form
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        header('Location: index.php');
        exit;
    }
    
    // Fetch reclamation for editing
    $reclamationModel = new Reclamation();
    $reclamation = $reclamationModel->findForUser($id, $userId);
    
    if (!$reclamation) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => "Réclamation introuvable !",
            'show' => true
        ];
        header('Location: index.php');
        exit;
    }
}

$pageTitle = "Modifier Réclamation";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../../../css/style.css">
    <script src="../../../js/reclamation-utils.js"></script>
</head>
<body>
<?php include '../../../navbar.php'; ?>

<main class="main-content">
    <div class="form-container">
        <div class="form-header">
            <h1>Modifier la Réclamation</h1>
            <a href="index.php" class="btn btn-cancel">← Retour à la liste</a>
        </div>

        <div class="form-card">
            <h2>Modifier la réclamation #<?= $reclamation->getId(); ?></h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="reclamation-form" id="editForm" onsubmit="return handleEditFormSubmit(event)">
                <input type="hidden" name="id" value="<?= $reclamation->getId(); ?>">

                <div class="form-group">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" 
                           value="<?= htmlspecialchars($reclamation->getTitre()); ?>" 
                           required minlength="3" maxlength="255"
                           placeholder="Résumé de votre réclamation">
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="5" 
                              required minlength="10" maxlength="5000"
                              placeholder="Expliquez votre problème en détail..."><?= htmlspecialchars($reclamation->getDescription()); ?></textarea>
                    <div class="form-help">
                        <span id="charCount"><?= mb_strlen($reclamation->getDescription()) ?> / 5000 caractères</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Mettre à jour</button>
                    <a href="index.php" class="btn btn-cancel">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
// Character counter for description
const description = document.getElementById('description');
const charCount = document.getElementById('charCount');

description.addEventListener('input', function() {
    const currentLength = this.value.length;
    charCount.textContent = currentLength + ' / 5000 caractères';
    
    if (currentLength > 4500) {
        charCount.style.color = '#ef4444';
        charCount.style.fontWeight = 'bold';
    } else if (currentLength > 4000) {
        charCount.style.color = '#f59e0b';
    } else {
        charCount.style.color = '#64748b';
        charCount.style.fontWeight = 'normal';
    }
});

// Gestionnaire de soumission avec validation
function handleEditFormSubmit(event) {
    const form = document.getElementById('editForm');
    if (!validateReclamationForm(form)) {
        event.preventDefault();
        return false;
    }
    // Si validation OK, le formulaire sera soumis normalement
    return true;
}

// Initialiser la validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editForm');
    if (form) {
        initRealtimeValidation(form);
    }
});

// Afficher l'alerte de succès si la réclamation a été modifiée
<?php if (isset($_SESSION['notification']) && $_SESSION['notification']['type'] === 'success'): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showAlert('Réclamation a été modifiée avec succès !', 'success');
    });
<?php endif; ?>
</script>

<?php include '../../../footer.php'; ?>
</body>
</html>