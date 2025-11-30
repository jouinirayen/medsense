<?php
/**
 * Page de visualisation détaillée d'une réclamation (ADMIN)
 */

require_once '../../../config/config.php';
 
 

$reclamationId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($reclamationId <= 0) {
    header('Location: admin_reclamations.php');
    exit();
}

try {
    $db = Database::getInstance();
    
    // Récupérer la réclamation
    $sql = "SELECT r.*, u.username FROM reclamation r 
            LEFT JOIN user u ON r.id_user = u.id
            WHERE r.id = ?";
    $reclamation = $db->fetch($sql, array($reclamationId), 'i');

    if (!$reclamation) {
        header('Location: admin_reclamations.php');
        exit();
    }

    $pageTitle = "Détails - " . htmlspecialchars($reclamation['titre']);

    // Récupérer les réponses
    $sqlReponses = "SELECT r.*, u.username FROM reponse r 
                    LEFT JOIN user u ON r.id_user = u.id
                    WHERE r.id_reclamation = ? 
                    ORDER BY r.date DESC";
    $reponses = $db->fetchAll($sqlReponses, array($reclamationId), 'i');

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération: " . $e->getMessage();
    header('Location: admin_reclamations.php');
    exit();
}

include '../../../config/header.php';
?>

<h2>Détails de la Réclamation - ADMIN</h2>

<div class="card">
    <h3><?php echo htmlspecialchars($reclamation['titre']); ?></h3>
    
    <div style="margin: 1rem 0;">
        <span class="badge <?php echo htmlspecialchars($reclamation['type']); ?>">
            <?php echo ucfirst(htmlspecialchars($reclamation['type'])); ?>
        </span>
        <span class="badge <?php echo str_replace(' ', '-', htmlspecialchars($reclamation['statut'])); ?>">
            <?php echo htmlspecialchars($reclamation['statut']); ?>
        </span>
    </div>

    <p><strong>Description :</strong></p>
    <p><?php echo nl2br(htmlspecialchars($reclamation['description'])); ?></p>

    <div class="card-meta">
        <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($reclamation['date'])); ?></p>
        <p><strong>Utilisateur :</strong> <?php echo htmlspecialchars($reclamation['username']); ?> (ID: <?php echo $reclamation['id_user']; ?>)</p>
        <p><strong>ID Réclamation :</strong> <?php echo htmlspecialchars($reclamation['id']); ?></p>
    </div>

    <div class="card-actions">
        <a href="ajouter_reponse.php?id=<?php echo $reclamation['id']; ?>" 
           class="btn btn-success">Répondre</a>
        <a href="admin_reclamations.php" class="btn">Retour</a>
    </div>
</div>

<h3>Réponses (<?php echo count($reponses); ?>)</h3>

<?php if (empty($reponses)): ?>
    <div class="message info">
        Aucune réponse pour le moment.
    </div>
<?php else: ?>
    <?php foreach ($reponses as $reponse): ?>
        <div class="card">
            <p><strong><?php echo htmlspecialchars($reponse['username'] ?? 'Admin'); ?></strong> 
               <span style="color: #7f8c8d; font-size: 0.9rem;">(ID: <?php echo $reponse['id_user']; ?>)</span></p>
            <p><?php echo nl2br(htmlspecialchars($reponse['contenu'])); ?></p>
            <div class="card-meta">
                <p><?php echo date('d/m/Y H:i', strtotime($reponse['date'])); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
include '../../../config/footer.php';
?>
