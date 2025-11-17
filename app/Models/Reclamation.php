<?php

namespace App\Models;

use App\Core\Model;
use Exception;

class Reclamation extends Model
{
    public function forUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM reclamation WHERE id_user = ? ORDER BY date DESC",
            [$userId],
            'i'
        );
    }

    public function findForUser(int $reclamationId, int $userId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM reclamation WHERE id = ? AND id_user = ?",
            [$reclamationId, $userId],
            'ii'
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM reclamation WHERE id = ?",
            [$id],
            'i'
        );
    }

    public function create(array $data): void
    {
        $this->db->execute(
            "INSERT INTO reclamation (titre, description, date, id_user, type, statut) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['titre'],
                $data['description'],
                $data['date'],
                $data['id_user'],
                $data['type'],
                $data['statut']
            ],
            'sssssi'
        );
    }

    public function update(int $id, int $userId, array $data): void
    {
        $this->db->execute(
            "UPDATE reclamation SET titre = ?, description = ? WHERE id = ? AND id_user = ?",
            [$data['titre'], $data['description'], $id, $userId],
            'ssii'
        );
    }

    public function deleteForUser(int $id, int $userId): void
    {
        $this->db->execute(
            "DELETE FROM reclamation WHERE id = ? AND id_user = ?",
            [$id, $userId],
            'ii'
        );
    }

    public function delete(int $id): void
    {
        $this->db->execute(
            "DELETE FROM reclamation WHERE id = ?",
            [$id],
            'i'
        );
    }

    public function all(?string $typeFilter = null): array
    {
        $sql = "SELECT r.*, u.username FROM reclamation r LEFT JOIN user u ON r.id_user = u.id";
        $params = [];
        $types = '';

        if (!empty($typeFilter)) {
            $sql .= " WHERE r.type = ?";
            $params[] = $typeFilter;
            $types .= 's';
        }

        $sql .= " ORDER BY r.date DESC";

        return $this->db->fetchAll($sql, $params, $types);
    }
}

