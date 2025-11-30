<?php
require_once 'config/config.php';
$pageTitle = "Accueil - MedSense";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <div class="header-content">
        <div class="logo-section">
            <a href="index.php" class="logo-link">
                <img src="../../../images/logo.svg" alt="MedSense Logo" class="logo">
                <div class="site-branding">
                    <h1 class="site-title">MedSense</h1>
                    <p class="tagline">Réclamations et Urgences</p>
                </div>
            </a>
        </div>
        <nav class="nav-menu">
            <ul>
                <li><a href="views/frontoffice/reclamation/index.php">📋 Mes Réclamations</a></li>
                <li><a href="views/frontoffice/reclamation/create.php">✏️ Nouvelle Réclamation</a></li>
                <li><a href="views/frontoffice/reclamation/urgence.php">🚨 Urgence</a></li>
                <li><a href="views/backoffice/reponse/admin_reclamations.php">⚙️ Admin</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="main-content">
    <h2>Bienvenue dans le Système de Réclamations et Urgences</h2>

    <div class="card main-menu" style="max-width: 700px; margin: 2rem auto; padding:1.5rem;">
        <h3>Menu Principal</h3>
        <div class="menu-links" style="display: flex; flex-direction: column; gap: 1rem; margin-top:1rem;">
            <a href="views/frontoffice/reclamation/index.php" class="btn btn-primary btn-lg">📋 Mes Réclamations</a>
            <a href="views/frontoffice/reclamation/create.php" class="btn btn-success btn-lg">✏️ Créer une Réclamation</a>
            <a href="views/frontoffice/reclamation/urgence.php" class="btn btn-danger btn-lg">🚨 Alerte Urgence</a>
            <a href="views/backoffice/reponse/admin_reclamations.php" class="btn btn-secondary btn-lg">⚙️ Administration</a>
        </div>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y'); ?> MedSense - Tous droits réservés</p>
</footer>

 

</body>
</html>
