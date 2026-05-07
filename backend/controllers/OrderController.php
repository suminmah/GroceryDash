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

        // Build delivery address string
        $address = implode(', ', array_filter([
            $_POST['line1']   ?? '',
            $_POST['line2']   ?? '',
            $_POST['city']    ?? '',
            $_POST['pincode'] ?? '',
        ]));

        if (!$address || !($_POST['city'] ?? '')) {
            flash('checkout_error', 'Please provide a valid delivery address.');
            redirect(APP_URL . '/checkout');
        }

        $orderNumber = generateOrderNumber();
        $orderData   = [
            'user_id'          => isLoggedIn() ? $_SESSION['user']['id'] : null,
            'order_number'     => $orderNumber,
            'subtotal'         => $totals['subtotal'],
            'delivery_fee'     => $totals['delivery_fee'],
            'total'            => $totals['total'],
            'payment_method'   => $_POST['payment_method'] ?? 'cod',
            'delivery_address' => $address,
            'delivery_slot'    => $_POST['delivery_slot'] ?? null,
            'notes'            => $_POST['notes'] ?? null,
        ];

        $orderItems = array_map(fn($item) => [
            'product_id' => $item['id'],
            'name'       => $item['name'],
            'price'      => $item['sale_price'] ?? $item['price'],
            'quantity'   => $item['quantity'],
        ], $cartItems);

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
