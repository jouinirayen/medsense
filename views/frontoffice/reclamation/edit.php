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
        // Update reclamation
        $reclamationModel = new Reclamation();
        $reclamationModel->update($id, $userId, [
            'titre' => $titre,
            'description' => $description
        ]);
        
        // Set success notification
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => "Réclamation mise à jour avec succès !",
            'show' => true
        ];
        
        header('Location: index.php');
        exit;
    } else {
        // If validation errors, get reclamation data from POST
        $reclamation = [
            'id' => $id,
            'titre' => $titre,
            'description' => $description
        ];
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
            <h2>Modifier la réclamation #<?= $reclamation['id']; ?></h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="reclamation-form">
                <input type="hidden" name="id" value="<?= $reclamation['id']; ?>">

                <div class="form-group">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" 
                           value="<?= htmlspecialchars($reclamation['titre']); ?>" 
                           required minlength="3" maxlength="255"
                           placeholder="Résumé de votre réclamation">
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="5" 
                              required minlength="10" maxlength="5000"
                              placeholder="Expliquez votre problème en détail..."><?= htmlspecialchars($reclamation['description']); ?></textarea>
                    <div class="form-help">
                        <span id="charCount"><?= mb_strlen($reclamation['description']) ?> / 5000 caractères</span>
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
    } else {
        charCount.style.color = '#64748b';
    }
});
</script>

<?php include '../../../footer.php'; ?>
</body>
</html>