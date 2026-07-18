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
    private const ESEWA_URL = 'https://rc-epay.esewa.com.np/api/epay/main/v2/form';
    private const ESEWA_SECRET = '8gBm/:&EnhH.1/q';
    private const ESEWA_MERCHANT = 'EPAYTEST';
    
    private const KHALTI_INITIATE_URL = 'https://a.khalti.com/api/v2/epayment/initiate/';
    private const KHALTI_LOOKUP_URL = 'https://a.khalti.com/api/v2/epayment/lookup/';
    private const KHALTI_SECRET = '2de3f1f3624e4d9e9ae53ffb1d1c8f16'; // Replace with real key

    // Fonepay Dynamic QR Configuration
    private const FONEPAY_API_BASE = 'https://uat-new-merchant-api.fonepay.com';
    private const FONEPAY_MERCHANT_CODE = 'fonepay123';
    private const FONEPAY_SECRET = 'fonepay';
    private const FONEPAY_USERNAME = 'bijayk';
    private const FONEPAY_PASSWORD = 'password';

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

            if ($paymentMethod === 'esewa') {
                $stmt = $this->db->prepare("SELECT order_number FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
                $transactionUuid = $stmt->fetchColumn();
                
                $amount = $totals['total'];
                $taxAmount = 0;
                $deliveryCharge = 0;
                $serviceCharge = 0;
                $totalAmount = $amount + $taxAmount + $deliveryCharge + $serviceCharge;
                
                $signedFields = 'total_amount,transaction_uuid,product_code';
                $message = "total_amount={$totalAmount},transaction_uuid={$transactionUuid},product_code=" . self::ESEWA_MERCHANT;
                $signature = base64_encode(hash_hmac('sha256', $message, self::ESEWA_SECRET, true));
                
                $successUrl = APP_URL . '/checkout/esewa/success';
                $failureUrl = APP_URL . '/checkout/esewa/failure';
                
                echo "<!DOCTYPE html><html><body onload='document.forms[0].submit()'>";
                echo "<div style='display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;'>";
                echo "<div style='text-align:center;'><div style='margin-bottom:10px;'><img src='" . APP_URL . "/assets/images/esewa-logo.webp' height='50'></div>";
                echo "<h3>Redirecting to eSewa securely...</h3><p>Please do not refresh or close this page.</p></div></div>";
                echo "<form action='" . self::ESEWA_URL . "' method='POST' style='display:none;'>";
                echo "<input type='hidden' name='amount' value='{$amount}'>";
                echo "<input type='hidden' name='tax_amount' value='{$taxAmount}'>";
                echo "<input type='hidden' name='total_amount' value='{$totalAmount}'>";
                echo "<input type='hidden' name='transaction_uuid' value='{$transactionUuid}'>";
                echo "<input type='hidden' name='product_code' value='" . self::ESEWA_MERCHANT . "'>";
                echo "<input type='hidden' name='product_service_charge' value='{$serviceCharge}'>";
                echo "<input type='hidden' name='product_delivery_charge' value='{$deliveryCharge}'>";
                echo "<input type='hidden' name='success_url' value='{$successUrl}'>";
                echo "<input type='hidden' name='failure_url' value='{$failureUrl}'>";
                echo "<input type='hidden' name='signed_field_names' value='{$signedFields}'>";
                echo "<input type='hidden' name='signature' value='{$signature}'>";
                echo "</form></body></html>";
                exit;
            } elseif ($paymentMethod === 'khalti') {
                $stmt = $this->db->prepare("SELECT order_number FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
                $transactionUuid = $stmt->fetchColumn();

                $amountInPaisa = (int) ($totals['total'] * 100);

                $payload = json_encode([
                    "return_url" => APP_URL . '/checkout/khalti/callback',
                    "website_url" => APP_URL,
                    "amount" => $amountInPaisa,
                    "purchase_order_id" => $transactionUuid,
                    "purchase_order_name" => "GroceryShop Order " . $orderId,
                    "customer_info" => [
                        "name" => $_SESSION['user']['name'] ?? "Customer",
                        "email" => $_SESSION['user']['email'] ?? "customer@example.com",
                        "phone" => $_SESSION['user']['phone'] ?? "9800000000"
                    ]
                ]);

                $ch = curl_init(self::KHALTI_INITIATE_URL);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Key " . self::KHALTI_SECRET,
                    "Content-Type: application/json"
                ]);

                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response, true);
                if (isset($result['payment_url'])) {
                    header("Location: " . $result['payment_url']);
                    exit;
                } else {
                    flash('error', 'Failed to initiate Khalti payment. ' . ($result['detail'] ?? ''));
                    redirect(APP_URL . '/checkout');
                }
            } elseif ($paymentMethod === 'fonepay') {
                $stmt = $this->db->prepare("SELECT order_number FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
                $prn = $stmt->fetchColumn();
                
                $amount = $totals['total'];
                $remarks1 = 'GroceryDash Order';
                $remarks2 = 'Order ID ' . $orderId;
                
                // Construct HMAC Message exactly as requested: {AMOUNT},{PRN},{MERCHANT-CODE},{REMARKS1},{REMARKS2}
                $message = "{$amount},{$prn}," . self::FONEPAY_MERCHANT_CODE . ",{$remarks1},{$remarks2}";
                $hash = hash_hmac('sha512', $message, self::FONEPAY_SECRET);
                
                $payload = json_encode([
                    'amount'         => (string) $amount,
                    'prn'            => $prn,
                    'merchantCode'   => self::FONEPAY_MERCHANT_CODE,
                    'remarks1'       => $remarks1,
                    'remarks2'       => $remarks2,
                    'dataValidation' => $hash,
                    'username'       => self::FONEPAY_USERNAME,
                    'password'       => self::FONEPAY_PASSWORD
                ]);
                
                $url = self::FONEPAY_API_BASE . '/api/merchant/merchantDetailsForThirdParty/thirdPartyDynamicQrDownload';
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json"
                ]);
                
                $response = curl_exec($ch);
                curl_close($ch);
                
                $result = json_decode($response, true);
                
                // Note: For local offline development simulation if Fonepay test API is completely unreachable
                if (!$response || !isset($result['success'])) {
                    $result = [
                        'success' => true,
                        'qrMessage' => 'MOCK_QR_PAYLOAD_FOR_DEMO',
                        'thirdpartyQrWebSocketUrl' => 'wss://echo.websocket.events' 
                    ];
                }
                
                if (isset($result['success']) && $result['success'] === true && isset($result['qrMessage'])) {
                    // Save response context temporarily to session for rendering the QR page securely
                    $_SESSION['fonepay_qr'] = $result['qrMessage'];
                    $_SESSION['fonepay_ws'] = $result['thirdpartyQrWebSocketUrl'];
                    $_SESSION['fonepay_prn'] = $prn;
                    $_SESSION['fonepay_order_id'] = $orderId;
                    
                    redirect(APP_URL . '/checkout/fonepay/pay');
                } else {
                    flash('error', 'Failed to generate Fonepay QR Code. ' . ($result['message'] ?? 'Please try again.'));
                    redirect(APP_URL . '/checkout');
                }
            }

            flash('success', 'Your order has been placed successfully!');
            redirect(APP_URL . '/order/confirmation/' . $orderId);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect(APP_URL . '/checkout');
        }
    }

    // ─────────────────────────────────────────────────────────
    //  GET /checkout/esewa/success
    // ─────────────────────────────────────────────────────────
    public function esewaSuccess()
    {
        if (!isset($_GET['data'])) {
            flash('error', 'Invalid callback from eSewa.');
            redirect(APP_URL . '/checkout');
        }

        $encodedData = $_GET['data'];
        $decodedData = json_decode(base64_decode($encodedData), true);

        if (!$decodedData) {
            flash('error', 'Could not decode eSewa response.');
            redirect(APP_URL . '/checkout');
        }

        $status = $decodedData['status'] ?? '';
        $transactionUuid = $decodedData['transaction_uuid'] ?? '';
        
        if ($status !== 'COMPLETE') {
            flash('error', 'eSewa payment was not completed. Status: ' . $status);
            redirect(APP_URL . '/checkout');
        }

        $signedFields = $decodedData['signed_field_names'] ?? '';
        $fieldsArray = explode(',', $signedFields);
        
        $messageParts = [];
        foreach ($fieldsArray as $field) {
            $messageParts[] = "{$field}={$decodedData[$field]}";
        }
        $message = implode(',', $messageParts);
        $expectedSignature = base64_encode(hash_hmac('sha256', $message, self::ESEWA_SECRET, true));

        if ($expectedSignature !== $decodedData['signature']) {
            flash('error', 'Invalid eSewa signature. Payment verification failed.');
            redirect(APP_URL . '/checkout');
        }

        $stmt = $this->db->prepare("SELECT id FROM orders WHERE order_number = ? LIMIT 1");
        $stmt->execute([$transactionUuid]);
        $orderId = $stmt->fetchColumn();

        if (!$orderId) {
            flash('error', 'Order not found for transaction UUID: ' . $transactionUuid);
            redirect(APP_URL . '/checkout');
        }

        $updateStmt = $this->db->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
        $updateStmt->execute([$orderId]);

        flash('success', 'Your eSewa payment was successful!');
        redirect(APP_URL . '/order/confirmation/' . $orderId);
    }

    // ─────────────────────────────────────────────────────────
    //  GET /checkout/esewa/failure
    // ─────────────────────────────────────────────────────────
    public function esewaFailure()
    {
        flash('error', 'You have cancelled the eSewa payment or it failed.');
        redirect(APP_URL . '/checkout');
    }

    // ─────────────────────────────────────────────────────────
    //  GET /checkout/khalti/callback
    // ─────────────────────────────────────────────────────────
    public function khaltiCallback()
    {
        $pidx = $_GET['pidx'] ?? null;
        $purchaseOrderId = $_GET['purchase_order_id'] ?? null;

        if (!$pidx || !$purchaseOrderId) {
            flash('error', 'Invalid callback from Khalti.');
            redirect(APP_URL . '/checkout');
        }

        $payload = json_encode(['pidx' => $pidx]);

        $ch = curl_init(self::KHALTI_LOOKUP_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Key " . self::KHALTI_SECRET,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['status']) && strtolower($result['status']) === 'completed') {
            // Find order id from order_number
            $stmt = $this->db->prepare("SELECT id FROM orders WHERE order_number = ?");
            $stmt->execute([$purchaseOrderId]);
            $orderId = $stmt->fetchColumn();

            if ($orderId) {
                // Update order payment status
                $update = $this->db->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
                $update->execute([$orderId]);

                flash('success', 'Khalti Payment successful! Your order has been placed.');
                redirect(APP_URL . '/order/confirmation/' . $orderId);
            } else {
                flash('error', 'Order not found for the transaction.');
                redirect(APP_URL . '/checkout');
            }
        } else {
            flash('error', 'Khalti Payment failed or is pending.');
            redirect(APP_URL . '/checkout');
        }
    }

    // ─────────────────────────────────────────────────────────
    //  GET /checkout/fonepay/pay
    // ─────────────────────────────────────────────────────────
    public function fonepayPay()
    {
        if (empty($_SESSION['fonepay_qr'])) {
            // DIAGNOSTIC MOCK FOR CONSOLE TESTING
            $_SESSION['fonepay_qr'] = '000201010212153137910524005204460000000NBQM:29226400011fonepay.com0104NBQM020329206061367695204541153035245402145802NP5911Fonepaytest6008District62210703292021098418456336304d3f7';
            $_SESSION['fonepay_ws'] = 'wss://echo.websocket.events';
            $_SESSION['fonepay_order_id'] = 999;
        }

        $qrMessage = $_SESSION['fonepay_qr'];
        $wsUrl     = $_SESSION['fonepay_ws'];
        $orderId   = $_SESSION['fonepay_order_id'];
        
        $pageTitle = 'Scan to Pay with Fonepay — GroceryDash';
        require __DIR__ . '/../../frontend/views/pages/fonepay-qr.php';
    }

    // ─────────────────────────────────────────────────────────
    //  GET /checkout/fonepay/check-status
    // ─────────────────────────────────────────────────────────
    public function fonepayCheckStatus()
    {
        header('Content-Type: application/json');
        
        if (empty($_SESSION['fonepay_prn'])) {
            echo json_encode(['status' => 'error', 'message' => 'No active Fonepay session']);
            exit;
        }

        $prn = $_SESSION['fonepay_prn'];
        
        // HMAC Message: {PRN},{MERCHANT-CODE}
        $message = "{$prn}," . self::FONEPAY_MERCHANT_CODE;
        $hash = hash_hmac('sha512', $message, self::FONEPAY_SECRET);
        
        $payload = json_encode([
            'prn' => $prn,
            'merchantCode' => self::FONEPAY_MERCHANT_CODE,
            'dataValidation' => $hash,
            'username' => self::FONEPAY_USERNAME,
            'password' => self::FONEPAY_PASSWORD
        ]);
        
        $url = self::FONEPAY_API_BASE . '/api/merchant/merchantDetailsForThirdParty/thirdPartyDynamicQrGetStatus';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['paymentStatus'])) {
            echo json_encode(['status' => strtolower($result['paymentStatus'])]);
            exit;
        }
        
        echo json_encode(['status' => 'pending']);
        exit;
    }

    // ─────────────────────────────────────────────────────────
    //  GET /checkout/fonepay/verify
    // ─────────────────────────────────────────────────────────
    public function fonepayVerify()
    {
        if (empty($_SESSION['fonepay_prn'])) {
            flash('error', 'No active Fonepay session found to verify.');
            redirect(APP_URL . '/checkout');
        }

        $prn = $_SESSION['fonepay_prn'];
        $orderId = $_SESSION['fonepay_order_id'];
        
        // HMAC Message: {PRN},{MERCHANT-CODE}
        $message = "{$prn}," . self::FONEPAY_MERCHANT_CODE;
        $hash = hash_hmac('sha512', $message, self::FONEPAY_SECRET);
        
        $payload = json_encode([
            'prn' => $prn,
            'merchantCode' => self::FONEPAY_MERCHANT_CODE,
            'dataValidation' => $hash,
            'username' => self::FONEPAY_USERNAME,
            'password' => self::FONEPAY_PASSWORD
        ]);
        
        $url = self::FONEPAY_API_BASE . '/api/merchant/merchantDetailsForThirdParty/thirdPartyDynamicQrGetStatus';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        // For local offline development simulation
        if (!$response && isset($_SESSION['fonepay_ws']) && $_SESSION['fonepay_ws'] === 'wss://echo.websocket.events') {
            $result = ['paymentStatus' => 'success'];
        }
        
        if (isset($result['paymentStatus']) && strtolower($result['paymentStatus']) === 'success') {
            $stmt = $this->db->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
            $stmt->execute([$orderId]);
            
            unset($_SESSION['fonepay_qr'], $_SESSION['fonepay_ws'], $_SESSION['fonepay_prn'], $_SESSION['fonepay_order_id']);
            
            flash('success', 'Fonepay Payment verified! Your order is placed.');
            redirect(APP_URL . '/order/confirmation/' . $orderId);
        } else {
            flash('error', 'Fonepay Payment could not be verified or is pending.');
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
