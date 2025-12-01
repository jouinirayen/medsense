<?php
require_once 'Utilisateur.php';

class UtilisateurRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // === CRUD METHODS ===
    public function find($id): ?Utilisateur
    {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? $this->hydrate($data) : null;
    }

    public function findByEmail($email): ?Utilisateur
    {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? $this->hydrate($data) : null;
    }

    public function save(Utilisateur $utilisateur): bool
    {
        if ($utilisateur->getId()) {
            return $this->update($utilisateur);
        } else {
            return $this->create($utilisateur);
        }
    }

    private function create(Utilisateur $utilisateur): bool
    {
        $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, dateNaissance, adresse, role, statut, date_inscription) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $utilisateur->getNom(),
                $utilisateur->getPrenom(),
                $utilisateur->getEmail(),
                $utilisateur->getMotDePasse(),
                $utilisateur->getDateNaissance(),
                $utilisateur->getAdresse(),
                $utilisateur->getRole(),
                $utilisateur->getStatut(),
                $utilisateur->getDateInscription()
            ]);

            if ($result) {
                $utilisateur->setId($this->pdo->lastInsertId());
            }

            return $result;
        } catch (PDOException $e) {
            throw new Exception("Erreur création utilisateur: " . $e->getMessage());
        }
    }

    private function update(Utilisateur $utilisateur): bool
    {
        $sql = "UPDATE utilisateur SET 
                nom = ?, prenom = ?, email = ?, dateNaissance = ?, adresse = ?, 
                role = ?, statut = ?, photo_profil = ?, reset_token = ?, reset_token_expires = ?
                WHERE id_utilisateur = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $utilisateur->getNom(),
                $utilisateur->getPrenom(),
                $utilisateur->getEmail(),
                $utilisateur->getDateNaissance(),
                $utilisateur->getAdresse(),
                $utilisateur->getRole(),
                $utilisateur->getStatut(),
                $utilisateur->getPhotoProfil(),
                $utilisateur->getResetToken(),
                $utilisateur->getResetTokenExpires(),
                $utilisateur->getId()
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erreur mise à jour utilisateur: " . $e->getMessage());
        }
    }

    public function delete($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
        return $stmt->execute([$id]);
    }

    // === BUSINESS METHODS ===
    public function findAll($limit = null): array
    {
        $sql = "SELECT * FROM utilisateur ORDER BY date_inscription DESC";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->pdo->query($sql);
        $users = [];
        
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $this->hydrate($data);
        }
        
        return $users;
    }

    public function findByRole($role): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE role = ? ORDER BY nom, prenom");
        $stmt->execute([$role]);
        
        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $this->hydrate($data);
        }
        
        return $users;
    }

    public function findByResetToken($token): ?Utilisateur
    {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? $this->hydrate($data) : null;
    }

    public function emailExists($email, $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM utilisateur WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id_utilisateur != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }

    public function updatePassword($userId, $hashedPassword): bool
    {
        $sql = "UPDATE utilisateur SET mot_de_passe = ?, reset_token = NULL, reset_token_expires = NULL 
                WHERE id_utilisateur = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$hashedPassword, $userId]);
    }

    // === STATISTICS ===
    public function getStats(): array
    {
        $stats = [];
        
        // Total users
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM utilisateur");
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // By status
        $stmt = $this->pdo->query("SELECT statut, COUNT(*) as count FROM utilisateur GROUP BY statut");
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // By role
        $stmt = $this->pdo->query("SELECT role, COUNT(*) as count FROM utilisateur GROUP BY role");
        $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // New this month
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM utilisateur WHERE MONTH(date_inscription) = MONTH(CURRENT_DATE())");
        $stats['new_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return $stats;
    }

    // === HYDRATION ===
    private function hydrate(array $data): Utilisateur
    {
        $utilisateur = new Utilisateur(
            $data['nom'],
            $data['prenom'],
            $data['email'],
            '', // Mot de passe non hydraté par sécurité
            $data['dateNaissance'],
            $data['adresse'],
            $data['role'],
            $data['statut']
        );

        $utilisateur->setId($data['id_utilisateur'])
                   ->setDateInscription($data['date_inscription'])
                   ->setMotDePasse($data['mot_de_passe']) // Déjà hashé
                   ->setResetToken($data['reset_token'])
                   ->setResetTokenExpires($data['reset_token_expires'])
                   ->setPhotoProfil($data['photo_profil']);

        return $utilisateur;
    }
}
?>