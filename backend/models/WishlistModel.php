<?php
require_once __DIR__ . '/../config/database.php';

class WishlistModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT w.id, w.product_id, w.created_at,
                    p.name, p.slug, p.price, p.sale_price,
                    p.image, p.unit, p.stock
             FROM   wishlists w
             JOIN   products  p ON p.id = w.product_id
             WHERE  w.user_id = :uid
             ORDER  BY w.created_at DESC"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, int $productId): bool
    {
        if ($this->isWishlisted($userId, $productId)) {
            return true;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO wishlists (user_id, product_id, created_at) VALUES (:uid, :pid, NOW())"
            );
            return $stmt->execute(['uid' => $userId, 'pid' => $productId]);
        } catch (PDOException $e) {
            // ✅ Re-throw exception so controller can handle it
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function remove(int $userId, int $productId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM wishlists WHERE user_id = :uid AND product_id = :pid"
        );
        return $stmt->execute(['uid' => $userId, 'pid' => $productId]);
    }

    public function isWishlisted(int $userId, int $productId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM wishlists WHERE user_id = :uid AND product_id = :pid"
        );
        $stmt->execute(['uid' => $userId, 'pid' => $productId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getProductIds(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT product_id FROM wishlists WHERE user_id = :uid"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}