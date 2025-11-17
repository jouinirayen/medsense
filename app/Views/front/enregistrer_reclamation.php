<?php


require_once '../../../config/config.php';
require_once '../../../config/db.php';
require_once '../../../config/auth.php';

requireLogin();
$titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$userId = getUserId();


$errors = array();

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
    header('Location: creer_reclamation.php');
    exit();
}

try {
    // Récupérer la base de données
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Insérer la réclamation
    $date = date('Y-m-d H:i:s');
    $type = TYPE_NORMAL;
    $statut = STATUS_OPEN;

    $sql = "INSERT INTO reclamation (titre, description, date, id_user, type, statut) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $params = array($titre, $description, $date, $userId, $type, $statut);
    $types = 'sssssi';

    $stmt = $db->execute($sql, $params, $types);

    $_SESSION['success_message'] = "Votre réclamation a été créée avec succès!";
    header('Location: mes_reclamations.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de l'enregistrement: " . $e->getMessage();
    header('Location: creer_reclamation.php');
    exit();
}

?>
