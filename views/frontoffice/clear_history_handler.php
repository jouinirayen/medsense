<?php
session_start();
require_once '../../controllers/ReservationController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: afficher_rendezvous_patient.php');
    exit;
}

$patientId = $_SESSION['user_id'];
$controller = new ReservationController();

if ($controller->clearHistory($patientId)) {
    header('Location: afficher_rendezvous_patient.php?status=history_cleared');
} else {
    header('Location: afficher_rendezvous_patient.php?status=error&message=' . urlencode('Erreur lors de la suppression de l\'historique'));
}
?>