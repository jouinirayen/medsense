<?php
// Simple File-Based Cache for Daily Article
require_once __DIR__ . '/../../../../controllers/ChatController.php';

$articleCacheFile = __DIR__ . '/daily_article.cache';
$currentTimestamp = time();
$_articleData = null;

// Check if cache exists and is fresh (less than 2 minutes old)
$refreshArticle = true;

if (file_exists($articleCacheFile)) {
    $cached = json_decode(file_get_contents($articleCacheFile), true);
    if ($cached && isset($cached['timestamp']) && ($currentTimestamp - $cached['timestamp'] < 120)) {
        $refreshArticle = false;
        $_articleData = $cached['data'];
    }
}

if ($refreshArticle) {
    try {
        $chat = new ChatController();
        $jsonStr = $chat->generateDailyArticle();
        $_articleData = json_decode($jsonStr, true);

        if (!$_articleData) {
            // Fallback if JSON parse fails
            $_articleData = [
                'title' => 'Santé au Quotidien',
                'content' => 'Prenez soin de vous chaque jour avec de petites habitudes saines.',
                'image_prompt' => 'Peaceful nature scene with sunlight, medical wellness concept'
            ];
        }

        // Save to cache
        file_put_contents($articleCacheFile, json_encode([
            'timestamp' => $currentTimestamp,
            'data' => $_articleData
        ]));
    } catch (Exception $e) {
        // Silent fail
    }
}

// Ensure unique image each day based on prompt
$imageUrl = 'https://image.pollinations.ai/prompt/' . urlencode($_articleData['image_prompt'] . ', photorealistic, 4k, soft light');
?>

<!-- Daily Article Section -->
<section style="max-width: 1100px; margin: 40px auto; padding: 0 15px;">
    <h2 style="font-size: 1.8rem; color: #1e293b; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-newspaper" style="color: #4f46e5;"></i> À la Une aujourd'hui
    </h2>

    <div class="article-card" style="
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px -5px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s;
    ">
        <!-- Image Section -->
        <div style="height: 300px; overflow: hidden; position: relative;">
            <img src="<?php echo $imageUrl; ?>" alt="Article Image"
                style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
            <div style="
                position: absolute; 
                top: 20px; 
                left: 20px; 
                background: rgba(255,255,255,0.9); 
                padding: 5px 15px; 
                border-radius: 20px; 
                font-size: 0.8rem; 
                font-weight: 700; 
                color: #4f46e5;
                text-transform: uppercase;
            ">
                Dossier Santé
            </div>
        </div>

        <!-- Content Section -->
        <div style="padding: 30px;">
            <h3 style="margin: 0 0 15px 0; font-size: 1.5rem; color: #0f172a; line-height: 1.3;">
                <?php echo htmlspecialchars($_articleData['title']); ?>
            </h3>
            <p style="color: #475569; font-size: 1.1rem; line-height: 1.6; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_articleData['content']); ?>
            </p>
            <button style="
                background: transparent; 
                border: 2px solid #4f46e5; 
                color: #4f46e5; 
                padding: 10px 20px; 
                border-radius: 10px; 
                font-weight: 600; 
                cursor: pointer; 
                transition: all 0.2s;
            " onmouseover="this.style.background='#4f46e5';this.style.color='white'"
                onmouseout="this.style.background='transparent';this.style.color='#4f46e5'">
                Lire la suite
            </button>
        </div>
    </div>
</section>

<style>
    @media (min-width: 768px) {
        .article-card {
            flex-direction: row !important;
            align-items: center;
        }

        .article-card>div:first-child {
            width: 50%;
            height: 350px !important;
        }

        .article-card>div:last-child {
            width: 50%;
        }
    }
</style>