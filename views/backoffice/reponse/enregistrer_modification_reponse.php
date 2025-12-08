<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_reclamations.php');
    exit;
}

$responseId = (int)($_POST['id'] ?? 0);
$reclamationId = (int)($_POST['reclamation_id'] ?? 0);
$contenu = trim($_POST['contenu'] ?? '');

// Validation
$errors = [];
if ($responseId <= 0) {
    $errors[] = "ID réponse invalide.";
}

if ($reclamationId <= 0) {
    $errors[] = "ID réclamation invalide.";
}

if (empty($contenu)) {
    $errors[] = "La réponse ne peut pas être vide.";
} elseif (strlen($contenu) < 5) {
    $errors[] = "La réponse doit contenir au moins 5 caractères.";
} elseif (strlen($contenu) > 3000) {
    $errors[] = "La réponse ne doit pas dépasser 3000 caractères.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_contenu'] = $contenu;
    header("Location: modifier_reponse.php?id=$responseId&reclamation_id=$reclamationId");
    exit;
}

// Check if response exists using Response model
$responseModel = new Response();
$response = $responseModel->findById($responseId);

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

// Update response using Response model
try {
    $response->setContenu($contenu)
             ->setDate(date('Y-m-d H:i:s')); // Update date to current time
    
    if ($response->update()) {
        // Set success notification
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => "Réponse modifiée avec succès !",
            'show' => true
        ];
        
        header("Location: details_reclamation.php?id=$reclamationId");
        exit;
    } else {
        throw new Exception("Échec de la mise à jour de la réponse.");
    }
    
} catch (Exception $e) {
    $_SESSION['errors'] = ["Erreur lors de la modification de la réponse: " . $e->getMessage()];
    $_SESSION['old_contenu'] = $contenu;
    header("Location: modifier_reponse.php?id=$responseId&reclamation_id=$reclamationId");
    exit;
}
?>

