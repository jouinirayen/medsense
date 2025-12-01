<?php
require_once 'config/config.php';
$pdo = (new config())->getConnexion();
$stmt = $pdo->prepare("SELECT * FROM rendezvous WHERE idMedecin = ? AND heureRdv = ?");
$stmt->execute([7, '11:00']);
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Slots found: " . count($slots) . "\n";
foreach ($slots as $s) {
    echo "ID: " . $s['idRDV'] . " | Status: " . $s['statut'] . "\n";
}
?>