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

$reclamationId = (int)($_POST['id_reclamation'] ?? 0);
$contenu = trim($_POST['contenu'] ?? '');
$adminId = 1; // Admin user ID

// Validation
$errors = [];
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
    header("Location: ajouter_reponse.php?id=$reclamationId");
    exit;
}

// Check if reclamation exists using direct database connection
$pdo = (new config())->getConnexion();
$stmt = $pdo->prepare("SELECT * FROM reclamation WHERE id = ?");
$stmt->execute([$reclamationId]);
$reclamation = $stmt->fetch();

if (!$reclamation) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => "Réclamation introuvable !",
        'show' => true
    ];
    header('Location: admin_reclamations.php');
    exit;
}

// Add response using direct database query
try {
    $date = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO reponse (contenu, date, id_reclamation, id_user) VALUES (?, ?, ?, ?)");
    $stmt->execute([$contenu, $date, $reclamationId, $adminId]);
    
    // Set success notification
    $_SESSION['notification'] = [
        'type' => 'success',
        'message' => "Réponse envoyée avec succès !",
        'show' => true
    ];
    
    header("Location: details_reclamation.php?id=$reclamationId");
    exit;
    
} catch (Exception $e) {
    $_SESSION['errors'] = ["Erreur lors de l'enregistrement de la réponse: " . $e->getMessage()];
    header("Location: ajouter_reponse.php?id=$reclamationId");
    exit;
}
?>