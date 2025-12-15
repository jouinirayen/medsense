<?php
// badwords.php → Version corrigée 2025 avec API APILayer Bad Words (endpoint correct + parsing adapté)

$API_KEY = "AIzaSyCnYJxvMskAGbOZRTS4-PxhkCX0HRsYews"; // Remplace par une VRAIE clé APILayer (inscris-toi sur apilayer.com et prends une clé pour "Bad Words API")

function containsBadWords($text) {
    global $API_KEY;

    // 1. Liste locale rapide (ajoute plus de mots si besoin, y compris anglais/variantes)
    $localBad = [
        'con', 'connard', 'salope', 'fdp', 'pute', 'merde', 'enculé', 'nique', 'ntm', 'tg',
        'shit', 'fuck', 'bitch', 'asshole', 'bastard', 'damn', 'cunt' // Ajoute des mots anglais courants
    ];
    if (preg_match('/\b(' . implode('|', $localBad) . ')\b/i', $text)) {
        return true;
    }

    // 2. Appel à l'API Bad Words (endpoint CORRECT)
    $url = "https://api.apilayer.com/bad_words"; // ← CHANGEMENT ICI (pas /moderator)

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $text); // Envoie le texte brut
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: text/plain",
        "apikey: $API_KEY"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Log pour debug (enlève en prod)
    error_log("API Response: $response | HTTP Code: $httpCode");

    if ($httpCode !== 200) {
        // Si API down → on laisse passer ou bloque tout ? Ici, on retourne false pour ne pas bloquer
        return false;
    }

    $data = json_decode($response, true);

    // Parsing adapté à Bad Words API (retourne bad_words_total et bad_words_list)
    return isset($data['bad_words_total']) && $data['bad_words_total'] > 0;
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