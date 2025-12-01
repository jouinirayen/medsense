<?php
session_start();
require_once '../../controllers/ReservationController.php';
require_once '../../controllers/UserController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$reservationController = new ReservationController();
$userController = new UserController();

// Get User Info
$currentUser = $userController->getUserById($userId);

// Get Appointments for Patient
$appointments = $reservationController->getAppointmentsByPatient($userId);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Rendez-vous</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/appointments.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div style="text-align: center;">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="front.php" class="nav-link"
                style="text-decoration: none; color: #333; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Accueil</span>
            </a>
            <a href="afficher_rendezvous_patient.php" class="nav-link active"
                style="text-decoration: none; color: #0ea5e9; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                <span>Mes Rendez-vous</span>
            </a>
            <a href="logout.php" class="nav-link"
                style="text-decoration: none; color: #333; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
            </a>
        </nav>
    </header>

    <main class="main-container">
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
                            <span class="status-badge status-<?php echo htmlspecialchars($appt['statut']); ?>">
                                <?php echo htmlspecialchars($appt['statut'] === 'pris' ? 'Confirmé' : $appt['statut']); ?>
                            </span>
                        </div>

                        <div class="card-body">
                            <div style="font-size: 1.5rem; color: var(--primary);">
                                <i class="far fa-clock"></i>
                            </div>
                            <div class="time-display">
                                <span class="time-label">Heure du rendez-vous</span>
                                <span class="time-value"><?php echo htmlspecialchars(substr($appt['heureRdv'], 0, 5)); ?></span>
                            </div>
                        </div>

                        <div class="card-footer" style="display: flex; gap: 0.5rem;">
                            <a href="modifier_rendezvous.php?id=<?php echo $appt['idRDV']; ?>" class="cancel-btn"
                                style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background-color: #3b82f6; color: white; flex: 1;">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="annuler_rendezvous.php?id=<?php echo $appt['idRDV']; ?>" class="cancel-btn"
                                style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; flex: 1;">
                                <i class="far fa-times-circle"></i> Annuler
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>

</html>