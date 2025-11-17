<?php
/**
 * Configuration générale du site
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'reclamations_db');

// Configuration du site
// Calcul automatique de l'URL de base
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Calculer le chemin de base
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = dirname($scriptName);
$baseDir = str_replace('\\', '/', $baseDir);
$baseDir = ($baseDir === '/' || $baseDir === '.') ? '' : rtrim($baseDir, '/');

// Si SITE_URL n'est pas défini, le calculer automatiquement
if (!defined('SITE_URL')) {
    define('SITE_URL', $protocol . '://' . $host . $baseDir . '/');
}

define('SITE_NAME', 'Système de Réclamations et reponses');

// URL des assets (CSS, JS, images) - chemins relatifs depuis la racine
define('CSS_URL', 'app/css/');
define('JS_URL', 'app/css/');
define('IMAGES_URL', 'app/images/');
define('ASSETS_URL', 'app/css/'); // Pour compatibilité

// Chemin des fichiers
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('FRONT_PATH', ROOT_PATH . 'app/Views/front/');
define('BACK_PATH', ROOT_PATH . 'app/Views/back/');
define('CSS_PATH', ROOT_PATH . 'app/css/');
define('IMAGES_PATH', ROOT_PATH . 'app/images/');

// Paramètres de session
define('SESSION_TIMEOUT', 3600); // 1 heure

// Types de réclamations
define('TYPE_NORMAL', 'normal');
define('TYPE_URGENCE', 'urgence');

// Statuts de réclamations
define('STATUS_OPEN', 'ouvert');
define('STATUS_IN_PROGRESS', 'en cours');
define('STATUS_CLOSED', 'fermé');

?>
