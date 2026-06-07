<?php
// backend/models/User.php

require_once __DIR__ . '/../config/database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function findByEmail(string $email): ?array
    {
        // 🔄 Use a LEFT JOIN to grab 'name' from customers without breaking pure admins
        $sql = "SELECT 
                    u.id, 
                    u.email, 
                    u.password, 
                    u.role, 
                    u.is_active, 
                    c.name
                FROM users u
                LEFT JOIN customers c ON c.user_id = u.id
                WHERE u.email = :email 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, password, role, phone, created_at FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, phone, password) VALUES (:name, :email, :phone, :password)"
        );
        $stmt->execute([
            ':name'     => $data['name'],
            ':email'    => $data['email'],
            ':phone'    => $data['phone'] ?? null,
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function verifyPassword(string $plainText, string $hash): bool {
        return password_verify($plainText, $hash);
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        foreach (['name', 'phone'] as $field) {
            if (isset($data[$field])) {
                $fields[]         = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $stmt = $this->db->prepare(
            "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id"
        );
        return $stmt->execute($params);
    }

    public function getAddresses(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM addresses WHERE user_id = :uid ORDER BY is_default DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function addAddress(int $userId, array $data): int {
        if (!empty($data['is_default'])) {
            $this->db->prepare(
                "UPDATE addresses SET is_default = 0 WHERE user_id = :uid"
            )->execute([':uid' => $userId]);
        }
        $stmt = $this->db->prepare(
            "INSERT INTO addresses (user_id, label, line1, line2, city, pincode, is_default)
             VALUES (:uid, :label, :line1, :line2, :city, :pincode, :def)"
        );
        $stmt->execute([
            ':uid'     => $userId,
            ':label'   => $data['label']      ?? 'Home',
            ':line1'   => $data['line1'],
            ':line2'   => $data['line2']      ?? null,
            ':city'    => $data['city'],
            ':pincode' => $data['pincode'],
            ':def'     => $data['is_default'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }
}
