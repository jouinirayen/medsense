<?php
session_start();

/**
 * ============================================================================
 * 1. IMPORTS & CONTROLLERS
 * ============================================================================
 */
require_once '../../controllers/ReservationController.php';
require_once '../../controllers/UserController.php';

/**
 * ============================================================================
 * 2. AUTHENTICATION CHECK
 * ============================================================================
 */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$reservationController = new ReservationController();
$userController = new UserController();

/**
 * ============================================================================
 * 3. DATA FETCHING
 * ============================================================================
 */
// Get User Info
$currentUser = $userController->getUserById($userId);

// Get Appointments
$appointments = $reservationController->getAppointmentsByPatient($userId);

/**
 * ============================================================================
 * 4. MODAL LOGIC (Modification Handling)
 * ============================================================================
 */
$showModifyModal = false;
$modalContent = '';

if (isset($_GET['modify_id'])) {
    $modifyId = $_GET['modify_id'];
    $modifyAppt = $reservationController->getAppointmentById($modifyId);

    if ($modifyAppt && $modifyAppt['idPatient'] == $userId) {
        $showModifyModal = true;
        // Data for modal
        $bookedSlots = $reservationController->getBookedSlots($modifyAppt['idMedecin']);
        $selectedDate = isset($_GET['date']) ? $_GET['date'] : $modifyAppt['date'];

        // Generate Doctor Slots
        $slots = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($modifyAppt["heure{$i}_debut"])) {
                $slots[] = date('H:i', strtotime($modifyAppt["heure{$i}_debut"]));
            }
        }

        // Build Modal HTML
        $modalContent = '<div class="date-picker-container" style="margin-bottom: 20px; text-align: left;">
            <label style="display: block; margin-bottom: 8px; color: #64748b; font-size: 0.9rem;">Sélectionner une date</label>
            <input type="date" class="date-picker-input" value="' . $selectedDate . '" min="' . date('Y-m-d') . '" 
                   onchange="window.location.href=\'?modify_id=' . $modifyId . '&date=\' + this.value"
                   style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
          </div>
          <div class="slots-container" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">';

        if (empty($slots)) {
            $modalContent .= '<div class="no-slots" style="grid-column: span 2; padding: 20px; background: #f8fafc; text-align: center; color: #64748b;">Aucun créneau disponible</div>';
        } else {
            foreach ($slots as $slot) {
                // Availability Logic
                $isBooked = false;
                foreach ($bookedSlots as $booked) {
                    $bookedTime = is_array($booked) ? $booked['heureRdv'] : $booked;
                    $bookedDate = is_array($booked) ? $booked['date'] : $modifyAppt['date'];

                    if ($bookedDate === $selectedDate && substr($bookedTime, 0, 5) === $slot) {
                        if (!($modifyAppt['date'] === $selectedDate && substr($modifyAppt['heureRdv'], 0, 5) === $slot)) {
                            $isBooked = true;
                            break;
                        }
                    }
                }

                $isCurrent = ($modifyAppt['date'] === $selectedDate && substr($modifyAppt['heureRdv'], 0, 5) === $slot);

                // Render Buttons
                if ($isCurrent) {
                    $modalContent .= '<button class="slot-btn-modern slot-btn-current" disabled style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 10px; border-radius: 8px; cursor: default;">' . $slot . ' (Actuel)</button>';
                } elseif ($isBooked) {
                    $modalContent .= '<button class="slot-btn-modern slot-btn-booked" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; padding: 10px; border-radius: 8px;">' . $slot . '</button>';
                } else {
                    $modalContent .= '<form action="confirmer_modification.php" method="POST" style="display: contents;">
                        <input type="hidden" name="id" value="' . $modifyId . '">
                        <input type="hidden" name="new_time" value="' . $slot . '">
                        <input type="hidden" name="new_date" value="' . $selectedDate . '">
                        <button type="submit" class="slot-btn-modern slot-btn-available" style="background: white; border: 1px solid #e2e8f0; color: #334155; padding: 10px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">' . $slot . '</button>
                    </form>';
                }
            }
        }
        $modalContent .= '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Rendez-vous</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- CSS Files -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/appointments.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/modifier_rendezvous.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/patient_appointments.css?v=<?php echo time(); ?>">

    <!-- Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div style="text-align: center;">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 80px; width: auto;">
        </div>

        <div class="user-info-bar">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['prenom'] . '+' . $currentUser['nom']); ?>&background=0ea5e9&color=fff"
                alt="Avatar" class="user-info-avatar">
            <div class="user-info-text">
                <span class="user-greeting">Bonjour
                    <?php echo htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']); ?></span>
                <span class="user-status">Bienvenue sur votre espace personnel</span>
            </div>
        </div>

        <nav class="nav-links">
            <a href="front.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Accueil</span>
            </a>

            <a href="afficher_rendezvous_patient.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                <span>Mes Rendez-vous</span>
            </a>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../backoffice/dashboard.php" class="nav-link">
                    <span class="nav-icon"><i class="fas fa-cog"></i></span>
                    <span>Admin</span>
                </a>
            <?php endif; ?>

            <a href="logout.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
            </a>
        </nav>
    </header>

    <main class="main-container">

        <!-- Page Title & Actions -->
        <div class="page-header">
            <div class="page-title">
                <h1>Mes Rendez-vous</h1>
                <p>Gérez vos consultations médicales</p>
            </div>
            <div class="user-badge">
                <div class="user-avatar-small">
                    <i class="fas fa-user"></i>
                </div>
                <span><?php echo htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']); ?></span>
            </div>
        </div>

        <div class="header-actions">
            <?php
            $hasHistory = false;
            foreach ($appointments as $appt) {
                if (in_array($appt['statut'], ['termine', 'annule', 'refuse'])) {
                    $hasHistory = true;
                    break;
                }
            }
            ?>
            <?php if ($hasHistory): ?>
                <button onclick="confirmClearHistory()" class="btn-clear-history">
                    <i class="fas fa-trash-alt"></i> Effacer l'historique
                </button>
            <?php endif; ?>
        </div>

        <!-- Appointments List -->
        <?php if (empty($appointments)): ?>
            <div class="empty-state">
                <div style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1.5rem;">
                    <i class="far fa-calendar-plus"></i>
                </div>
                <h3 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 0.5rem;">Aucun rendez-vous planifié</h3>
                <p style="color: #64748b;">Vous n'avez pas encore de consultation à venir.</p>
                <a href="front.php" class="btn-primary">Prendre un rendez-vous</a>
            </div>
        <?php else: ?>
            <div class="appointments-grid">
                <?php foreach ($appointments as $appt): ?>
                    <div class="appointment-card">

                        <!-- Card Header -->
                        <div class="card-header">
                            <div class="doctor-profile">
                                <div class="doctor-avatar">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div class="doctor-info">
                                    <h3>Dr.
                                        <?php echo htmlspecialchars(($appt['medecinPrenom'] ?? '') . ' ' . ($appt['medecinNom'] ?? '')); ?>
                                    </h3>
                                    <p class="specialty">
                                        <?php echo ucfirst(htmlspecialchars($appt['serviceNom'] ?? 'Médecine Générale')); ?>
                                    </p>
                                </div>
                            </div>
                            <span
                                class="status-badge status-<?php echo htmlspecialchars(str_replace(' ', '-', $appt['statut'])); ?>">
                                <?php
                                $statusText = $appt['statut'];
                                if ($statusText === 'pris' || $statusText === 'confirme')
                                    $statusText = 'Confirmé';
                                elseif ($statusText === 'termine')
                                    $statusText = 'Terminé';
                                elseif ($statusText === 'annule')
                                    $statusText = 'Annulé';
                                elseif ($statusText === 'en attente')
                                    $statusText = 'En attente';
                                echo htmlspecialchars($statusText);
                                ?>
                            </span>
                        </div>

                        <!-- Card Body (Time) -->
                        <div class="card-body">
                            <div style="font-size: 1.5rem; color: var(--primary);">
                                <i class="far fa-clock"></i>
                            </div>
                            <div class="time-display">
                                <span class="time-label">Heure du rendez-vous</span>
                                <span class="time-value"><?php echo htmlspecialchars(substr($appt['heureRdv'], 0, 5)); ?></span>
                                <div style="font-size: 0.9em; color: #64748b; margin-top: 2px;">
                                    <?php echo htmlspecialchars(date('d/m/Y', strtotime($appt['date']))); ?>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <?php if ($appt['statut'] === 'confirme'): ?>
                            <div
                                style="text-align: center; margin: 1rem 0; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.5rem; font-weight: 500;">
                                    <i class="fas fa-qrcode"></i> À présenter au médecin
                                </p>
                                <?php
                                $qrToken = hash('sha256', $appt['idRDV'] . 'MedsenseSecret');
                                $qrUrl = "http://192.168.1.106/projet2025/views/backoffice/scan_handler.php?id=" . $appt['idRDV'] . "&token=" . $qrToken;
                                ?>
                                <div class="qr-code-display" id="qrcode-<?php echo $appt['idRDV']; ?>"
                                    data-url="<?php echo htmlspecialchars($qrUrl); ?>"
                                    style="display: inline-block; padding: 5px; background: white; border: 1px solid #e2e8f0; border-radius: 8px;">
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Countdown & Progress Bar -->
                        <?php
                        $apptDateTime = strtotime($appt['date'] . ' ' . $appt['heureRdv']);
                        $now = time();
                        $diff = $apptDateTime - $now;
                        $maxWindow = 7 * 24 * 60 * 60;

                        $percent = ($diff > $maxWindow) ? 100 : (($diff > 0) ? ($diff / $maxWindow) * 100 : 0);

                        $barColor = '#0ea5e9'; // Blue
                        if ($diff < 24 * 60 * 60 && $diff > 0)
                            $barColor = '#ef4444'; // Red
                        elseif ($diff < 3 * 24 * 60 * 60 && $diff > 0)
                            $barColor = '#eab308'; // Yellow
                        ?>

                        <?php if ($appt['statut'] === 'confirme' || $appt['statut'] === 'pris'): ?>
                            <div style="padding: 0 1.5rem 1rem 1.5rem;">
                                <div
                                    style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #64748b; margin-bottom: 5px;">
                                    <span>Temps restant</span>
                                    <span class="countdown-timer" data-time="<?php echo $apptDateTime; ?>">
                                        <?php echo ($diff > 0) ? ceil($diff / (24 * 60 * 60)) . ' jours' : 'Passé'; ?>
                                    </span>
                                </div>
                                <div
                                    style="width: 100%; height: 8px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                    <div
                                        style="width: <?php echo $percent; ?>%; height: 100%; background-color: <?php echo $barColor; ?>; transition: width 0.3s ease;">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Card Footer (Actions) -->
                        <?php if ($appt['statut'] !== 'annule'): ?>
                            <div class="card-footer" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">

                                <!-- Rating Button -->
                                <?php if ($appt['statut'] === 'termine'): ?>
                                    <?php if (empty($appt['note'])): ?>
                                        <a href="#" onclick="openRatingModal(<?php echo $appt['idRDV']; ?>); return false;" class="rate-btn"
                                            style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background-color: #f59e0b; color: white; flex: 1; padding: 0.5rem; border-radius: 6px; font-weight: 500;">
                                            <i class="fas fa-star"></i> Noter le médecin
                                        </a>
                                    <?php else: ?>
                                        <div style="flex: 1; text-align: center; color: #f59e0b; font-weight: 600;">
                                            <i class="fas fa-star"></i> Votre note : <?php echo htmlspecialchars($appt['note']); ?>/5
                                        </div>
                                    <?php endif; ?>

                                    <!-- Modify/Cancel Buttons -->
                                <?php elseif ($appt['statut'] !== 'annule' && $appt['statut'] !== 'termine'): ?>
                                    <?php if ($appt['statut'] !== 'en attente'): ?>
                                        <a href="?modify_id=<?php echo $appt['idRDV']; ?>" class="cancel-btn"
                                            style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background-color: #3b82f6; color: white; flex: 1;">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                    <?php endif; ?>
                                    <a href="#" onclick="confirmCancellation(<?php echo $appt['idRDV']; ?>); return false;"
                                        class="cancel-btn"
                                        style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; flex: 1;">
                                        <i class="far fa-times-circle"></i> Annuler
                                    </a>
                                <?php endif; ?>

                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- 
    ========================================================================
       JAVASCRIPT SECTION
    ========================================================================
    -->
    <script>
        // -----------------------
        // 1. Cancellation Logic
        // -----------------------
        function confirmCancellation(id) {
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#3b82f6',
                confirmButtonText: 'Oui, annuler',
                cancelButtonText: 'Retour'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'annuler_rendezvous.php';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'cancel_id';
                    input.value = id;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            })
        }

        // -----------------------
        // 2. Clear History Logic
        // -----------------------
        function confirmClearHistory() {
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Voulez-vous vraiment supprimer tout l'historique de vos rendez-vous terminés ou annulés ? Cette action est irréversible.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, effacer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'clear_history_handler.php';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // -----------------------
        // 3. Main Init & Updates
        // -----------------------
        document.addEventListener('DOMContentLoaded', function () {

            // A. Check for Cancel Status in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'cancelled') {
                new Audio('../son/suc.mp3').play().catch(e => console.log(e));
                Swal.fire('Annulé!', 'Votre rendez-vous a été annulé avec succès.', 'success');
                window.history.replaceState({}, document.title, window.location.pathname);
            } else if (urlParams.get('status') === 'history_cleared') {
                new Audio('../son/suc.mp3').play().catch(e => console.log(e));
                Swal.fire('Effacé!', 'Votre historique a été nettoyé.', 'success');
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // B. QR Code Generation
            document.querySelectorAll('.qr-code-display').forEach(function (container) {
                var url = container.getAttribute('data-url');
                new QRCode(container, {
                    text: url,
                    width: 120, height: 120,
                    colorDark: "#000000", colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            });

            // C. Countdown Timers
            function updateCountdowns() {
                document.querySelectorAll('.countdown-timer').forEach(el => {
                    const targetTimestamp = parseInt(el.getAttribute('data-time'));
                    if (!targetTimestamp) return;

                    const now = Math.floor(Date.now() / 1000);
                    const diff = targetTimestamp - now;
                    const progressBar = el.closest('div[style*="padding"]').querySelector('div[style*="width: 100%"] > div');

                    if (diff <= 0) {
                        el.textContent = "Passé";
                        if (progressBar) {
                            progressBar.style.width = '0%';
                            progressBar.style.backgroundColor = '#ef4444';
                        }
                        return;
                    }

                    // Format Time
                    const d = Math.floor(diff / (24 * 3600));
                    const h = Math.floor((diff % (24 * 3600)) / 3600);
                    const m = Math.floor((diff % 3600) / 60);
                    const s = diff % 60;
                    el.textContent = `${d}j ${h}h ${m}m ${s}s`;

                    // Update Bar
                    const maxWindow = 7 * 24 * 3600;
                    let percent = Math.min(100, Math.max(0, (diff / maxWindow) * 100));

                    if (progressBar) {
                        progressBar.style.width = `${percent}%`;
                        if (diff < 24 * 3600) progressBar.style.backgroundColor = '#ef4444';
                        else if (diff < 3 * 24 * 3600) progressBar.style.backgroundColor = '#eab308';
                        else progressBar.style.backgroundColor = '#0ea5e9';
                    }
                });
            }
            updateCountdowns();
            setInterval(updateCountdowns, 1000);

            // D. Modification Modal Handling
            <?php if (isset($showModifyModal) && $showModifyModal): ?>
                Swal.fire({
                    title: 'Choisir une nouvelle date et heure',
                    html: `<?php echo $modalContent; ?>`,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '600px',
                    allowOutsideClick: false,
                    didOpen: () => {
                        const buttons = Swal.getPopup().querySelectorAll('.slot-btn-available');
                        buttons.forEach(btn => {
                            btn.addEventListener('mouseenter', () => {
                                btn.style.borderColor = '#3b82f6';
                                btn.style.color = '#3b82f6';
                                btn.style.backgroundColor = '#eff6ff';
                            });
                            btn.addEventListener('mouseleave', () => {
                                btn.style.borderColor = '#e2e8f0';
                                btn.style.color = '#334155';
                                btn.style.backgroundColor = 'white';
                            });
                        });
                    }
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.close) {
                        const url = new URL(window.location);
                        url.searchParams.delete('modify_id');
                        url.searchParams.delete('date');
                        window.history.replaceState({}, '', url);
                    }
                });
            <?php endif; ?>
        });

        // -----------------------
        // 4. Rating Logic
        // -----------------------
        function openRatingModal(appointmentId) {
            Swal.fire({
                title: 'Noter votre consultation',
                html: `
                    <div class="star-rating-container" style="display: flex; justify-content: center; gap: 10px; font-size: 2rem; margin: 1rem 0; cursor: pointer;">
                        <i class="far fa-star star-btn" data-value="1" style="color: #cbd5e1; transition: color 0.2s;"></i>
                        <i class="far fa-star star-btn" data-value="2" style="color: #cbd5e1; transition: color 0.2s;"></i>
                        <i class="far fa-star star-btn" data-value="3" style="color: #cbd5e1; transition: color 0.2s;"></i>
                        <i class="far fa-star star-btn" data-value="4" style="color: #cbd5e1; transition: color 0.2s;"></i>
                        <i class="far fa-star star-btn" data-value="5" style="color: #cbd5e1; transition: color 0.2s;"></i>
                    </div>
                    <input type="hidden" id="selected-rating" value="0">
                    <p style="color: #64748b; font-size: 0.9rem;">Cliquez sur les étoiles pour noter</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'Envoyer la note',
                confirmButtonColor: '#f59e0b',
                cancelButtonText: 'Annuler',
                didOpen: () => {
                    const stars = Swal.getPopup().querySelectorAll('.star-btn');
                    const input = Swal.getPopup().querySelector('#selected-rating');
                    stars.forEach(star => {
                        star.addEventListener('mouseover', function () { highlightStars(stars, parseInt(this.getAttribute('data-value'))); });
                        star.addEventListener('mouseout', function () { highlightStars(stars, parseInt(input.value)); });
                        star.addEventListener('click', function () {
                            input.value = parseInt(this.getAttribute('data-value'));
                            highlightStars(stars, input.value);
                        });
                    });
                },
                preConfirm: () => {
                    const rating = Swal.getPopup().querySelector('#selected-rating').value;
                    if (rating == 0) {
                        Swal.showValidationMessage('Veuillez sélectionner une note');
                        return false;
                    }
                    return rating;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'rating_handler.php';

                    const inputId = document.createElement('input');
                    inputId.type = 'hidden'; inputId.name = 'appointment_id'; inputId.value = appointmentId;

                    const inputRating = document.createElement('input');
                    inputRating.type = 'hidden'; inputRating.name = 'rating'; inputRating.value = result.value;

                    form.appendChild(inputId);
                    form.appendChild(inputRating);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function highlightStars(stars, value) {
            stars.forEach(star => {
                const starVal = parseInt(star.getAttribute('data-value'));
                if (starVal <= value) {
                    star.classList.remove('far'); star.classList.add('fas'); star.style.color = '#f59e0b';
                } else {
                    star.classList.remove('fas'); star.classList.add('far'); star.style.color = '#cbd5e1';
                }
            });
        }
    </script>
</body>

</html>