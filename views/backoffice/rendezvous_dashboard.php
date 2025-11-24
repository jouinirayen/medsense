<?php
require_once '../../controllers/RendezvousController.php';

$controller = new RendezvousController();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only handle updates if they come from this page (e.g. quick status toggle, though currently not implemented)
    // For now, we can remove the add/update logic as it's moved to add_creneau.php (and potentially edit_creneau.php)
    // But wait, the user only asked for add_creneau.php.
    // If I remove the form, I can't edit anymore unless I create edit_creneau.php or keep edit logic here?
    // The user said "separé du dashboard rendezvous pour alleger le code".
    // I will remove the logic.
}





$slots = $controller->obtenirTousLesCreneaux();
$services = $controller->obtenirServices();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des créneaux</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <img src="../uploads/logo.jpeg" alt="Logo Medsense" style="height: 125px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="../frontoffice/front.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Front Office</span>
            </a>
            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="rendezvous_dashboard.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-calendar-plus"></i></span>
                <span>Créneaux</span>
            </a>
        </nav>
    </header>

<main class="main-content">
    <section class="hero-section">
        <h1 class="hero-title">Gestion des créneaux disponibles</h1>
        <p class="hero-description">
            Ajoutez, modifiez ou supprimez les créneaux horaires disponibles pour vos services médicaux.
        </p>
    </section>

    <section class="action-section" style="margin-bottom: 20px;">
        <div class="container">
            <a href="add_creneau.php" class="btn-primary">
                <i class="fas fa-plus"></i> Ajouter un créneau
            </a>
        </div>
    </section>

    <section class="form-section">
        <div class="form-container">
            <?php if (!empty($message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="table-section">
        <div class="table-header-info">
            <h2 class="section-title">Liste des créneaux</h2>
            <div class="table-count-badge">
                <i class="fas fa-calendar-check"></i>
                <span class="count-number"><?php echo count($slots); ?></span>
                <span class="count-text">créneau<?php echo count($slots) > 1 ? 'x' : ''; ?> programmé<?php echo count($slots) > 1 ? 's' : ''; ?></span>
            </div>
        </div>
        <div class="table-container">
            <?php if (empty($slots)): ?>
                <div class="empty-slots">
                    <div class="empty-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>Aucun créneau disponible</h3>
                    <p>Vous n'avez aucun créneau programmé pour le moment. Ajoutez-en un ci-dessus.</p>
                </div>
            <?php else: ?>
            <table class="slots-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-stethoscope"></i> Service</th>
                        <th><i class="fas fa-calendar-day"></i> Date</th>
                        <th><i class="fas fa-clock"></i> Heure</th>
                        <th><i class="fas fa-info-circle"></i> Statut</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($slots as $slot): ?>
                        <tr>
                            <td>
                                <div class="service-cell">
                                    <i class="fas fa-circle" style="font-size: 8px; color: #3b82f6; margin-right: 8px;"></i>
                                    <strong><?php echo $slot['service_name']; ?></strong>
                                </div>
                            </td>
                            <td>
                                <div class="date-cell">
                                    <i class="fas fa-calendar-alt" style="margin-right: 6px; color: #64748b;"></i>
                                    <?php echo date('d/m/Y', strtotime($slot['appointment_date'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="time-cell">
                                    <i class="fas fa-clock" style="margin-right: 6px; color: #64748b;"></i>
                                    <?php echo substr($slot['appointment_time'], 0, 5); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($slot['is_booked']): ?>
                                    <span class="status-badge status-reserved">
                                        <i class="fas fa-lock"></i>
                                        Réservé
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-available">
                                        <i class="fas fa-check-circle"></i>
                                        Disponible
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_creneau.php?id=<?php echo $slot['id']; ?>" class="btn-edit-modern">
                                        <i class="fas fa-edit"></i>
                                        <span>Modifier</span>
                                    </a>
                                    <a href="delete_creneau.php?id=<?php echo $slot['id']; ?>" class="btn-delete-modern" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce créneau ?');">
                                        <i class="fas fa-trash"></i>
                                        <span>Supprimer</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </section>
</main>

</body>
</html>

