<?php
/**
 * Export PDF des utilisateurs - MVC Version
 * admin-export-pdf.php
 */

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';

// Récupérer les filtres de l'URL
$filters = [];
if (!empty($_GET['search'])) {
    $filters['search'] = htmlspecialchars($_GET['search']);
}
if (!empty($_GET['role'])) {
    $filters['role'] = htmlspecialchars($_GET['role']);
}
if (!empty($_GET['statut'])) {
    $filters['statut'] = htmlspecialchars($_GET['statut']);
}
if (!empty($_GET['type'])) {
    $filters['type'] = htmlspecialchars($_GET['type']);
}
if (!empty($_GET['user_ids'])) {
    $filters['user_ids'] = explode(',', $_GET['user_ids']);
}

try {
    $adminController = new AdminController();
    
    // Vérifier si la méthode existe
    if (method_exists($adminController, 'xportUsersToPDF')) {
        $adminController->xportUsersToPDF($filters);
    } else {
        // Fallback vers une méthode alternative
        throw new Exception('La méthode d\'export PDF n\'est pas disponible');
    }
    
} catch (Exception $e) {
    // Enregistrer l'erreur et rediriger
    $_SESSION['error_message'] = 'Erreur lors de l\'export PDF: ' . $e->getMessage();
    header('Location: admin-users.php');
    exit;
}