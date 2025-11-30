<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = $_GET['id'] ?? null;
$userId = 1; // Hardcoded user ID

if (!$id) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => "ID réclamation manquant !",
        'show' => true
    ];
    header('Location: index.php');
    exit;
}

// Fetch reclamation to check if it exists
$reclamationModel = new Reclamation();
$reclamation = $reclamationModel->findForUser($id, $userId);

if (!$reclamation) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => "Réclamation introuvable !",
        'show' => true
    ];
    header('Location: index.php');
    exit;
}

// Delete the reclamation and its responses
$responseModel = new Response();
$responseModel->deleteForReclamation($id);
$reclamationModel->deleteForUser($id, $userId);

// Set success notification
$_SESSION['notification'] = [
    'type' => 'success',
    'message' => "Réclamation supprimée avec succès !",
    'show' => true
];

header('Location: index.php');
exit;