<?php
/**
 * Endpoint to generate Image Prompt (AI/LLM)
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

try {
    // Use ChatController (LLM) for high relevance English Description
    $controller = new ChatController();
    $prompt = $controller->generateVisualPrompt($serviceName);

    echo json_encode([
        'success' => true,
        'prompt' => $prompt
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la génération: ' . $e->getMessage()
    ]);
}
