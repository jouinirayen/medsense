<?php


require_once '../../../config/config.php';
 
$userId = getUserId();
$reclamationId = isset($_POST['id']) ? intval($_POST['id']) : 0;
$titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';


$errors = array();

if ($reclamationId <= 0) {
    $errors[] = "ID réclamation invalide.";
}
if (empty($titre)) {
    $errors[] = "Le titre est requis.";
}
if (strlen($titre) > 255) {
    $errors[] = "Le titre ne doit pas dépasser 255 caractères.";
}
if (empty($description)) {
    $errors[] = "La description est requise.";
}
if (strlen($description) < 10) {
    $errors[] = "La description doit contenir au moins 10 caractères.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: modifier_reclamation.php?id=' . $reclamationId);
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

   
    $sqlUpdate = "UPDATE reclamation SET titre = ?, description = ? WHERE id = ? AND id_user = ?";
    $params = array($titre, $description, $reclamationId, $userId);
    $types = 'ssii';

    $db->execute($sqlUpdate, $params, $types);

    $_SESSION['success_message'] = "Réclamation mise à jour avec succès!";
    header('Location: mes_reclamations.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de la mise à jour: " . $e->getMessage();
    header('Location: modifier_reclamation.php?id=' . $reclamationId);
    exit();
}

?>
