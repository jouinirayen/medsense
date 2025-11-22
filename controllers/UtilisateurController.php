<?php
require_once __DIR__ .'/../models/Utilisateur.php'; 
require_once __DIR__ . '/../config.php';
class UtilisateurController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // 🔹 CRUD complet pour la gestion admin
    public function getAllUsers() {
        try {
            $req = $this->pdo->query('SELECT * FROM utilisateur ORDER BY date_inscription DESC');
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getUserById($id) {
        try {
            $req = $this->pdo->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = ?');
            $req->execute([$id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function createUser($user) {
        try {
            $req = $this->pdo->prepare(
                'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, dateNaissance, adresse, role, statut) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $hashedPassword = password_hash($user->getMotDePasse(), PASSWORD_DEFAULT);

            $req->execute([
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $hashedPassword,
                $user->getDateNaissance(),
                $user->getAdresse(),
                $user->getRole(),
                $user->getStatut()
            ]);

            return array("success" => true, "message" => "Utilisateur créé avec succès");
        } catch (Exception $e) {
            return array("success" => false, "message" => "Erreur: " . $e->getMessage());
        }
    }

    public function updateUser($id, $user) {
        try {
            $req = $this->pdo->prepare(
                'UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, dateNaissance = ?, adresse = ?, role = ?, statut = ? 
                 WHERE id_utilisateur = ?'
            );

            $req->execute([
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getDateNaissance(),
                $user->getAdresse(),
                $user->getRole(),
                $user->getStatut(),
                $id
            ]);

            return array("success" => true, "message" => "Utilisateur modifié avec succès");
        } catch (Exception $e) {
            return array("success" => false, "message" => "Erreur: " . $e->getMessage());
        }
    }

    public function deleteUser($id) {
        try {
            $req = $this->pdo->prepare('DELETE FROM utilisateur WHERE id_utilisateur = ?');
            $req->execute([$id]);
            return array("success" => true, "message" => "Utilisateur supprimé avec succès");
        } catch (Exception $e) {
            return array("success" => false, "message" => "Erreur: " . $e->getMessage());
        }
    }

    public function searchUsers($search) {
        try {
            $req = $this->pdo->prepare(
                'SELECT * FROM utilisateur 
                 WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? 
                 ORDER BY nom, prenom'
            );
            $searchTerm = '%' . $search . '%';
            $req->execute([$searchTerm, $searchTerm, $searchTerm]);
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getStats() {
        try {
            $stats = [];

            // Total users
            $req = $this->pdo->query('SELECT COUNT(*) as total FROM utilisateur');
            $stats['total'] = $req->fetch()['total'];

            // Users by status
            $req = $this->pdo->query('SELECT statut, COUNT(*) as count FROM utilisateur GROUP BY statut');
            $stats['by_status'] = $req->fetchAll();

            // Users by role
            $req = $this->pdo->query('SELECT role, COUNT(*) as count FROM utilisateur GROUP BY role');
            $stats['by_role'] = $req->fetchAll();

            // New users this month
            $req = $this->pdo->query('SELECT COUNT(*) as count FROM utilisateur WHERE MONTH(date_inscription) = MONTH(CURRENT_DATE())');
            $stats['new_this_month'] = $req->fetch()['count'];

            return $stats;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>