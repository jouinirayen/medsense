<?php
/**
 * Page d'administration - Liste de toutes les réclamations
 */

require_once '../../../config/config.php';
 
// Vérifier que l'utilisateur est admin
 

$pageTitle = "Gérer les Réclamations";

// Récupérer les messages de session
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Filtre par type
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Afficher le header
include '../../../config/header.php';

try {
    $db = Database::getInstance();
    
    // Récupérer les réclamations avec filtre optionnel
    $sql = "SELECT r.*, u.username FROM reclamation r 
            LEFT JOIN user u ON r.id_user = u.id 
            WHERE 1=1";
    $params = array();
    $types = '';

    if ($filter === 'urgence') {
        $sql .= " AND r.type = ?";
        $params[] = TYPE_URGENCE;
        $types = 's';
    } elseif ($filter === 'normal') {
        $sql .= " AND r.type = ?";
        $params[] = TYPE_NORMAL;
        $types = 's';
    }

    $sql .= " ORDER BY r.date DESC";
    
    $reclamations = $db->fetchAll($sql, $params, $types);

} catch (Exception $e) {
    $errorMessage = "Erreur lors de la récupération: " . $e->getMessage();
    $reclamations = array();
}
?>

<h2>Gestion des Réclamations</h2>

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
    <h3>Filtrer par type :</h3>
    <a href="admin_reclamations.php" class="btn <?php echo ($filter === '') ? 'btn-success' : ''; ?>">
        Toutes (<?php echo count($reclamations); ?>)
    </a>
    <a href="admin_reclamations.php?filter=normal" class="btn <?php echo ($filter === 'normal') ? 'btn-success' : ''; ?>">
        Réclamations normales
    </a>
    <a href="admin_reclamations.php?filter=urgence" class="btn btn-danger" style="<?php echo ($filter === 'urgence') ? 'background-color: #c0392b;' : ''; ?>">
        🚨 Urgences
    </a>
</div>

<?php if (empty($reclamations)): ?>
    <div class="message info">
        Aucune réclamation trouvée.
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Utilisateur</th>
                <th>Date</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Réponses</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reclamations as $rec): ?>
                <?php 
                    // Compter les réponses
                    try {
                        $sqlCount = "SELECT COUNT(*) as count FROM reponse WHERE id_reclamation = ?";
                        $countResult = $db->fetch($sqlCount, array($rec['id']), 'i');
                        $replyCount = $countResult ? $countResult['count'] : 0;
                    } catch (Exception $e) {
                        $replyCount = 0;
                    }
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($rec['id']); ?></td>
                    <td><?php echo htmlspecialchars($rec['titre']); ?></td>
                    <td><?php echo htmlspecialchars($rec['username'] ?? 'Utilisateur inconnu'); ?></td>
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
                    <td><?php echo $replyCount; ?></td>
                    <td>
                        <div class="card-actions">
                            <a href="details_reclamation.php?id=<?php echo $rec['id']; ?>" 
                               class="btn btn-small">Détails</a>
                            <a href="ajouter_reponse.php?id=<?php echo $rec['id']; ?>" 
                               class="btn btn-small btn-success">Répondre</a>
                            <a href="admin_supprimer_reclamation.php?id=<?php echo $rec['id']; ?>" 
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
