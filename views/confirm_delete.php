<?php

include __DIR__ . '/../controllers/ServiceController.php';

$serviceController = new ServiceController();


if (isset($_GET['delete_id'])) {
    $serviceController->gererSuppression();
    return;
}

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    header('Location: delete_service.php');
}

$service = $serviceController->obtenirServiceParId($id);
if (!$service) {
    header('Location: delete_service.php');
}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <img src="../uploads/logo.jpeg" alt="Logo Medsense" style="height: 125px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="../index.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Front Office</span>
            </a>
            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="add_service.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-plus"></i></span>
                <span>Ajouter</span>
            </a>
            <a href="list_services.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-list"></i></span>
                <span>Liste</span>
            </a>
            <a href="edit_service.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-edit"></i></span>
                <span>Modifier</span>
            </a>
        </nav>
    </header>

<main class="main-content">
    <section class="hero-section">
        <h1 class="hero-title">Confirmer la suppression</h1>
        <p class="hero-description">
            Êtes-vous sûr de vouloir supprimer ce service ? Cette action est irréversible.
        </p>
    </section>

    <section class="confirm-section">
        <div class="confirm-message">
            <p><strong>Voulez-vous vraiment supprimer ce service ?</strong></p>
            
            <div class="service-preview">
                <h3><?php echo $service['name']; ?></h3>
                <p><?php echo $service['description']; ?></p>
                <p><i class="<?php echo $service['icon']; ?>"></i> <?php echo $service['icon']; ?></p>
                <p>Lien: <?php echo $service['link']; ?></p>
            </div>
            
            <div class="confirm-actions">
                <form method="GET" action="confirm_delete.php" style="display: inline;">
                    <input type="hidden" name="delete_id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn-delete">
                        <i class="fas fa-trash"></i> Oui, supprimer
                    </button>
                </form>
                <a href="delete_service.php" class="btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </div>
    </section>
</main>

</body>
</html>

