<?php
/**
 * Back-office: View list of all services
 */

require_once __DIR__ . '/../../controllers/ServiceController.php';

$serviceController = new ServiceController();

// Get all services
$services = $serviceController->obtenirTousLesServices();

$pageTitle = 'Liste des Services';
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
            <h1 class="hero-title">Liste de tous les services</h1>
            <p class="hero-description">
                Consultez la liste complète de tous vos services avec leurs détails.
            </p>
        </section>

        <?php if (isset($_GET['message']) || isset($_GET['error'])): ?>
            <section class="form-section">
                <div class="form-container">
                    <?php if (isset($_GET['message'])): ?>
                        <div class="message success">
                            <i class="fas fa-check-circle"></i> <?php echo $_GET['message']; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="message error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $_GET['error']; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="table-section">
            <div class="table-container">
                <?php if (empty($services)): ?>
                    <p>Aucun service disponible. <a href="add_service.php">Ajoutez un service</a> d'abord.</p>
                <?php else: ?>
                    <div class="table-header-info">
                        <p class="table-count">
                            <i class="fas fa-list"></i> Total: <strong><?php echo count($services); ?></strong>
                            service<?php echo count($services) > 1 ? 's' : ''; ?>
                        </p>
                    </div>
                    <table class="services-edit-table" id="servicesTable">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Icon</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td>
                                        <?php if ($service['image']): ?>
                                            <img src="../<?php echo htmlspecialchars($service['image']); ?>" alt="Service"
                                                class="service-thumbnail">
                                        <?php else: ?>
                                            <span class="no-image"><i class="fas fa-image"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="service-name"><?php echo htmlspecialchars($service['name']); ?></div>
                                    </td>
                                    <td>
                                        <div class="service-desc">
                                            <?php echo htmlspecialchars(substr($service['description'], 0, 50)) . '...'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="service-icon-cell">
                                            <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </main>

</body>

</html>