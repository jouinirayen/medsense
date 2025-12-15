<?php
header('Content-Type: application/json');
require_once '../../../controllers/ChatController.php';
require_once '../../../controllers/ServiceController.php';

try {
    $serviceController = new ServiceController();
    $chatController = new ChatController();

    // 1. Get existing service names
    $services = $serviceController->obtenirTousLesServices();
    $serviceNames = array_map(function ($s) {
        return $s['name'];
    }, $services);

    // 2. Ask AI
    $jsonResponse = $chatController->analyzeMarketTrends($serviceNames);

    echo $jsonResponse;

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
