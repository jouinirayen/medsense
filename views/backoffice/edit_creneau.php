<?php
include_once '../../controllers/RendezvousController.php';


$rc = new RendezvousController();

if(isset($_POST['Save'])){
    $rc->modifierCreneau(
        $_POST['id'],
        $_POST['service_id'],
        $_POST['appointment_date'],
        $_POST['appointment_time'],
        $_POST['is_booked']
    );
    header('Location:rendezvous_dashboard.php');
}

// Fetch services for the dropdown (needed for the form)
$services = $rc->obtenirServices();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Créneau</title>
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
<?php
if(isset($_GET['id'])){
    $slot = $rc->obtenirCreneauParId($_GET['id']);
?>
    <section class="hero-section">
        <h1 class="hero-title">Modifier un créneau</h1>
        <p class="hero-description">
            Modifiez les informations du créneau sélectionné.
        </p>
    </section>

    <section class="form-section">
        <div class="form-container">
            
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $slot['id']; ?>">
                <div class="form-group">
                    <label for="service_id">Service concerné *</label>
                    <select id="service_id" name="service_id">
                        <option value="">Sélectionnez un service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['id']; ?>"
                                <?php echo ((int) $slot['service_id'] === (int) $service['id']) ? 'selected' : ''; ?>>
                                <?php echo $service['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="appointment_date">Date *</label>
                    <input type="date" id="appointment_date" name="appointment_date" value="<?php echo $slot['appointment_date']; ?>">
                </div>
                <div class="form-group">
                    <label for="appointment_time">Heure *</label>
                    <input type="time" id="appointment_time" name="appointment_time" value="<?php echo substr($slot['appointment_time'], 0, 5); ?>">
                </div>
                <div class="form-group">
                    <label for="is_booked">Statut</label>
                    <select id="is_booked" name="is_booked">
                        <option value="0" <?php echo (!$slot['is_booked']) ? 'selected' : ''; ?>>Disponible</option>
                        <option value="1" <?php echo ($slot['is_booked']) ? 'selected' : ''; ?>>Réservé</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="Save" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Enregistrer les modifications
                    </button>
                    <a href="edit_creneau.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
        </div>
    </section>
<?php
} else {
    $creneaux = $rc->obtenirTousLesCreneaux();
?>
    <section class="hero-section">
        <h1 class="hero-title">Modifier un créneau</h1>
        <p class="hero-description">
            Sélectionnez un créneau dans la liste ci-dessous pour le modifier.
        </p>
    </section>

    <section class="table-section">
        <div class="table-container">
            <table class="services-edit-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($creneaux as $c): ?>
                    <tr>
                        <td><?php echo $c['service_name']; ?></td>
                        <td><?php echo $c['appointment_date']; ?></td>
                        <td><?php echo substr($c['appointment_time'], 0, 5); ?></td>
                        <td>
                            <?php if($c['is_booked']): ?>
                                <span class="badge badge-booked">Réservé</span>
                            <?php else: ?>
                                <span class="badge badge-available">Disponible</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_creneau.php?id=<?php echo $c['id']; ?>" class="btn-edit-modern">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php
}
?>
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

const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', validateForm);
}
</script>

</body>
</html>
