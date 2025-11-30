<?php
require_once '../../../config/config.php';

class User
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new config())->getConnexion();
    }

    /**
     * Find a user by username
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find a user by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Check if a username or email already exists
     */
    public function exists(string $username, string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM user WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0) > 0;
    }

    /**
     * Create a new user with hashed password
     */
    public function create(string $username, string $email, string $password): void
    {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed]);
    }

    /**
     * Get all users
     */
    public function all(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM user ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Update user info
     */
    public function update(int $id, array $data): void
    {
        $fields = [];
        $values = [];

        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $values[] = $data['username'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $values[] = $data['email'];
        }
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) return;

        $values[] = $id;

        $stmt = $this->pdo->prepare("UPDATE user SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($values);
    }

    /**
     * Delete user
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM user WHERE id = ?");
        $stmt->execute([$id]);
    }
}
