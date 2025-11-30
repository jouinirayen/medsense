<?php
require_once '../../../config/config.php';

class Response
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new config())->getConnexion();
    }

    public function forReclamation(int $reclamationId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.username FROM reponse r LEFT JOIN user u ON r.id_user = u.id WHERE r.id_reclamation = ? ORDER BY r.date DESC"
        );
        $stmt->execute([$reclamationId]);
        return $stmt->fetchAll();
    }

    public function countForReclamation(int $reclamationId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM reponse WHERE id_reclamation = ?");
        $stmt->execute([$reclamationId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    public function create(int $reclamationId, int $userId, string $contenu): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO reponse (contenu, date, id_reclamation, id_user) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $contenu,
            date('Y-m-d H:i:s'),
            $reclamationId,
            $userId
        ]);
    }

    public function deleteForReclamation(int $reclamationId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM reponse WHERE id_reclamation = ?");
        $stmt->execute([$reclamationId]);
    }
}
