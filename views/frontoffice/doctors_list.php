<?php
session_start();
require_once '../../controllers/ServiceController.php';
require_once '../../controllers/UserController.php';
require_once '../../controllers/ReservationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect doctors to their dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'medecin') {
    header('Location: my_appointments.php');
    exit;
}

$serviceController = new ServiceController();
$userController = new UserController();

if (!isset($_GET['service_id'])) {
    header('Location: front.php');
    exit();
}

$service_id = $_GET['service_id'];
$service = $serviceController->obtenirServiceParId($service_id);
$doctors = $userController->getDoctorsByService($service_id);

if (!$service) {
    header('Location: front.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médecins - <?php echo htmlspecialchars($service->getName()); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/doctors_list.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div style="text-align: center;">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 80px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="front.php" class="nav-link"
                style="text-decoration: none; color: #333; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Accueil</span>
            </a>
            <a href="../backoffice/dashboard.php" class="nav-link"
                style="text-decoration: none; color: #333; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-cog"></i></span>
                <span>Admin</span>
            </a>
        </nav>
    </header>

    <main class="main-content">
        <section class="doctors-section">
            <a href="front.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Retour aux services
            </a>

            <div class="section-header">
                <h1 class="section-title">Nos Médecins - <?php echo htmlspecialchars($service->getName()); ?></h1>
                <p class="section-subtitle">Découvrez les spécialistes de notre service
                    <?php echo htmlspecialchars($service->getName()); ?>
                </p>
            </div>

            <?php if (empty($doctors)): ?>
                <div class="no-services" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-user-md" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                    <p style="color: #64748b; font-size: 1.1rem;">Aucun médecin n'est disponible pour ce service pour le
                        moment.</p>
                </div>
            <?php else: ?>
                <div class="doctors-grid">
                    <?php foreach ($doctors as $doctor): ?>
                        <?php
                        // Fetch booked slots for this doctor
                        $bookedSlots = (new ReservationController())->getBookedSlots($doctor['id_utilisateur']);

                        // Define available slots from doctor's schedule
                        $slots = [];
                        for ($i = 1; $i <= 4; $i++) {
                            if (!empty($doctor["heure{$i}_debut"])) {
                                // Format time to HH:MM
                                $time = date('H:i', strtotime($doctor["heure{$i}_debut"]));
                                $slots[] = $time;
                            }
                        }
                        ?>
                        <div class="doctor-card">
                            <div class="card-header">
                                <div class="doctor-avatar">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <h3 class="doctor-name">Dr.
                                    <?php echo htmlspecialchars($doctor['nom'] . ' ' . $doctor['prenom']); ?>
                                </h3>
                                <p class="doctor-specialty"><?php echo htmlspecialchars($service->getName()); ?></p>
                            </div>

                            <div class="doctor-info">
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                    <span><?php echo htmlspecialchars($doctor['email']); ?></span>
                                </div>
                                <?php if (!empty($doctor['adresse'])): ?>
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($doctor['adresse']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Slots Section -->
                            <div class="slots-container">
                                <span class="slots-title">Disponibilités</span>
                                <div class="slots-grid">
                                    <?php if (empty($slots)): ?>
                                        <span style="font-size: 0.85rem; color: #94a3b8;">Aucun créneau</span>
                                    <?php else: ?>
                                        <?php foreach ($slots as $slot): ?>
                                            <?php
                                            // Check if slot is booked
                                            $isBooked = false;
                                            foreach ($bookedSlots as $booked) {
                                                if (substr($booked, 0, 5) === $slot) {
                                                    $isBooked = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php if ($isBooked): ?>
                                                <button disabled class="slot-btn booked">
                                                    <?php echo $slot; ?>
                                                </button>
                                            <?php else: ?>
                                                <a href="prendre_rendezvous.php?doctor_id=<?php echo $doctor['id_utilisateur']; ?>&slot_time=<?php echo $slot; ?>"
                                                    class="slot-btn available">
                                                    <?php echo $slot; ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

</body>

</html>