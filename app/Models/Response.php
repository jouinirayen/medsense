<?php

namespace App\Models;

use App\Core\Model;

class Response extends Model
{
    public function forReclamation(int $reclamationId): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, u.username FROM reponse r LEFT JOIN user u ON r.id_user = u.id WHERE r.id_reclamation = ? ORDER BY r.date DESC",
            [$reclamationId],
            'i'
        );
    }

    public function countForReclamation(int $reclamationId): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM reponse WHERE id_reclamation = ?",
            [$reclamationId],
            'i'
        );

        return (int)($result['count'] ?? 0);
    }

    public function create(int $reclamationId, int $userId, string $contenu): void
    {
        $this->db->execute(
            "INSERT INTO reponse (contenu, date, id_reclamation, id_user) VALUES (?, ?, ?, ?)",
            [$contenu, date('Y-m-d H:i:s'), $reclamationId, $userId],
            'ssii'
        );
    }

    public function deleteForReclamation(int $reclamationId): void
    {
        $this->db->execute(
            "DELETE FROM reponse WHERE id_reclamation = ?",
            [$reclamationId],
            'i'
        );
    }
}

