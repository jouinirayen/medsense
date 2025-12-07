<?php
require_once __DIR__ . '/../models/Utilisateur.php';

class AdminController {
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_secure' => false,
                'cookie_httponly' => true,
                'cookie_samesite' => 'Strict'
            ]);
        }
        
        if (!$this->isAdmin()) {
            header('Location: ../../frontoffice/auth/sign-in.php');
            exit;
        }
        require_once __DIR__ . '/../config.php';
        $this->pdo = config::getConnexion();
    }

    public function dashboard(): array {
        $stats = $this->getStats();
        $recentUsers = $this->getRecentUsers(5);
        $pendingDoctors = $this->getPendingDoctors();
        
        return [
            "success" => true,
            "stats" => $stats,
            "recentUsers" => $recentUsers,
            "pendingDoctors" => $pendingDoctors
        ];
    }

    private function getStats(): array {
        try {
            $req = $this->pdo->query('SELECT COUNT(*) as total FROM utilisateur');
            $total = $req->fetch(PDO::FETCH_ASSOC)['total'];
            
            $req = $this->pdo->query(
                "SELECT COUNT(*) as new_this_month FROM utilisateur 
                 WHERE MONTH(date_inscription) = MONTH(CURRENT_DATE()) 
                 AND YEAR(date_inscription) = YEAR(CURRENT_DATE())"
            );
            $newThisMonth = $req->fetch(PDO::FETCH_ASSOC)['new_this_month'];
            
            $req = $this->pdo->query(
                'SELECT role, COUNT(*) as count FROM utilisateur GROUP BY role'
            );
            $byRole = $req->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total' => $total,
                'new_this_month' => $newThisMonth,
                'by_role' => $byRole
            ];
        } catch (Exception $e) {
            return ['total' => 0, 'new_this_month' => 0, 'by_role' => []];
        }
    }

    private function getRecentUsers($limit = 5): array {
        try {
            $req = $this->pdo->prepare(
                "SELECT id_utilisateur, nom, prenom, email, date_inscription, role 
                 FROM utilisateur 
                 ORDER BY date_inscription DESC 
                 LIMIT ?"
            );
            $req->bindValue(1, $limit, PDO::PARAM_INT);
            $req->execute();
            
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function manageUsers($action, $data = null, $userId = null): array {
        switch ($action) {
            case 'list':
                return $this->listUsers($data ?? []);
            case 'create':
                return $this->createUser($data);
            case 'update':
                return $this->updateUser($userId, $data);
            case 'delete':
                return $this->deactivateUser($userId);
            case 'permanent_delete':
                return $this->permanentlyDeleteUser($userId);
            case 'get':
                return $this->getUser($userId);
            default:
                return ["success" => false, "message" => "Action non reconnue"];
        }
    }

    private function listUsers($filters = []): array {
        try {
            $sql = 'SELECT * FROM utilisateur WHERE 1=1';
            $params = [];
            
            if (!empty($filters['search'])) {
                $sql .= ' AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)';
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['role'])) {
                $sql .= ' AND role = ?';
                $params[] = $filters['role'];
            }
            
            if (!empty($filters['statut'])) {
                $sql .= ' AND statut = ?';
                $params[] = $filters['statut'];
            }
            
            $sql .= ' ORDER BY date_inscription DESC';
            
            $req = $this->pdo->prepare($sql);
            $req->execute($params);
            
            $users = $req->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                "success" => true,
                "users" => $users,
                "count" => count($users)
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    private function createUser($data): array {
        try {
            $required = ['nom', 'prenom', 'email', 'mot_de_passe', 'role', 'statut'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ["success" => false, "message" => "Le champ $field est obligatoire"];
                }
            }
            
            $check = $this->pdo->prepare('SELECT id_utilisateur FROM utilisateur WHERE email = ?');
            $check->execute([$data['email']]);
            if ($check->fetch()) {
                return ["success" => false, "message" => "Cet email est déjà utilisé"];
            }
            
            $hashedPassword = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
            
            $sql = 'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, statut, date_inscription) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())';
            $req = $this->pdo->prepare($sql);
            $success = $req->execute([
                htmlspecialchars($data['nom']),
                htmlspecialchars($data['prenom']),
                htmlspecialchars($data['email']),
                $hashedPassword,
                $data['role'],
                $data['statut']
            ]);
            
            if ($success) {
                return ["success" => true, "message" => "Utilisateur créé avec succès"];
            }
            
            return ["success" => false, "message" => "Erreur lors de la création"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function getApprovalStats(): array {
        try {
            $req = $this->pdo->prepare(
                'SELECT statut, COUNT(*) as count 
                 FROM utilisateur 
                 WHERE role = "medecin" 
                 GROUP BY statut'
            );
            $req->execute();
            $statusStats = $req->fetchAll(PDO::FETCH_ASSOC);
            
            $req = $this->pdo->prepare(
                'SELECT COUNT(*) as count 
                 FROM utilisateur 
                 WHERE role = "medecin" 
                 AND statut = "actif"
                 AND YEARWEEK(date_inscription, 1) = YEARWEEK(CURDATE(), 1)'
            );
            $req->execute();
            $weeklyApproved = $req->fetch(PDO::FETCH_ASSOC);
            
            return [
                "success" => true,
                "status_stats" => $statusStats,
                "weekly_approved" => $weeklyApproved['count'] ?? 0
            ];
        } catch (Exception $e) {
            return [
                "success" => false, 
                "message" => "Erreur: " . $e->getMessage(),
                "status_stats" => [],
                "weekly_approved" => 0
            ];
        }
    }

    private function updateUser($userId, $data): array {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }
            
            $updates = [];
            $params = [];
            
            $allowedFields = ['nom', 'prenom', 'email', 'role', 'statut'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = htmlspecialchars($data[$field]);
                }
            }
            
            if (empty($updates)) {
                return ["success" => false, "message" => "Aucune donnée à mettre à jour"];
            }
            
            $params[] = $userId;
            $sql = "UPDATE utilisateur SET " . implode(', ', $updates) . " WHERE id_utilisateur = ?";
            
            $req = $this->pdo->prepare($sql);
            $success = $req->execute($params);
            
            if ($success) {
                return ["success" => true, "message" => "Utilisateur mis à jour avec succès"];
            }
            
            return ["success" => false, "message" => "Erreur lors de la mise à jour"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function deactivateUser($userId): array {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }
            
            if ($userId == $_SESSION['user_id']) {
                return ["success" => false, "message" => "Vous ne pouvez pas désactiver votre propre compte"];
            }
            
            $req = $this->pdo->prepare(
                "UPDATE utilisateur SET statut = 'inactif' WHERE id_utilisateur = ?"
            );
            $success = $req->execute([$userId]);
            
            if ($success) {
                return ["success" => true, "message" => "Utilisateur désactivé avec succès"];
            }
            
            return ["success" => false, "message" => "Erreur lors de la désactivation"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    private function getUser($userId): array {
        try {
            $user = $this->getUserById($userId);
            
            if (!$user) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }
            
            unset($user['mot_de_passe']);
            
            return [
                "success" => true,
                "user" => $user
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function activateUser($userId): array {
        try {
            $user = $this->getUserById($userId);
            
            if (!$user) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }
            
            $req = $this->pdo->prepare(
                "UPDATE utilisateur SET statut = 'actif' WHERE id_utilisateur = ?"
            );
            $success = $req->execute([$userId]);
            
            if ($success) {
                return ["success" => true, "message" => "Utilisateur activé avec succès"];
            }
            
            return ["success" => false, "message" => "Erreur lors de l'activation"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function getAllUsers($filters = []): array {
        return $this->listUsers($filters);
    }

 public function approveDoctor($userId): array {
    try {
        $user = $this->getUserById($userId);
        
        if (!$user) {
            return ["success" => false, "message" => "Utilisateur non trouvé"];
        }

        if ($user['role'] !== 'medecin') {
            return ["success" => false, "message" => "Cet utilisateur n'est pas un médecin"];
        }

        // TEMPORAIRE : Ne pas vérifier le diplôme pour le test
        // if (empty($user['diplome_path'])) {
        //     return ["success" => false, "message" => "Le médecin n'a pas de diplôme uploadé"];
        // }

        $req = $this->pdo->prepare(
            "UPDATE utilisateur SET statut = 'actif' WHERE id_utilisateur = ?"
        );
        $success = $req->execute([$userId]);
        
        if ($success) {
            return [
                "success" => true, 
                "message" => "Médecin approuvé avec succès"
            ];
        }
        
        return ["success" => false, "message" => "Erreur lors de la mise à jour"];
    } catch (Exception $e) {
        return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
    }
}

    public function rejectDoctor($userId, $reason = ''): array {
        try {
            $user = $this->getUserById($userId);
            
            if (!$user) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }

            if ($user['role'] !== 'medecin') {
                return ["success" => false, "message" => "Cet utilisateur n'est pas un médecin"];
            }

            $req = $this->pdo->prepare(
                "UPDATE utilisateur SET statut = 'rejeté' WHERE id_utilisateur = ?"
            );
            $success = $req->execute([$userId]);
            
            if ($success) {
                $message = "Médecin rejeté avec succès";
                if (!empty($reason)) {
                    $message .= " - Raison: " . $reason;
                }
                return ["success" => true, "message" => $message];
            }
            
            return ["success" => false, "message" => "Erreur lors de la mise à jour"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function suspendDoctor($userId, $reason = ''): array {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }

            if ($userId == $_SESSION['user_id']) {
                return ["success" => false, "message" => "Vous ne pouvez pas suspendre votre propre compte"];
            }

            if ($user['role'] !== 'medecin') {
                return ["success" => false, "message" => "Cet utilisateur n'est pas un médecin"];
            }

            $req = $this->pdo->prepare(
                "UPDATE utilisateur SET statut = 'suspendu' WHERE id_utilisateur = ?"
            );
            $success = $req->execute([$userId]);
            
            if ($success) {
                return ["success" => true, "message" => "Médecin suspendu avec succès"];
            }
            
            return ["success" => false, "message" => "Erreur lors de la mise à jour"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function getPendingDoctors(): array {
        try {
            $req = $this->pdo->prepare(
                'SELECT * FROM utilisateur 
                 WHERE role = "medecin" AND statut = "en_attente" 
                 ORDER BY date_inscription DESC'
            );
            $req->execute();
            
            $doctors = $req->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                "success" => true,
                "doctors" => $doctors,
                "count" => count($doctors)
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function getAllDoctors($filters = []): array {
        try {
            $sql = 'SELECT * FROM utilisateur WHERE role = "medecin"';
            $params = [];
            
            if (!empty($filters['statut'])) {
                $sql .= ' AND statut = ?';
                $params[] = $filters['statut'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= ' AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)';
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= ' ORDER BY date_inscription DESC';
            
            $req = $this->pdo->prepare($sql);
            $req->execute($params);
            
            $doctors = $req->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                "success" => true,
                "doctors" => $doctors,
                "count" => count($doctors)
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    private function getUserById($id): ?array {
        try {
            $req = $this->pdo->prepare(
                'SELECT * FROM utilisateur WHERE id_utilisateur = ?'
            );
            $req->execute([$id]);
            return $req->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Erreur getUserById: " . $e->getMessage());
            return null;
        }
    }

    private function isAdmin(): bool {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
public function handleApprovalRequest($data): array {
    $action = $data['action'] ?? '';
    $doctorId = $data['doctor_id'] ?? '';
    $reason = $data['reason'] ?? '';
    
    error_log("handleApprovalRequest - Action: " . $action . ", ID: " . $doctorId);
    
    if (empty($doctorId)) {
        return ["success" => false, "message" => "ID médecin manquant"];
    }
    
    switch ($action) {
        case 'approve':
            error_log("Appel de approveDoctor pour ID: " . $doctorId);
            return $this->approveDoctor($doctorId);
        case 'reject':
            return $this->rejectDoctor($doctorId, $reason);
        case 'suspend':
            return $this->suspendDoctor($doctorId, $reason);
        default:
            return ["success" => false, "message" => "Action non reconnue"];
    }
}

    public function exportUsersToExcel($filters = []): void {
        try {
            $result = $this->listUsers($filters);
            
            if (!$result['success']) {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }
            
            $users = $result['users'];
            
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="utilisateurs_' . date('Y-m-d_H-i') . '.xls"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
                  xmlns:x="urn:schemas-microsoft-com:office:excel"
                  xmlns="http://www.w3.org/TR/REC-html40">
                  <head>
                  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                  <style>
                  td { border: 1px solid #ddd; padding: 5px; font-size: 11px; }
                  th { border: 1px solid #ddd; padding: 8px; font-size: 12px; font-weight: bold; background-color: #f2f2f2; }
                  .title { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 20px; }
                  </style>
                  </head>
                  <body>';
            
            echo '<table border="1">';
            
            echo '<tr><td colspan="7" class="title">Liste des Utilisateurs</td></tr>';
            echo '<tr><td colspan="7">Généré le : ' . date('d/m/Y H:i') . '</td></tr>';
            echo '<tr><td colspan="7"></td></tr>';
            
            echo '<tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Date d\'inscription</th>
                  </tr>';
            
            foreach ($users as $user) {
                echo '<tr>
                        <td>' . $user['id_utilisateur'] . '</td>
                        <td>' . htmlspecialchars($user['nom']) . '</td>
                        <td>' . htmlspecialchars($user['prenom']) . '</td>
                        <td>' . htmlspecialchars($user['email']) . '</td>
                        <td>' . $user['role'] . '</td>
                        <td>' . $user['statut'] . '</td>
                        <td>' . date('d/m/Y', strtotime($user['date_inscription'])) . '</td>
                      </tr>';
            }
            
            echo '<tr><td colspan="7"></td></tr>';
            echo '<tr>
                    <td colspan="6" style="font-weight: bold; text-align: right;">Total d\'utilisateurs :</td>
                    <td style="font-weight: bold;">' . count($users) . '</td>
                  </tr>';
            
            echo '</table>';
            echo '</body></html>';
            
            exit;
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de l'export : " . $e->getMessage()
            ]);
            exit;
        }
    }

    public function permanentlyDeleteUser($userId): array {
        try {
            $user = $this->getUserById($userId);
            
            if (!$user) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }
            
            if ($userId == $_SESSION['user_id']) {
                return ["success" => false, "message" => "Vous ne pouvez pas supprimer votre propre compte"];
            }
            
            if ($user['role'] === 'admin') {
                return ["success" => false, "message" => "Impossible de supprimer un administrateur"];
            }
            
            // Supprimer les fichiers associés
            if (!empty($user['photo_profil'])) {
                $photoPath = __DIR__ . '/../../uploads/profiles/' . $user['photo_profil'];
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
            
            if (!empty($user['diplome_path'])) {
                $diplomePath = __DIR__ . '/../../uploads/diplomes/' . $user['diplome_path'];
                if (file_exists($diplomePath)) {
                    unlink($diplomePath);
                }
            }
            
            // Supprimer l'utilisateur
            $req = $this->pdo->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
            $success = $req->execute([$userId]);
            
            if ($success) {
                return ["success" => true, "message" => "Utilisateur supprimé définitivement"];
            }
            
            return ["success" => false, "message" => "Erreur lors de la suppression"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function verifyDiploma($doctorId, $status, $comment = ''): array {
        try {
            $user = $this->getUserById($doctorId);
            
            if (!$user) {
                return ["success" => false, "message" => "Médecin non trouvé"];
            }

            if ($user['role'] !== 'medecin') {
                return ["success" => false, "message" => "Cet utilisateur n'est pas un médecin"];
            }

            $req = $this->pdo->prepare(
                "UPDATE utilisateur SET 
                 diplome_statut = ?,
                 diplome_commentaire = ?,
                 diplome_date_verification = NOW()
                 WHERE id_utilisateur = ?"
            );
            $success = $req->execute([$status, $comment, $doctorId]);
            
            if ($success) {
                return ["success" => true, "message" => "Diplôme vérifié avec succès"];
            }
            
            return ["success" => false, "message" => "Erreur lors de la vérification"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    public function exportDoctorsToExcel(): void {
        try {
            $result = $this->getAllDoctors();
            
            if (!$result['success']) {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }
            
            $doctors = $result['doctors'];
            
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="medecins_' . date('Y-m-d_H-i') . '.xls"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
                  xmlns:x="urn:schemas-microsoft-com:office:excel"
                  xmlns="http://www.w3.org/TR/REC-html40">
                  <head>
                  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                  <style>
                  td { border: 1px solid #ddd; padding: 5px; font-size: 11px; }
                  th { border: 1px solid #ddd; padding: 8px; font-size: 12px; font-weight: bold; background-color: #f2f2f2; }
                  .title { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 20px; }
                  </style>
                  </head>
                  <body>';
            
            echo '<table border="1">';
            
            echo '<tr><td colspan="10" class="title">Liste des Médecins</td></tr>';
            echo '<tr><td colspan="10">Généré le : ' . date('d/m/Y H:i') . '</td></tr>';
            echo '<tr><td colspan="10"></td></tr>';
            
            echo '<tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Spécialité</th>
                    <th>Diplôme</th>
                    <th>Statut Diplôme</th>
                    <th>Statut Compte</th>
                    <th>Date Inscription</th>
                    <th>Dernière Connexion</th>
                  </tr>';
            
            foreach ($doctors as $doctor) {
                echo '<tr>
                        <td>' . $doctor['id_utilisateur'] . '</td>
                        <td>' . htmlspecialchars($doctor['nom']) . '</td>
                        <td>' . htmlspecialchars($doctor['prenom']) . '</td>
                        <td>' . htmlspecialchars($doctor['email']) . '</td>
                        <td>' . htmlspecialchars($doctor['specialite'] ?? 'Non spécifiée') . '</td>
                        <td>' . ($doctor['diplome_path'] ?? 'Non fourni') . '</td>
                        <td>' . ($doctor['diplome_statut'] ?? 'Non vérifié') . '</td>
                        <td>' . $doctor['statut'] . '</td>
                        <td>' . date('d/m/Y', strtotime($doctor['date_inscription'])) . '</td>
                        <td>' . (!empty($doctor['derniere_connexion']) ? date('d/m/Y H:i', strtotime($doctor['derniere_connexion'])) : 'Jamais') . '</td>
                      </tr>';
            }
            
            echo '<tr><td colspan="10"></td></tr>';
            echo '<tr>
                    <td colspan="9" style="font-weight: bold; text-align: right;">Total médecins :</td>
                    <td style="font-weight: bold;">' . count($doctors) . '</td>
                  </tr>';
            
            echo '</table>';
            echo '</body></html>';
            
            exit;
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de l'export : " . $e->getMessage()
            ]);
            exit;
        }
    }
}