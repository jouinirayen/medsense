<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for notification
$notification = $_SESSION['notification'] ?? null;
if ($notification) {
    unset($_SESSION['notification']);
}

// Filter by type
$filter = $_GET['filter'] ?? '';

// Fetch all reclamations using direct database connection
$pdo = (new config())->getConnexion();

if ($filter === 'urgence') {
    $sql = "SELECT r.*, u.username FROM reclamation r 
            LEFT JOIN user u ON r.id_user = u.id 
            WHERE r.type = 'urgence'
            ORDER BY r.date DESC";
} elseif ($filter === 'normal') {
    $sql = "SELECT r.*, u.username FROM reclamation r 
            LEFT JOIN user u ON r.id_user = u.id 
            WHERE r.type = 'normal'
            ORDER BY r.date DESC";
} else {
    $sql = "SELECT r.*, u.username FROM reclamation r 
            LEFT JOIN user u ON r.id_user = u.id 
            ORDER BY r.date DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute();
$reclamations = $stmt->fetchAll();

$pageTitle = "Gestion des Réclamations - Admin";
?>

<?php include '../../../admin_sidebar.php'; ?>

<div class="admin-content">
    <!-- Header Section -->
    <div class="content-header" style="margin-bottom: 2rem;">
        <h1 style="color: #1e293b; font-size: 2rem; margin-bottom: 0.5rem;">Gestion des Réclamations</h1>
        <p style="color: #64748b; font-size: 1.1rem;">Interface d'administration - Gestion de toutes les réclamations</p>
    </div>

    <!-- Filter Section -->
    <div class="filter-section" style="background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 1rem; color: #1e293b;">Filtrer par type :</h3>
        <div class="filter-buttons" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="admin_reclamations.php" class="btn <?= ($filter === '') ? 'btn-primary' : 'btn-secondary'; ?>">
                Toutes (<?= count($reclamations); ?>)
            </a>
            <a href="admin_reclamations.php?filter=normal" class="btn <?= ($filter === 'normal') ? 'btn-primary' : 'btn-secondary'; ?>">
                Réclamations Normales
            </a>
            <a href="admin_reclamations.php?filter=urgence" class="btn <?= ($filter === 'urgence') ? 'btn-danger' : 'btn-secondary'; ?>">
                🚨 Urgences
            </a>
        </div>
    </div>

    <?php if (empty($reclamations)): ?>
        <div class="empty-state" style="text-align: center; padding: 3rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <i class="fas fa-inbox" style="font-size: 4rem; color: #6b7280; margin-bottom: 1rem;"></i>
            <h3 style="color: #374151; margin-bottom: 0.5rem;">Aucune réclamation trouvée</h3>
            <p style="color: #6b7280;">Aucune réclamation ne correspond aux critères de recherche.</p>
        </div>
    <?php else: ?>
        <div class="reclamations-grid">
            <?php foreach ($reclamations as $rec): ?>
                <?php 
                    // Count responses for this reclamation
                    $responseModel = new Response();
                    $responses = $responseModel->forReclamation($rec['id']);
                    $responseCount = count($responses);
                ?>
                <div class="reclamation-card" style="background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 1.5rem; border-left: 4px solid <?= $rec['type'] === 'urgence' ? '#ef4444' : '#3b82f6'; ?>">
                    <div class="reclamation-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                        <div class="reclamation-title" style="flex: 1;">
                            <h3 style="margin: 0; color: #1f2937; font-size: 1.2rem;">
                                <?= htmlspecialchars($rec['titre']); ?>
                            </h3>
                            <p style="margin: 0.25rem 0 0 0; color: #6b7280; font-size: 0.9rem;">
                                <i class="fas fa-user"></i> Par <?= htmlspecialchars($rec['username'] ?? 'Utilisateur inconnu'); ?>
                            </p>
                        </div>
                        <div class="reclamation-meta" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <span class="badge <?= $rec['type'] === 'urgence' ? 'badge-urgence' : 'badge-normal'; ?>" style="padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                <i class="fas <?= $rec['type'] === 'urgence' ? 'fa-exclamation-triangle' : 'fa-file-alt'; ?>"></i>
                                <?= htmlspecialchars($rec['type']); ?>
                            </span>
                            <span class="badge statut-<?= str_replace(' ', '-', $rec['statut']); ?>" style="padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                <i class="fas 
                                    <?= $rec['statut'] === 'fermé' ? 'fa-check-circle' : 
                                       ($rec['statut'] === 'en cours' ? 'fa-spinner' : 'fa-clock'); ?>">
                                </i>
                                <?= htmlspecialchars($rec['statut']); ?>
                            </span>
                        </div>
                    </div>

                    <p class="reclamation-description" style="color: #4b5563; margin-bottom: 1rem; line-height: 1.5;">
                        <?= htmlspecialchars(substr($rec['description'], 0, 150)) . (strlen($rec['description']) > 150 ? '...' : ''); ?>
                    </p>

                    <div class="reclamation-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div class="reclamation-info" style="display: flex; gap: 1rem; color: #6b7280; font-size: 0.9rem;">
                            <span>
                                <i class="far fa-calendar"></i>
                                <?= date('d/m/Y H:i', strtotime($rec['date'])); ?>
                            </span>
                            <span>
                                <i class="far fa-comments"></i>
                                <?= $responseCount; ?> réponse(s)
                            </span>
                        </div>
                        <div class="reclamation-actions" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="details_reclamation.php?id=<?= $rec['id']; ?>" class="btn btn-primary btn-small">
                                <i class="fas fa-eye"></i>
                                Détails
                            </a>
                            <a href="ajouter_reponse.php?id=<?= $rec['id']; ?>" class="btn btn-success btn-small">
                                <i class="fas fa-reply"></i>
                                Répondre
                            </a>
                            <a href="admin_supprimer_reclamation.php?id=<?= $rec['id']; ?>" class="btn btn-danger btn-small" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réclamation ? Cette action est irréversible.')">
                                <i class="fas fa-trash"></i>
                                Supprimer
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Notification Container -->
<div id="notificationContainer"></div>

<script>
// Notification system
function showNotification(message, type = 'success') {
    const container = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icons = {
        success: '✓',
        error: '✗',
        warning: '⚠'
    };

    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${icons[type]}</span>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;

    container.appendChild(notification);

    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        hideNotification(notification);
    }, 5000);
}

function hideNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 300);
}

// Show notification if exists from PHP session
<?php if ($notification && $notification['show']): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('<?= addslashes($notification['message']) ?>', '<?= $notification['type'] ?>');
    });
<?php endif; ?>
</script>

<style>
/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-small {
    padding: 8px 16px;
    font-size: 0.8rem;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* Badge Styles */
.badge-normal {
    background: #dbeafe;
    color: #1e40af;
}

.badge-urgence {
    background: #fecaca;
    color: #dc2626;
}

.statut-ouvert {
    background: #fef3c7;
    color: #92400e;
}

.statut-en-cours {
    background: #dbeafe;
    color: #1e40af;
}

.statut-fermé {
    background: #dcfce7;
    color: #166534;
}

/* Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
    max-width: 350px;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification.success {
    background: #10b981;
    border-left: 4px solid #059669;
}

.notification.error {
    background: #ef4444;
    border-left: 4px solid #dc2626;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-icon {
    font-size: 18px;
}

.notification-close {
    background: none;
    border: none;
    color: white;
    font-size: 16px;
    cursor: pointer;
    margin-left: auto;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .reclamation-footer {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .reclamation-actions {
        width: 100%;
        justify-content: center;
    }
    
    .filter-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php include '../../../admin_footer.php'; ?>