<?php
// backend/models/Order.php

require_once __DIR__ . '/../config/database.php';

class Order {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function create(array $data, array $items): int {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO orders
                    (user_id, order_number, subtotal, delivery_fee, discount, total,
                     payment_method, delivery_address, delivery_slot, notes)
                 VALUES
                    (:uid, :num, :sub, :dfee, :disc, :total,
                     :pay, :addr, :slot, :notes)"
            );
            $stmt->execute([
                ':uid'   => $data['user_id']         ?? null,
                ':num'   => $data['order_number'],
                ':sub'   => $data['subtotal'],
                ':dfee'  => $data['delivery_fee'],
                ':disc'  => $data['discount']        ?? 0,
                ':total' => $data['total'],
                ':pay'   => $data['payment_method']  ?? 'cod',
                ':addr'  => $data['delivery_address'],
                ':slot'  => $data['delivery_slot']   ?? null,
                ':notes' => $data['notes']            ?? null,
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                "INSERT INTO order_items (order_id, product_id, name, price, quantity)
                 VALUES (:oid, :pid, :name, :price, :qty)"
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    ':oid'   => $orderId,
                    ':pid'   => $item['product_id'],
                    ':name'  => $item['name'],
                    ':price' => $item['price'],
                    ':qty'   => $item['quantity'],
                ]);
                // Decrement stock
                $this->db->prepare(
                    "UPDATE products SET stock = GREATEST(stock - :qty, 0) WHERE id = :pid"
                )->execute([':qty' => $item['quantity'], ':pid' => $item['product_id']]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findByNumber(string $number): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM orders WHERE order_number = :num LIMIT 1"
        );
        $stmt->execute([':num' => $number]);
        $order = $stmt->fetch();
        if (!$order) return null;
        $order['items'] = $this->getItems((int) $order['id']);
        return $order;
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function getItems(int $orderId): array {
        $stmt = $this->db->prepare(
            "SELECT oi.*, p.image FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = :oid"
        );
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $orderId, string $status): bool {
        $stmt = $this->db->prepare(
            "UPDATE orders SET status = :status WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $orderId]);
    }

    public function findById(int $orderId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM orders WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();
        if (!$order) return null;
        $order['items'] = $this->getItems($orderId);
        return $order;
    }
}
