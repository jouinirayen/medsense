<?php
/**
 * AJAX Endpoint for AI Smart Search
 * Receives 'query' from POST
 * Returns JSON { "success": true, "match": "Dentiste" } or { "success": false }
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
$userQuery = $data['query'] ?? '';

if (empty($userQuery)) {
    echo json_encode(['success' => false, 'error' => 'Empty query']);
    exit;
}

$chatController = new ChatController();

// Call AI Logic
$resultName = $chatController->analyzeSearchQuery($userQuery);

if ($resultName !== "0" && !empty($resultName)) {
    echo json_encode([
        'success' => true,
        'match' => $resultName,
        'original_query' => $userQuery
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No match found'
    ]);
}
