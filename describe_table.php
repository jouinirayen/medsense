<?php
require_once 'config/config.php';
$pdo = (new config())->getConnexion();
$stmt = $pdo->query("DESCRIBE rendezvous");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . " | " . $col['Key'] . "\n";
}
?>