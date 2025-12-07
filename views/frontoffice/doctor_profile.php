<?php
session_start();

/**
 * ============================================================================
 * 1. IMPORTS & CONTROLLERS
 * ============================================================================
 */
require_once '../../controllers/UserController.php';
require_once '../../controllers/ServiceController.php';
require_once '../../controllers/ReservationController.php';

$userController = new UserController();
$serviceController = new ServiceController();
$reservationController = new ReservationController();

/**
 * ============================================================================
 * 2. INPUT VALIDATION & REDIRECTS
 * ============================================================================
 */
if (!isset($_GET['doctor_id'])) {
    header('Location: front.php');
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

/**
 * ============================================================================
 * 4. VIEW LOGIC (Formatters & Display Variables)
 * ============================================================================
 */
// Doctor Image
$doctorImage = !empty($doctor['image']) ? $doctor['image'] : 'https://img.freepik.com/free-photo/portrait-smiling-male-doctor_171337-1532.jpg';
if (empty($doctor['image']) && $doctor_id % 2 == 0) {
    $doctorImage = 'https://img.freepik.com/free-photo/pleased-young-female-doctor-wearing-medical-robe-stethoscope-around-neck-standing-with-closed-posture_409827-254.jpg';
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

            // Render Slot
            if ($isBooked || $isPast) {
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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/doctor_profile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/doctors_list.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/doctor_calendar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/booking_modal.css?v=<?php echo time(); ?>">

    <!-- Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Global JS Variables -->
    <script>
        window.doctorId = <?php echo $doctor_id; ?>;
    </script>
</head>

<body>
    <!-- Header -->
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
            <a href="javascript:history.back()" class="nav-link"
                style="text-decoration: none; color: #333; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
        </nav>
    </header>

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
    <script src="../js/booking_modal.js?v=<?php echo time(); ?>"></script>

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
                        window.location.href = 'afficher_rendezvous_patient.php';
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