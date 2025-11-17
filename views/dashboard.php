
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
            <a href="dashboard.php" class="nav-link active">
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
        <h1 class="hero-title">Tableau de bord administrateur</h1>
        <p class="hero-description">
            Gérez vos services facilement. Ajoutez, modifiez, supprimez ou consultez la liste de tous vos services.
        </p>
    </section>

    <section class="dashboard-section">
        <!-- Display messages -->
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
        
        <!-- Navigation Links -->
        <div class="dashboard-nav">
            <a href="add_service.php" class="nav-button">
                <div class="nav-button-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="nav-button-content">
                    <h3>Ajouter un service</h3>
                    <p>Créer un nouveau service</p>
                </div>
            </a>
            <a href="edit_service.php" class="nav-button">
                <div class="nav-button-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="nav-button-content">
                    <h3>Modifier un service</h3>
                    <p>Éditer un service existant</p>
                </div>
            </a>
            <a href="delete_service.php" class="nav-button">
                <div class="nav-button-icon">
                    <i class="fas fa-trash"></i>
                </div>
                <div class="nav-button-content">
                    <h3>Supprimer un service</h3>
                    <p>Retirer un service</p>
                </div>
            </a>
            <a href="list_services.php" class="nav-button">
                <div class="nav-button-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="nav-button-content">
                    <h3>Liste des services</h3>
                    <p>Voir tous les services</p>
                </div>
            </a>
        </div>
    </section>
</main>

</body>
</html>

