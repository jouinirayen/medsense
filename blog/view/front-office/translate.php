<?php
// translate.php – VERSION FINALE DÉCEMBRE 2025 (Gemini 2.5 Flash – 100% stable & fonctionnel)
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$text = trim($data['text'] ?? '');

if (empty($text)) {
    echo json_encode(['error' => 'Texte vide']);
    exit;
}

// Colle TA NOUVELLE CLÉ ICI
$api_key = "AIzaSyAwTKq64IvMGM6HmVsD8qnGl1vfCaAlAcY";  // Ex: AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxx

$prompt = "Traduis ce texte en français, anglais et arabe.
Réponds EXACTEMENT avec ce format (rien d'autre, pas de code, pas d'explication) :

FR: [traduction en français]
EN: [traduction en anglais]
AR: [traduction en arabe]

Texte à traduire : \"$text\"";

$payload = [
    "contents" => [[
        "role" => "user",
        "parts" => [["text" => $prompt]]
    ]],
    "generationConfig" => [
        "temperature" => 0.1,
        "maxOutputTokens" => 500
    ]
];

// Modèle stable 2025 : Gemini 2.5 Flash (rapide & gratuit)
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$api_key";
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => false  // Garde pour tests ; true en prod
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(['error' => "HTTP $http_code – Vérifie ta clé ou quota"]);
    exit;
}

$result = json_decode($response, true);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $trad = $result['candidates'][0]['content']['parts'][0]['text'];
    // Nettoyage pour format parfait
    $trad = preg_replace('/^```(?:\w+)?\s*|\s*```$/m', '', $trad);
    $trad = trim($trad);
    echo json_encode(["response" => $trad]);
} else {
    $err = $result['error']['message'] ?? 'Réponse vide (modèle ou clé ?)';
    echo json_encode(["error" => "Gemini : $err"]);
}