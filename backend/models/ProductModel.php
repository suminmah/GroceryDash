<?php
// ============================================================
//  backend/models/ProductModel.php
//  Table: Products (product_id, slug, name, category_id,
//                   price, is_active)
//  Joins: Categories, Inventory
// ============================================================

require_once __DIR__ . '/../config/database.php';

class ProductModel
{
    private PDO $db;

    // Number of products per page for paginated listing
    private const PER_PAGE = 12;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  single row
    // ─────────────────────────────────────────────────────────

    /**
     * Fetch one product by its primary key.
     * Joins category name and current stock from Inventory.
     * Used by: product detail page, cart enrichment
     *
     * @return array|null
     */
  public function findById(int $productId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id AS product_id,
                    p.slug,
                    p.name,
                    p.price,
                    p.unit,
                    p.image,
                    p.category_id,
                    c.name AS category_name,
                    COALESCE(i.quantity, 0) AS stock_qty
            FROM   products p
            JOIN   categories c ON c.id = p.category_id
            LEFT   JOIN inventory i ON i.product_id = p.id
            WHERE  p.id = :id
            LIMIT  1"
        );
        $stmt->execute([':id' => $productId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Fetch one product by its SKU code.
     * Used by: barcode scan, stock import, admin lookup
     *
     * @return array|null
     */
    public function findBySku(string $sku): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT  p.*, c.name AS category_name,
                     COALESCE(i.quantity, 0) AS stock_qty
             FROM    Products   p
             JOIN    Categories c ON c.id = p.category_id
             LEFT    JOIN Inventory i ON i.product_id = p.id
             WHERE   p.sku = :sku
             LIMIT   1"
        );
        $stmt->execute([':sku' => $sku]);
        return $stmt->fetch() ?: null;
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  paginated listing with filters
    // ─────────────────────────────────────────────────────────

    /**
     * Return a paginated, filterable product list.
     * Used by: /shop page, search results, category pages
     *
     * Supported $filters keys:
     *   category_id  (int|int[])  — filter by one or many category IDs
     *   search       (string)     — partial name / sku match
     *   min_price    (float)
     *   max_price    (float)
     *   is_perishable (0|1)
     *   in_stock_only (bool)      — only rows where stock_qty > buffer_threshold
     *   sort         (string)     — 'price_asc'|'price_desc'|'name_asc'|'newest'
     *
     * @param  array $filters
     * @param  int   $page    1-based page number
     * @return array[]
     */
    public function getAll(array $filters = [], int $page = 1): array
    {
        [$where, $params] = $this->buildWhereClause($filters);

        $orderBy = match($filters['sort'] ?? '') {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'name_asc'   => 'p.name ASC',
            default      => 'p.id DESC',   // newest first
        };

        $offset = (max(1, $page) - 1) * self::PER_PAGE;

        $sql = "SELECT  p.id AS product_id,
                        p.slug,
                        p.name,
                        p.price,
                        p.sale_price,
                        p.unit,
                        p.image,
                        p.is_active,
                        p.category_id,
                        c.name AS category_name,
                        COALESCE(i.quantity, 0) AS stock_qty
                FROM    Products   p
                JOIN    Categories c  ON c.id = p.category_id
                LEFT    JOIN Inventory i ON i.product_id  = p.id
                WHERE   $where
                ORDER   BY $orderBy
                LIMIT   :limit
                OFFSET  :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  self::PER_PAGE, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,        PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count rows matching the same filters (used to calculate total pages).
     * Used by: pagination on /shop
     *
     * @return int
     */
    public function count(array $filters = []): int
    {
        [$where, $params] = $this->buildWhereClause($filters);

        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM   Products   p
             JOIN   Categories c  ON c.id = p.category_id
             LEFT   JOIN Inventory i ON i.product_id  = p.id
             WHERE  $where"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * How many pages exist for the given filters?
     */
    public function totalPages(array $filters = []): int
    {
        return (int) ceil($this->count($filters) / self::PER_PAGE);
    }

    /**
     * Return the configured items-per-page constant.
     */
    public function perPage(): int
    {
        return self::PER_PAGE;
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  specialised queries
    // ─────────────────────────────────────────────────────────

    /**
     * Full-text search across product name and SKU.
     * Used by: header search, /search page
     *
     * @return array[]  Up to 20 matching products
     */
    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            "SELECT  p.id AS product_id, p.name, p.price, p.sale_price, p.image, p.unit,
                     c.name AS category_name,
                     COALESCE(i.quantity, 0) AS stock_qty
             FROM    Products   p
             JOIN    Categories c  ON c.id = p.category_id
             LEFT    JOIN Inventory i ON i.product_id  = p.id
             WHERE   p.name LIKE :q
             ORDER   BY p.name ASC
             LIMIT   20"
        );
        $like = '%' . $query . '%';
        $stmt->execute([':q' => $like]);
        return $stmt->fetchAll();
    }

    /**
     * Return products in a given category (and optionally all sub-categories).
     * Pass an array of IDs to include descendants.
     * Used by: category page
     *
     * @param  int|int[] $categoryIds
     * @return array[]
     */
    public function getByCategory(mixed $categoryIds, int $page = 1): array
    {
        $ids     = (array) $categoryIds;
        $holders = implode(',', array_fill(0, count($ids), '?'));

        $offset = (max(1, $page) - 1) * self::PER_PAGE;
        $stmt   = $this->db->prepare(
            "SELECT  p.id AS product_id, p.name, p.sku, p.price,
                     c.name AS category_name,
                     COALESCE(i.quantity, 0) AS stock_qty
             FROM    Products   p
             JOIN    Categories c  ON c.category_id = p.category_id
             LEFT    JOIN Inventory i ON i.product_id  = p.id
             WHERE   p.category_id IN ($holders)
             ORDER   BY p.name ASC
             LIMIT   ? OFFSET ?"
        );
        $stmt->execute([...$ids, self::PER_PAGE, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Return products whose stock is at or below their buffer threshold.
     * Used by: admin low-stock dashboard widget
     *
     * @return array[]
     */
    public function getLowStock(): array
    {
        return $this->db->query(
            "SELECT  p.id AS product_id, p.sku, p.name,
                     i.quantity AS stock_qty, i.buffer_threshold,
                     c.name AS category_name
             FROM    Products   p
             JOIN    Inventory  i  ON i.product_id  = p.id
             JOIN    Categories c  ON c.id = p.category_id
             WHERE   i.quantity <= i.buffer_threshold
             ORDER   BY i.quantity ASC"
        )->fetchAll();
    }

    /**
     * Return perishable products only.
     * Used by: expiry-management admin page
     *
     * @return array[]
     */
    public function getPerishable(): array
    {
        return $this->db->query(
            "SELECT  p.*, c.name AS category_name,
                     COALESCE(i.quantity, 0) AS stock_qty
             FROM    Products   p
             JOIN    Categories c  ON c.id = p.category_id
             LEFT    JOIN Inventory i ON i.product_id = p.id
             WHERE   p.is_perishable = 1
             ORDER   BY p.name ASC"
        )->fetchAll();
    }

    /**
     * Return products related to a given product (same category, different ID).
     * Used by: "You might also like" section on product detail
     *
     * @return array[]
     */
    public function getRelated(int $categoryId, int $excludeId, int $limit = 4): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id AS product_id, 
                    p.name, 
                    p.price, 
                    p.image,
                     COALESCE(i.quantity, 0) AS stock_qty
             FROM    Products  p
             LEFT    JOIN Inventory i ON i.product_id = p.id
             WHERE   p.category_id = :cat
             AND     p.id  != :excl
             ORDER   BY RAND()
             LIMIT   :lim"
        );
        $stmt->bindValue(':cat',  $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':excl', $excludeId,  PDO::PARAM_INT);
        $stmt->bindValue(':lim',  $limit,      PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    //  WRITE  —  create / update / delete
    // ─────────────────────────────────────────────────────────

    /**
     * Create a new product row.
     *
     * @param  array{sku:string, name:string, category_id:int,
     *                price:float, is_perishable:bool} $data
     * @return int  New product_id
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO products (slug, name, category_id, price, image)
            VALUES (:slug, :name, :cat, :price, :img)"
        );
        $stmt->execute([
            ':slug'  => trim($data['slug']),
            ':name'  => trim($data['name']),
            ':cat'   => (int)$data['category_id'],
            ':price' => (float)$data['price'],
            ':img'   => $data['image'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing product.
     *
     * @return bool
     */
    public function update(int $productId, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE products
            SET    slug = :slug, name = :name, category_id = :cat, price = :price
            WHERE  id = :id"
        );
        return $stmt->execute([
            ':slug'  => trim($data['slug']),
            ':name'  => trim($data['name']),
            ':cat'   => (int)$data['category_id'],
            ':price' => (float)$data['price'],
            ':id'    => $productId,
        ]);
    }

    /**
     * Delete a product.
     * Note: Inventory row cascades on delete per FK constraint.
     *
     * @return bool
     */
   public function delete(int $productId): bool
    {
        // Ensure the column name is 'id'
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $productId]);
    }

    // ─────────────────────────────────────────────────────────
    //  PRIVATE helpers
    // ─────────────────────────────────────────────────────────

    /**
     * Build the WHERE clause + named param array from a filters array.
     * Re-used by getAll() and count() to keep logic in sync.
     *
     * @return array{0: string, 1: array}
     */
    private function buildWhereClause(array $filters): array
    {
        $conditions = ['1 = 1'];  // always-true base
        $params     = [];

        // Filter by one category
        if (!empty($filters['category_id']) && !is_array($filters['category_id'])) {
            $conditions[]              = 'p.category_id = :cat';
            $params[':cat']            = (int) $filters['category_id'];
        }

        // Filter by multiple category IDs (e.g. parent + descendants)
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $placeholders = [];
            foreach ($filters['category_ids'] as $i => $cid) {
                $placeholder = ":cid_$i";
                $placeholders[] = $placeholder;
                $params[$placeholder] = (int)$cid;
            }
            $conditions[] = "p.category_id IN (" . implode(',', $placeholders) . ")";
        }

        // Name / SKU search
        if (!empty($filters['search'])) {
            $conditions[]              = '(p.name LIKE :search OR p.sku LIKE :search2)';
            $like                      = '%' . $filters['search'] . '%';
            $params[':search']         = $like;
            $params[':search2']        = $like;
        }

        // Price range
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $conditions[]              = 'p.price >= :min_price';
            $params[':min_price']      = (float) $filters['min_price'];
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $conditions[]              = 'p.price <= :max_price';
            $params[':max_price']      = (float) $filters['max_price'];
        }

        // Perishable flag
        if (isset($filters['is_perishable'])) {
            $conditions[]              = 'p.is_perishable = :perish';
            $params[':perish']         = (int) $filters['is_perishable'];
        }

        // In-stock only: stock must be above the buffer threshold
        if (!empty($filters['in_stock_only'])) {
            // Match your inventory table's 'quantity' column
            $conditions[] = 'COALESCE(i.quantity, 0) > 0'; 
        }

        return [implode(' AND ', $conditions), $params];
    }
}
