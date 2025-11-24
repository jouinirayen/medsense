<?php
require_once '../../controllers/RendezvousController.php';

$rendezvousController = new RendezvousController();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = (int) $_POST['cancel_id'];
    if ($rendezvousController->annulerRendezVous($cancelId)) {
        $message = "Le rendez-vous a été annulé.";
    } else {
        $error = "Impossible d'annuler ce rendez-vous.";
    }
}

$slots = $rendezvousController->obtenirTousLesRendezVousReserves();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendez-vous disponibles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 125px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="front.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Front Office</span>
            </a>
            <a href="rendezvous_list.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                <span>Rendez-vous</span>
            </a>
            <a href="../backoffice/dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-cog"></i></span>
                <span>Admin</span>
            </a>
        </nav>
    </header>

<main class="main-content">
    <section class="hero-section">
        <h1 class="hero-title">Rendez-vous réservés</h1>
        <p class="hero-description">
            Retrouvez ici l'ensemble des créneaux déjà réservés. Vous pouvez annuler un rendez-vous pour libérer le créneau.
        </p>
    </section>

    <?php if (!empty($message) || !empty($error)): ?>
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
    <?php endif; ?>

    <section class="table-section">
        <div class="table-header-info">
            <div class="table-count-badge">
                <i class="fas fa-calendar-check"></i>
                <span class="count-number"><?php echo count($slots); ?></span>
                <span class="count-text">rendez-vous réservé<?php echo count($slots) > 1 ? 's' : ''; ?></span>
            </div>
        </div>
        <?php if (empty($slots)): ?>
            <div class="empty-bookings">
                <div class="empty-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3>Aucun rendez-vous réservé</h3>
                <p>Vous n'avez aucun rendez-vous réservé pour le moment.</p>
            </div>
        <?php else: ?>
        <div class="bookings-grid">
            <?php foreach ($slots as $slot): ?>
                <div class="booking-card">
                    <div class="booking-card-header">
                        <div class="booking-service">
                            <div class="booking-icon">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <div class="booking-info">
                                <h3><?php echo $slot['service_name']; ?></h3>
                                <?php if (!empty($slot['service_description'])): ?>
                                <p><?php echo $slot['service_description']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="booking-meta">
                        <div class="booking-meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="meta-content">
                                <label>Date</label>
                                <span><?php echo date('d/m/Y', strtotime($slot['appointment_date'])); ?></span>
                            </div>
                        </div>
                        <div class="booking-meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="meta-content">
                                <label>Heure</label>
                                <span><?php echo substr($slot['appointment_time'], 0, 5); ?></span>
                            </div>
                        </div>
                        <?php if (!empty($slot['booked_email'])): ?>
                        <div class="booking-meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="meta-content">
                                <label>Email</label>
                                <span><?php echo $slot['booked_email']; ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="booking-actions">
                        <form method="POST" action="rendezvous_list.php" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?');">
                            <input type="hidden" name="cancel_id" value="<?php echo $slot['id']; ?>">
                            <button type="submit" class="btn-cancel">
                                <i class="fas fa-times-circle"></i>
                                <span>Annuler</span>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</main>

</body>
</html>

