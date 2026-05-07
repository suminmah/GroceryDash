<?php
// backend/models/Category.php

require_once __DIR__ . '/../config/database.php';

class Category {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll(): array {
        return $this->db->query(
            "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC"
        )->fetchAll();
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM categories WHERE slug = :slug AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
