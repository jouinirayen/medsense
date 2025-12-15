<?php
// translate.php – VERSION FINALE DÉCEMBRE 2025 (Gemini 2.5 Flash – stable & gratuit)
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$text = trim($data['text'] ?? '');

if (empty($text)) {
    echo json_encode(['error' => 'Texte vide']);
    exit;
}

// === CRÉE UNE NOUVELLE CLÉ FRAÎCHE ICI : https://aistudio.google.com/app/apikey ===
$api_key = "AIzaSyCLy5UQYdKymJ1KsIctvDeSIrZ8Te76lBg";  // Très important : une clé valide et récente !

$prompt = "Traduis ce texte en français, anglais et arabe.
Réponds EXACTEMENT avec ce format (rien d'autre, pas de markdown, pas de code) :

FR: [traduction française]
EN: [traduction anglaise]
AR: [traduction arabe]

Texte à traduire : \"$text\"";

$payload = [
    "contents" => [[
        "role" => "user",
        "parts" => [["text" => $prompt]]
    ]],
    "generationConfig" => [
        "temperature" => 0.2,
        "maxOutputTokens" => 500
    ]
];

// Modèle stable en décembre 2025
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$api_key";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Debug temporaire (regarde tes logs PHP pour voir l'erreur exacte)
error_log("Translator Gemini - HTTP: $http_code | Response: $response");

if ($http_code !== 200) {
    echo json_encode(['error' => "Erreur API Gemini (HTTP $http_code) – Clé invalide, quota dépassé ou problème réseau ?"]);
    exit;
}

$result = json_decode($response, true);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $trad = trim($result['candidates'][0]['content']['parts'][0]['text']);
    // Nettoie les blocs code markdown si Gemini en ajoute
    $trad = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $trad);
    echo json_encode(["response" => $trad]);
} else {
    $err = $result['error']['message'] ?? 'Réponse vide ou modèle invalide';
    echo json_encode(["error" => "Gemini erreur : $err"]);
}
?>