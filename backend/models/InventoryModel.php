<?php
// ============================================================
//  backend/models/InventoryModel.php
//  Table: Inventory (inventory_id, product_id, stock_qty,
//                    buffer_threshold)
//  One-to-one with Products
// ============================================================

require_once __DIR__ . '/../config/database.php';

class InventoryModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  single row
    // ─────────────────────────────────────────────────────────

    /**
     * Get the inventory record for one product.
     * Used by: product detail page (stock badge), checkout stock guard
     *
     * @return array|null  {inventory_id, product_id, stock_qty, buffer_threshold}
     */
    public function findByProductId(int $productId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT inventory_id, product_id, quantity As stock_qty
             FROM   Inventory
             WHERE  product_id = :pid
             LIMIT  1"
        );
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Shorthand: return current stock quantity for a product.
     * Returns 0 if no Inventory row exists yet.
     * Used by: add-to-cart validation
     */
    public function getStockQty(int $productId): int
    {
        $stmt = $this->db->prepare(
            "SELECT stock_qty FROM Inventory WHERE product_id = :pid LIMIT 1"
        );
        $stmt->execute([':pid' => $productId]);
        return (int) ($stmt->fetchColumn() ?? 0);
    }

    /**
     * Check whether a product has enough stock to fulfil a quantity.
     * Used by: add-to-cart, checkout validation
     *
     * @param  int $required  Quantity the customer wants
     * @return bool
     */
    public function hasSufficientStock(int $productId, int $required): bool
    {
        return $this->getStockQty($productId) >= $required;
    }

    /**
     * Check if a product is "effectively in stock" (above its buffer).
     * Used by: product card stock badge
     */
    public function isAvailable(int $productId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT stock_qty > buffer_threshold AS available
             FROM   Inventory
             WHERE  product_id = :pid
             LIMIT  1"
        );
        $stmt->execute([':pid' => $productId]);
        return (bool) ($stmt->fetchColumn() ?? false);
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  list queries
    // ─────────────────────────────────────────────────────────

    /**
     * Return all inventory rows with product name and category.
     * Used by: admin inventory management page
     *
     * @return array[]
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT  i.inventory_id,
                     i.product_id,
                     i.stock_qty,
                     i.buffer_threshold,
                     p.name  AS product_name,
                     p.sku,
                     p.is_perishable,
                     c.name  AS category_name
             FROM    Inventory  i
             JOIN    Products   p ON p.product_id   = i.product_id
             JOIN    Categories c ON c.category_id  = p.category_id
             ORDER   BY p.name ASC"
        )->fetchAll();
    }

    /**
     * Return only products at or below their buffer threshold.
     * Used by: admin low-stock alert panel
     *
     * @return array[]
     */
    public function getLowStockItems(int $threshold = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id, p.name, i.quantity
            FROM products p
            INNER JOIN inventory i ON i.product_id = p.id
            WHERE i.quantity <= :threshold
            ORDER BY i.quantity ASC"
        );
        $stmt->execute([':threshold' => $threshold]);
        return $stmt->fetchAll();
    }

    /**
     * Count how many products are currently low on stock.
     * Used by: admin dashboard badge
     */
    public function countLowStock(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM Inventory
             WHERE  stock_qty <= buffer_threshold"
        )->fetchColumn();
    }

    /**
     * Return inventory for an array of product IDs in one query.
     * Used by: cart page (batch check before checkout)
     *
     * @param  int[]  $productIds
     * @return array  Keyed by product_id for O(1) lookup
     */
    public function getForProducts(array $productIds): array
    {
        if (empty($productIds)) return [];

        $holders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt    = $this->db->prepare(
            "SELECT product_id, stock_qty, buffer_threshold
             FROM   Inventory
             WHERE  product_id IN ($holders)"
        );
        $stmt->execute($productIds);

        // Re-index by product_id
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['product_id']] = $row;
        }
        return $result;
    }

    // ─────────────────────────────────────────────────────────
    //  WRITE  —  create / update / adjust
    // ─────────────────────────────────────────────────────────

    /**
     * Create an inventory record for a new product.
     * Usually called right after ProductModel::create().
     *
     * @param  array{product_id:int, stock_qty:int, buffer_threshold:int} $data
     * @return int  New inventory_id
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Inventory (product_id, stock_qty, buffer_threshold)
             VALUES (:pid, :qty, :buf)"
        );
        $stmt->execute([
            ':pid' => (int) $data['product_id'],
            ':qty' => (int) ($data['stock_qty']         ?? 0),
            ':buf' => (int) ($data['buffer_threshold']  ?? 0),
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Set the stock quantity directly (admin restock form).
     *
     * @return bool
     */
    public function setStock(int $productId, int $newQty): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Inventory
             SET    stock_qty = :qty
             WHERE  product_id = :pid"
        );
        return $stmt->execute([':qty' => max(0, $newQty), ':pid' => $productId]);
    }

    /**
     * Update buffer threshold (admin safety-stock setting).
     *
     * @return bool
     */
    public function setBufferThreshold(int $productId, int $threshold): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Inventory
             SET    buffer_threshold = :buf
             WHERE  product_id = :pid"
        );
        return $stmt->execute([':buf' => max(0, $threshold), ':pid' => $productId]);
    }

    /**
     * Deduct stock after an order is placed.
     * Uses GREATEST(0, …) so stock never goes negative.
     * Called inside OrderModel::create() transaction.
     *
     * @return bool
     */
    public function deductStock(int $productId, int $quantity): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Inventory
             SET    stock_qty = GREATEST(0, stock_qty - :qty)
             WHERE  product_id = :pid"
        );
        return $stmt->execute([':qty' => $quantity, ':pid' => $productId]);
    }

    /**
     * Add stock (restock / return / manual correction).
     *
     * @return bool
     */
    public function addStock(int $productId, int $quantity): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Inventory
             SET    stock_qty = stock_qty + :qty
             WHERE  product_id = :pid"
        );
        return $stmt->execute([':qty' => abs($quantity), ':pid' => $productId]);
    }
}
