<?php
session_start();

/**
 * ============================================================================
 * 1. IMPORTS & CONTROLLERS
 * ============================================================================
 */
require_once '../../../controllers/UserController.php';
require_once '../../../controllers/ServiceController.php';
require_once '../../../controllers/ReservationController.php';

$userController = new UserController();
$serviceController = new ServiceController();
$reservationController = new ReservationController();

/**
 * ============================================================================
 * 2. INPUT VALIDATION & REDIRECTS
 * ============================================================================
 */
if (!isset($_GET['doctor_id'])) {
    header('Location: ../page-accueil/front.php');
    exit();
}

$doctor_id = intval($_GET['doctor_id']);
$doctor = $userController->getUserById($doctor_id);

if (!$doctor || $doctor['role'] !== 'medecin') {
    header('Location: front.php');
    exit();
}

$service = $serviceController->obtenirServiceParId($doctor['idService']);

/**
 * ============================================================================
 * 3. DATA FETCHING (Slots & Availability)
 * ============================================================================
 */
// Get generic slots (e.g., 09:00, 10:00) defined for this doctor
$slots = [];
for ($i = 1; $i <= 4; $i++) {
    if (!empty($doctor["heure{$i}_debut"])) {
        $slots[] = date('H:i', strtotime($doctor["heure{$i}_debut"]));
    }
}

// Get already booked slots to exclude them from availability
$bookedSlots = $reservationController->getBookedSlots($doctor_id);

// Get doctor unavailabilities (days off/time off)
$unavailabilities = $reservationController->getUnavailabilities($doctor_id);

/**
 * ============================================================================
 * 4. VIEW LOGIC (Formatters & Display Variables)
 * ============================================================================
 */
// Doctor Image
// Doctor Image
if (!empty($doctor['image'])) {
    if (filter_var($doctor['image'], FILTER_VALIDATE_URL)) {
        $doctorImage = $doctor['image'];
    } else {
        $doctorImage = '../uploads/doctors/' . basename($doctor['image']);
    }
} else {
    // Default placeholder
    $doctorImage = 'https://img.freepik.com/free-photo/portrait-smiling-male-doctor_171337-1532.jpg';
    if ($doctor_id % 2 == 0) {
        $doctorImage = 'https://img.freepik.com/free-photo/pleased-young-female-doctor-wearing-medical-robe-stethoscope-around-neck-standing-with-closed-posture_409827-254.jpg';
    }
}

// Profile Info
$rating = isset($doctor['note_globale']) ? number_format((float) $doctor['note_globale'], 1) : "0.0";
$reviews = (isset($doctor['nb_avis']) ? $doctor['nb_avis'] : "0") . " avis";
$experience = !empty($doctor['experience']) ? htmlspecialchars($doctor['experience']) : "Non spécifié";
$price = !empty($doctor['prix_consultation']) ? htmlspecialchars($doctor['prix_consultation']) : "Non spécifié";
$languages = !empty($doctor['langues']) ? htmlspecialchars($doctor['langues']) : "Non spécifié";
$bio = !empty($doctor['bio']) ? htmlspecialchars($doctor['bio']) : "Ce médecin n'a pas encore ajouté de biographie.";
$address = !empty($doctor['adresse']) ? htmlspecialchars($doctor['adresse']) : null;

/**
 * ============================================================================
 * 5. CALENDAR GENERATION (Server-Side)
 * ============================================================================
 */
$weekOffset = isset($_GET['week_offset']) ? max(0, intval($_GET['week_offset'])) : 0;

$today = new DateTime();
$startDate = clone $today;
$startDate->modify("+$weekOffset weeks");

$daysToShow = 7;
$calendarHtml = '';

