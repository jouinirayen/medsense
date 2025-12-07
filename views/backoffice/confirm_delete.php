<?php

require_once __DIR__ . '/../../controllers/ServiceController.php';

$pageTitle = "Confirmer la suppression";
$serviceController = new ServiceController();


if (isset($_GET['delete_id'])) {
    $serviceController->supprimerService($_GET['delete_id']);
    header('Location: list_services.php?message=Service supprimé avec succès!');
    exit;
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
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">

            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="../frontoffice/logout.php" class="nav-link logout-link">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
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

        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <h2>Êtes-vous sûr ?</h2>
        <p>Vous êtes sur le point de supprimer le service :</p>
        <div class="service-preview">
            <?php echo htmlspecialchars($service->getName()); ?>
        </div>
        <p style="color: #64748b; margin-bottom: 2rem;">Cette action est irréversible.</p>
        <section class="confirm-section">
            <div class="confirm-message">
                <div class="service-preview">
                    <p><i class="<?php echo $service->getIcon(); ?>"></i> <?php echo $service->getIcon(); ?></p>
                    <p>Lien: <?php echo $service->getLink(); ?></p>
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