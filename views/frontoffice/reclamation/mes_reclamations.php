<?php

require_once '../../../config/config.php';
 

$pageTitle = "Mes réclamations";
$userId = getUserId();


$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);


include '../../../config/header.php';

try {
    $db = Database::getInstance();
    
    // Récupérer les réclamations de l'utilisateur
    $sql = "SELECT * FROM reclamation WHERE id_user = ? ORDER BY date DESC";
    $reclamations = $db->fetchAll($sql, array($userId), 'i');

} catch (Exception $e) {
    $errorMessage = "Erreur lors de la récupération des réclamations: " . $e->getMessage();
    $reclamations = array();
}
?>

<h2>Mes Réclamations</h2>

<?php if (!empty($successMessage)): ?>
    <div class="message success">
        <?php echo htmlspecialchars($successMessage); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMessage)): ?>
    <div class="message error">
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
<?php endif; ?>

<div style="margin-bottom: 2rem;">
    <a href="creer_reclamation.php" class="btn btn-success">+ Créer une nouvelle réclamation</a>
</div>

<?php if (empty($reclamations)): ?>
    <div class="message info">
        Vous n'avez aucune réclamation pour le moment. 
        <a href="creer_reclamation.php">Créer une réclamation</a>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Date</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reclamations as $rec): ?>
                <tr>
                    <td><?php echo htmlspecialchars($rec['id']); ?></td>
                    <td><?php echo htmlspecialchars($rec['titre']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($rec['date'])); ?></td>
                    <td>
                        <span class="badge <?php echo htmlspecialchars($rec['type']); ?>">
                            <?php echo ucfirst(htmlspecialchars($rec['type'])); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?php echo str_replace(' ', '-', htmlspecialchars($rec['statut'])); ?>">
                            <?php echo htmlspecialchars($rec['statut']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="card-actions">
                            <a href="details_reclamation.php?id=<?php echo $rec['id']; ?>" 
                               class="btn btn-small">Voir</a>
                            <a href="modifier_reclamation.php?id=<?php echo $rec['id']; ?>" 
                               class="btn btn-small btn-warning">Modifier</a>
                            <a href="supprimer_reclamation.php?id=<?php echo $rec['id']; ?>" 
                               class="btn btn-small btn-danger"
                               onclick="return confirmDelete('Êtes-vous sûr de vouloir supprimer cette réclamation?')">Supprimer</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
include '../../../config/footer.php';
?>
