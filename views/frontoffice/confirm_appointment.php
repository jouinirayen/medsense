<?php
require_once '../../controllers/ServiceController.php';
require_once '../../controllers/RendezvousController.php';

$serviceName = $_GET['service'] ?? 'Ophthalmology';
$serviceDescription = $_GET['description'] ?? 'Eye care and vision health services';
$serviceIcon = $_GET['icon'] ?? 'fas fa-hand-holding-medical';
$serviceId = isset($_POST['service_id'])
    ? (int) $_POST['service_id']
    : (isset($_GET['service_id']) ? (int) $_GET['service_id'] : null);
$emailAddress = $_POST['email'] ?? ($_GET['email'] ?? '');
$submittedDate = $_POST['appointment_date'] ?? null;
$submittedTime = $_POST['appointment_time'] ?? null;
$reservationError = '';

$controller = new ServiceController();
$rendezvousController = new RendezvousController();
$serviceFromDb = null;
if ($serviceId) {
    $serviceFromDb = $controller->obtenirServiceParId($serviceId);
    if ($serviceFromDb) {
        $serviceName = $serviceFromDb['name'];
        $serviceDescription = $serviceFromDb['description'];
        if (!empty($serviceFromDb['icon'])) {
            $serviceIcon = $serviceFromDb['icon'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedDate = $submittedDate;
    $selectedTime = $submittedTime;
    $emailAddress = trim($emailAddress);

    if (isset($_POST['update_date'])) {
        // Just refresh the page with the new date selected
        // The variables $submittedDate etc are already set above
    } elseif (empty($serviceId) || empty($selectedDate) || empty($selectedTime) || empty($emailAddress)) {
        $reservationError = "Veuillez sélectionner un créneau et indiquer une adresse email.";
    } else {
        $reserved = $rendezvousController->reserverCreneau($serviceId, $selectedDate, $selectedTime, $emailAddress);
        if ($reserved) {
            $rendezvousController->envoyerConfirmationEmail(
                $emailAddress,
                $serviceName,
                date('d/m/Y', strtotime($selectedDate)),
                $selectedTime
            );
            $query = http_build_query([
                'service' => $serviceName,
                'description' => $serviceDescription,
                'date' => $selectedDate,
                'time' => $selectedTime,
                'email' => $emailAddress
            ]);
            header("Location: confirmation_success.php?$query");
            exit;
        } else {
            $reservationError = "Désolé, ce créneau n'est plus disponible ou une erreur est survenue.";
        }
    }
}

$availableSlots = $serviceId ? $rendezvousController->obtenirRendezVousDisponibles($serviceId) : [];

$slotsByDate = [];
foreach ($availableSlots as $slot) {
    $date = $slot['appointment_date'];
    $time = substr($slot['appointment_time'], 0, 5);

    if (!isset($slotsByDate[$date])) {
        $slotsByDate[$date] = [];
    }
    if (!in_array($time, $slotsByDate[$date], true)) {
        $slotsByDate[$date][] = $time;
    }
}

$defaultDateFromDb = null;
$defaultTimeFromDb = null;
foreach ($slotsByDate as $date => $times) {
    $defaultDateFromDb = $date;
    $defaultTimeFromDb = $times[0];
    break;
}

$appointmentDate = $submittedDate ?? ($_GET['date'] ?? ($defaultDateFromDb ?? date('Y-m-d')));
$appointmentTime = $submittedTime ?? ($_GET['time'] ?? ($defaultTimeFromDb ?? '09:00'));

if (isset($slotsByDate[$appointmentDate]) && !in_array($appointmentTime, $slotsByDate[$appointmentDate], true)) {
    $appointmentTime = $slotsByDate[$appointmentDate][0];
}

$hasSlots = !empty($slotsByDate);
$humanReadableDate = static function ($date) {
    return date('l, F j, Y', strtotime($date));
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Appointment</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/confirm-appointment.css">
</head>
<body>
    <main class="confirm-page">
        <header class="confirm-header">
            <a href="front.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>
            <div>
                <h1>Confirm Your Appointment</h1>
                <p>Please review your appointment details before confirming</p>
            </div>
        </header>

        <section class="details-card">
            <form method="POST" class="booking-form">
                <input type="hidden" name="service_id" value="<?php echo $serviceId; ?>">
                <input type="hidden" name="service" value="<?php echo $serviceName; ?>">
                <input type="hidden" name="description" value="<?php echo $serviceDescription; ?>">
                <input type="hidden" name="icon" value="<?php echo $serviceIcon; ?>">

            <div class="card-heading">
                <div class="card-heading-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <p class="label">Appointment Details</p>
                    <p class="hint">Make sure everything looks good before you confirm</p>
                </div>
            </div>

            <div class="detail-block">
                <div class="detail-item">
                    <div class="detail-icon service">
                        <i class="<?php echo $serviceIcon; ?>"></i>
                    </div>
                    <div>
                        <p class="detail-label">Medical Service</p>
                        <p class="detail-title"><?php echo $serviceName; ?></p>
                        <p class="detail-description"><?php echo $serviceDescription; ?></p>
                    </div>
                </div>
            </div>

            <?php if (!empty($reservationError)): ?>
            <div class="no-slots-message" style="border-color: rgba(248,113,113,0.6); color:#b91c1c;">
                <i class="fas fa-exclamation-triangle" style="color:#f97316;"></i>
                <div><?php echo $reservationError; ?></div>
            </div>
            <?php endif; ?>

            <?php if ($hasSlots): ?>
            <div class="schedule-row">
                <div class="schedule-item">
                    <div class="detail-icon date">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="schedule-field">
                        <label class="detail-label" for="appointment-date">Date</label>
                        <select id="appointment-date" class="schedule-input" name="appointment_date">
                            <?php foreach ($slotsByDate as $date => $times): ?>
                                <option value="<?php echo $date; ?>" <?php echo ($date === $appointmentDate) ? 'selected' : ''; ?>>
                                    <?php echo $humanReadableDate($date); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_date" value="1" class="btn-update-date" style="margin-top: 5px; background: none; border: none; color: #3b82f6; cursor: pointer; text-decoration: underline; font-size: 0.9em;">
                            Mettre à jour les horaires
                        </button>
                        <p class="schedule-hint">Dates disponibles pour ce service</p>
                    </div>
                </div>

                <div class="schedule-item">
                    <div class="detail-icon time">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="schedule-field">
                        <label class="detail-label" for="appointment-time">Time</label>
                        <select id="appointment-time" class="schedule-input" name="appointment_time">
                            <?php foreach ($slotsByDate[$appointmentDate] ?? [] as $time): ?>
                                <option value="<?php echo $time; ?>" <?php echo ($time === $appointmentTime) ? 'selected' : ''; ?>>
                                    <?php echo $time; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="schedule-hint">Horaires disponibles pour la date choisie</p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="no-slots-message">
                <i class="fas fa-calendar-times"></i>
                <div>
                    <p>Aucune disponibilité enregistrée pour l’instant.</p>
                    <small>Ajoutez des créneaux dans la table <code>rendezvous</code> pour activer la sélection automatique.</small>
                </div>
            </div>
            <?php endif; ?>

            <label class="email-label" for="email">Email Address <span>*</span></label>
            <div class="email-input">
                <i class="fas fa-envelope"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo $emailAddress; ?>"
                    placeholder="you@example.com"
                >
            </div>
            <p class="email-hint">Update this email if you want confirmations and reminders elsewhere</p>

            <div class="actions">
                <a href="front.php" class="btn secondary">Cancel</a>
                <button class="btn primary" type="submit" <?php echo $hasSlots ? '' : 'disabled'; ?>>
                    <i class="fas fa-check-circle"></i>
                    <span>Confirm Appointment</span>
                </button>
            </div>
            </form>
        </section>
    </main>

</body>
</html>

