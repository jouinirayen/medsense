<?php
require_once '../../../controllers/ChatController.php';

header('Content-Type: application/json');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['message'])) {
    $chatController = new ChatController();
    $response = $chatController->handleChat($data['message']);
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'No message provided']);
}
