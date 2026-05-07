<?php
// backend/controllers/OrderController.php

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/functions.php';

class OrderController {
    private Order $order;
    private User  $user;

    public function __construct() {
        $this->order = new Order();
        $this->user  = new User();
    }

    /** GET /checkout */
    public function checkoutForm() {
        $cartController = new \CartController();
        $cartItems      = $cartController->buildCartItems();

        if (empty($cartItems)) {
            redirect(APP_URL . '/cart');
        }

        $totals    = cartTotals($cartItems);
        $addresses = isLoggedIn()
            ? $this->user->getAddresses($_SESSION['user']['id'])
            : [];

        require __DIR__ . '/../../frontend/views/pages/checkout.php';
    }

    /** POST /checkout */
    public function placeOrder() {
        verifyCsrf();

        $cartController = new \CartController();
        $cartItems      = $cartController->buildCartItems();

        if (empty($cartItems)) {
            redirect(APP_URL . '/cart');
        }

        $totals = cartTotals($cartItems);
        error_log('Totals: ' . print_r($totals, true));

        global $db;

        $address = '';

        // 2. Check if a saved address was selected
        if (!empty($_POST['saved_address'])) {
            $addressId = (int)$_POST['saved_address'];
            
            // Fetch directly from the addresses table
            $stmt = $db->prepare("SELECT * FROM addresses WHERE id = ? LIMIT 1");
            $stmt->execute([$addressId]);
            $savedAddr = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($savedAddr) {
                $address = implode(', ', array_filter([
                    $savedAddr['line1'],
                    $savedAddr['line2'] ?? '',
                    $savedAddr['city'],
                    $savedAddr['pincode']
                ]));
            }
        }


        // Build delivery address string
        if (empty($address)) {
            $addressParts = array_filter([
                $_POST['line1']   ?? '',
                $_POST['line2']   ?? '',
                $_POST['city']    ?? '',
                $_POST['pincode'] ?? '',
            ]);

            if (!empty($addressParts)) {
                $address = implode(', ', $addressParts);
            }
        }

        if (empty(trim($address))) {
            flash('checkout_error', 'Please provide a valid delivery address.');
            redirect(APP_URL . '/checkout');
        }

        $orderNumber = generateOrderNumber();
        $orderData   = [
            'user_id'          => isLoggedIn() ? $_SESSION['user']['id'] : null,
            'subtotal'         => (float) ($totals['subtotal'] ?? 0),
            'delivery_fee'     => (float) ($totals['delivery_fee'] ?? 0),
            'discount'         => (float) ($totals['discount'] ?? 0),
            'total'            => (float) ($totals['total'] ?? 0),
            'payment_method'   => $_POST['payment_method'] ?? 'cod',
            'delivery_address' => $address,
            'delivery_slot_id'    => $_POST['delivery_slot'] ?? null,
            'notes'            => $_POST['notes'] ?? null,
        ];

        $orderItems = array_map(function($item) {
            $name = $item['name'] ?? 'Product';

            return [
                'product_id' => $item['id'],
                'price'      => $item['sale_price'] ?? $item['price'],
                'quantity'   => $item['quantity'],
            ];
    }, $cartItems);

        $orderId = $this->order->create($orderData, $orderItems);
    

        // Clear cart
        $_SESSION['cart']           = [];
        $_SESSION['last_order_num'] = $orderNumber;

        redirect(APP_URL . '/order/confirmation/' . $orderNumber);
    }

    /** GET /order/confirmation/{number} */
    public function confirmation(string $number) {
        $order = $this->order->findByNumber($number);
        if (!$order) {
            http_response_code(404);
            require __DIR__ . '/../../frontend/views/errors/404.php';
            return;
        }
        require __DIR__ . '/../../frontend/views/pages/order-confirmation.php';
    }

    /** GET /order/track/{number} */
    public function track(string $number){
        $order = $this->order->findByNumber($number);
        if (!$order) {
            http_response_code(404);
            require __DIR__ . '/../../frontend/views/errors/404.php';
            return;
        }
        require __DIR__ . '/../../frontend/views/pages/track-order.php';
    }

    /** GET /account/orders */
    public function myOrders() {
        requireLogin();
        $orders = $this->order->getByUser($_SESSION['user']['id']);
        require __DIR__ . '/../../frontend/views/pages/my-orders.php';
    }
}
