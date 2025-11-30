<?php

require_once '../../../config/config.php';
 
$pageTitle = "Alerte Urgence";
$userId = getUserId();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();

        // Créer une urgence
        $titre = "🚨 Urgence";
        $description = "Alerte urgence envoyée par l'utilisateur";
        $date = date('Y-m-d H:i:s');
        $type = TYPE_URGENCE;
        $statut = STATUS_OPEN;

        $sql = "INSERT INTO reclamation (titre, description, date, id_user, type, statut) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $params = array($titre, $description, $date, $userId, $type, $statut);
        $types = 'sssssi';

        $db->execute($sql, $params, $types);

        $_SESSION['success_message'] = "⚠️ ALERTE URGENCE ENVOYÉE! Les administrateurs ont été notifiés.";
        header('Location: urgence.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur lors de l'envoi de l'urgence: " . $e->getMessage();
    }
}

include '../../../config/header.php';
?>

<h2>Bouton d'Urgence</h2>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="message success">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="message error">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="card" style="text-align: center; padding: 3rem;">
    <h3 style="color: #e74c3c; font-size: 1.5rem;">⚠️ ALERTE D'URGENCE</h3>
    
    <p style="margin: 2rem 0; font-size: 1.1rem;">
        Cliquez sur le bouton ci-dessous pour signaler une situation d'urgence.<br>
        <strong>Les administrateurs seront notifiés immédiatement.</strong>
    </p>

    <form method="POST" style="margin: 2rem 0;">
        <button type="submit" class="btn btn-danger" style="font-size: 1.2rem; padding: 1.5rem 3rem; animation: pulse 1s infinite;">
            🚨 ENVOYER L'ALERTE D'URGENCE
        </button>
    </form>

    <div style="background-color: #fff3cd; border: 2px solid #f39c12; padding: 1.5rem; border-radius: 5px; margin: 2rem 0;">
        <strong>Avertissement :</strong><br>
        Utilisez ce bouton uniquement en cas d'urgence réelle. 
        L'utilisation abusive de cette fonction peut avoir des conséquences.
    </div>

    <div class="card-actions" style="justify-content: center; gap: 1rem;">
        <a href="mes_reclamations.php" class="btn">Mes Réclamations</a>
        <a href="../../../index.php" class="btn">Accueil</a>
    </div>
</div>

<?php
include '../../../config/footer.php';
?>
