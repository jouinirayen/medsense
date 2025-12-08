<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$responseId = $_GET['id'] ?? null;
$reclamationId = $_GET['reclamation_id'] ?? null;

if (!$responseId || !$reclamationId) {
    header('Location: admin_reclamations.php');
    exit;
}

// Check if response exists using Response model
$responseModel = new Response();
$response = $responseModel->findById((int)$responseId);

if (!$response) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => "Réponse introuvable !",
        'show' => true
    ];
    header("Location: details_reclamation.php?id=$reclamationId");
    exit;
}

// Check if reclamation exists
$pdo = (new config())->getConnexion();
$stmt = $pdo->prepare("SELECT * FROM reclamation WHERE id = ?");
$stmt->execute([$reclamationId]);
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

$reclamation = new Reclamation();
$reclamation->hydrate($reclamationData);

// Delete response using Response model
try {
    if ($response->delete()) {
        // Set success notification
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => "Réponse supprimée avec succès !",
            'show' => true
        ];
        
        header("Location: details_reclamation.php?id=$reclamationId");
        exit;
    } else {
        throw new Exception("Échec de la suppression de la réponse.");
    }
    
} catch (Exception $e) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => "Erreur lors de la suppression de la réponse: " . $e->getMessage(),
        'show' => true
    ];
    header("Location: details_reclamation.php?id=$reclamationId");
    exit;
}
?>

