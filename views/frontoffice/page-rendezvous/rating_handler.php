<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../controllers/ReservationController.php';
require_once '../../../controllers/ChatController.php';
require_once '../../../controllers/MailerService.php';

// Check auth
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointment_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $rating = intval($rating);
    $comment = $_POST['comment'] ?? '';

    if (!$appointmentId || !$rating || $rating < 1 || $rating > 5) {
        header("Location: afficher_rendezvous_patient.php?status=error&message=" . urlencode("Données invalides"));
        exit;
    }

    $reservationController = new ReservationController();

    // Perform Rating
    $result = $reservationController->rateAppointment($appointmentId, $rating, $_SESSION['user_id'], $comment);

    if ($result['success']) {
        // --- AUTOMATION START ---

        // 1. Get Details for AI & Email (Patient Email, Doctor Name)
        $pdo = (new config())->getConnexion();
        $stmtDetails = $pdo->prepare("
    SELECT
    u_patient.email as patientEmail,
    u_patient.prenom as patientPrenom,
    u_patient.nom as patientNom,
    u_doctor.prenom as doctorPrenom,
    u_doctor.nom as doctorNom
    FROM rendezvous r
    JOIN utilisateur u_patient ON r.idPatient = u_patient.id_utilisateur
    JOIN utilisateur u_doctor ON r.idMedecin = u_doctor.id_utilisateur
    WHERE r.idRDV = ?
    ");
        $stmtDetails->execute([$appointmentId]);
        $details = $stmtDetails->fetch(PDO::FETCH_ASSOC);

        if ($details) {
            $patientName = $details['patientPrenom'] . ' ' . $details['patientNom'];
            $doctorName = $details['doctorPrenom'] . ' ' . $details['doctorNom'];
            $patientEmail = $details['patientEmail'];

            // 2. Generate AI Response
            $chatController = new ChatController();
            $aiResponse = $chatController->generateReviewResponse($patientName, $rating, $comment, $doctorName);

            // 3. Update Database with AI Response
            $stmtUpdate = $pdo->prepare("UPDATE rendezvous SET reponse_medecin = ? WHERE idRDV = ?");
            $stmtUpdate->execute([$aiResponse, $appointmentId]);

            // 4. Send Email
            $mailer = new MailerService();
            $mailer->sendReviewResponseEmail($patientEmail, $patientName, $doctorName, $aiResponse);
        }

        // --- AUTOMATION END ---

        header("Location: afficher_rendezvous_patient.php?status=rated");
        exit;
    } else {
        header("Location: afficher_rendezvous_patient.php?status=error&message=" . urlencode($result['error']));
        exit;
    }
}
?>