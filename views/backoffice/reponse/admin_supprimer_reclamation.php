<?php
/**
 * Script de suppression d'une réclamation (ADMIN)
 */

require_once '../../../config/config.php';
 
 

$reclamationId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($reclamationId <= 0) {
    $_SESSION['error_message'] = "ID réclamation invalide.";
    header('Location: admin_reclamations.php');
    exit();
}

try {
    $db = Database::getInstance();
    
    // Vérifier que la réclamation existe
    $sql = "SELECT id FROM reclamation WHERE id = ?";
    $reclamation = $db->fetch($sql, array($reclamationId), 'i');

    if (!$reclamation) {
        $_SESSION['error_message'] = "Réclamation non trouvée.";
        header('Location: admin_reclamations.php');
        exit();
    }

    // Supprimer d'abord les réponses liées
    $sqlDeleteReponses = "DELETE FROM reponse WHERE id_reclamation = ?";
    $db->execute($sqlDeleteReponses, array($reclamationId), 'i');

    // Supprimer la réclamation
    $sqlDelete = "DELETE FROM reclamation WHERE id = ?";
    $db->execute($sqlDelete, array($reclamationId), 'i');

    $_SESSION['success_message'] = "Réclamation supprimée avec succès!";
    header('Location: admin_reclamations.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage();
    header('Location: admin_reclamations.php');
    exit();
}

?>
