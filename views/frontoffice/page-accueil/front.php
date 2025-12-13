<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Redirect doctors to their dashboard
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'medecin') {
    header('Location: ../../backoffice/dashboard_medecin/afficher_rendezvous_medecin.php');
    exit;
}

// Redirect admins to their dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: /projet2025/views/backoffice/dashboard_service/dashboard.php');
    exit;
}


require_once '../../../controllers/ServiceController.php';
require_once '../../../controllers/UserController.php';

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
    return '../rendezvous_avec_docteur/doctors_list.php?service_id=' . $service['id'];
}

$activePage = 'accueil';
?>
<?php include 'partials/header.php'; ?>

<main class="main-content">

    <?php include 'partials/hero.php'; ?>

    <?php include 'partials/search.php'; ?>

    <?php include 'partials/daily_tip_handler.php'; ?>

    <?php include 'partials/services_list.php'; ?>

    <?php include 'partials/prevention_planner.php'; ?>

    <?php include 'partials/daily_article_handler.php'; ?>

    <?php include 'partials/chat_widget.php'; ?>

    <!-- Chatbot Scripts and Styles -->
    <link rel="stylesheet" href="css/chat.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/siri_wave.css?v=<?php echo time(); ?>">

    <?php include 'partials/voice_bubble_widget.php'; ?>

    <script src="js/voice_handler.js?v=<?php echo time(); ?>"></script>
    <script src="js/siri_controller.js?v=<?php echo time(); ?>"></script>
    <script src="js/chat.js?v=<?php echo time(); ?>"></script>

</main>

<?php include 'partials/footer.php'; ?>