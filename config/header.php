<?php
/**
 * En-tête du site
 */

if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
}

// Calculer le chemin relatif vers la racine depuis le fichier qui inclut header.php
$basePath = dirname(dirname(__FILE__)); // Racine du projet
$currentFile = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;
$currentDir = dirname($currentFile);

// Calculer la profondeur (nombre de niveaux à remonter)
$relativePath = str_replace('\\', '/', str_replace($basePath . '/', '', $currentDir . '/'));
$relativePath = trim($relativePath, '/');
$depth = $relativePath ? substr_count($relativePath, '/') + 1 : 0;
$rootPath = $depth > 0 ? str_repeat('../', $depth) : '';

// Logo path - uses IMAGES_URL from config
$logoPath = $rootPath . IMAGES_URL . 'logo.svg';
$cssPath = $rootPath . CSS_URL . 'style.css';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <a href="<?php echo SITE_URL; ?>index.php" style="display: flex; align-items: center; text-decoration: none;">
                    <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo SITE_NAME; ?>" class="logo" onerror="this.style.display='none'; console.error('Logo not found at: <?php echo htmlspecialchars($logoPath); ?>');">
                </a>
                <div class="site-branding">
                    <h1><?php echo SITE_NAME; ?></h1>
                    <p class="tagline">Gestion des réclamations & réponses</p>
                </div>
            </div>
            <nav class="nav-menu">
                    <?php if (isLoggedIn()): ?>
                        <ul>
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo SITE_URL; ?>app/Views/back/admin_reclamations.php">Gérer les réclamations</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo SITE_URL; ?>app/Views/front/mes_reclamations.php">Mes réclamations</a></li>
                                <li><a href="<?php echo SITE_URL; ?>app/Views/front/creer_reclamation.php">Créer une réclamation</a></li>
                                <li><a href="<?php echo SITE_URL; ?>app/Views/front/urgence.php" class="btn-urgence">🚨 Urgence</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo SITE_URL; ?>logout.php">Déconnexion (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                        </ul>
                    <?php else: ?>
                        <ul>
                            <li><a href="<?php echo SITE_URL; ?>login.php">Connexion</a></li>
                            <li><a href="<?php echo SITE_URL; ?>register.php">Inscription</a></li>
                        </ul>
                    <?php endif; ?>
                </nav>
            </div>
    </header>

    <main class="container">
