<?php
/**
 * Page de création d'une réclamation
 */

require_once '../../../config/config.php';
require_once '../../../config/db.php';
require_once '../../../config/auth.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

$pageTitle = "Créer une réclamation";

// Initialiser les variables
$errors = array();
$success = false;

// Afficher le header
include '../../../config/header.php';
?>

<div class="form-container">
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
</div>

<?php
include '../../../config/footer.php';
?>
