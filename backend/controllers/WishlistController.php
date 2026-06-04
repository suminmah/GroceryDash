<?php
require_once __DIR__ . '/../models/WishlistModel.php';
require_once __DIR__ . '/../helpers/functions.php';

class WishlistController
{
    private WishlistModel $wishlist;

    public function __construct()
    {
        $this->wishlist = new WishlistModel();
    }

    public function index()
    {
        requireLogin();
        $items = $this->wishlist->getByUser($_SESSION['user']['id']);
        $pageTitle = 'My Wishlist — GroceryDash';
        require __DIR__ . '/../../frontend/views/pages/wishlist.php';
    }

    public function toggle()
    {
        header('Content-Type: application/json');

        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'redirect' => APP_URL . '/login']);
            exit;
        }

        // CSRF verification – expect only 'csrf_token' field
        if (!isset($_POST['csrf']) || !verifyCsrf($_POST['csrf'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
            exit;
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        // Robust user ID retrieval
        $userId = (int) ($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);

        if ($productId <= 0 || $userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product or user ID.']);
            exit;
        }

        try {
            if ($this->wishlist->isWishlisted($userId, $productId)) {
                $this->wishlist->remove($userId, $productId);
                echo json_encode(['success' => true, 'wishlisted' => false]);
            } else {
                $this->wishlist->add($userId, $productId);
                echo json_encode(['success' => true, 'wishlisted' => true]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}