<?php
header("Content-Type: application/json");

// Configuration sécurisée
$api_key = getenv('OPENAI_API_KEY'); // Utilisez toujours getenv pour les clés API

if (!$api_key) {
    echo json_encode([
        "reply" => "Erreur de configuration: Clé API non définie",
        "error" => "API_KEY_MISSING"
    ]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$user_message = $input["message"] ?? "";

if (!$user_message) {
    echo json_encode(["reply" => "Message vide"]);
    exit;
}

$data = [
    "model" => "gpt-4", // Modèle corrigé (gptaz-4.1-mini n'existe pas)
    "messages" => [
        ["role" => "user", "content" => $user_message]
    ],
    "max_tokens" => 500,
    "temperature" => 0.7
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $api_key"
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curl_error) {
    echo json_encode([
        "reply" => "Erreur de connexion à l'API",
        "error" => $curl_error
    ]);
    exit;
}

$result = json_decode($response, true);

if ($http_code !== 200) {
    echo json_encode([
        "reply" => "Erreur API OpenAI",
        "error_code" => $http_code,
        "error_detail" => $result['error']['message'] ?? 'Erreur inconnue'
    ]);
    exit;
}

$reply = $result["choices"][0]["message"]["content"] ?? null;

if (!$reply) {
    echo json_encode([
        "reply" => "Erreur de traitement de la réponse",
        "debug" => $result
    ]);
    exit;
}

echo json_encode(["reply" => $reply]);