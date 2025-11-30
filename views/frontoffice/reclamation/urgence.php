<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reclamationModel = new Reclamation();
    $reclamationModel->create([
        'titre' => "🚨 Urgence - " . date('H:i:s'),
        'description' => "Alerte urgence envoyée par l'utilisateur. Intervention immédiate requise.",
        'date' => date('Y-m-d H:i:s'),
        'id_user' => 1,
        'type' => 'urgence',
        'statut' => 'ouvert'
    ]);

    // Set success notification
    $_SESSION['notification'] = [
        'type' => 'success',
        'message' => "Alerte urgence envoyée avec succès ! L'équipe a été notifiée.",
        'show' => true
    ];

    header('Location: index.php');
    exit;
}

$pageTitle = "Alerte Urgence";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>
<?php include '../../../navbar.php'; ?>

<main class="main-content">
    <div class="form-container">
        <div class="form-header">
            <h1>🚨 Alerte Urgence</h1>
            <a href="index.php" class="btn btn-cancel">← Retour à la liste</a>
        </div>

        <div class="form-card">
            <div class="alert alert-warning">
                <h3><i class="fas fa-exclamation-triangle"></i> Attention - Usage d'urgence uniquement</h3>
                <p>Cette fonctionnalité est réservée aux situations critiques nécessitant une intervention immédiate.</p>
                <ul>
                    <li>Cette alerte notifie immédiatement l'équipe de support</li>
                    <li>Utilisez uniquement pour des situations critiques</li>
                    <li>Une réponse prioritaire sera apportée à votre demande</li>
                    <li>L'abus de cette fonctionnalité peut entraîner des restrictions</li>
                </ul>
            </div>

            <form method="POST" action="" onsubmit="return confirmUrgence()">
                <div class="form-group" style="text-align: center;">
                    <p style="margin-bottom: 2rem; font-size: 1.1rem; color: #666;">
                        Cliquez sur le bouton ci-dessous pour envoyer une alerte urgence
                    </p>
                    
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fas fa-bell"></i>
                        ENVOYER L'ALERTE D'URGENCE
                    </button>
                    
                    <p style="margin-top: 1rem; color: #dc3545; font-weight: 600;">
                        Une confirmation vous sera demandée avant l'envoi
                    </p>
                </div>
            </form>

            <div class="emergency-contact" style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 1.5rem; margin-top: 2rem;">
                <h4 style="color: #155724; margin-bottom: 0.5rem;">
                    <i class="fas fa-phone-alt"></i> Contact d'urgence
                </h4>
                <p style="color: #155724; margin: 0;">
                    Si la situation nécessite une intervention immédiate, contactez directement le support au <strong>+33 1 23 45 67 89</strong>
                </p>
            </div>
        </div>
    </div>
</main>

<script>
function confirmUrgence() {
    return confirm("🚨 CONFIRMATION D'URGENCE\n\nÊtes-vous certain de vouloir envoyer une alerte urgence ?\n\nCette action notifie immédiatement l'équipe de support et doit être réservée aux situations critiques.\n\nCliquez sur OK pour confirmer l'envoi de l'alerte.");
}
</script>

<?php include '../../../footer.php'; ?>
</body>
</html>