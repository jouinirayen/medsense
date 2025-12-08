<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => "ID réclamation manquant !",
        'show' => true
    ];
    header('Location: admin_reclamations.php');
    exit;
}

// Use direct database connection to check if reclamation exists
$pdo = (new config())->getConnexion();
$stmt = $pdo->prepare("SELECT * FROM reclamation WHERE id = ?");
$stmt->execute([$id]);
$reclamationData = $stmt->fetch();

if (!$reclamationData) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => "Réclamation introuvable !",
        'show' => true
    ];
    header('Location: admin_reclamations.php');
    exit;
}

// Delete the reclamation and its responses using models
try {
    $responseModel = new Response();
    $responseModel->deleteForReclamation($id);
    
    // Delete reclamation
    $stmt = $pdo->prepare("DELETE FROM reclamation WHERE id = ?");
    $stmt->execute([$id]);

    // Set success notification
    $_SESSION['notification'] = [
        'type' => 'success',
        'message' => "Réclamation supprimée avec succès !",
        'show' => true
    ];
    
} catch (Exception $e) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => "Erreur lors de la suppression : " . $e->getMessage(),
        'show' => true
    ];
}

header('Location: admin_reclamations.php');
exit;
?>