<?php
// backend/models/Product.php

require_once __DIR__ . '/../config/database.php';

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /** All active products, optionally filtered */
    public function getAll(array $filters = [], int $page = 1): array {
        $where  = ['p.is_active = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[]              = 'c.slug = :cat';
            $params[':cat']       = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $where[]              = 'p.name LIKE :search';
            $params[':search']    = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['min_price'])) {
            $where[]              = 'COALESCE(p.sale_price, p.price) >= :min';
            $params[':min']       = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[]              = 'COALESCE(p.sale_price, p.price) <= :max';
            $params[':max']       = $filters['max_price'];
        }

        $orderMap = [
            'price_asc'  => 'COALESCE(p.sale_price, p.price) ASC',
            'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
            'newest'     => 'p.created_at DESC',
            'popular'    => 'p.is_featured DESC',
        ];
        $orderBy = $orderMap[$filters['sort'] ?? ''] ?? 'p.is_featured DESC';

        $offset = ($page - 1) * PRODUCTS_PER_PAGE;
        $sql    = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                   FROM products p
                   JOIN categories c ON c.id = p.category_id
                   WHERE " . implode(' AND ', $where) . "
                   ORDER BY $orderBy
                   LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  PRODUCTS_PER_PAGE, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,           PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Count for pagination */
    public function count(array $filters = []): int {
        $where  = ['p.is_active = 1'];
        $params = [];
        if (!empty($filters['category'])) {
            $where[]        = 'c.slug = :cat';
            $params[':cat'] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $where[]           = 'p.name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        $sql  = "SELECT COUNT(*) FROM products p
                 JOIN categories c ON c.id = p.category_id
                 WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.slug = :slug AND p.is_active = 1 LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE id = :id AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getFeatured(int $limit = 8): array {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name AS category_name FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.is_featured = 1 AND p.is_active = 1
             ORDER BY p.created_at DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRelated(int $categoryId, int $excludeId, int $limit = 4): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM products
             WHERE category_id = :cat AND id != :excl AND is_active = 1
             ORDER BY RAND() LIMIT :lim"
        );
        $stmt->bindValue(':cat',  $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':excl', $excludeId,  PDO::PARAM_INT);
        $stmt->bindValue(':lim',  $limit,      PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function search(string $query): array {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name AS category_name FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.is_active = 1 AND p.name LIKE :q
             ORDER BY p.is_featured DESC LIMIT 20"
        );
        $stmt->execute([':q' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }
}
