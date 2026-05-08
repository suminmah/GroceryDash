<?php
// backend/helpers/functions.php

/**
 * Redirect to a URL
 */
function redirect(string $url) {
    header("Location: $url");
    exit;
}

/**
 * Sanitize output to prevent XSS
 */
function e(?string $value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format price in Rs.
 */
function formatPrice(float $price): string {
    return 'Rs. ' . number_format($price, 2);
}

/**
 * Generate a random order number
 */
function generateOrderNumber(): string {
    return 'FC-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99);
}

/**
 * Return current user from session
 */
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']['id']);
}

/**
 * Check if user is admin
 */
function isAdmin(): bool {
    return isset($_SESSION['user']['role']) && strtolower($_SESSION['user']['role']) === 'admin';
}

/**
 * Require login — redirect if guest
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Flash messages (store once, show once)
 */
function flash(string $key, string $message = ''): ?string {
    if ($message !== '') {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

/**
 * Get cart item count (session-based)
 */
function cartCount(): int {
    return array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 80): string {
    return strlen($text) > $length ? substr($text, 0, $length) . '…' : $text;
}

/**
 * Slug generator
 */
function slugify(string $text): string {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
}

/**
 * Validate CSRF token
 */
function csrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

/**
 * Calculate cart totals
 */
function cartTotals(array $cartItems): array {
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $price     = $item['sale_price'] ?? $item['price'];
        $subtotal += $price * $item['quantity'];
    }
    $deliveryFee = $subtotal >= FREE_DELIVERY_THRESHOLD ? 0 : DELIVERY_FEE;
    return [
        'subtotal'     => $subtotal,
        'delivery_fee' => $deliveryFee,
        'total'        => $subtotal + $deliveryFee,
    ];
}

function productImageUrl(?string $image): string {
    if (!empty($image)) {
        return APP_URL . '/assets/images/products/' . urlencode($image);
    }
    return APP_URL . '/assets/images/products/placeholder.jpg';
}
