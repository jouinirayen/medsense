<?php
include_once '../../controllers/RendezvousController.php';


$rc = new RendezvousController();

if(isset($_POST['Save'])){
    $rc->ajouterCreneau(
        $_POST['service_id'],
        $_POST['appointment_date'],
        $_POST['appointment_time'],
        $_POST['is_booked']
    );
    header('Location:rendezvous_dashboard.php');
}

// Fetch services for the dropdown
$services = $rc->obtenirServices();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Créneau</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <img src="../uploads/logo.jpeg" alt="Logo Medsense" style="height: 125px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="../frontoffice/front.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Front Office</span>
            </a>
            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="rendezvous_dashboard.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-calendar-plus"></i></span>
                <span>Créneaux</span>
            </a>
        </nav>
    </header>

<main class="main-content">
    <section class="hero-section">
        <h1 class="hero-title">Ajouter un nouveau créneau</h1>
        <p class="hero-description">
            Créez un nouveau créneau horaire disponible pour vos services médicaux.
        </p>
    </section>

    <section class="form-section">
        <div class="form-container">
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="service_id">Service concerné *</label>
                    <select id="service_id" name="service_id">
                        <option value="">Sélectionnez un service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['id']; ?>">
                                <?php echo $service['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="appointment_date">Date *</label>
                    <input type="date" id="appointment_date" name="appointment_date">
                </div>
                <div class="form-group">
                    <label for="appointment_time">Heure *</label>
                    <input type="time" id="appointment_time" name="appointment_time">
                </div>
                <div class="form-group">
                    <label for="is_booked">Statut</label>
                    <select id="is_booked" name="is_booked">
                        <option value="0">Disponible</option>
                        <option value="1">Réservé</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="Save" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Ajouter le créneau
                    </button>
                    <a href="rendezvous_dashboard.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
        </div>
    </section>
</main>

<script>
function validateForm(event) {
    const serviceId = document.getElementById('service_id').value;
    const date = document.getElementById('appointment_date').value;
    const time = document.getElementById('appointment_time').value;

    if (serviceId === "") {
        alert("Veuillez sélectionner un service.");
        event.preventDefault();
        return false;
    }
    if (date === "") {
        alert("Veuillez sélectionner une date.");
        event.preventDefault();
        return false;
    }
    if (time === "") {
        alert("Veuillez sélectionner une heure.");
        event.preventDefault();
        return false;
    }
    return true;
}

document.querySelector('form').addEventListener('submit', validateForm);
</script>

</body>
</html>
