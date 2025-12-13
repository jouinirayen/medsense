<?php
session_start();
// Placeholder for Blog
require_once '../../../controllers/UserController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$userController = new UserController();
$currentUser = $userController->getUserById($_SESSION['user_id']);

$activePage = 'blog';
require_once '../page-accueil/partials/header.php';
?>
<main style="padding: 2rem; text-align: center;">
    <h1>Blog Santé</h1>
    <p>Cette page est en cours de construction.</p>
</main>
</body>

</html>