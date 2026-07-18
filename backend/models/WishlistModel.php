<?php
// backend/models/WishlistModel.php
require_once __DIR__ . '/../config/database.php';

class WishlistModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Fetch all product items wishlisted by a specific user profile
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT w.id AS wishlist_id, w.product_id, w.created_at,
                    p.name, p.slug, p.price, p.sale_price, p.image, p.stock
             FROM wishlists w
             INNER JOIN products p ON p.id = w.product_id
             WHERE w.user_id = :uid
             ORDER BY w.created_at DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extract a flat array containing only product IDs wishlisted by a user
     */
    public function getWishlistedProductIds(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT product_id FROM wishlists WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Fetch product details for an array of product IDs (for guest sessions)
     */
    public function getByProductIds(array $productIds): array
    {
        if (empty($productIds)) return [];
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $sql = "SELECT p.id AS product_id, p.name, p.slug, p.price, p.sale_price, p.image, p.stock
                FROM products p
                WHERE p.id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($productIds));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add product item to user's collection safely
     */
    public function add(int $userId, int $productId): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO wishlists (user_id, product_id, created_at) 
             VALUES (:uid, :pid, NOW())
             ON DUPLICATE KEY UPDATE created_at = VALUES(created_at)"
        );
        return $stmt->execute([':uid' => $userId, ':pid' => $productId]);
    }

    /**
     * Remove item execution
     */
    public function remove(int $userId, int $productId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM wishlists WHERE user_id = :uid AND product_id = :pid"
        );
        return $stmt->execute([':uid' => $userId, ':pid' => $productId]);
    }

    /**
     * Conditional Boolean Verification
     */
    public function isWishlisted(int $userId, int $productId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM wishlists WHERE user_id = :uid AND product_id = :pid LIMIT 1"
        );
        $stmt->execute([':uid' => $userId, ':pid' => $productId]);
        return (bool) $stmt->fetchColumn();
    }
}