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
     * Creates an auth user account and a client demographic profile atomically.
     * * @param array $authData ['email', 'password', 'role']
     * @param array $profileData ['name', 'phone']
     * @return int The newly generated User ID
     */
    public function createSeparatedCustomer(array $authData, array $profileData): int
    {
        $this->db->beginTransaction();

        try {
            // 1. Core Authentication Write
            $sqlUser = "INSERT INTO users (email, password, role, is_active, created_at) 
                        VALUES (:email, :password, :role, 1, NOW())";
            
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([
                ':email'    => $authData['email'],
                ':password' => $authData['password'],
                ':role'     => $authData['role'] ?? 'customer'
            ]);

            $userId = (int)$this->db->lastInsertId();

            if ($userId <= 0) {
                throw new RuntimeException("Failed to generate system login entity.");
            }

            // 2. Customer Profile Domain Write (Only if role is customer)
            if (($authData['role'] ?? 'customer') === 'customer') {
                $sqlCustomer = "INSERT INTO customers (user_id, name, phone, created_at) 
                                VALUES (:user_id, :name, :phone, NOW())";
                
                $stmtCustomer = $this->db->prepare($sqlCustomer);
                $stmtCustomer->execute([
                    ':user_id' => $userId,
                    ':name'    => $profileData['name'],
                    ':phone'   => $profileData['phone'] ?? null
                ]);
            }

            $this->db->commit();
            return $userId;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Database Transaction Rollback -> User Creation Failed: " . $e->getMessage());
            throw $e;
        }
    }

    /** Fetch all raw system access credentials */
    public function getAllRawAuthUsers(): array 
    {
        $sql = "SELECT id, email, role, is_active, created_at FROM users ORDER BY id DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Fetch fully combined Customer demographic profiles via an Inner Join */
    public function getAllCustomersWithAuth(): array
    {
        $sql = "SELECT 
                    c.id AS customer_id, 
                    u.id AS user_id, 
                    c.name, 
                    u.email, 
                    c.phone, 
                    u.is_active, 
                    c.created_at
                FROM customers c
                INNER JOIN users u ON c.user_id = u.id
                ORDER BY c.id DESC";
                
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
     * Count customers — admin dashboard stat
     */
    public function countCustomers(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM users WHERE role = 'customer'"
        )->fetchColumn();
    }

    public function getAll(string $status = '', int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        
        // 🔄 CHANGED: Select c.name instead of u.name, and LEFT JOIN the customers table
        $sql = "SELECT 
                    o.id,
                    o.user_id,
                    o.total_price,
                    o.status,
                    o.delivery_slot,
                    o.created_at,
                    c.name AS user_name,  -- Safely mapping to the expected view variable alias
                    u.email AS user_email
                FROM orders o
                INNER JOIN users u ON o.user_id = u.id
                LEFT JOIN customers c ON c.user_id = u.id "; // 👈 Crucial relationship bridge

        $whereClause = [];
        $params = [];

        if (!empty($status)) {
            $whereClause[] = "o.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }

        $sql .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // Explicitly bind variables if pagination parameters are used as integers
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}