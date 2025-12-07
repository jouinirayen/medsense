<?php
session_start();
require_once '../../config/config.php';
require_once '../../controllers/ReservationController.php';

// Check auth
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointment_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $rating = intval($rating);

    if (!$appointmentId || !$rating || $rating < 1 || $rating > 5) {
        header("Location: afficher_rendezvous_patient.php?status=error&message=" . urlencode("Données invalides"));
        exit;
    }

    $reservationController = new ReservationController();
    $result = $reservationController->rateAppointment($appointmentId, $rating, $_SESSION['user_id']);

    if ($result['success']) {
        header("Location: afficher_rendezvous_patient.php?status=rated");
        exit;
    } else {
        header("Location: afficher_rendezvous_patient.php?status=error&message=" . urlencode($result['error']));
        exit;
    }
}
?>