<?php
require_once '../../../config/config.php';

class Response
{
    private $pdo;
    private $id;
    private $contenu;
    private $date;
    private $id_reclamation;
    private $id_user;
    private $username; // For joins

    public function __construct()
    {
        $this->pdo = (new config())->getConnexion();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getReclamationId(): ?int
    {
        return $this->id_reclamation;
    }

    public function setReclamationId(int $id_reclamation): self
    {
        $this->id_reclamation = $id_reclamation;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->id_user;
    }

    public function setUserId(int $id_user): self
    {
        $this->id_user = $id_user;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    // Hydrate from array
    public function hydrate(array $data): self
    {
        if (isset($data['id'])) {
            $this->setId((int)$data['id']);
        }
        if (isset($data['contenu'])) {
            $this->setContenu($data['contenu']);
        }
        if (isset($data['date'])) {
            $this->setDate($data['date']);
        }
        if (isset($data['id_reclamation'])) {
            $this->setReclamationId((int)$data['id_reclamation']);
        }
        if (isset($data['id_user'])) {
            $this->setUserId((int)$data['id_user']);
        }
        if (isset($data['username'])) {
            $this->setUsername($data['username']);
        }
        return $this;
    }

    // Convert to array
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'contenu' => $this->getContenu(),
            'date' => $this->getDate(),
            'id_reclamation' => $this->getReclamationId(),
            'id_user' => $this->getUserId(),
            'username' => $this->getUsername()
        ];
    }

    public function forReclamation(int $reclamationId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.username FROM reponse r 
             LEFT JOIN user u ON r.id_user = u.id 
             WHERE r.id_reclamation = ? 
             ORDER BY r.date DESC"
        );
        $stmt->execute([$reclamationId]);
        $results = $stmt->fetchAll();
        
        $responses = [];
        foreach ($results as $data) {
            $response = new self();
            $response->hydrate($data);
            $responses[] = $response;
        }
        return $responses;
    }

    public function countForReclamation(int $reclamationId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM reponse WHERE id_reclamation = ?");
        $stmt->execute([$reclamationId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    public function create(): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO reponse (contenu, date, id_reclamation, id_user) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $this->contenu,
            $this->date,
            $this->id_reclamation,
            $this->id_user
        ]);
    }

    public function deleteForReclamation(int $reclamationId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM reponse WHERE id_reclamation = ?");
        return $stmt->execute([$reclamationId]);
    }

    /**
     * Trouve une réponse par son ID
     */
    public function findById(int $id): ?self
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.username FROM reponse r 
             LEFT JOIN user u ON r.id_user = u.id 
             WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result) {
            $response = new self();
            $response->hydrate($result);
            return $response;
        }
        return null;
    }

    /**
     * Met à jour une réponse existante
     */
    public function update(): bool
    {
        if (!$this->id) {
            throw new Exception("Cannot update without ID");
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE reponse 
            SET contenu = ?, date = ? 
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $this->contenu,
            $this->date,
            $this->id
        ]);
    }

    /**
     * Supprime une réponse par son ID
     */
    public function delete(): bool
    {
        if (!$this->id) {
            throw new Exception("Cannot delete without ID");
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM reponse WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Supprime une réponse par ID (méthode statique)
     */
    public function deleteById(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM reponse WHERE id = ?");
        return $stmt->execute([$id]);
    }
}