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

// Get appointment ID
$appointmentId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$appointmentId) {
    header("Location: afficher_rendezvous_patient.php");
    exit;
}

// Get appointment details
$appointment = $reservationController->getAppointmentById($appointmentId);

if (!$appointment) {
    header("Location: afficher_rendezvous_patient.php");
    exit;
}

// Get booked slots for the doctor to disable them
$bookedSlots = $reservationController->getBookedSlots($appointment['idMedecin']);

// Define available slots from doctor's schedule
$slots = [];
for ($i = 1; $i <= 4; $i++) {
    if (!empty($appointment["heure{$i}_debut"])) {
        // Format time to HH:MM
        $time = date('H:i', strtotime($appointment["heure{$i}_debut"]));
        $slots[] = $time;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le rendez-vous</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/doctors_list.css">
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
        <div class="modifier-container">
            <div class="page-header">
                <a href="afficher_rendezvous_patient.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <h1 style="font-size: 1.5rem; color: #1e293b;">Modifier le rendez-vous</h1>
            </div>

            <div class="current-info">
                <h3 style="margin-bottom: 0.5rem; color: #334155;">Rendez-vous actuel</h3>
                <p style="color: #64748b; margin-bottom: 0.25rem;">
                    <strong>Médecin :</strong> Dr.
                    <?php echo $appointment['medecinPrenom'] . ' ' . $appointment['medecinNom']; ?>
                </p>
                <p style="color: #64748b;">
                    <strong>Heure :</strong> <?php echo substr($appointment['heureRdv'], 0, 5); ?>
                </p>
            </div>

            <h3 style="margin-bottom: 1rem; color: #334155;">Choisir un nouveau créneau</h3>

            <div class="slots-grid">
                <?php if (empty($slots)): ?>
                    <span style="font-size: 0.85rem; color: #94a3b8;">Aucun créneau disponible</span>
                <?php else: ?>
                    <?php foreach ($slots as $slot): ?>
                        <?php
                        // Check if slot is booked
                        $isBooked = false;
                        foreach ($bookedSlots as $booked) {
                            if (substr($booked, 0, 5) === $slot && substr($appointment['heureRdv'], 0, 5) !== $slot) {
                                $isBooked = true;
                                break;
                            }
                        }

                        $isCurrent = (substr($appointment['heureRdv'], 0, 5) === $slot);
                        ?>

                        <?php if ($isCurrent): ?>
                            <button class="slot-btn current" disabled>
                                <?php echo $slot; ?> (Actuel)
                            </button>
                        <?php elseif ($isBooked): ?>
                            <button disabled class="slot-btn booked">
                                <?php echo $slot; ?>
                            </button>
                        <?php else: ?>
                            <a href="confirmer_modification.php?id=<?php echo $appointmentId; ?>&new_time=<?php echo $slot; ?>"
                                class="slot-btn available"
                                style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                                <?php echo $slot; ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>

</html>