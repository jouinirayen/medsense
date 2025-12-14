
<?php
header('Content-Type: application/json');

$api_key = 'AIzaSyCIh75IA3NwdMxmGA9ZEdk_aDmNt-7PVfs'; // Mets ici ta clé API Gemini

$data = json_decode(file_get_contents('php://input'), true);
$message = trim($data['text'] ?? '');

if (empty($message)) {
    echo json_encode(['response' => 'Pose-moi une question !']);
    exit;
}

/*
   PROMPT OFFICIEL MEDSENSE (version enrichie + anti-safety)
*/
$systemPrompt = "
Tu es l’assistant IA officiel de MedSense, une plateforme professionnelle 
d'information générale sur la santé, l'hygiène de vie et le bien-être. 
Tu ne donnes jamais de diagnostic ni de conseils médicaux personnalisés.

Style attendu :
• toujours en français
• clair, structuré, pédagogique et bien expliqué
• réponses enrichies, avec des points • quand utile
• ton professionnel, neutre et bienveillant
• tu adaptes la longueur selon la question
• tu ne dis jamais que tu es une IA

Règle importante :
• Tu ne commences jamais par une salutation, sauf si l’utilisateur dit « Bonjour ».

Exemple si l’utilisateur dit « Bonjour » :
→ « Bonjour ! En quoi puis-je vous aider aujourd’hui ? »

Exemple pour une question de contenu :
→ réponse directement utile, sans introduction.

Question de l'utilisateur :
$message
";

/*
   PAYLOAD JSON → structure officielle Gemini 2.5 v1beta
*/
$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $message]
            ]
        ]
    ],
    "system_instruction" => [
        "parts" => [
            ["text" => $systemPrompt]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 600
    ]
];

// Endpoint officiel
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";

// cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-goog-api-key: ' . $api_key
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

/*
   DEBUG LOGS — très utiles pour XAMPP
*/
error_log("=== GEMINI DEBUG ===");
error_log("HTTP Code: $http_code");
error_log("cURL Error: $curl_error");
error_log("Message envoyé: $message");
error_log("Payload JSON: " . json_encode($payload));
error_log("Réponse brute: " . $response);

if ($curl_error || $http_code !== 200) {
    echo json_encode(['response' => "IA indisponible ($http_code). Réessaie."]);
    exit;
}

$result = json_decode($response, true);

/*
   EXTRACTION SÉCURISÉE
*/
$candidate = $result['candidates'][0] ?? null;

if (!$candidate) {
    echo json_encode(['response' => "Erreur interne API. Réessaie."]);
    exit;
}

$finishReason = $candidate['finishReason'] ?? 'UNKNOWN';
$safetyRatings = json_encode($candidate['safetyRatings'] ?? []);

error_log("FinishReason: $finishReason");
error_log("Safety Ratings: $safetyRatings");

// Si Gemini bloque pour raisons Safety
if (
    $finishReason === 'SAFETY' ||
    strpos($safetyRatings, 'BLOCK') !== false ||
    strpos($safetyRatings, 'HIGH') !== false
) {
    echo json_encode([
        'response' => "Contenu sensible détecté. Reformule en ajoutant : « information générale sur le bien-être »."
    ]);
    exit;
}

$content = $candidate['content'] ?? null;
$text = $content['parts'][0]['text'] ?? '';

if (empty(trim($text))) {
    echo json_encode(['response' => "Réponse vide de l’API. Reformule ta question."]);
    exit;
}

echo json_encode(['response' => trim($text)]);
?>
