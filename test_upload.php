<?php
// test_upload.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de structure des dossiers</h2>";

// Vérifier la racine du projet
$root = $_SERVER['DOCUMENT_ROOT'];
echo "Document root: $root<br>";

// Vérifier le dossier uploads
$uploadsDir = $root . '/uploads/diplomes/';
echo "Uploads directory: $uploadsDir<br>";
echo "Exists: " . (is_dir($uploadsDir) ? 'YES' : 'NO') . "<br>";
echo "Writable: " . (is_writable($uploadsDir) ? 'YES' : 'NO') . "<br>";

// Vérifier le chemin depuis différents points
echo "<h3>Test depuis sign-up-medecin.php</h3>";
$testPath = '../../../uploads/diplomes/';
echo "Chemin relatif (../../../): $testPath<br>";
echo "Chemin absolu: " . realpath($testPath) . "<br>";

echo "<h3>Test depuis AdminController.php</h3>";
$adminPath = __DIR__ . '/controllers/backoffice/../../uploads/diplomes/';
echo "Chemin AdminController: $adminPath<br>";
echo "Chemin absolu: " . realpath($adminPath) . "<br>";

// Créer un fichier test
$testFile = $uploadsDir . 'test.txt';
if (file_put_contents($testFile, 'Test content')) {
    echo "<p style='color:green'>Fichier test créé avec succès: $testFile</p>";
    unlink($testFile);
} else {
    echo "<p style='color:red'>Échec création fichier test</p>";
}
?>