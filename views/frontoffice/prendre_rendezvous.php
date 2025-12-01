<?php
session_start();
require_once '../../controllers/ReservationController.php';
require_once '../../models/Reservation.php';
require_once '../../controllers/UserController.php';

$reservationController = new ReservationController();
$userController = new UserController();

// Declarations at the top
$doctor_id = null;
$slot_time = null;
$error = null;
$success = false;
$user = null;
$nom_value = '';
$prenom_value = '';

// Get doctor_id
if (isset($_GET['doctor_id'])) {
    $doctor_id = $_GET['doctor_id'];
} else {
    $doctor_id = null;
}

// Get slot_time
if (isset($_GET['slot_time'])) {
    $slot_time = $_GET['slot_time'];
} else {
    $slot_time = null;
}

// Get user
if (isset($_SESSION['user_id'])) {
    $user = $userController->getUserById($_SESSION['user_id']);
} else {
    // Enforce login to ensure we can capture the user ID for the appointment
    header('Location: login.php');
    exit;
}

// Calculate form values using if/else
if ($user) {
    if (isset($user['nom'])) {
        $nom_value = $user['nom'];
    } else {
        $nom_value = '';
    }

    if (isset($user['prenom'])) {
        $prenom_value = $user['prenom'];
    } else {
        $prenom_value = '';
    }
} else {
    $nom_value = '';
    $prenom_value = '';
}

if (!$doctor_id || !$slot_time) {
    header('Location: front.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Post variables with if/else
    if (isset($_POST['nom'])) {
        $nom = $_POST['nom'];
    } else {
        $nom = '';
    }

    if (isset($_POST['prenom'])) {
        $prenom = $_POST['prenom'];
    } else {
        $prenom = '';
    }

    if (empty($nom) || empty($prenom)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        if (isset($_SESSION['user_id'])) {
            $patientId = $_SESSION['user_id'];
        } else {
            $patientId = null;
        }

        // Create Reservation object using constructor
        $reservation = new Reservation(
            null, // idRDV
            $doctor_id,
            $slot_time,
            $nom,
            $prenom,
            'pris', // statut
            $patientId
        );

        // Attempt to book
        $result = $reservationController->bookSlot($reservation);
        
        if ($result && isset($result['success']) && $result['success']) {
            $success = true;
            
            // Send confirmation email
            require_once 'mailing_handler.php';
            sendConfirmationEmail(
                $result['patientEmail'],
                "$prenom $nom",
                $result['doctorPrenom'] . " " . $result['doctorNom'],
                date('Y-m-d'), // Assuming today's date for booking action
                $slot_time
            );
        } else {
            $error = "Ce créneau n'est plus disponible.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmer le rendez-vous</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/booking.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="confirmation-container">
        <h2 style="margin-bottom: 1.5rem; text-align: center;">Confirmer votre rendez-vous</h2>

        <div style="text-align: center; margin-bottom: 2rem; color: #64748b;">
            <p><i class="far fa-clock"></i> <?php echo $slot_time; ?></p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-input" value="<?php echo $nom_value; ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Prénom</label>
                <input type="text" name="prenom" class="form-input" value="<?php echo $prenom_value; ?>">
            </div>

            <button type="submit" class="submit-btn">Confirmer le rendez-vous</button>
            <a href="javascript:history.back()"
                style="display: block; text-align: center; margin-top: 1rem; color: #64748b; text-decoration: none;">Annuler</a>
        </form>
    </div>

    <?php if ($success): ?>
        <script>
            // Play success sound
            const audio = new Audio('../son/suc.mp3');
            audio.play().catch(error => console.log('Audio play failed:', error));

            Swal.fire({
                title: 'Rendez-vous confirmé !',
                text: 'Votre rendez-vous à <?php echo $slot_time; ?> a été réservé avec succès.',
                icon: 'success',
                confirmButtonText: 'Voir mes rendez-vous',
                confirmButtonColor: '#0ea5e9',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'afficher_rendezvous_patient.php';
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>