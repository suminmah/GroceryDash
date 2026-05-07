<?php
// ============================================================
//  backend/models/OrderModel.php
//  Aligned to ACTUAL orders table columns:
//    id, user_id, order_number, status, subtotal, delivery_fee,
//    discount, total, payment_method, payment_status,
//    delivery_address, delivery_slot_id, notes, created_at
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/InventoryModel.php';

class OrderModel
{
    private PDO            $db;
    private InventoryModel $inventory;

    private const VALID_STATUSES = [
        'pending', 'confirmed', 'packed',
        'out_for_delivery', 'delivered', 'cancelled',
    ];

    public function __construct()
    {
        $this->db        = Database::connect();
        $this->inventory = new InventoryModel();
    }

    // ─────────────────────────────────────────────────────────
    //  READ — single order
    // ─────────────────────────────────────────────────────────

    /**
     * Fetch one complete order by its primary key (id).
     * Includes all order fields + customer info + delivery slot.
     * Used by: order detail, confirmation page, tracking page
     */
    public function findById(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT  o.id,
                     o.user_id,
                     o.order_number,
                     o.status,
                     o.subtotal,
                     o.delivery_fee,
                     o.discount,
                     o.total,
                     o.payment_method,
                     o.payment_status,
                     o.delivery_address,
                     o.delivery_slot_id,
                     o.notes,
                     o.created_at,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time,
                     u.name  AS customer_name,
                     u.email AS customer_email
             FROM    orders         o
             LEFT    JOIN Delivery_Slots ds ON ds.slot_id = o.delivery_slot_id
             JOIN    users          u  ON u.id       = o.user_id
             WHERE   o.id = :id
             LIMIT   1"
        );
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();
        if (!$order) return null;

