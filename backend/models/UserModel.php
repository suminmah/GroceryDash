<?php
// backend/models/UserModel.php
// Aligned to actual DB: id, name, email, phone, password, role, is_active

require_once __DIR__ . '/../config/database.php';

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Find user by email — returns full row including 'password'
     * Used by: login
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, phone, password, role, is_active
             FROM   users
             WHERE  email = :email
             LIMIT  1"
        );
        $stmt->execute([':email' => trim($email)]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find user by primary key — omits password for safety
     * Used by: account page, session refresh
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, phone, role, is_active, created_at
             FROM   users
             WHERE  id = :id
             LIMIT  1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Register a new customer
     * Used by: /register
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, phone, password)
             VALUES (:name, :email, :phone, :password)"
        );
        $stmt->execute([
            ':name'     => trim($data['name']),
            ':email'    => trim($data['email']),
            ':phone'    => $data['phone'] ?? null,
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Verify plain-text password against stored hash
     * Used by: login
     */
    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * Update profile fields
     * Used by: account settings
     */
    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET name = :name, phone = :phone WHERE id = :id"
        );
        return $stmt->execute([
            ':name'  => trim($data['name']),
            ':phone' => $data['phone'] ?? null,
            ':id'    => $id,
        ]);
    }

    /**
     * All customers — admin list
     */
    public function getAllCustomers(): array
    {
        return $this->db->query(
            "SELECT id, name, email, phone, role, is_active, created_at
             FROM   users
             WHERE  role = 'customer'
             ORDER  BY created_at DESC"
        )->fetchAll();
    }

    /**
     * Count customers — admin dashboard stat
     */
    public function countCustomers(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM users WHERE role = 'customer'"
        )->fetchColumn();
    }
}