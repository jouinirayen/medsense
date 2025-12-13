<?php
/**
 * AJAX Endpoint for AI Description Generator
 * Receives 'service_name' from POST
 * Returns JSON { "success": true, "description": "..." }
 */

require_once '../../../controllers/ChatController.php';

// Check Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

// Get Data
$data = json_decode(file_get_contents('php://input'), true);
$serviceName = $data['service_name'] ?? '';

if (empty($serviceName)) {
    echo json_encode(['success' => false, 'error' => 'Nom du service manquant']);
    exit;
}

$chatController = new ChatController();

// Call AI Logic
$description = $chatController->generateServiceDescription($serviceName);

if ($description && $description !== "Impossible de générer une description.") {
    echo json_encode([
        'success' => true,
        'description' => $description
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Generation failed'
    ]);
}
