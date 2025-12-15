<?php
session_start();
require_once '../../../controllers/ServiceController.php';
require_once '../../../controllers/UserController.php';
require_once '../../../controllers/ReservationController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Redirect doctors to their dashboard


$serviceController = new ServiceController();
$userController = new UserController();
$currentUser = $userController->getUserById($_SESSION['user_id']);

if (!isset($_GET['service_id'])) {
    header('Location: ../page-accueil/front.php');
    exit();
}

$service_id = $_GET['service_id'];
$service = $serviceController->obtenirServiceParId($service_id);
$doctors = $userController->getDoctorsByService($service_id);

if (!$service) {
    header('Location: ../page-accueil/front.php');
    exit();
}

// Define extra CSS for this page
$extraCss = ['css/doctors_list.css'];

// Define navigation paths for header (since we're in rendezvous_avec_docteur/)
$navPaths = [
    'accueil' => '../page-accueil/front.php',
    'rendezvous' => '/projet_unifie/views/frontoffice/page-rendezvous/afficher_rendezvous_patient.php',
    'blog' => '/projet_unifie/views/frontoffice/page-blog/blog.php',
    'reclamation' => '/projet_unifie/views/frontoffice/page-reclamation/reclamation.php',
    'admin' => '/projet_unifie/views/backoffice/dashboard_service/dashboard.php',
    'logout' => '../logout.php'
];
?>
<?php include '../page-accueil/partials/header.php'; ?>

<main class="main-content">
    <section class="doctors-section">
        <a href="../page-accueil/front.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour aux services
        </a>

        <div class="section-header">
            <h1 class="section-title">Nos Médecins - <?php echo htmlspecialchars($service['name']); ?></h1>
            <p class="section-subtitle">Découvrez les spécialistes de notre service
                <?php echo htmlspecialchars($service['name']); ?>
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

                    // Image Handling
                    if (!empty($doctor['image'])) {
                        if (filter_var($doctor['image'], FILTER_VALIDATE_URL)) {
                            $doctorImage = $doctor['image'];
                        } else {
                            // Ensure we point to uploads directory relative to this view
                            $doctorImage = '../../uploads/doctors/' . basename($doctor['image']);
                        }
                    } else {
                        // Dynamic placeholder based on name
                        $doctorImage = "https://ui-avatars.com/api/?name=" . urlencode($doctor['nom'] . ' ' . $doctor['prenom']) . "&background=random&color=fff&size=128";
                    }
                    ?>
                    <div class="doctor-card" id="card-<?php echo $doctor['id_utilisateur']; ?>">
                        <div class="card-header">
                            <img src="<?php echo $doctorImage; ?>" alt="Dr. <?php echo htmlspecialchars($doctor['nom']); ?>"
                                class="doctor-image">
                            <div class="rating-badge">
                                <span class="star-icon"><i class="fas fa-star"></i></span>
                                <span><?php echo isset($doctor['note_globale']) ? number_format((float) $doctor['note_globale'], 1) : "0.0"; ?></span>
                            </div>
                        </div>

                        <div class="card-body">
                            <span class="specialty-badge">
                                <?php echo htmlspecialchars($service['name']); ?> <i class="fas fa-stethoscope"></i>
                            </span>

                            <h3 class="doctor-name">
                                <?php echo htmlspecialchars($doctor['nom'] . ' ' . $doctor['prenom']); ?> Dr.
                            </h3>

                            <div class="availability-text">
                                Disponible à la réservation dès aujourd'hui <i class="far fa-clock"></i>
                            </div>

                            <a href="doctor_profile.php?doctor_id=<?php echo $doctor['id_utilisateur']; ?>" class="btn-book"
                                style="display: block; text-decoration: none; text-align: center;">
                                Prendre rendez-vous
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include '../page-accueil/partials/footer.php'; ?>