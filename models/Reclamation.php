<?php
require_once '../../../config/config.php';

class Reclamation
{
    const TYPE_NORMAL = 'normal';
    const TYPE_URGENCE = 'urgence';

    const STATUS_OPEN = 'ouvert';
    const STATUS_IN_PROGRESS = 'en cours';
    const STATUS_CLOSED = 'fermé';

    private $pdo;

    public function __construct()
    {
        $this->pdo = (new config())->getConnexion();
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reclamation WHERE id_user = ? ORDER BY date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reclamation WHERE id = ? AND id_user = ?");
        $stmt->execute([$id, $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO reclamation (titre, description, date, id_user, type, statut) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['date'],
            $data['id_user'],
            $data['type'],
            $data['statut']
        ]);
    }

    public function update(int $id, int $userId, array $data): void
    {
        $stmt = $this->pdo->prepare("UPDATE reclamation SET titre = ?, description = ? WHERE id = ? AND id_user = ?");
        $stmt->execute([
            $data['titre'],
            $data['description'],
            $id,
            $userId
        ]);
    }

    public function deleteForUser(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM reclamation WHERE id = ? AND id_user = ?");
        $stmt->execute([$id, $userId]);
    }
}
