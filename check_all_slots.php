<?php
require_once 'config/config.php';
$pdo = (new config())->getConnexion();

echo "--- Column Type ---\n";
$stmt = $pdo->query("SHOW COLUMNS FROM rendezvous LIKE 'heureRdv'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($col);

echo "\n--- All Slots for Doctor 7 ---\n";
$stmt = $pdo->prepare("SELECT * FROM rendezvous WHERE idMedecin = ?");
$stmt->execute([7]);
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Count: " . count($slots) . "\n";
foreach ($slots as $s) {
    echo "ID: " . $s['idRDV'] . " | Time: " . $s['heureRdv'] . " | Status: " . $s['statut'] . "\n";
}
?>