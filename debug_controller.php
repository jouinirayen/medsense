<?php
require_once 'controllers/ReservationController.php';
require_once 'models/Reservation.php';

// Simulate the request data
$doctorId = 7;
$time = '11:00';
$nom = 'TestNom';
$prenom = 'TestPrenom';
$patientId = null; // Or a valid ID if needed

$controller = new ReservationController();
$reservation = new Reservation(null, $doctorId, $time, $nom, $prenom, 'pris', $patientId);

echo "Attempting to book slot via Controller...\n";
$result = $controller->bookSlot($reservation);

if ($result) {
    echo "Booking SUCCESSFUL.\n";
} else {
    echo "Booking FAILED.\n";
}

echo "Check debug_log.txt for details.\n";
?>