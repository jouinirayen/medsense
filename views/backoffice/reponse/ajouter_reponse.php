<?php
/**
 * Page d'ajout de réponse à une réclamation
 */

require_once '../../../config/config.php';
 
 

$reclamationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$adminId = getUserId();

if ($reclamationId <= 0) {
    header('Location: admin_reclamations.php');
    exit();
}

try {
    $db = Database::getInstance();
    
    // Vérifier que la réclamation existe
    $sql = "SELECT * FROM reclamation WHERE id = ?";
    $reclamation = $db->fetch($sql, array($reclamationId), 'i');

    if (!$reclamation) {
        header('Location: admin_reclamations.php');
        exit();
    }

    $pageTitle = "Répondre - " . htmlspecialchars($reclamation['titre']);

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération: " . $e->getMessage();
    header('Location: admin_reclamations.php');
    exit();
}

include '../../../config/header.php';
?>

<h2>Répondre à la Réclamation</h2>

<div class="card">
    <h3><?php echo htmlspecialchars($reclamation['titre']); ?></h3>
    <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($reclamation['description'])); ?></p>
    <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($reclamation['date'])); ?></p>
</div>

<form action="enregistrer_reponse.php" method="POST">
    <input type="hidden" name="id_reclamation" value="<?php echo htmlspecialchars($reclamationId); ?>">

    <div class="form-group">
        <label for="contenu">Votre réponse *</label>
        <textarea id="contenu" name="contenu" required 
                  placeholder="Écrivez votre réponse à cette réclamation..."></textarea>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success">Envoyer la réponse</button>
        <a href="details_reclamation.php?id=<?php echo $reclamationId; ?>" class="btn">Annuler</a>
    </div>
</form>

<?php
include '../../../config/footer.php';
?>