        $order['items'] = $this->getOrderItems($orderId);
        return $order;
    }

    /**
     * Fetch one complete order by its order_number string.
     * Used by: order confirmation redirect, tracking by order number
     */
    public function findByOrderNumber(string $orderNumber): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT  o.id,
                     o.user_id,
                     o.order_number,
                     o.status,
                     o.subtotal,
                     o.delivery_fee,
                     o.discount,
                     o.total,
                     o.payment_method,
                     o.payment_status,
                     o.delivery_address,
                     o.delivery_slot_id,
                     o.notes,
                     o.created_at,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time,
                     u.name  AS customer_name,
                     u.email AS customer_email
             FROM    orders         o
             LEFT    JOIN Delivery_Slots ds ON ds.slot_id = o.delivery_slot_id
             JOIN    users          u  ON u.id       = o.user_id
             WHERE   o.order_number = :num
             LIMIT   1"
        );
        $stmt->execute([':num' => $orderNumber]);
        $order = $stmt->fetch();
        if (!$order) return null;

        $order['items'] = $this->getOrderItems((int) $order['id']);
        return $order;
    }

    // ─────────────────────────────────────────────────────────
    //  READ — order lists
    // ─────────────────────────────────────────────────────────

    /**
     * All orders for one customer, newest first.
     * Used by: /account/orders
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT  o.id,
                     o.order_number,
                     o.status,
                     o.subtotal,
                     o.delivery_fee,
                     o.discount,
                     o.total,
                     o.payment_method,
                     o.payment_status,
                     o.created_at,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time,
                     COUNT(oi.id) AS item_count
             FROM    orders         o
             LEFT    JOIN Delivery_Slots ds ON ds.slot_id  = o.delivery_slot_id
             LEFT    JOIN Order_Items    oi ON oi.order_id = o.id
             WHERE   o.user_id = :uid
             GROUP   BY o.id,
                        o.order_number,
                        o.status,
                        o.subtotal,
                        o.delivery_fee,
                        o.discount,
                        o.total,
                        o.payment_method,
                        o.payment_status,
                        o.created_at,
                        ds.slot_date,
                        ds.start_time,
                        ds.end_time
             ORDER   BY o.created_at DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * All orders — admin list, with optional status filter and pagination.
     * Used by: /admin/orders
     */
    public function getAll(string $status = '', int $page = 1, int $perPage = 20): array
    {
        $where  = '1 = 1';
        $params = [];
        if ($status !== '') {
            $where             = 'o.status = :status';
            $params[':status'] = $status;
        }

        $offset = (max(1, $page) - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT  o.id,
                     o.order_number,
                     o.status,
                     o.subtotal,
                     o.delivery_fee,
                     o.discount,
                     o.total,
                     o.payment_method,
                     o.payment_status,
                     o.created_at,
                     u.name  AS customer_name,
                     u.email AS customer_email,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time
             FROM    orders         o
             JOIN    users          u  ON u.id       = o.user_id
             LEFT    JOIN Delivery_Slots ds ON ds.slot_id = o.delivery_slot_id
             WHERE   $where
             ORDER   BY o.created_at DESC
             LIMIT   :lim OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim',    $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count orders — used for pagination and dashboard stats.
     */
    public function count(string $status = ''): int
    {
        if ($status !== '') {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM orders WHERE status = :s"
            );
            $stmt->execute([':s' => $status]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM orders");
        }
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get all line items for one order.
     * Used by: order detail, confirmation, invoice
     */
    public function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            "SELECT  oi.id,
                     oi.product_id,
                     oi.quantity,
                     oi.price,
                     oi.quantity * oi.price         AS line_total,
                     COALESCE(oi.name, p.name, 'Deleted product') AS name
             FROM    Order_Items oi
             LEFT    JOIN Products p ON p.id = oi.product_id
             WHERE   oi.order_id = :oid"
        );
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    //  READ — reporting / dashboard
    // ─────────────────────────────────────────────────────────

    /**
     * Daily revenue for a date range.
     * Used by: admin sales chart
     */
    public function getDailyRevenue(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT  DATE(created_at) AS order_date,
                     COUNT(*)         AS order_count,
                     SUM(total)       AS revenue
             FROM    orders
             WHERE   DATE(created_at) BETWEEN :from AND :to
             AND     status != 'cancelled'
             GROUP   BY DATE(created_at)
             ORDER   BY order_date ASC"
        );
        $stmt->execute([':from' => $from, ':to' => $to]);
        return $stmt->fetchAll();
    }

    /**
     * Top-selling products by units sold.
     * Used by: admin bestsellers widget
     */
    public function getTopProducts(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT  oi.product_id,
                     p.name                           AS product_name,
                     p.price,
                     SUM(oi.quantity)                 AS units_sold,
                     SUM(oi.quantity * oi.price) AS revenue
             FROM    Order_Items oi
             JOIN    Products    p ON p.id = oi.product_id
             JOIN    orders      o ON o.id         = oi.order_id
             WHERE   o.status != 'cancelled'
             GROUP   BY oi.product_id,
                        p.name,
                        p.price
             ORDER   BY units_sold DESC
             LIMIT   :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Aggregate stats for the admin dashboard card row.
     */
    public function getDashboardStats(): array
    {
        return $this->db->query(
            "SELECT
                COUNT(*)                                               AS total_orders,
                COALESCE(SUM(total), 0)                                AS total_revenue,
                SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered_count,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count,
                COALESCE(SUM(
                    CASE WHEN DATE(created_at) = CURDATE()
                         AND  status != 'cancelled'
                    THEN total END
                ), 0) AS today_revenue
             FROM orders"
        )->fetch();
    }

    // ─────────────────────────────────────────────────────────
    //  WRITE — create order (transactional)
    // ─────────────────────────────────────────────────────────

    /**
     * Place a new order.
     * Inserts into orders + Order_Items, deducts inventory.
     * Entire operation is wrapped in a DB transaction.
     *
     * @param array{
     *   user_id: int,
     *   order_number: string,
     *   subtotal: float,
     *   delivery_fee: float,
     *   discount: float,
     *   total: float,
     *   payment_method: string,
     *   delivery_address: string,
     *   delivery_slot_id: int,
     *   notes: string
     * } $data
     * @param array[] $items  [{product_id, quantity, unit_price}, …]
     * @return int  New order id
     */
    public function create(array $data, array $items): int
{
    $this->db->beginTransaction();
    try {
        // 1. Generate Order Number
        $datePrefix = date('Ymd');
        $stmt = $this->db->prepare(
            "SELECT order_number 
            FROM orders 
            WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(["ORD-{$datePrefix}-%"]);
        $last = $stmt->fetchColumn();
        $parts = explode('-', $last);
        $newSeq = $last ? str_pad((int)end($parts) + 1, 4, '0', STR_PAD_LEFT) : '0001';
        $orderNumber = "ORD-{$datePrefix}-{$newSeq}";

        // 2. Insert Main Order
        // Note: Using 'id' from your image_df335f.png as the PK
        $sqlOrder = "INSERT INTO orders 
            (order_number, user_id, subtotal, delivery_fee, discount, total, status, payment_method, payment_status, delivery_address, delivery_slot_id, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmtOrder = $this->db->prepare($sqlOrder);
        $stmtOrder->execute([
            $orderNumber,
            $data['user_id'] ? (int)$data['user_id'] : null,
            (float)($data['subtotal'] ?? 0),
            (float)($data['delivery_fee'] ?? 0),
            (float)($data['discount'] ?? 0),
            (float)($data['total'] ?? 0),
            'pending',
            $data['payment_method'] ?? 'cod',
            'pending',
            $data['delivery_address'],
            (int)$data['delivery_slot_id'],
            $data['notes'] ?? ''
        ]);

        // 3. GET THE ID IMMEDIATELY
        $orderId = (int)$this->db->lastInsertId();
        
        if ($orderId <= 0) {
            throw new RuntimeException("Failed to get Order ID.");
        }

        // 4. Insert Items (Matching your order_items structure)
        $sqlItems = "INSERT INTO order_items (order_id, product_id, name, quantity, price) VALUES (?, ?, ?, ?, ?)";
        $stmtItems = $this->db->prepare($sqlItems);

        foreach ($items as $item) {
            $itemName = !empty($item['name']) ? $item['name'] : "Item #{$item['product_id']}";
            $stmtItems->execute([
                $orderId,
                (int)$item['product_id'],
                $itemName,
                (int)$item['quantity'],
                (float)$item['price']
            ]);

            // 5. Update Stock in products table
            $updateStock = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $updateStock->execute([(int)$item['quantity'], (int)$item['product_id']]);
        }

        $this->db->commit();
        return $orderId;

    } catch (Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log("Order Error: " . $e->getMessage());
        throw $e;
    }
}

    // ─────────────────────────────────────────────────────────
    //  WRITE — status updates
    // ─────────────────────────────────────────────────────────

    /**
     * Update order status — guards against invalid values.
     * Used by: admin order management
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid order status: $status");
        }
        $stmt = $this->db->prepare(
            "UPDATE orders SET status = :status WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $orderId]);
    }

    /**
     * Cancel an order and restore stock for all its items.
     * Runs inside a transaction.
     */
    public function cancel(int $orderId): bool
    {
        $order = $this->findById($orderId);
        if (!$order || $order['status'] === 'cancelled') {
            return false;
        }

        $this->db->beginTransaction();
        try {
            foreach ($order['items'] as $item) {
                if ($item['product_id']) {
                    $this->inventory->addStock(
                        (int) $item['product_id'],
                        (int) $item['quantity']
                    );
                }
            }
            $this->db->prepare(
                "UPDATE orders SET status = 'cancelled' WHERE id = :id"
            )->execute([':id' => $orderId]);

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw new RuntimeException('Cancel failed: ' . $e->getMessage(), 0, $e);
        }
    }
}