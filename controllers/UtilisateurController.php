<?php
require_once __DIR__ .'/../models/Utilisateur.php';
require_once __DIR__ . '/../config.php';

class UtilisateurController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

   
    public function getAllUsers($asObjects = false) {
        try {
            $req = $this->pdo->query('SELECT * FROM utilisateur ORDER BY date_inscription DESC');
            
            if ($asObjects) {
                $users = [];
                while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                    $users[] = $this->rowToUser($row);
                }
                return $users;
            }
            
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error in getAllUsers: ' . $e->getMessage());
            return $asObjects ? [] : null;
        }
    }

    public function getUserById($id, $asObject = false) {
        try {
            $req = $this->pdo->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = ?');
            $req->execute([$id]);
            $row = $req->fetch(PDO::FETCH_ASSOC);
            
            if ($asObject && $row) {
                return $this->rowToUser($row);
            }
            
            return $row;
        } catch (Exception $e) {
            error_log('Error in getUserById: ' . $e->getMessage());
            return $asObject ? null : false;
        }
    }

    /**
     * Créer un utilisateur
     */
    public function createUser(Utilisateur $user): array {
        try {
            // Vérifier si l'email existe
            if ($this->emailExists($user->getEmail())) {
                return ["success" => false, "message" => "Cet email existe déjà"];
            }

            $req = $this->pdo->prepare(
                'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, dateNaissance, adresse, role, statut, diplome_path) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $success = $req->execute([
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getMotDePasse(),
                $user->getDateNaissance(),
                $user->getAdresse(),
                $user->getRole(),
                $user->getStatut(),
                $user->getDiplomePath()
            ]);

            if ($success) {
                $userId = $this->pdo->lastInsertId();
                return [
                    "success" => true,
                    "message" => "Utilisateur créé avec succès",
                    "id" => $userId
                ];
            }

            return ["success" => false, "message" => "Erreur lors de la création"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser($id, Utilisateur $user): array {
        try {
            // Vérifier si l'utilisateur existe
            $existingUser = $this->getUserById($id);
            if (!$existingUser) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }

            // Vérifier l'unicité de l'email
            if ($user->getEmail() !== $existingUser['email'] && 
                $this->emailExists($user->getEmail(), $id)) {
                return ["success" => false, "message" => "Cet email est déjà utilisé"];
            }

            $req = $this->pdo->prepare(
                'UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, dateNaissance = ?, 
                 adresse = ?, role = ?, statut = ?, diplome_path = ? WHERE id_utilisateur = ?'
            );

            $success = $req->execute([
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getDateNaissance(),
                $user->getAdresse(),
                $user->getRole(),
                $user->getStatut(),
                $user->getDiplomePath(),
                $id
            ]);

            if ($success) {
                return ["success" => true, "message" => "Utilisateur modifié avec succès"];
            }

            return ["success" => false, "message" => "Erreur lors de la mise à jour"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser($id): array {
        try {
            // Vérifier si l'utilisateur existe
            $existingUser = $this->getUserById($id);
            if (!$existingUser) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }

            $req = $this->pdo->prepare('DELETE FROM utilisateur WHERE id_utilisateur = ?');
            $success = $req->execute([$id]);

            if ($success) {
                return ["success" => true, "message" => "Utilisateur supprimé avec succès"];
            }

            return ["success" => false, "message" => "Erreur lors de la suppression"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    /**
     * Rechercher des utilisateurs
     */
    public function searchUsers($search) {
        try {
            $req = $this->pdo->prepare(
                'SELECT * FROM utilisateur 
                 WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? 
                 ORDER BY nom, prenom'
            );
            $searchTerm = '%' . $search . '%';
            $req->execute([$searchTerm, $searchTerm, $searchTerm]);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error in searchUsers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtenir les statistiques
     */
    public function getStats(): array {
        try {
            $stats = [];

            $req = $this->pdo->query('SELECT COUNT(*) as total FROM utilisateur');
            $stats['total'] = $req->fetch()['total'];

            $req = $this->pdo->query('SELECT statut, COUNT(*) as count FROM utilisateur GROUP BY statut');
            $stats['by_status'] = $req->fetchAll(PDO::FETCH_ASSOC);

            $req = $this->pdo->query('SELECT role, COUNT(*) as count FROM utilisateur GROUP BY role');
            $stats['by_role'] = $req->fetchAll(PDO::FETCH_ASSOC);

            $req = $this->pdo->query('SELECT COUNT(*) as count FROM utilisateur WHERE MONTH(date_inscription) = MONTH(CURRENT_DATE())');
            $stats['new_this_month'] = $req->fetch()['count'];

            return $stats;
        } catch (Exception $e) {
            error_log('Error in getStats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifier si un email existe
     */
    public function emailExists($email, $excludeId = null): bool {
        try {
            if ($excludeId) {
                $query = "SELECT COUNT(*) FROM utilisateur WHERE email = ? AND id_utilisateur != ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$email, $excludeId]);
            } else {
                $query = "SELECT COUNT(*) FROM utilisateur WHERE email = ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$email]);
            }
            
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log('Error in emailExists: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Convertir une ligne DB en objet Utilisateur
     */
    private function rowToUser($row): Utilisateur {
        $user = new Utilisateur(
            $row['nom'],
            $row['prenom'],
            $row['email'],
            $row['mot_de_passe'],
            $row['dateNaissance'] ?? '',
            $row['adresse'] ?? '',
            $row['role'] ?? 'utilisateur',
            $row['statut'] ?? 'actif',
            $row['diplome_path'] ?? null
        );
        
        $user->setId($row['id_utilisateur'])
             ->setDateInscription($row['date_inscription'])
             ->setResetToken($row['reset_token'] ?? null)
             ->setResetTokenExpires($row['reset_token_expires'] ?? null)
             ->setPhotoProfil($row['photo_profil'] ?? null)
             ->setDiplomePath($row['diplome_path'] ?? null);
        
        return $user;
    }

    /**
     * Mettre à jour le chemin du diplôme d'un utilisateur
     */
    public function updateDiplomePath($userId, $diplomePath): array {
        try {
            // Vérifier si l'utilisateur existe
            $existingUser = $this->getUserById($userId);
            if (!$existingUser) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }

            $query = "UPDATE utilisateur SET diplome_path = ? WHERE id_utilisateur = ?";
            $stmt = $this->pdo->prepare($query);
            $success = $stmt->execute([$diplomePath, $userId]);
            
            if ($success) {
                return ["success" => true, "message" => "Diplôme mis à jour avec succès"];
            }
            
            return ["success" => false, "message" => "Erreur lors de la mise à jour du diplôme"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    /**
     * Supprimer le diplôme d'un utilisateur
     */
    public function removeDiplome($userId): array {
        try {
            // Vérifier si l'utilisateur existe
            $existingUser = $this->getUserById($userId);
            if (!$existingUser) {
                return ["success" => false, "message" => "Utilisateur non trouvé"];
            }

            $query = "UPDATE utilisateur SET diplome_path = NULL WHERE id_utilisateur = ?";
            $stmt = $this->pdo->prepare($query);
            $success = $stmt->execute([$userId]);
            
            if ($success) {
                return ["success" => true, "message" => "Diplôme supprimé avec succès"];
            }
            
            return ["success" => false, "message" => "Erreur lors de la suppression du diplôme"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur: " . $e->getMessage()];
        }
    }

    /**
     * Obtenir les médecins avec diplôme (pour validation)
     */
    public function getMedecinsWithDiplome() {
        try {
            $req = $this->pdo->query(
                'SELECT * FROM utilisateur 
                 WHERE role = "medecin" AND diplome_path IS NOT NULL 
                 ORDER BY date_inscription DESC'
            );
            
            $medecins = [];
            while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                $medecins[] = $this->rowToUser($row);
            }
            return $medecins;
        } catch (Exception $e) {
            error_log('Error in getMedecinsWithDiplome: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtenir les utilisateurs par rôle
     */
    public function getUsersByRole($role, $withDiplomeOnly = false) {
        try {
            $sql = 'SELECT * FROM utilisateur WHERE role = ?';
            $params = [$role];
            
            if ($withDiplomeOnly) {
                $sql .= ' AND diplome_path IS NOT NULL';
            }
            
            $sql .= ' ORDER BY nom, prenom';
            
            $req = $this->pdo->prepare($sql);
            $req->execute($params);
            
            $users = [];
            while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                $users[] = $this->rowToUser($row);
            }
            return $users;
        } catch (Exception $e) {
            error_log('Error in getUsersByRole: ' . $e->getMessage());
            return [];
        }
    }
}
?>