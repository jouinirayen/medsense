<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? "MedSense"); ?></title>
    <link rel="stylesheet" href="../css/style.css">
 
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <a href="/index.php" class="logo-link">
                    <img src="../../../images/logo.svg" alt="MedSense Logo" class="logo">
                    <div class="site-branding">
                        <h1 class="site-title">MedSense</h1>
                        <p class="tagline">Réclamations et Urgences</p>
                    </div>
                </a>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="index.php" class="<?= ($currentPage == 'index.php') ? 'active' : '' ?>">📋 Mes Réclamations</a></li>
                    <li><a href="create.php" class="<?= ($currentPage == 'create.php') ? 'active' : '' ?>">✏️ Nouvelle Réclamation</a></li>
                    <li><a href="urgence.php" class="<?= ($currentPage == 'urgence.php') ? 'active' : '' ?>">🚨 Urgence</a></li>
                    <li><a href="../../backoffice/reponse/admin_reclamations.php" class="<?= ($currentPage == 'admin_reclamations.php') ? 'active' : '' ?>">⚙️ Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>