<?php
session_start();
require_once '../../controllers/ReservationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$reservationController = new ReservationController();
$error = null;
$success = false;

// Handle Cancellation (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $result = $reservationController->cancelAppointment($_POST['cancel_id']);

    if ($result) {
        $success = true;

        if (is_array($result)) {
            // Send cancellation email
            require_once 'mailing_handler.php';
            sendCancellationEmail(
                $result['patientEmail'],
                $result['patientPrenom'] . " " . $result['patientNom'],
                $result['medecinPrenom'] . " " . $result['medecinNom'],
                date('Y-m-d', strtotime($result['heureRdv'])),
                date('H:i', strtotime($result['heureRdv']))
            );
        }
    } else {
        $error = "Erreur lors de l'annulation.";
    }
}

// Handle Confirmation View (GET)
$appointmentId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$appointmentId && !$success) {
    // If no ID provided and not a success post, redirect back
    header("Location: afficher_rendezvous_patient.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annuler le rendez-vous</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/annuler_rendezvous.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="cancel-container">
        <div class="warning-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>

        <h2 style="margin-bottom: 1rem; color: #1e293b;">Confirmer l'annulation</h2>

        <p style="color: #64748b; margin-bottom: 1rem;">
            Êtes-vous sûr de vouloir annuler ce rendez-vous ?
        </p>
        <p style="color: #64748b; font-size: 0.9rem;">
            Cette action est irréversible.
        </p>

        <?php if ($error): ?>
            <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="cancel_id" value="<?php echo $appointmentId; ?>">

            <div class="btn-group">
                <a href="afficher_rendezvous_patient.php" class="btn-cancel">Retour</a>
                <button type="submit" class="btn-confirm">Oui, annuler</button>
            </div>
        </form>
    </div>

    <?php if ($success): ?>
        <audio id="successSound" src="../son/suc.mp3" preload="auto"></audio>
        <script>
            const audio = document.getElementById('successSound');
            // Try to play audio
            audio.play().catch(e => console.log("Audio play failed:", e));

            Swal.fire({
                title: 'Succès!',
                text: 'Rendez-vous annulé avec succès.',
                icon: 'success',
                confirmButtonColor: '#0ea5e9',
                confirmButtonText: 'OK',
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