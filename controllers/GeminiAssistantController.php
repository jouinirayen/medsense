<?php
header("Content-Type: application/json");

// Configuration sécurisée
$api_key = getenv('OPENAI_API_KEY');

// Validation de la clé API
if (!$api_key || trim($api_key) === '') {
    echo json_encode([
        "reply" => "Erreur de configuration: Clé API non définie",
        "error" => "API_KEY_MISSING"
    ]);
    exit;
}

// Validation de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "reply" => "Méthode non autorisée",
        "error" => "METHOD_NOT_ALLOWED"
    ]);
    exit;
}

// Lecture et validation de l'entrée
$input = json_decode(file_get_contents("php://input"), true);

// Vérification du JSON valide
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "reply" => "Format JSON invalide",
        "error" => "INVALID_JSON"
    ]);
    exit;
}

$user_message = trim($input["message"] ?? "");

if ($user_message === "") {
    echo json_encode(["reply" => "Message vide"]);
    exit;
}

// Limiter la longueur du message pour éviter les abus
if (strlen($user_message) > 4000) {
    echo json_encode(["reply" => "Message trop long (max 4000 caractères)"]);
    exit;
}

$data = [
    "model" => "gpt-4",
    "messages" => [
        ["role" => "user", "content" => $user_message]
    ],
    "max_tokens" => 500,
    "temperature" => 0.7
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $api_key"
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2
]);

$response = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curl_error) {
    error_log("Erreur cURL OpenAI: " . $curl_error);
    echo json_encode([
        "reply" => "Erreur de connexion au service",
        "error" => "CONNECTION_ERROR"
    ]);
    exit;
}

$result = json_decode($response, true);

if ($http_code !== 200) {
    error_log("Erreur API OpenAI [$http_code]: " . json_encode($result['error'] ?? 'Unknown'));
    
    // Messages d'erreur plus précis selon le code HTTP
    $error_messages = [
        401 => "Clé API invalide ou expirée",
        429 => "Quota dépassé ou limite de taux",
        500 => "Erreur interne du serveur OpenAI",
        503 => "Service temporairement indisponible"
    ];
    
    $user_message = $error_messages[$http_code] ?? "Erreur API OpenAI";
    
    echo json_encode([
        "reply" => $user_message,
        "error_code" => $http_code,
        "error_detail" => $result['error']['message'] ?? 'Erreur inconnue'
    ]);
    exit;
}

$reply = $result["choices"][0]["message"]["content"] ?? null;

if (!$reply) {
    error_log("Réponse OpenAI vide: " . json_encode($result));
    echo json_encode([
        "reply" => "Erreur de traitement de la réponse",
        "error" => "EMPTY_RESPONSE"
    ]);
    exit;
}

// Nettoyer la réponse si nécessaire
$reply = trim($reply);

echo json_encode(["reply" => $reply]);