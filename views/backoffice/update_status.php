<?php
session_start();
require_once '../../controllers/ReservationController.php';
require_once '../../controllers/UserController.php';

// Check role
$userController = new UserController();
if (!$userController->isLoggedIn() || $_SESSION['role'] !== 'medecin') {
    header('Location: ../frontoffice/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$id || !$status) {
        header('Location: afficher_rendezvous_medecin.php?error=missing_params');
        exit;
    }

    $reservationController = new ReservationController();

    // Map action to status
    // 'accept' -> 'confirme'
    // 'refuse' -> 'annule' (or delete?)

    $newStatus = null;
    if ($status === 'accept') {
        $newStatus = 'confirme';
    } elseif ($status === 'refuse') {
        // If refusing, we might want to actually DELETE or status 'annule'
        // Let's use 'annule' for history, or delete to free up slot immediately?
        // User request "ne soit confirmé que si le médecin l'accepte".
        // Refusal means slot becomes free? 'annule' usually frees slot if checked properly.
        // My getBookedSlots checks 'pris', 'confirme', 'en attente'. 'annule' is NOT in that list, so slot is free.
        $newStatus = 'annule';
    }

    if ($newStatus) {
        // Fetch details BEFORE update (or after, but we need patient info)
        $appt = $reservationController->getAppointmentById($id);

        if ($reservationController->updateStatus($id, $newStatus)) {
            // Send Email
            if ($appt) {
                require_once '../frontoffice/mailing_handler.php';
                $patientName = $appt['patientPrenom'] . ' ' . $appt['patientNom'];
                $doctorName = $appt['medecinPrenom'] . ' ' . $appt['medecinNom'];
                $toEmail = $appt['patientEmail'];
                // Clean time
                $time = substr($appt['heureRdv'], 0, 5);

                if ($newStatus === 'confirme') {
                    sendConfirmationEmail($toEmail, $patientName, $doctorName, $appt['date'], $time);
                } elseif ($newStatus === 'annule') {
                    sendCancellationEmail($toEmail, $patientName, $doctorName, $appt['date'], $time);
                }
            }
            header('Location: afficher_rendezvous_medecin.php?success=1');
            exit;
        } else {
            header('Location: afficher_rendezvous_medecin.php?error=update_failed');
            exit;
        }
    } else {
        header('Location: afficher_rendezvous_medecin.php?error=invalid_status');
        exit;
    }
}
?>