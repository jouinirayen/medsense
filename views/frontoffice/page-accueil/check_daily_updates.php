<?php
// check_daily_updates.php
// Checks if daily tip or article needs refreshing (2 min rule) and returns data

require_once __DIR__ . '/../../controllers/ChatController.php';

header('Content-Type: application/json');

$response = [
    'tip' => null,
    'article' => null
];

$currentTimestamp = time();
$chatController = new ChatController();

// --- 1. Check Daily Tip ---
$tipCacheFile = 'partials/daily_tip.cache';
$tipContent = '';
$refreshTip = true;

if (file_exists($tipCacheFile)) {
    $cacheData = json_decode(file_get_contents($tipCacheFile), true);
    // If cache is fresh (less than 120s), use it
    if ($cacheData && isset($cacheData['timestamp']) && ($currentTimestamp - $cacheData['timestamp'] < 120)) {
        $refreshTip = false;
        $tipContent = $cacheData['content'];
    }
}

if ($refreshTip) {
    try {
        $tipContent = $chatController->generateDailyHealthTip();
        file_put_contents($tipCacheFile, json_encode([
            'timestamp' => $currentTimestamp,
            'content' => $tipContent
        ]));
        $response['tip_updated'] = true;
    } catch (Exception $e) {
        $tipContent = "Une pomme par jour éloigne le médecin pour toujours !";
    }
}
$response['tip'] = $tipContent;


// --- 2. Check Daily Article ---
$articleCacheFile = 'partials/daily_article.cache';
$articleData = null;
$refreshArticle = true;

if (file_exists($articleCacheFile)) {
    $cached = json_decode(file_get_contents($articleCacheFile), true);
    if ($cached && isset($cached['timestamp']) && ($currentTimestamp - $cached['timestamp'] < 120)) {
        $refreshArticle = false;
        $articleData = $cached['data'];
    }
}

if ($refreshArticle) {
    try {
        $jsonStr = $chatController->generateDailyArticle();
        $articleData = json_decode($jsonStr, true);

        if (!$articleData) {
            $articleData = [
                'title' => 'Santé au Quotidien',
                'content' => 'Prenez soin de vous chaque jour avec de petites habitudes saines.',
                'image_prompt' => 'Peaceful nature scene with sunlight'
            ];
        }

        file_put_contents($articleCacheFile, json_encode([
            'timestamp' => $currentTimestamp,
            'data' => $articleData
        ]));
        $response['article_updated'] = true;
    } catch (Exception $e) {
        // fail silent
    }
}

// Generate Image URL
if ($articleData) {
    $response['article'] = $articleData;
    $response['article']['imageUrl'] = 'https://image.pollinations.ai/prompt/' . urlencode($articleData['image_prompt'] . ', photorealistic, 4k, soft light');
}


echo json_encode($response);
