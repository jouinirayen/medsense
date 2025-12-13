<?php
header("Content-Type: application/json");


$input = json_decode(file_get_contents("php://input"), true);
$user_message = $input["message"] ?? "";


if (!$user_message) {
    echo json_encode(["reply" => "Message vide"]);
    exit;
}


$api_key = getenv('OPENAI_API_KEY');


$data = [
    "model" => "gpt-4.1-mini",
    "messages" => [
        ["role" => "user", "content" => $user_message]
    ],
    "max_tokens" => 500
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $api_key"
]);

$response = curl_exec($ch);
curl_close($ch);


$result = json_decode($response, true);


$reply = $result["choices"][0]["message"]["content"] ?? null;

if (!$reply) {
    echo json_encode([
        "reply" => "Erreur IA",
        "debug" => $result 
    ]);
    exit;
}

echo json_encode(["reply" => $reply]);
