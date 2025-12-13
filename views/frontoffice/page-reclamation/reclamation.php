<?php
session_start();
// Placeholder for Reclamation
require_once '../../../controllers/UserController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$userController = new UserController();
$currentUser = $userController->getUserById($_SESSION['user_id']);

$activePage = 'reclamation';
require_once '../page-accueil/partials/header.php';
?>
<main style="padding: 2rem; text-align: center;">
    <h1>Réclamations</h1>
    <p>Cette page est en cours de construction.</p>
</main>
</body>

</html>