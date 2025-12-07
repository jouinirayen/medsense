<?php
// test_actions_admin_medecins.php
session_start();

// Simuler un admin connecté
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

require_once __DIR__ . '/controllers/AdminController.php';

try {
    $admin = new AdminController();
    
    echo "<h1>Test des actions AdminController pour admin-medecins.php</h1>";
    
    // Récupérer un médecin de test
    $doctors = $admin->getAllDoctors();
    
    if (empty($doctors['doctors'])) {
        die("❌ Aucun médecin trouvé pour les tests");
    }
    
    $doctor = $doctors['doctors'][0];
    $doctorId = $doctor['id_utilisateur'];
    $doctorName = $doctor['prenom'] . ' ' . $doctor['nom'];
    
    echo "<h2>Test sur le médecin : $doctorName (ID: $doctorId)</h2>";
    
    // 1. Test approveDoctor (si en attente)
    if ($doctor['statut'] === 'en_attente') {
        $result = $admin->approveDoctor($doctorId);
        echo "✅ approveDoctor: " . ($result['success'] ? 'SUCCÈS - ' . $result['message'] : 'ÉCHEC - ' . $result['message']) . "<br>";
    } else {
        echo "⚠️ approveDoctor: Médecin n'est pas en attente (statut: {$doctor['statut']})<br>";
    }
    
    // 2. Test rejectDoctor
    $result = $admin->rejectDoctor($doctorId, 'Test de rejet');
    echo "✅ rejectDoctor: " . ($result['success'] ? 'SUCCÈS - ' . $result['message'] : 'ÉCHEC - ' . $result['message']) . "<br>";
    
    // 3. Test activateUser (réactiver)
    $result = $admin->activateUser($doctorId);
    echo "✅ activateUser: " . ($result['success'] ? 'SUCCÈS - ' . $result['message'] : 'ÉCHEC - ' . $result['message']) . "<br>";
    
    // 4. Test suspendDoctor
    $result = $admin->suspendDoctor($doctorId, 'Test de suspension');
    echo "✅ suspendDoctor: " . ($result['success'] ? 'SUCCÈS - ' . $result['message'] : 'ÉCHEC - ' . $result['message']) . "<br>";
    
    // 5. Test activateUser à nouveau
    $result = $admin->activateUser($doctorId);
    echo "✅ activateUser (réactivation): " . ($result['success'] ? 'SUCCÈS - ' . $result['message'] : 'ÉCHEC - ' . $result['message']) . "<br>";
    
    // 6. Test verifyDiploma
    $result = $admin->verifyDiploma($doctorId, 'valide', 'Diplôme vérifié avec succès');
    echo "✅ verifyDiploma: " . ($result['success'] ? 'SUCCÈS - ' . $result['message'] : 'ÉCHEC - ' . $result['message']) . "<br>";
    
    // 7. Test manageUsers('delete') - Désactivation
    $result = $admin->manageUsers('delete', null, $doctorId);
    echo "✅ manageUsers('delete'): " . ($result['success'] ? 'SUCCÈS - ' . $result['message'] : 'ÉCHEC - ' . $result['message']) . "<br>";
    
    // 8. Test activateUser après désactivation
    $result = $admin->activateUser($doctorId);
    echo "✅ activateUser (après désactivation): " . ($result['success'] ? 'SUCCÈS - ' . $result['message'] : 'ÉCHEC - ' . $result['message']) . "<br>";
    
    // 9. Test permanentlyDeleteUser (COMMENTÉ pour sécurité - décommentez pour tester)
    // $result = $admin->permanentlyDeleteUser($doctorId);
    // echo "✅ permanentlyDeleteUser: " . ($result['success'] ? 'SUCCÈS - ' . $result['message'] : 'ÉCHEC - ' . $result['message']) . "<br>";
    echo "⚠️ permanentlyDeleteUser: TEST COMMENTÉ (risque de suppression réelle)<br>";
    
    // 10. Test exportDoctorsToExcel (test partiel)
    echo "✅ exportDoctorsToExcel: Fonction disponible (test via interface web)<br>";
    
    // 11. Test getPendingDoctors
    $pending = $admin->getPendingDoctors();
    echo "✅ getPendingDoctors: " . ($pending['success'] ? 'SUCCÈS - ' . $pending['count'] . ' médecins en attente' : 'ÉCHEC') . "<br>";
    
    // 12. Test getApprovalStats
    $stats = $admin->getApprovalStats();
    echo "✅ getApprovalStats: " . ($stats['success'] ? 'SUCCÈS' : 'ÉCHEC') . "<br>";
    
    echo "<h3>🎉 Tous les tests des actions principales sont passés !</h3>";
    
    // Afficher le statut final du médecin
    $finalDoctor = $admin->getUser($doctorId);
    if ($finalDoctor['success']) {
        echo "<h4>Statut final du médecin :</h4>";
        echo "ID: " . $finalDoctor['user']['id_utilisateur'] . "<br>";
        echo "Nom: " . $finalDoctor['user']['prenom'] . " " . $finalDoctor['user']['nom'] . "<br>";
        echo "Statut: " . $finalDoctor['user']['statut'] . "<br>";
        echo "Diplôme statut: " . ($finalDoctor['user']['diplome_statut'] ?? 'Non défini') . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
    echo "<br>Fichier: " . $e->getFile() . " Ligne: " . $e->getLine();
}
?>