// Generate column for each day
for ($i = 0; $i < $daysToShow; $i++) {
    $currentDay = clone $startDate;
    $currentDay->modify("+$i days");
    $dateString = $currentDay->format('Y-m-d');

    // Header Data
    $daysFr = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    $dayName = $daysFr[$currentDay->format('w')];
    $dayNumber = $currentDay->format('d');

    // Start Column
    $calendarHtml .= '<div class="day-column">';
    $calendarHtml .= '  <div class="day-header">';
    $calendarHtml .= '      <span class="day-name">' . $dayName . '</span>';
    $calendarHtml .= '      <span class="day-date">' . $dayNumber . '</span>';
    $calendarHtml .= '  </div>';

    if (empty($slots)) {
        $calendarHtml .= '<div style="font-size: 0.8rem; color: #94a3b8;">-</div>';
    } else {
        foreach ($slots as $slotTime) {
            // Check availability
            $isBooked = false;
            $isPast = false;

            // Past check
            $slotDateTime = new DateTime("$dateString $slotTime");
            if ($slotDateTime < new DateTime()) {
                $isPast = true;
            }

            // Booking check
            foreach ($bookedSlots as $book) {
                // $book['heureRdv'] is usually "HH:MM:SS"
                if ($book['date'] === $dateString && substr($book['heureRdv'], 0, 5) === $slotTime) {
                    $isBooked = true;
                    break;
                }
            }

            // Unavailability Check
            $isUnavailable = false;
            foreach ($unavailabilities as $unavail) {
                if ($unavail['date'] === $dateString) {
                    if ($unavail['heure_debut'] === null) {
                        // Full day off
                        $isUnavailable = true;
                        break;
                    } else {
                        // Partial day off
                        $startObj = new DateTime($dateString . ' ' . $unavail['heure_debut']);
                        $endObj = new DateTime($dateString . ' ' . $unavail['heure_fin']);
                        $slotObj = new DateTime($dateString . ' ' . $slotTime);

                        // If slot is within the unavailable range
                        // Use >= start and < end assuming slot duration is handled or simple point check
                        if ($slotObj >= $startObj && $slotObj < $endObj) {
                            $isUnavailable = true;
                            break;
                        }
                    }
                }
            }

            // Render Slot
            if ($isUnavailable) {
                $calendarHtml .= '<a class="time-slot unavailable" style="background-color: #fee2e2; color: #ef4444; pointer-events: none; cursor: not-allowed;" title="Médecin absent">Absent</a>';
            } elseif ($isBooked || $isPast) {
                $calendarHtml .= '<a class="time-slot booked" style="background-color: #e2e8f0; color: #94a3b8; pointer-events: none; cursor: not-allowed;">Indisponible</a>';
            } else {
                $calendarHtml .= '<a href="#" class="time-slot" onclick="event.preventDefault(); openBookingModal(\'' . $slotTime . '\', \'' . $dateString . '\');">' . $slotTime . '</a>';
            }
        }
    }
    $calendarHtml .= '</div>';
}

// Navigation & Display Logic
$monthsFr = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
$monthDisplay = $monthsFr[$startDate->format('n') - 1] . ' ' . $startDate->format('Y');

$prevOffset = $weekOffset - 1;
$nextOffset = $weekOffset + 1;
$prevLink = ($weekOffset <= 0) ? '#' : "?doctor_id=$doctor_id&week_offset=$prevOffset#slots-section";
$nextLink = "?doctor_id=$doctor_id&week_offset=$nextOffset#slots-section";

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dr. <?php echo htmlspecialchars($doctor['nom']); ?> - Profil</title>

    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- CSS Files -->
    <link rel="stylesheet" href="../page-accueil/css/style.css">
    <link rel="stylesheet" href="css/doctor_profile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/doctors_list.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/doctor_calendar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/booking_modal.css?v=<?php echo time(); ?>">

    <!-- Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Global JS Variables -->
    <script>
        window.doctorId = <?php echo $doctor_id; ?>;
    </script>
</head>

<?php
// Ensure currentUser is available for the header
if (!isset($currentUser)) {
    $currentUser = $userController->getUserById($_SESSION['user_id']);
}

// Define navigation paths for the header
$navPaths = [
    'accueil' => '../page-accueil/front.php',
    'rendezvous' => '/projet2025/views/frontoffice/page-rendezvous/afficher_rendezvous_patient.php',
    'blog' => '/projet2025/views/frontoffice/page-blog/blog.php',
    'reclamation' => '/projet2025/views/frontoffice/page-reclamation/reclamation.php',
    'admin' => '/projet2025/views/backoffice/dashboard_service/dashboard.php',
    'logout' => '../logout.php'
];
?>

