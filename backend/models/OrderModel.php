<?php
// ============================================================
//  backend/models/OrderModel.php
//  Aligned to ACTUAL DB columns:
//    users        → id, name, email, password, role
//    Orders       → order_id, user_id, total_amount, status,
//                   delivery_slot_id, order_date
//    Order_Items  → item_id, order_id, product_id, quantity, unit_price
//    Delivery_Slots → slot_id, slot_date, start_time, end_time, max_capacity
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/InventoryModel.php';

class OrderModel
{
    private PDO            $db;
    private InventoryModel $inventory;

    private const VALID_STATUSES = [
        'Pending', 'Confirmed', 'Packed',
        'Out for Delivery', 'Delivered', 'Cancelled',
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
     * Fetch one complete order with items, slot info and customer name.
     * Used by: order detail, confirmation page, tracking page
     */
    public function findById(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT  o.order_id,
                     o.user_id,
                     o.total_amount,
                     o.status,
                     o.order_date,
                     o.delivery_slot_id,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time,
                     u.name   AS customer_name,
                     u.email  AS customer_email
             FROM    Orders         o
             JOIN    Delivery_Slots ds ON ds.slot_id = o.delivery_slot_id
             JOIN    users          u  ON u.id       = o.user_id
             WHERE   o.order_id = :id
             LIMIT   1"
        );
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();
        if (!$order) return null;

        $order['items'] = $this->getOrderItems($orderId);
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
            "SELECT  o.order_id,
                     o.total_amount,
                     o.status,
                     o.order_date,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time,
                     COUNT(oi.item_id) AS item_count
             FROM    Orders         o
             JOIN    Delivery_Slots ds ON ds.slot_id  = o.delivery_slot_id
             JOIN    Order_Items    oi ON oi.order_id = o.order_id
             WHERE   o.user_id = :uid
             GROUP   BY o.order_id,
                        o.total_amount,
                        o.status,
                        o.order_date,
                        ds.slot_date,
                        ds.start_time,
                        ds.end_time
             ORDER   BY o.order_date DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * All orders — admin list with optional status filter and pagination.
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
                     o.total,
                     o.status,
                     o.created_at,
                     u.name   AS customer_name,
                     u.email  AS customer_email,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time
             FROM    Orders         o
             JOIN    users          u  ON u.id      = o.user_id
             JOIN    Delivery_Slots ds ON ds.slot_id = o.delivery_slot_id
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
                "SELECT COUNT(*) FROM Orders WHERE status = :s"
            );
            $stmt->execute([':s' => $status]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM Orders");
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
            "SELECT  oi.item_id,
                     oi.product_id,
                     oi.quantity,
                     oi.unit_price,
                     oi.quantity * oi.unit_price        AS line_total,
                     COALESCE(p.name, 'Deleted product') AS product_name,
                     p.sku,
                     p.is_perishable
             FROM    Order_Items oi
             LEFT    JOIN Products p ON p.product_id = oi.product_id
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
            "SELECT DATE(created_at) AS order_date,
                    COUNT(*) AS order_count,
                    SUM(total) AS revenue
            FROM   orders
            WHERE  DATE(created_at) BETWEEN :from AND :to
            AND  status != 'cancelled'
            GROUP  BY DATE(created_at)
            ORDER  BY order_date ASC"
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
                    p.name AS product_name,
                    SUM(oi.quantity) AS units_sold,
                    SUM(oi.quantity * oi.price) AS revenue
            FROM    order_items oi
            JOIN    products p ON p.id = oi.product_id
            JOIN    orders o ON o.id = oi.order_id
            WHERE   o.status != 'cancelled'
            GROUP   BY oi.product_id
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
                COUNT(*)                                        AS total_orders,
                COALESCE(SUM(total), 0)                  AS total_revenue,
                SUM(CASE WHEN status = 'Pending'   THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) AS delivered_count,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled_count,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE()
                                  AND status != 'Cancelled'
                             THEN total END), 0)         AS today_revenue
             FROM Orders"
        )->fetch();
    }

    // ─────────────────────────────────────────────────────────
    //  WRITE — create order (transactional)
    // ─────────────────────────────────────────────────────────

    /**
     * Place a new order.
     * Validates stock, inserts Orders + Order_Items, deducts inventory.
     * Entire operation runs in one DB transaction.
     *
     * @param  array{user_id:int, delivery_slot_id:int, total_amount:float} $orderData
     * @param  array[] $items  [{product_id, quantity, unit_price}, …]
     * @return int  New order_id
     * @throws RuntimeException on stock shortage or DB failure
     */
    public function create(array $orderData, array $items): int
    {
        // Pre-flight stock check
        foreach ($items as $item) {
            if (!$this->inventory->hasSufficientStock(
                (int) $item['product_id'],
                (int) $item['quantity']
            )) {
                throw new RuntimeException(
                    "Insufficient stock for product ID {$item['product_id']}"
                );
            }
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO Orders (user_id, total_amount, status, delivery_slot_id)
                 VALUES (:uid, :amount, 'Pending', :slot)"
            );
            $stmt->execute([
                ':uid'    => (int)   $orderData['user_id'],
                ':amount' => (float) $orderData['total_amount'],
                ':slot'   => (int)   $orderData['delivery_slot_id'],
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                "INSERT INTO Order_Items (order_id, product_id, quantity, unit_price)
                 VALUES (:oid, :pid, :qty, :price)"
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    ':oid'   => $orderId,
                    ':pid'   => (int)   $item['product_id'],
                    ':qty'   => (int)   $item['quantity'],
                    ':price' => (float) $item['unit_price'],
                ]);
                $this->inventory->deductStock(
                    (int) $item['product_id'],
                    (int) $item['quantity']
                );
            }

            $this->db->commit();
            return $orderId;

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw new RuntimeException('Order creation failed: ' . $e->getMessage(), 0, $e);
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
            "UPDATE Orders SET status = :status WHERE order_id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $orderId]);
    }

    /**
     * Cancel an order and restore stock for all its items.
     * Runs inside a transaction.
     * Used by: customer cancel, admin cancel
     */
    public function cancel(int $orderId): bool
    {
        $order = $this->findById($orderId);
        if (!$order || $order['status'] === 'Cancelled') {
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
                "UPDATE Orders SET status = 'Cancelled' WHERE order_id = :id"
            )->execute([':id' => $orderId]);

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw new RuntimeException('Cancel failed: ' . $e->getMessage(), 0, $e);
        }
    }
}