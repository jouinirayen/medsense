<?php


require_once '../../../config/config.php';
require_once '../../../config/db.php';
require_once '../../../config/auth.php';


requireLogin();

$userId = getUserId();
$reclamationId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($reclamationId <= 0) {
    $_SESSION['error_message'] = "ID réclamation invalide.";
    header('Location: mes_reclamations.php');
    exit();
}

try {
    $db = Database::getInstance();
    
    // Vérifier que la réclamation appartient à l'utilisateur
    $sql = "SELECT id FROM reclamation WHERE id = ? AND id_user = ?";
    $reclamation = $db->fetch($sql, array($reclamationId, $userId), 'ii');

    if (!$reclamation) {
        $_SESSION['error_message'] = "Réclamation non trouvée ou accès refusé.";
        header('Location: mes_reclamations.php');
        exit();
    }

   
    $sqlDeleteReponses = "DELETE FROM reponse WHERE id_reclamation = ?";
    $db->execute($sqlDeleteReponses, array($reclamationId), 'i');

    $sqlDelete = "DELETE FROM reclamation WHERE id = ? AND id_user = ?";
    $db->execute($sqlDelete, array($reclamationId, $userId), 'ii');

    $_SESSION['success_message'] = "Réclamation supprimée avec succès!";
    header('Location: mes_reclamations.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage();
    header('Location: mes_reclamations.php');
    exit();
}

?>
