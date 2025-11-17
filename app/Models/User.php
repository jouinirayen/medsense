<?php

namespace App\Models;

use App\Core\Model;
use Exception;

class User extends Model
{
    public function findByUsername(string $username): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM user WHERE username = ?",
            [$username],
            's'
        );
    }

    public function exists(string $username, string $email): bool
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM user WHERE username = ? OR email = ?",
            [$username, $email],
            'ss'
        );

        return (int)($result['count'] ?? 0) > 0;
    }

    public function create(string $username, string $email, string $password): void
    {
        $hashed = hash('sha256', $password);

        $this->db->execute(
            "INSERT INTO user (username, email, password) VALUES (?, ?, ?)",
            [$username, $email, $hashed],
            'sss'
        );
    }
}

