<?php
session_start();
include '../../models/Reservation.php';
require_once '../../controllers/ReservationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$reservationController = new ReservationController();
$error = null;
$success = false;

// Get parameters
$appointmentId = isset($_GET['id']) ? $_GET['id'] : null;
$newTime = isset($_GET['new_time']) ? $_GET['new_time'] : (isset($_POST['new_time']) ? $_POST['new_time'] : null);

if (!$appointmentId || !$newTime) {
    header("Location: afficher_rendezvous_patient.php");
    exit;
}

// Get appointment details
$appointment = $reservationController->getAppointmentById($appointmentId);

if (!$appointment) {
    header("Location: afficher_rendezvous_patient.php");
    exit;
}

// Handle Confirmation (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation = new Reservation();
    $reservation->setHeureRdv($newTime);

    if ($reservationController->updateAppointment($appointmentId, $reservation)) {
        $success = true;
    } else {
        $error = "Ce créneau n'est plus disponible ou une erreur est survenue.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmer la modification</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/modifier_rendezvous.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div style="text-align: center;">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="afficher_rendezvous_patient.php" class="nav-link active"
                style="text-decoration: none; color: #0ea5e9; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                <span>Mes Rendez-vous</span>
            </a>
        </nav>
    </header>

    <main class="main-content">
        <div class="confirmation-box">
            <h1 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 1rem;">Confirmer la modification</h1>
            <p style="color: #64748b;">Vous êtes sur le point de modifier votre rendez-vous avec <strong>Dr.
                    <?php echo $appointment['medecinPrenom'] . ' ' . $appointment['medecinNom']; ?></strong>.
            </p>

            <?php if ($error): ?>
                <div
                    style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="time-comparison">
                <div class="time-card old">
                    <span class="time-label">Ancien créneau</span>
                    <span class="time-value"><?php echo substr($appointment['heureRdv'], 0, 5); ?></span>
                </div>

                <div class="arrow-icon">
                    <i class="fas fa-arrow-right"></i>
                </div>

                <div class="time-card new">
                    <span class="time-label">Nouveau créneau</span>
                    <span class="time-value"><?php echo $newTime; ?></span>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="new_time" value="<?php echo $newTime; ?>">
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                    <a href="modifier_rendezvous.php?id=<?php echo $appointmentId; ?>" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-confirm">Confirmer la modification</button>
                </div>
            </form>
        </div>
    </main>

    <?php if ($success): ?>
        <audio id="successSound" src="../son/suc.mp3" preload="auto"></audio>
        <script>
            const audio = document.getElementById('successSound');
            // Try to play audio
            audio.play().catch(e => console.log("Audio play failed:", e));

            Swal.fire({
                title: 'Modification confirmée !',
                text: 'Votre rendez-vous a été déplacé avec succès.',
                icon: 'success',
                confirmButtonColor: '#0ea5e9',
                confirmButtonText: 'Voir mes rendez-vous',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'afficher_rendezvous_patient.php';
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>