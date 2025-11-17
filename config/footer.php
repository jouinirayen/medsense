<?php
/**
 * Pied de page du site
 */

// Calculer le chemin relatif vers la racine depuis le fichier qui inclut footer.php
$basePath = dirname(dirname(__FILE__)); // Racine du projet
$currentFile = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;
$currentDir = dirname($currentFile);

// Calculer la profondeur (nombre de niveaux à remonter)
$relativePath = str_replace('\\', '/', str_replace($basePath . '/', '', $currentDir . '/'));
$relativePath = trim($relativePath, '/');
$depth = $relativePath ? substr_count($relativePath, '/') + 1 : 0;
$rootPath = $depth > 0 ? str_repeat('../', $depth) : '';

$jsPath = $rootPath . JS_URL . 'script.js';
?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> - Tous droits réservés</p>
        </div>
    </footer>

    <script src="<?php echo $jsPath; ?>"></script>
</body>
</html>
