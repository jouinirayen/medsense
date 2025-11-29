<?php
include_once '../../controllers/RendezvousController.php';

$rc = new RendezvousController();

if (isset($_GET['id'])) {
    $rc->supprimerCreneau($_GET['id']);
}

header('Location: rendezvous_dashboard.php');
exit;
?>
