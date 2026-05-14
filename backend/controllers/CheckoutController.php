<?php
// ============================================================
//  backend/controllers/CheckoutController.php
//  Handles: slot selection, order placement, confirmation,
//           order tracking, my-orders list
// ============================================================

require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/DeliverySlotModel.php';
require_once __DIR__ . '/../models/InventoryModel.php';
require_once __DIR__ . '/../helpers/functions.php';

class CheckoutController
{
    private OrderModel        $orders;
    private DeliverySlotModel $slots;
    private InventoryModel    $inventory;
    private PDO               $db;

    public function __construct()
    {
        $this->orders    = new OrderModel();
        $this->slots     = new DeliverySlotModel();
        $this->inventory = new InventoryModel();
        $this->db        = Database::connect();
    }

    // ─────────────────────────────────────────────────────────
    //  GET /checkout
    // ─────────────────────────────────────────────────────────

    /**
     * Show the checkout form.
     * Loads: cart items from session, available delivery slots,
     *        saved addresses (if logged in).
     */
    public function form()
    {
        requireLogin();
    
        $cartItems = $this->getCartItems();
        if (empty($cartItems)) {
            redirect(APP_URL . '/cart');
        }
        
        $totals = calcTotals($cartItems);
        
        // Get available delivery slots
        $slotModel = new DeliverySlotModel();
        $slots = $slotModel->getAvailableSlots(date('Y-m-d'));
        
        // Check if there's a flash error from previous attempt
        $slotError = flash('slot_error');
        $selectedSlotId = $_GET['slot'] ?? $_SESSION['checkout_slot_id'] ?? null;
        
        require __DIR__ . '/../../frontend/views/pages/checkout.php';
    }

    // ─────────────────────────────────────────────────────────
    //  POST /checkout
    // ─────────────────────────────────────────────────────────

    /**
     * Process the checkout form and place the order.
     */
    public function place()
    {
        requireLogin();
        verifyCsrf();
        unset($_SESSION['csrf_token']);
        csrfToken(); // regenerate for next request

        $slotId        = (int) ($_POST['delivery_slot_id'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'cod';

        // Build address from saved or manual fields
        $address = '';
        if (!empty($_POST['saved_address'])) {
            $addressId = (int) $_POST['saved_address'];
            $stmt = Database::connect()->prepare("SELECT * FROM addresses WHERE id = ? LIMIT 1");
            $stmt->execute([$addressId]);
            $savedAddr = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($savedAddr) {
                $address = implode(', ', array_filter([
                    $savedAddr['line1'],
                    $savedAddr['line2'] ?? '',
                    $savedAddr['city'],
                    $savedAddr['pincode'],
                ]));
            }
        }
        if (empty($address)) {
            $address = implode(', ', array_filter([
                $_POST['line1']   ?? '',
                $_POST['line2']   ?? '',
                $_POST['city']    ?? '',
                $_POST['pincode'] ?? '',
            ]));
        }
        if (empty(trim($address))) {
            flash('checkout_error', 'Please provide a valid delivery address.');
            redirect(APP_URL . '/checkout');
        }

        // Validate slot
        if (!$slotId || !$this->slots->isSlotAvailable($slotId)) {
            flash('slot_error', 'The selected delivery slot is no longer available.');
            redirect(APP_URL . '/checkout');
        }

        // Cart
        $cartItems = $this->getCartItems();
        if (empty($cartItems)) {
            redirect(APP_URL . '/cart');
        }

        $totals = $this->calcTotals($cartItems);  // $this-> not global

        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['sale_price'] ?? $item['price'],
            ];
        }

        $orderData = [
            'user_id'          => $_SESSION['user']['id'],
            'subtotal'         => $totals['subtotal'],
            'delivery_fee'     => $totals['delivery_fee'],
            'discount'         => 0,
            'total'            => $totals['total'],
            'payment_method'   => $paymentMethod,
            'delivery_address' => $address,
            'delivery_slot_id' => $slotId,
            'notes'            => $_POST['notes'] ?? '',
        ];

        try {
            $orderId = $this->orders->create($orderData, $items);
            $this->slots->incrementBooked($slotId);
            $_SESSION['cart'] = [];
            flash('success', 'Your order has been placed successfully!');
            redirect(APP_URL . '/order/confirmation/' . $orderId);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect(APP_URL . '/checkout');
        }
    }

    // ─────────────────────────────────────────────────────────
    //  GET /order/confirmation/{id}
    // ─────────────────────────────────────────────────────────

    /**
     * Order confirmation page shown after successful checkout.
     */
    public function confirmation(int $orderId)
    {
        $order = $this->orders->findById($orderId);

        if (!$order) {
            http_response_code(404);
            require __DIR__ . '/../../frontend/views/errors/404.php';
            return;
        }

        // Security: only the customer who placed it (or admin) may view
        if (isLoggedIn() && !isAdmin()) {
            if ((int) $order['user_id'] !== (int) $_SESSION['user']['id']) {
                http_response_code(403);
                die('Access denied.');
            }
        }

        $pageTitle = 'Order Confirmed! — GroceryDash';
        require __DIR__ . '/../../frontend/views/pages/order-confirmation.php';
    }

