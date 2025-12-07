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
$appointmentId = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);
$newTime = isset($_GET['new_time']) ? $_GET['new_time'] : (isset($_POST['new_time']) ? $_POST['new_time'] : null);
$newDate = isset($_GET['new_date']) ? $_GET['new_date'] : (isset($_POST['new_date']) ? $_POST['new_date'] : null);

if (!$appointmentId || !$newTime || !$newDate) {
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
// Handle Confirmation (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation = new Reservation();
    $reservation->setHeureRdv($newTime);
    $reservation->setDate($newDate);

    if ($reservationController->updateAppointment($appointmentId, $reservation)) {
        // Success: Redirect to list
        header("Location: afficher_rendezvous_patient.php?status=modified");
        exit;
    } else {
        // Error: Redirect to list (or back to modifier) with error
        $error = "Ce créneau n'est plus disponible ou une erreur est survenue.";
        header("Location: afficher_rendezvous_patient.php?status=error&message=" . urlencode($error));
        exit;
    }
}
?>