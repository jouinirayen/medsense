<?php
// Simple File-Based Cache for Daily Tip
require_once __DIR__ . '/../../../../controllers/ChatController.php';

$cacheFile = __DIR__ . '/daily_tip.cache';
$currentTimestamp = time();
$tipContent = '';

// Check if cache exists and is fresh (less than 2 minutes old)
$refreshCache = true;

if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    if ($cacheData && isset($cacheData['timestamp']) && ($currentTimestamp - $cacheData['timestamp'] < 120)) {
        $refreshCache = false;
        $tipContent = $cacheData['content'];
    }
}

if ($refreshCache) {
    try {
        $chatController = new ChatController();
        $tipContent = $chatController->generateDailyHealthTip();

        // Save to cache
        file_put_contents($cacheFile, json_encode([
            'timestamp' => $currentTimestamp,
            'content' => $tipContent
        ]));
    } catch (Exception $e) {
        $tipContent = "Une pomme par jour éloigne le médecin pour toujours !";
    }
}
?>

<!-- HTML Widget for Daily Tip -->
<div class="daily-tip-widget" style="
    max-width: 1100px;
    margin: 30px auto 10px auto;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(147, 197, 253, 0.3);
    border-radius: 20px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 25px;
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease;
">
    <!-- Avatar Container -->
    <div style="
        width: 70px;
        height: 70px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
        overflow: hidden;
        flex-shrink: 0;
    ">
        <img src="/projet_unifie/views/images/doctor_tip_avatar.png" alt="Docteur Medsense"
            style="width: 100%; height: 100%; object-fit: cover;">
    </div>

    <!-- Content -->
    <div style="flex-grow: 1; z-index: 1;">
        <h4 style="
            margin: 0 0 6px 0; 
            color: #2563eb; 
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            font-weight: 700;
        ">
            Conseil Santé du Jour
        </h4>
        <p style="
            margin: 0; 
            color: #1e293b; 
            font-size: 1.15rem; 
            font-weight: 500; 
            line-height: 1.5;
        ">
            « <?php echo htmlspecialchars($tipContent); ?> »
        </p>
    </div>

    <!-- Decorative Elements -->
    <div style="
        position: absolute;
        right: -30px;
        top: -30px;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    "></div>
</div>

<style>
    .daily-tip-widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.15) !important;
        border-color: rgba(59, 130, 246, 0.4) !important;
    }
</style>