    // ─────────────────────────────────────────────────────────
    //  GET /order/track/{id}
    // ─────────────────────────────────────────────────────────

    /**
     * Public order tracking page (by order_id).
     */
    public function track(int $orderId)
    {
        $order = $this->orders->findById($orderId);

        if (!$order) {
            http_response_code(404);
            require __DIR__ . '/../../frontend/views/errors/404.php';
            return;
        }

        $pageTitle = 'Track Order #' . $orderId . ' — GroceryDash';
        require __DIR__ . '/../../frontend/views/pages/track-order.php';
    }

    // ─────────────────────────────────────────────────────────
    //  GET /account/orders
    // ─────────────────────────────────────────────────────────

    /**
     * Logged-in customer's order history.
     */
    public function myOrders()
    {
        if (!isset($_SESSION['user']['id'])) {
            redirect(APP_URL . '/login');
        }
        // requireLogin();
        $orderModel = new OrderModel();
        $orders = $orderModel->getByUser($_SESSION['user']['id']);
        $pageTitle = 'My Orders';
        require __DIR__ . '/../../frontend/views/pages/my-orders.php';
    }

    // ─────────────────────────────────────────────────────────
    //  GET /account/orders/{id}  — single order detail
    // ─────────────────────────────────────────────────────────

    /**
     * Customer view of a single order's full detail.
     */
    public function orderDetail(int $orderId)
    {
        requireLogin();
        $order = $this->orders->findById($orderId);

        if (!$order || (int) $order['user_id'] !== (int) $_SESSION['user']['id']) {
            http_response_code(403);
            die('Access denied.');
        }

        $pageTitle = 'Order #' . $orderId . ' — GroceryDash';
        require __DIR__ . '/../../frontend/views/pages/order-detail.php';
    }

    // ─────────────────────────────────────────────────────────
    //  POST /order/cancel/{id}
    // ─────────────────────────────────────────────────────────

    /**
     * Customer cancels their own pending order.
     */
    public function cancel(int $orderId)
    {
        requireLogin();
        verifyCsrf();

        $order = $this->orders->findById($orderId);

        if (!$order || (int) $order['user_id'] !== (int) $_SESSION['user']['id']) {
            http_response_code(403);
            die('Access denied.');
        }

        if (!in_array($order['status'], ['Pending', 'Confirmed'], true)) {
            flash('error', 'This order can no longer be cancelled.');
            redirect(APP_URL . '/account/orders/' . $orderId);
        }

        try {
            $this->orders->cancel($orderId);
            flash('success', 'Order #' . $orderId . ' has been cancelled.');
        } catch (RuntimeException $e) {
            flash('error', 'Could not cancel order: ' . $e->getMessage());
        }

        redirect(APP_URL . '/account/orders');
    }

    // ─────────────────────────────────────────────────────────
    //  PRIVATE helpers
    // ─────────────────────────────────────────────────────────

    /**
     * Retrieve cart items from the session.
     * This is an alias for buildCartItems() used by public controller actions.
     *
     * @return array[]
     */
    private function getCartItems(): array
    {
        return $this->buildCartItems();
    }

    /**
     * Rebuild cart items from session, enriched with DB product data.
     * Session stores: ['p_{id}' => ['product_id' => x, 'quantity' => y]]
     *
     * @return array[]
     */
    private function buildCartItems(): array
    {
        require_once __DIR__ . '/../models/ProductModel.php';
        $productModel = new ProductModel();
        $items = [];

        foreach ($_SESSION['cart'] ?? [] as $entry) {
            $product = $productModel->findById((int) $entry['product_id']);
            if ($product) {
                $items[] = array_merge($product, ['quantity' => (int) $entry['quantity']]);
            }
        }
        return $items;
    }

    /**
     * Calculate subtotal, delivery fee, and total from cart items.
     *
     * @return array{subtotal:float, delivery_fee:float, total:float}
     */
    private function calcTotals(array $cartItems): array
    {
        $subtotal = 0.0;
        foreach ($cartItems as $item) {
            $subtotal += (float) $item['price'] * (int) $item['quantity'];
        }
        $deliveryFee = $subtotal >= FREE_DELIVERY_THRESHOLD ? 0.0 : (float) DELIVERY_FEE;
        return [
            'subtotal'     => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total'        => $subtotal + $deliveryFee,
        ];
    }

    /**
     * Validate that every item in the cart still has enough stock.
     *
     * @return string[]  Array of human-readable error messages (empty = OK)
     */
    private function validateCartStock(array $cartItems): array
    {
        $errors     = [];
        $productIds = array_column($cartItems, 'product_id');
        $stockMap   = $this->inventory->getForProducts($productIds);

        foreach ($cartItems as $item) {
            $pid   = (int) $item['product_id'];
            $stock = (int) ($stockMap[$pid]['stock_qty'] ?? 0);
            $qty   = (int) $item['quantity'];

            if ($stock < $qty) {
                $errors[] = "\"{$item['name']}\" only has {$stock} left (you have {$qty} in cart).";
            }
        }
        return $errors;
    }
}
