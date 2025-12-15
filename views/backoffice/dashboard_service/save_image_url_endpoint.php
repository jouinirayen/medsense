<?php
/**
 * Endpoint to Save Image from URL (Async Backend Saver)
 * Robust cURL implementation to avoid file_get_contents timeouts
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$imageUrl = $data['url'] ?? '';

if (empty($imageUrl)) {
    echo json_encode(['success' => false, 'error' => 'URL manquante']);
    exit;
}

// Security: Basic check (optional but good)
// if (strpos($imageUrl, 'pollinations.ai') === false) { ... }

try {
    $uploadDir = '../../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = 'service_gen_' . time() . '_' . rand(1000, 9999) . '.jpg';
    $filePath = $uploadDir . $fileName;

    // Use cURL for better reliability
    $ch = curl_init($imageUrl);
    $fp = fopen($filePath, 'wb');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30s timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix SSL issues in local XAMPP

    curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    fclose($fp);

    if ($error) {
        // Clean up empty file
        if (file_exists($filePath))
            unlink($filePath);
        throw new Exception("Erreur cURL: " . $error);
    }

    if ($httpCode !== 200) {
        if (file_exists($filePath))
            unlink($filePath);
        throw new Exception("Erreur HTTP: " . $httpCode);
    }

    // Verify it's an image
    if (filesize($filePath) < 100) {
        unlink($filePath);
        throw new Exception("Fichier téléchargé invalide/vide.");
    }

    echo json_encode([
        'success' => true,
        'path' => 'uploads/' . $fileName
    ]);

} catch (Exception $e) {
    if (isset($filePath) && file_exists($filePath))
        unlink($filePath); // Cleanup
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