<body>
    <!-- Header included from partials -->
    <?php include '../page-accueil/partials/header.php'; ?>

    <main class="profile-container">

        <!-- 1. Doctor Profile Header -->
        <div class="profile-header-card">
            <div class="doctor-main-info">
                <div class="profile-rating-badge">
                    <span class="star-icon"><i class="fas fa-star"></i></span>
                    <span><?php echo $rating; ?></span>
                    <span
                        style="color: #94a3b8; font-weight: 400; font-size: 0.9em; margin-left: 5px;">(<?php echo $reviews; ?>)</span>
                </div>

                <h1 class="doctor-name-large">Dr
                    <?php echo htmlspecialchars($doctor['prenom'] . ' ' . $doctor['nom']); ?>
                </h1>

                <span class="doctor-specialty-badge">
                    <?php echo htmlspecialchars($service['name']); ?> <i class="fas fa-stethoscope"></i>
                </span>

                <p class="doctor-bio">
                    <?php echo $bio; ?>
                </p>

                <div class="actions-row">
                    <a href="#slots-section" class="btn-primary-action">
                        Prendre rendez-vous <i class="far fa-calendar-alt"></i>
                    </a>
                </div>
            </div>

            <div class="doctor-image-container">
                <img src="<?php echo $doctorImage; ?>" alt="Dr. Profile" class="doctor-profile-image">
                <div class="verified-badge">
                    Compte vérifié <i class="fas fa-check-circle verified-icon"></i>
                </div>
            </div>
        </div>

        <!-- Reviews Marquee Section -->
        <?php
        $patientReviews = $reservationController->getDoctorReviews($doctor_id);
        if (!empty($patientReviews)):
            ?>
            <style>
                .reviews-marquee-container {
                    width: 100%;
                    overflow: hidden;
                    background: transparent;
                    padding: 2rem 0;
                    margin-bottom: 3rem;
                    position: relative;
                }
                .reviews-track {
                    display: flex;
                    gap: 1.5rem;
                    width: max-content;
                    animation: scroll 40s linear infinite;
                    padding: 10px; 
                }
                .reviews-track:hover {
                    animation-play-state: paused;
                }
                .review-card-mini {
                    background: white;
                    border: none;
                    border-radius: 16px;
                    padding: 1.5rem;
                    width: 320px;
                    flex-shrink: 0;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    position: relative;
                }
                .review-card-mini:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                }
                .review-mini-header {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    margin-bottom: 1rem;
                }
                .review-avatar {
                    width: 40px;
                    height: 40px;
                    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                    color: white;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 700;
                    font-size: 1rem;
                }
                .review-info {
                    display: flex;
                    flex-direction: column;
                }
                .review-author {
                    font-weight: 700;
                    color: #1e293b;
                    font-size: 0.95rem;
                }
                .review-date {
                    font-size: 0.75rem;
                    color: #94a3b8;
                }
                .review-stars-static {
                    color: #fbbf24;
                    font-size: 0.9rem;
                    margin-bottom: 0.8rem;
                }
                .review-mini-body {
                    color: #334155;
                    font-size: 0.95rem;
                    line-height: 1.6;
                    font-style: normal;
                    position: relative;
                }
                .quote-icon {
                    position: absolute;
                    top: 1rem;
                    right: 1.5rem;
                    font-size: 3rem;
                    color: #f1f5f9;
                    font-family: serif;
                    line-height: 1;
                }
                @keyframes scroll {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
            </style>

            <div class="reviews-marquee-container">
                <h3 style="margin: 0 0 1.5rem 0.5rem; font-size: 1.4rem; color: #0f172a; font-weight: 700; display:flex; align-items:center; gap:10px;">
                    <i class="far fa-comments" style="color:#3b82f6;"></i> Ce que disent nos patients
                </h3>
                <div class="reviews-track">
                    <?php
                    // Duplicate reviews to create seamless loop
                    $displayReviews = array_merge($patientReviews, $patientReviews);
                    foreach ($displayReviews as $rev):
                        $initial = strtoupper(substr($rev['prenom'], 0, 1));
                    ?>
                    <div class="review-card-mini">
                        <div class="quote-icon">"</div>
                        <div class="review-mini-header">
                            <div class="review-avatar"><?= $initial ?></div>
                            <div class="review-info">
                                <span class="review-author"><?= htmlspecialchars($rev['prenom'] . ' ' . $rev['nom']) ?></span>
                                <span class="review-date"><?= date('d/m/Y', strtotime($rev['date'])) ?></span>
                            </div>
                        </div>
                        <div class="review-stars-static">
                            <?php for($i=0; $i<5; $i++): ?>
                                <i class="<?= $i < $rev['note'] ? 'fas' : 'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="review-mini-body"><?= htmlspecialchars($rev['commentaire']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 2. Info Grid (Languages, Price, Experience) -->
        <div class="info-grid">
            <div class="info-card">
                <div class="info-card-icon icon-yellow"><i class="far fa-comment-alt"></i></div>
                <div class="info-label">Langues</div>
                <div class="info-value"><?php echo $languages; ?></div>
            </div>

            <div class="info-card">
                <div class="info-card-icon icon-green"><i class="fas fa-dollar-sign"></i></div>
                <div class="info-label">Prix de la consultation</div>
                <div class="info-value"><?php echo $price; ?></div>
            </div>

            <div class="info-card">
                <div class="info-card-icon icon-purple"><i class="fas fa-graduation-cap"></i></div>
                <div class="info-label">Expérience</div>
                <div class="info-value"><?php echo $experience; ?></div>
            </div>
        </div>

        <!-- 3. Map & Calendar Section -->
        <div class="info-grid" style="grid-template-columns: 350px 1fr;">

            <!-- Address Card -->
            <div class="info-card" style="align-items: flex-start; text-align: left;">
                <div class="info-label"><i class="fas fa-map-marker-alt"></i> Adresse de la clinique</div>

                <?php if ($address): ?>
                    <p style="color: #64748b; font-size: 0.9em; margin-bottom: 1rem;"><?php echo $address; ?></p>
                    <div style="width: 100%; height: 200px; border-radius: 12px; overflow: hidden;">
                        <iframe width="100%" height="100%" id="gmap_canvas"
                            src="https://maps.google.com/maps?q=<?php echo urlencode($address); ?>&t=&z=15&ie=UTF8&iwloc=&output=embed"
                            frameborder="0" scrolling="no" marginheight="0" marginwidth="0">
                        </iframe>
                    </div>
                <?php else: ?>
                    <p style="color: #64748b; font-size: 0.9em; margin-bottom: 2rem;">L'adresse est actuellement
                        indisponible.</p>
                    <div
                        style="background: #f1f5f9; width: 100%; height: 150px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 0.8rem;">
                        Carte indisponible
                    </div>
                <?php endif; ?>
            </div>

            <!-- Calendar Wrapper -->
            <div id="slots-section" class="slots-overlay"
                style="background: transparent; border: none; padding: 0; text-align: left;">
                <div class="calendar-wrapper">
                    <!-- Nav Header -->
                    <div class="calendar-header">
                        <div class="calendar-title">
                            <i class="far fa-calendar-alt"></i> Disponibilités
                            <span id="calendar-month-display"
                                style="font-weight: 400; color: #64748b; margin-left:10px; font-size: 0.9em;">
                                <?php echo $monthDisplay; ?>
                            </span>
                        </div>
                        <div class="calendar-nav">
                            <?php if ($weekOffset <= 0): ?>
                                <button class="nav-btn" disabled><i class="fas fa-chevron-left"></i></button>
                            <?php else: ?>
                                <a href="<?php echo $prevLink; ?>" class="nav-btn"
                                    style="text-decoration:none; display:flex; align-items:center; justify-content:center; color:inherit;">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <a href="<?php echo $nextLink; ?>" class="nav-btn"
                                style="text-decoration:none; display:flex; align-items:center; justify-content:center; color:inherit;">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Slots Grid -->
                    <div id="php-calendar-days" class="calendar-days">
                        <?php echo $calendarHtml; ?>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Modal Script -->
    <script src="js/booking_modal.js?v=<?php echo time(); ?>"></script>

    <!-- Feedback Scripts -->
    <?php if (isset($_GET['booking_status'])): ?>
        <script>
            const status = "<?php echo htmlspecialchars($_GET['booking_status']); ?>";
            const message = "<?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : ''; ?>";
            const slotTime = "<?php echo isset($_GET['time']) ? htmlspecialchars($_GET['time']) : ''; ?>";

            if (status === 'success') {
                const audio = new Audio('../son/suc.mp3');
                audio.play().catch(e => console.log(e));

                Swal.fire({
                    title: 'Demande envoyée !',
                    text: 'Votre demande de rendez-vous pour ' + slotTime + ' est en attente de validation.',
                    icon: 'info',
                    confirmButtonText: 'Voir mes rendez-vous',
                    confirmButtonColor: '#0ea5e9',
                    showCancelButton: true,
                    cancelButtonText: 'Rester ici',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/projet2025/views/frontoffice/page-rendezvous/afficher_rendezvous_patient.php';
                    }
                });
            } else if (status === 'error') {
                Swal.fire({
                    title: 'Erreur',
                    text: message || "Une erreur est survenue lors de la réservation.",
                    icon: 'error',
                    confirmButtonColor: '#0ea5e9'
                });
            }

            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname + window.location.search.split('&booking_status')[0]);
        </script>
    <?php endif; ?>
</body>

</html>