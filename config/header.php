<?php
// Calcul chemin racine pour accéder à css et images
$basePath = dirname(__DIR__); // config -> go one level up
$currentDir = dirname($_SERVER['SCRIPT_FILENAME']);

// Calcul profondeur
$relativePath = str_replace($basePath, '', $currentDir);
$relativePath = trim($relativePath, '/');
$depth = $relativePath ? substr_count($relativePath, '/') + 1 : 0;
$rootPath = str_repeat('../', $depth);

// Chemins CSS et logo
$cssPath = $rootPath . 'css/style.css';
$logoPath = $rootPath . 'images/logo.png';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle ?? "MedSense"; ?></title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>">
</head>
<body>
<header>
    <img src="<?php echo $logoPath; ?>" alt="Logo" class="logo">
</header>
<main>
