<?php
require_once 'config/config.php';

try {
    $pdo = (new config())->getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $doctorId = 7;
    $time = '11:00';
    $nom = 'TestNom';
    $prenom = 'TestPrenom';
    $patientId = null; // Try with NULL first, as it should be allowed

    echo "--- START DEBUG ---\n";

    // Check doctor
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch();
    if ($doctor) {
        echo "Doctor ID $doctorId found.\n";
    } else {
        echo "Doctor ID $doctorId NOT FOUND.\n";
    }

    // Check existing
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rendezvous WHERE idMedecin = ? AND heureRdv = ? AND statut = 'pris'");
    $stmt->execute([$doctorId, $time]);
    $count = $stmt->fetchColumn();
    echo "Existing 'pris' slots: $count\n";

    // Attempt Insert
    $pdo->beginTransaction();
    echo "Attempting INSERT...\n";

    $stmt = $pdo->prepare("INSERT INTO rendezvous (idMedecin, heureRdv, patientNom, patientPrenom, statut, idPatient) VALUES (?, ?, ?, ?, 'pris', ?)");
    // We use NULL for patientId for this test
    $stmt->execute([$doctorId, $time, $nom, $prenom, $patientId]);

    echo "INSERT SUCCESSFUL (Rolling back now)\n";
    $pdo->rollBack();

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
echo "--- END DEBUG ---\n";
?>