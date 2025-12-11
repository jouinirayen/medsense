<?php
// badwords.php  →  Version 2025 avec clé API (la plus puissante)

$API_KEY = "AIzaSyCHUt8Bd0qm0VGW1cBug0AtoGy7_mRUm_U"; // COLLE TA CLÉ ENTRE LES GUILLEMETS

function containsBadWords($text) {
    global $API_KEY;

    // 1. Liste locale rapide (au cas où l’API est lente)
    $localBad = ['con', 'connard', 'salope', 'fdp', 'pute', 'merde', 'enculé', 'nique', 'ntm', 'tg'];
    if (preg_match('/\b(' . implode('|', $localBad) . ')\b/i', $text)) {
        return true;
    }

    // 2. Appel à l’API Moderator (très puissante)
    $url = "https://api.apilayer.com/moderator";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $text);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: text/plain",
        "apikey: $API_KEY"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    // Si l’API dit qu’il y a du contenu inapproprié
    return !empty($data['has_bad']) || !empty($data['bad_words_list']);
}

// Réponse AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $text = $input['text'] ?? '';

    header('Content-Type: application/json');
    echo json_encode([
        'bad' => containsBadWords($text)
    ]);
    exit;
}
?>