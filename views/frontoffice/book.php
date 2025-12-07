<?php
session_start();

// =============================================================================
// IMPORTATIONS
// =============================================================================
require_once '../../controllers/ReservationController.php';
require_once '../../models/Reservation.php';
require_once '../../controllers/UserController.php';

// =============================================================================
// LOGIQUE PRINCIPALE (SANS FONCTIONS)
// =============================================================================

// Initialisation des contrôleurs
$reservationController = new ReservationController();
$userController = new UserController();

// Sécurité : Uniquement POST autorisé
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: front.php');
    exit;
}

// Récupération des données POST
$doctor_id = $_POST['doctor_id'] ?? null;
$slot_time = $_POST['slot_time'] ?? null;
$date = $_POST['date'] ?? null;
$nom_value = $_POST['nom'] ?? '';
$prenom_value = $_POST['prenom'] ?? '';

// Sécurité : Redirection si les informations essentielles sont manquantes
if (!$doctor_id || !$slot_time || !$date) {
    header('Location: front.php');
    exit;
}

// 1. Validation des champs (Logique inlinée)
if (empty($nom_value) || empty($prenom_value)) {
    $error = "Veuillez remplir tous les champs.";
} elseif (!preg_match("/^[a-zA-ZÀ-ÿ '-]+$/u", $nom_value)) {
    $error = "Le nom doit contenir uniquement des lettres.";
} elseif (!preg_match("/^[a-zA-ZÀ-ÿ '-]+$/u", $prenom_value)) {
    $error = "Le prénom doit contenir uniquement des lettres.";
}

// Si erreur de validation, redirection immédiate avec message
if (isset($error)) {
    header("Location: doctor_profile.php?doctor_id=$doctor_id&booking_status=error&message=" . urlencode($error));
    exit;
}

// 2. Traitement de la réservation (Logique inlinée)
$patientId = $_SESSION['user_id'] ?? null;
$status = 'en attente';

// Création de l'objet Reservation
$reservation = new Reservation(
    null,
    $doctor_id,
    $slot_time,
    $nom_value,
    $prenom_value,
    $status,
    $patientId,
    $date
);

// Tentative d'enregistrement via le contrôleur
$result = $reservationController->bookSlot($reservation);

// 3. Vérification du résultat
if ($result && isset($result['success']) && $result['success']) {
    // Succès -> Redirection vers le profil du médecin avec paramètres de succès
    header("Location: doctor_profile.php?doctor_id=$doctor_id&booking_status=success&date=$date&time=$slot_time");
    exit;
} else {
    // Échec -> Redirection avec message d'erreur
    $msg = "Ce créneau n'est plus disponible.";
    header("Location: doctor_profile.php?doctor_id=$doctor_id&booking_status=error&message=" . urlencode($msg));
    exit;
}
?>