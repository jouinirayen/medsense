<?php
// controllers/logout.php - Fichier dédié à la déconnexion
session_start();

echo "<!-- Début du processus de déconnexion -->";

// Afficher l'état avant déconnexion
echo "<!-- Avant déconnexion - Session ID: " . session_id() . " -->";
echo "<!-- Données session avant: ";
print_r($_SESSION);
echo " -->";

// Vider toutes les variables de session
$_SESSION = array();

// Détruire le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Détruire la session
session_destroy();

echo "<!-- Session détruite, redirection en cours -->";

// Redirection vers l'accueil
header("Location: ../views/frontoffice/home/index.php");
exit;
?>