<?php
require_once '../../controllers/ReservationController.php';
require_once '../../controllers/UserController.php';

$userController = new UserController();
$userController->requireRole('medecin');

$userId = $_SESSION['user_id'];
$reservationController = new ReservationController();

// Get User Info
$currentUser = $userController->getUserById($userId);

// Get Appointments for Doctor
$filterDate = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : null;
$appointments = $reservationController->getAppointmentsByDoctor($userId, $filterDate);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Consultations</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/appointments.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <header class="header">
        <div style="text-align: center;">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="afficher_rendezvous_medecin.php" class="nav-link active"
                style="text-decoration: none; color: #0ea5e9; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                <span>Mes Consultations</span>
            </a>
            <a href="../frontoffice/logout.php" class="nav-link"
                style="text-decoration: none; color: #333; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
            </a>
        </nav>
    </header>

    <main class="main-container">
        <div class="page-header">
            <div class="page-title">
                <h1>Mes Consultations</h1>
                <p>Gérez vos rendez-vous et patients</p>
            </div>

            <!-- Date Filter Form -->
            <form method="GET" action="" class="date-filter-form"
                style="display: flex; gap: 10px; align-items: center;">
                <label for="filter-date" style="font-weight: 500; color: #64748b;">Filtrer par date :</label>
                <input type="date" id="filter-date" name="date"
                    value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>"
                    style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; transition: border-color 0.2s;">
                <button type="submit"
                    style="background-color: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 500;">
                    <i class="fas fa-filter"></i>
                </button>
                <?php if (isset($_GET['date']) && !empty($_GET['date'])): ?>
                    <a href="afficher_rendezvous_medecin.php"
                        style="color: #64748b; text-decoration: none; font-size: 0.9em;">
                        <i class="fas fa-times"></i> Effacer
                    </a>
                <?php endif; ?>
            </form>

            <div class="user-badge">
                <div class="user-avatar-small">
                    <i class="fas fa-user-md"></i>
                </div>
                <span>Dr. <?php echo htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']); ?></span>
            </div>
        </div>

        <?php if (empty($appointments)): ?>
            <div class="empty-state">
                <div style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1.5rem;">
                    <i class="far fa-calendar-check"></i>
                </div>
                <h3 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 0.5rem;">Aucune consultation prévue</h3>
                <p style="color: #64748b;">Vous n'avez pas de rendez-vous à venir.</p>
            </div>
        <?php else: ?>
            <div class="appointments-grid">
                <?php foreach ($appointments as $appt): ?>
                    <div class="appointment-card">
                        <div class="card-header">
                            <div class="doctor-profile">
                                <div class="doctor-avatar">
                                    <i class="fas fa-user-injured"></i>
                                </div>
                                <div class="doctor-info">
                                    <h3><?php echo htmlspecialchars(($appt['patientPrenom'] ?? '') . ' ' . ($appt['patientNom'] ?? '')); ?>
                                    </h3>
                                    <p class="specialty">Patient</p>
                                </div>
                            </div>
                            <span
                                class="status-badge status-<?php echo htmlspecialchars(str_replace(' ', '-', $appt['statut'])); ?>">
                                <?php
                                $statusText = $appt['statut'];
                                if ($appt['statut'] === 'pris' || $appt['statut'] === 'confirme') {
                                    $statusText = 'Confirmé';
                                } elseif ($appt['statut'] === 'en attente') {
                                    $statusText = 'En attente';
                                }
                                echo htmlspecialchars($statusText);
                                ?>
                            </span>
                        </div>

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

                        <?php if ($appt['statut'] === 'en attente'): ?>
                            <div class="card-footer"
                                style="display: flex; gap: 0.5rem; margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                                <button onclick="updateStatus(<?php echo $appt['idRDV']; ?>, 'accept')" class="btn-accept"
                                    style="flex: 1; padding: 0.5rem; background-color: #22c55e; color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                    <i class="fas fa-check"></i> Accepter
                                </button>
                                <button onclick="updateStatus(<?php echo $appt['idRDV']; ?>, 'refuse')" class="btn-refuse"
                                    style="flex: 1; padding: 0.5rem; background-color: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                    <i class="fas fa-times"></i> Refuser
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Hidden Scanner Modal Structure (Injected via Swal but useful to have definition logic) -->


    <!-- Logic to handle URL parameters for alerts -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Succès',
                    text: 'Statut mis à jour avec succès.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
                // Optional: Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let errorMsg = 'Une erreur est survenue.';
                const code = "<?php echo htmlspecialchars($_GET['error']); ?>";
                if (code === 'missing_params') errorMsg = 'Paramètres manquants.';
                if (code === 'update_failed') errorMsg = 'La mise à jour a échoué.';
                if (code === 'invalid_status') errorMsg = 'Statut invalide.';

                Swal.fire({
                    title: 'Erreur',
                    text: errorMsg,
                    icon: 'error'
                });
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        </script>
    <?php endif; ?>

    <script>
        function updateStatus(id, action) {
            let title = action === 'accept' ? 'Accepter le rendez-vous ?' : 'Refuser le rendez-vous ?';
            let confirmBtnColor = action === 'accept' ? '#22c55e' : '#ef4444';

            Swal.fire({
                title: title,
                text: "Cette action mettra à jour le statut.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: confirmBtnColor,
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Oui, continuer'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove JSON fetch, use standard Form POST to follow redirects
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'update_status.php';

                    const hiddenId = document.createElement('input');
                    hiddenId.type = 'hidden';
                    hiddenId.name = 'id';
                    hiddenId.value = id;
                    form.appendChild(hiddenId);

                    const hiddenStatus = document.createElement('input');
                    hiddenStatus.type = 'hidden';
                    hiddenStatus.name = 'status';
                    hiddenStatus.value = action;
                    form.appendChild(hiddenStatus);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>

</html>