<?php
require_once '../../controllers/ReservationController.php';
require_once '../../controllers/UserController.php';

$userController = new UserController();
$userController->requireRole('medecin');

$userId = $_SESSION['user_id'];
$reservationController = new ReservationController();

// Get User Info
$currentUser = $userController->getUserById($userId);

// Handle Flash Messages
$message = "";
$messageType = "";
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'];
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

// Get Appointments for Doctor
$appointments = $reservationController->getAppointmentsByDoctor($userId);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Consultations</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/appointments.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div style="text-align: center;">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="afficher_rendezvous_medecin.php" class="nav-link active"
                style="text-decoration: none; color: #0ea5e9; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                <span>Mes Consultations</span>
            </a>
            <a href="../frontoffice/logout.php" class="nav-link"
                style="text-decoration: none; color: #333; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
            </a>
        </nav>
    </header>

    <main class="main-container">
        <div class="page-header">
            <div class="page-title">
                <h1>Mes Consultations</h1>
                <p>Gérez vos rendez-vous avec les patients</p>
            </div>
            <div class="user-badge">
                <div class="user-avatar-small">
                    <i class="fas fa-user-md"></i>
                </div>
                <span>Dr. <?php echo htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']); ?></span>
            </div>
        </div>

        <?php if (empty($appointments)): ?>
            <div class="empty-state">
                <div style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1.5rem;">
                    <i class="far fa-calendar-check"></i>
                </div>
                <h3 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 0.5rem;">Aucune consultation prévue</h3>
                <p style="color: #64748b;">Vous n'avez pas de rendez-vous à venir.</p>
            </div>
        <?php else: ?>
            <div class="appointments-grid">
                <?php foreach ($appointments as $appt): ?>
                    <div class="appointment-card">
                        <div class="card-header">
                            <div class="doctor-profile">
                                <div class="doctor-avatar">
                                    <i class="fas fa-user-injured"></i>
                                </div>
                                <div class="doctor-info">
                                    <h3><?php echo htmlspecialchars(($appt['patientPrenom'] ?? '') . ' ' . ($appt['patientNom'] ?? '')); ?>
                                    </h3>
                                    <p class="specialty">Patient</p>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo htmlspecialchars($appt['statut']); ?>">
                                <?php echo htmlspecialchars($appt['statut'] === 'pris' ? 'Confirmé' : $appt['statut']); ?>
                            </span>
                        </div>

                        <div class="card-body">
                            <div style="font-size: 1.5rem; color: var(--primary);">
                                <i class="far fa-clock"></i>
                            </div>
                            <div class="time-display">
                                <span class="time-label">Heure du rendez-vous</span>
                                <span class="time-value"><?php echo htmlspecialchars(substr($appt['heureRdv'], 0, 5)); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <audio id="successSound" src="../son/suc.mp3" preload="auto"></audio>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!empty($message) && $messageType === 'success'): ?>
                const audio = document.getElementById('successSound');
                // Try to play audio
                audio.play().catch(e => console.log("Audio play failed:", e));

                Swal.fire({
                    title: 'Succès!',
                    text: '<?php echo addslashes($message); ?>',
                    icon: 'success',
                    confirmButtonColor: '#0ea5e9',
                    confirmButtonText: 'OK'
                });
            <?php elseif (!empty($message) && $messageType === 'error'): ?>
                Swal.fire({
                    title: 'Erreur',
                    text: '<?php echo addslashes($message); ?>',
                    icon: 'error',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>