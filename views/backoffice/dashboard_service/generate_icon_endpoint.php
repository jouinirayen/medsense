<?php
require_once '../../../controllers/ChatController.php';

header('Content-Type: application/json');

/*
 * Endpoint: generate_icon_endpoint.php
 * Method: POST
 * Input: JSON { "service_name": "..." }
 * Output: JSON { "success": true, "icon": "fas fa-..." }
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $serviceName = $input['service_name'] ?? '';

    if (!empty($serviceName)) {
        $controller = new ChatController();
        $iconClass = $controller->generateIconClass($serviceName);

        echo json_encode([
            'success' => true,
            'icon' => $iconClass
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Service name is required'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid Request Method']);
}
