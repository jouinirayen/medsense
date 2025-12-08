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

// Check if reclamation exists using Reclamation model
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

// Create Reclamation object to check current status
$reclamation = new Reclamation();
$reclamation->hydrate($reclamationData);

// Add response using Response model
try {
    $responseModel = new Response();
    $responseModel->setContenu($contenu)
                  ->setDate(date('Y-m-d H:i:s'))
                  ->setReclamationId($reclamationId)
                  ->setUserId($adminId);
    
    if ($responseModel->create()) {
        // FONCTIONNALITÉ: Changer automatiquement le statut de la réclamation
        $currentStatut = $reclamation->getStatut();
        $newStatut = null;
        $statutMessage = "";
        
        // Logique de changement de statut
        if ($currentStatut === Reclamation::STATUS_OPEN) {
            // Si "ouvert" → passer à "en cours"
            $newStatut = Reclamation::STATUS_IN_PROGRESS;
            $statutMessage = " et le statut a été changé de 'ouvert' à 'en cours'";
        } elseif ($currentStatut === Reclamation::STATUS_IN_PROGRESS) {
            // Si "en cours" → passer à "fermé"
            $newStatut = Reclamation::STATUS_CLOSED;
            $statutMessage = " et le statut a été changé de 'en cours' à 'fermé'";
        }
        // Si déjà "fermé", on ne change rien
        
        // Mettre à jour le statut si nécessaire
        if ($newStatut !== null) {
            $reclamation->setStatut($newStatut);
            // Utiliser updateStatut() pour admin (ne vérifie pas id_user)
            if (!$reclamation->updateStatut()) {
                // Si la mise à jour du statut échoue, on continue quand même
                // car la réponse a été créée avec succès
                error_log("Erreur lors de la mise à jour du statut de la réclamation #$reclamationId");
            }
        }
        
        // Set success notification
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => "Réponse envoyée avec succès !" . $statutMessage,
            'show' => true
        ];
        
        header("Location: details_reclamation.php?id=$reclamationId");
        exit;
    } else {
        throw new Exception("Échec de la création de la réponse.");
    }
    
} catch (Exception $e) {
    $_SESSION['errors'] = ["Erreur lors de l'enregistrement de la réponse: " . $e->getMessage()];
    header("Location: ajouter_reponse.php?id=$reclamationId");
    exit;
}
?>