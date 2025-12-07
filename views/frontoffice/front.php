<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect doctors to their dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'medecin') {
    header('Location: ../backoffice/afficher_rendezvous_medecin.php');
    exit;
}

// Redirect admins to their dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ../backoffice/dashboard.php');
    exit;
}


require_once '../../controllers/ServiceController.php';
require_once '../../controllers/UserController.php';

$serviceController = new ServiceController();
$userController = new UserController();
$currentUser = $userController->getUserById($_SESSION['user_id']);



if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
} else {
    $searchTerm = '';
}

// Récupération des services (filtrés ou tous)
if (!empty($searchTerm)) {
    // Si on cherche quelque chose, on demande au contrôleur de filtrer
    $services = $serviceController->rechercherServices($searchTerm);
} else {
    // Sinon, on prend tout
    $services = $serviceController->obtenirTousLesServices();
}

$totalServicesCount = count($services);




$servicesToDisplay = $services;
$isAutoScroll = false;

if ($totalServicesCount > 3 && empty($searchTerm)) {

    $servicesToDisplay = array_merge($services, $services);
    $isAutoScroll = true;
}


function generateBookingLink($service)
{
    // Always link to the doctors list for the service
    return 'doctors_list.php?service_id=' . $service['id'];
}

?>
<?php include 'partials/header.php'; ?>

<main class="main-content">

    <?php include 'partials/hero.php'; ?>

    <?php include 'partials/search.php'; ?>

    <?php include 'partials/services_list.php'; ?>

</main>

<?php include 'partials/footer.php'; ?>