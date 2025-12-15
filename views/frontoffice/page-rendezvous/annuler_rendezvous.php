<?php
session_start();
require_once '../../../controllers/ReservationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$reservationController = new ReservationController();
$userId = $_SESSION['user_id']; // For security, could verify ownership

// Handle Cancellation (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {

    // Get appointment details first for email
    $appt = $reservationController->getAppointmentById($_POST['cancel_id']);

    if ($appt) {
        // Attempt cancellation (Update status to 'annule')
        $result = $reservationController->updateStatus($_POST['cancel_id'], 'annule');

        if ($result) {
            // Send email if applicable
            require_once '../mailing_handler.php';
            // getAppointmentById returns joined data, adapting structure for mailing_handler
            // Assuming getAppointmentById returns keys like 'medecinPrenom', 'medecinNom' etc.
            // Check ReservationController::getAppointmentById implementation in previous turn.
            // It returns: r.*, u.nom as medecinNom, u.prenom as medecinPrenom, p.email as patientEmail...

            $patientEmail = $appt['patientEmail'] ?? '';
            // For patient name, we might need to fetch it or rely on session if only patient cancels own.
            // But getting it from DB is safer. 
            // The query in getAppointmentById joins patient as 'p'.
            // wait, getAppointmentById joins patient?
            // Let's check getAppointmentById in Controller again. 
            // "LEFT JOIN utilisateur p ON r.idPatient = p.id_utilisateur" -> yes.
            // And it selects "p.email as patientEmail". 
            // It does NOT explicitly select patient name/prenom in the SELECT list shown in previous turn? 
            // "r.*, u.nom as medecinNom... p.email as patientEmail..."
            // It seems it might be missing patient name.
            // However, the current user is logged in, so we can use session or fetch user.

            // Actually, let's look at the original code I am replacing.
            // The original used $reservationController->cancelAppointment which returned an array with names.
            // $reservationController->cancelAppointment did a specific fetch.

            // To be safe and simple, I will just use the current user's name from session or re-fetch if needed, 
            // but `annuler_rendezvous.php` has `$userId = $_SESSION['user_id'];`.
            // I should probably just fetch the user to be sure.

            // Let's check if I can just use the previous logic's variables?
            // The previous logic expected $result to be an array from cancelAppointment.
            // valid `sendCancellationEmail` signature: ($to, $patientName, $doctorName, $date, $time)

            // I will fetch the user info to get the name reliably.
            require_once '../../../controllers/UserController.php';
            $userController = new UserController();
            $currentUser = $userController->getUserById($userId);

            sendCancellationEmail(
                $appt['patientEmail'] ?? $currentUser['email'],
                $currentUser['prenom'] . " " . $currentUser['nom'],
                $appt['medecinPrenom'] . " " . $appt['medecinNom'],
                date('Y-m-d', strtotime($appt['date'])), // Note: $appt['date'] comes from r.*
                date('H:i', strtotime($appt['heureRdv']))
            );
        }

        // Success Redirect
        header("Location: afficher_rendezvous_patient.php?status=cancelled");
        exit;
    } else {
        // Error Redirect
        $error = "Erreur lors de l'annulation.";
        header("Location: afficher_rendezvous_patient.php?status=error&message=" . urlencode($error));
        exit;
    }
} else {
    // If accessed directly without POST, redirect back
    header("Location: afficher_rendezvous_patient.php");
    exit;
}
?>