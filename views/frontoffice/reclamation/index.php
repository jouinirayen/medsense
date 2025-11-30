<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for notification
$notification = $_SESSION['notification'] ?? null;
if ($notification) {
    // Clear the notification from session
    unset($_SESSION['notification']);
}

// Use fixed user ID
$userId = 1;

// Fetch all reclamations for user
$reclamationModel = new Reclamation();
$reclamations = $reclamationModel->forUser($userId);

$pageTitle = "Mes Réclamations";
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
    <h1><?= htmlspecialchars($pageTitle); ?></h1>

    <div style="margin-bottom: 1rem;">
        <a href="create.php" class="btn btn-create">✏️ Créer une nouvelle réclamation</a>
        <a href="urgence.php" class="btn btn-urgence">🚨 Urgence</a>
    </div>

    <?php if ($reclamations): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reclamations as $rec): ?>
                    <tr>
                        <td><?= $rec['id']; ?></td>
                        <td><?= htmlspecialchars($rec['titre']); ?></td>
                        <td><?= htmlspecialchars($rec['type']); ?></td>
                        <td><?= htmlspecialchars($rec['statut']); ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($rec['date'])); ?></td>
                        <td>
                            <a href="show.php?id=<?= $rec['id']; ?>" class="btn btn-action">Voir</a>
                            <a href="edit.php?id=<?= $rec['id']; ?>" class="btn btn-action">Modifier</a>
                            <a href="delete.php?id=<?= $rec['id']; ?>" class="btn btn-delete" onclick="return confirm('Voulez-vous vraiment supprimer cette réclamation ? Cette action est irréversible.')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune réclamation trouvée.</p>
    <?php endif; ?>

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

    // Enhanced confirmation for delete
    document.querySelectorAll('a[href*="delete.php"]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Voulez-vous vraiment supprimer cette réclamation ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        });
    });
    </script>
</main>

<?php include '../../../footer.php'; ?>
</body>
</html>