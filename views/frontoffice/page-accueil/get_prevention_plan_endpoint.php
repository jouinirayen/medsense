<?php
header('Content-Type: application/json');
require_once '../../../controllers/ChatController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $age = $data['age'] ?? '';
    $gender = $data['gender'] ?? '';

    if (empty($age) || empty($gender)) {
        echo json_encode(['error' => 'Veuillez remplir tous les champs.']);
        exit;
    }

    try {
        $chatController = new ChatController();
        $jsonResponse = $chatController->generatePreventionPlan($age, $gender);
        echo $jsonResponse;
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>