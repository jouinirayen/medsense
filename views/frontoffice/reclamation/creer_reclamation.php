<?php
/**
 * Page de création d'une réclamation
 */

require_once '../../../config/config.php';

// Vérifier que l'utilisateur est connecté (if needed)

$pageTitle = "Créer une réclamation";

// Initialiser les variables
$errors = array();
$success = false;

// Afficher le header
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <!-- Correct relative path to CSS -->
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>
<header>
    <img src="../../../images/logo.png" alt="Logo" class="logo">
</header>

<main class="form-container">
    <h2>Créer une nouvelle réclamation</h2>

    <?php if (!empty($errors)): ?>
        <div class="message error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="enregistrer_reclamation.php" method="POST">
        <div class="form-group">
            <label for="titre">Titre de la réclamation *</label>
            <input type="text" id="titre" name="titre" required maxlength="255" 
                   placeholder="Résumé de votre réclamation">
        </div>

        <div class="form-group">
            <label for="description">Description détaillée *</label>
            <textarea id="description" name="description" required 
                      placeholder="Expliquez votre problème en détail..."></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success">Soumettre la réclamation</button>
            <a href="mes_reclamations.php" class="btn">Annuler</a>
        </div>
    </form>
</main>

<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> MedSense - Tous droits réservés</p>
    </div>
</footer>

<!-- Correct relative path to JS -->
<script src="../../../css/script.js"></script>
</body>
</html>
