<?php
/**
 * Script d'enregistrement d'une réponse
 */

require_once '../../../config/config.php';
 
 

$reclamationId = isset($_POST['id_reclamation']) ? intval($_POST['id_reclamation']) : 0;
$contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';
$adminId = getUserId();

// Validation
$errors = array();

if ($reclamationId <= 0) {
    $errors[] = "ID réclamation invalide.";
}
if (empty($contenu)) {
    $errors[] = "La réponse ne peut pas être vide.";
}
if (strlen($contenu) < 5) {
    $errors[] = "La réponse doit contenir au moins 5 caractères.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: ajouter_reponse.php?id=' . $reclamationId);
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

    // Insérer la réponse
    $date = date('Y-m-d H:i:s');
    $sqlInsert = "INSERT INTO reponse (contenu, date, id_reclamation, id_user) 
                  VALUES (?, ?, ?, ?)";
    $params = array($contenu, $date, $reclamationId, $adminId);
    $types = 'ssii';

    $db->execute($sqlInsert, $params, $types);

    $_SESSION['success_message'] = "Réponse envoyée avec succès!";
    header('Location: details_reclamation.php?id=' . $reclamationId);
    exit();

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de l'enregistrement: " . $e->getMessage();
    header('Location: ajouter_reponse.php?id=' . $reclamationId);
    exit();
}

?>
