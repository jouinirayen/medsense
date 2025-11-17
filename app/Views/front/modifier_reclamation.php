<?php


require_once '../../../config/config.php';
require_once '../../../config/db.php';
require_once '../../../config/auth.php';


requireLogin();

$userId = getUserId();
$reclamationId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($reclamationId <= 0) {
    header('Location: mes_reclamations.php');
    exit();
}

try {
    $db = Database::getInstance();
 
    $sql = "SELECT * FROM reclamation WHERE id = ? AND id_user = ?";
    $reclamation = $db->fetch($sql, array($reclamationId, $userId), 'ii');

    if (!$reclamation) {
        header('Location: mes_reclamations.php');
        exit();
    }

    $pageTitle = "Modifier - " . htmlspecialchars($reclamation['titre']);

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération: " . $e->getMessage();
    header('Location: mes_reclamations.php');
    exit();
}

include '../../../config/header.php';
?>

<h2>Modifier la Réclamation</h2>

<form action="update_reclamation.php" method="POST">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($reclamation['id']); ?>">

    <div class="form-group">
        <label for="titre">Titre *</label>
        <input type="text" id="titre" name="titre" required maxlength="255" 
               value="<?php echo htmlspecialchars($reclamation['titre']); ?>">
    </div>

    <div class="form-group">
        <label for="description">Description *</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($reclamation['description']); ?></textarea>
    </div>

    <div class="form-group">
        <p><strong>Type :</strong> <span class="badge <?php echo htmlspecialchars($reclamation['type']); ?>">
            <?php echo ucfirst(htmlspecialchars($reclamation['type'])); ?>
        </span></p>
        <p><strong>Statut :</strong> <span class="badge <?php echo str_replace(' ', '-', htmlspecialchars($reclamation['statut'])); ?>">
            <?php echo htmlspecialchars($reclamation['statut']); ?>
        </span></p>
        <p><strong>Date de création :</strong> <?php echo date('d/m/Y H:i', strtotime($reclamation['date'])); ?></p>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
        <a href="mes_reclamations.php" class="btn">Annuler</a>
    </div>
</form>

<?php
include '../../../config/footer.php';
?>
