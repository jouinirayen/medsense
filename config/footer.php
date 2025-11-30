<?php
// Calcul chemin racine pour accéder au JS
$basePath = dirname(__DIR__);
$currentDir = dirname($_SERVER['SCRIPT_FILENAME']);
$relativePath = str_replace($basePath, '', $currentDir);
$relativePath = trim($relativePath, '/');
$depth = $relativePath ? substr_count($relativePath, '/') + 1 : 0;
$rootPath = str_repeat('../', $depth);

// Chemin JS
$jsPath = $rootPath . 'css/script.js';
?>
</main>
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> MedSense - Tous droits réservés</p>
    </div>
</footer>
<script src="<?php echo $jsPath; ?>"></script>
</body>
</html>
