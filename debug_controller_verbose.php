    $reservation = new Reservation(null, $doctorId, $time, $nom, $prenom, 'pris', $patientId);

    $dId = $reservation->getIdMedecin();
    $t = $reservation->getHeureRdv();
    $n = $reservation->getPatientNom();
    $p = $reservation->getPatientPrenom();
    $pId = $reservation->getIdPatient();

    echo "Checking if slot taken...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rendezvous WHERE idMedecin = ? AND heureRdv = ? AND statut = 'pris'");
    $stmt->execute([$dId, $t]);
    if ($stmt->fetchColumn() > 0) {
        echo "Slot ALREADY TAKEN.\n";
    } else {
        echo "Slot AVAILABLE.\n";
    }

    echo "Attempting INSERT with values: $dId, $t, $n, $p, $pId\n";
    $stmt = $pdo->prepare("INSERT INTO rendezvous (idMedecin, heureRdv, patientNom, patientPrenom, statut, idPatient) VALUES (?, ?, ?, ?, 'pris', ?)");
    $stmt->execute([$dId, $t, $n, $p, $pId]);
    echo "INSERT SUCCESSFUL.\n";

    // Cleanup
    $pdo->prepare("DELETE FROM rendezvous WHERE idMedecin = ? AND heureRdv = ?")->execute([$dId, $t]);
    echo "Cleanup done.\n";

} catch (PDOException $e) {
    echo "PDO ERROR: " . $e->getMessage() . "\n";
}
?>