<?php

include 'controllers/ServiceController.php';

$serviceController = new ServiceController();

$allServices = $serviceController->obtenirTousLesServices();
$totalServicesCount = count($allServices);

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($searchTerm)) {
    $services = $serviceController->rechercherServices($searchTerm);
} else {
    $services = $allServices;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <img src="images/logo.jpeg" alt="Logo Medsense" style="height: 125px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Accueil</span>
            </a>
            <a href="index.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                <span>Rendez-vous</span>
            </a>
            <a href="views/dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-cog"></i></span>
                <span>Admin</span>
            </a>
        </nav>
    </header>

<main class="main-content">
    <section class="hero-section">
        <h1 class="hero-title">Prenez rapidement un rendez-vous avec votre médecin!</h1>
        <p class="hero-description">
            Sélectionnez votre médecin, choisissez la date et l'heure de votre rdv et recevez votre sms/mail de confirmation. C'est aussi simple que ça !
        </p>
    </section>

    <section class="search-section">
        <div class="search-container">
            <form method="GET" action="index.php" class="search-form">
                <div class="search-box-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input 
                        type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="Rechercher un service..." 
                        value="<?php echo $searchTerm; ?>"
                        autocomplete="off"
                    >
                    <?php if (!empty($searchTerm)): ?>
                        <a href="index.php" class="search-clear" title="Effacer la recherche">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i>
                    <span>Rechercher</span>
                </button>
            </form>
            <?php if (!empty($searchTerm)): ?>
                <div style="text-align: center; margin-top: 20px;">
                    <p class="search-results-count">
                        <i class="fas fa-check-circle" style="margin-right: 8px; color: #10b981;"></i>
                        <?php echo count($services); ?> service<?php echo count($services) > 1 ? 's' : ''; ?> trouvé<?php echo count($services) > 1 ? 's' : ''; ?> pour "<?php echo $searchTerm; ?>"
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="services-section">
        <h2 class="section-title">Nos Services</h2>
        <div class="slider-wrapper <?php echo ($totalServicesCount > 3 && empty($searchTerm)) ? 'auto-scroll-container' : ''; ?>">
            <div class="services-grid <?php echo ($totalServicesCount > 3 && empty($searchTerm)) ? 'auto-scroll' : ''; ?>">
                <?php if (empty($services)): ?>
                    <div class="no-services">
                        <p>Aucun service trouvé.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <div class="service-card" <?php if (!empty($service['image'])): ?>style="background-image: url('<?php echo $service['image']; ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"<?php endif; ?>>
                            <div class="service-icon">
                                <i class="<?php echo $service['icon']; ?>"></i>
                            </div>
                            <h3 class="service-title"><?php echo $service['name']; ?></h3>
                            <p class="service-description"><?php echo $service['description']; ?></p>
                            <div class="service-footer">
                                <a href="<?php echo $service['link']; ?>" class="view-doctors-link">Voir les médecins</a>
                                <a href="<?php echo $service['link']; ?>" class="arrow-button"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                            <?php if ($totalServicesCount > 3 && empty($searchTerm)): ?>
                                <?php foreach ($services as $service): ?>
                                    <div class="service-card" <?php if (!empty($service['image'])): ?>style="background-image: url('<?php echo $service['image']; ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"<?php endif; ?>>
                                        <div class="service-icon">
                                            <i class="<?php echo $service['icon']; ?>"></i>
                                        </div>
                                        <h3 class="service-title"><?php echo $service['name']; ?></h3>
                                        <p class="service-description"><?php echo $service['description']; ?></p>
                                        <div class="service-footer">
                                            <a href="<?php echo $service['link']; ?>" class="view-doctors-link">Voir les médecins</a>
                                            <a href="<?php echo $service['link']; ?>" class="arrow-button"><i class="fas fa-arrow-right"></i></a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="how-it-works-section">
        <div class="how-it-works-container">
            <h2 class="how-it-works-title">Comment ça marche ?</h2>
            <p class="how-it-works-description">
                Prendre rendez-vous avec votre médecin n'a jamais été aussi simple ! Notre plateforme vous permet de réserver votre consultation en seulement trois étapes faciles. Créez votre compte en quelques secondes, recherchez la spécialité médicale dont vous avez besoin, puis choisissez le praticien qui vous convient le mieux parmi notre réseau de professionnels de santé qualifiés.
            </p>
            <img src="images/comment.jpeg" alt="Comment ça marche" style="width: 100%; max-width: 1200px; height: auto; display: block; margin: 0 auto; border-radius: 12px;">
        </div>
    </section>
</main>

</body>
</html>
