<?php


include __DIR__ . '/../controllers/ServiceController.php';

$serviceController = new ServiceController();
$serviceController->gererAjout();

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
            <a href="add_service.php" class="nav-link active">
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
        <h1 class="hero-title">Ajouter un nouveau service</h1>
        <p class="hero-description">
            Remplissez le formulaire ci-dessous pour ajouter un nouveau service à votre catalogue.
        </p>
    </section>

    <section class="form-section">
        <div class="form-container">
        <form method="POST" action="add_service.php">
            <input type="hidden" name="add_service" value="1">
            
            <div class="form-group">
                <label for="name">Nom du service *</label>
                <input type="text" id="name" name="name" placeholder="Ex: Développement Web">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="4" placeholder="Décrivez le service..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="icon">Icône Font Awesome *</label>
                <input type="text" id="icon" name="icon" placeholder="Ex: fas fa-code">
                <small>Exemples: fas fa-code, fas fa-paint-brush, fas fa-mobile-alt</small>
            </div>
            
            <div class="form-group">
                <label for="link">Lien de la page *</label>
                <input type="text" id="link" name="link" placeholder="Ex: service_web.php">
            </div>
            
            <div class="form-group">
                <label for="image">Image de fond (chemin ou URL)</label>
                <input type="text" id="image" name="image" placeholder="Ex: uploads/mon_image.jpg ou https://...">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus"></i> Ajouter le service
                </button>
                <a href="dashboard.php" class="btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
        </div>
    </section>
</main>

</body>
</html>

