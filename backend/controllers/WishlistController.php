<?php
// backend/controllers/WishlistController.php
require_once __DIR__ . '/../models/WishlistModel.php';
require_once __DIR__ . '/../helpers/functions.php';

class WishlistController
{
    private WishlistModel $wishlist;

    public function __construct()
    {
        $this->wishlist = new WishlistModel();
    }

    /**
     * Renders the Wishlist Grid UI Collection View
     */
    public function index()
    {
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        
        if ($userId > 0) {
            $items = $this->wishlist->getByUser($userId);
        } else {
            $guestIds = $_SESSION['guest_wishlist'] ?? [];
            $items = $this->wishlist->getByProductIds($guestIds);
        }
        
        $pageTitle = 'My Wishlist — GroceryDash';
        require __DIR__ . '/../../frontend/views/pages/wishlist.php';
    }

    /**
     * Unified Toggle Asynchronous API Endpoint (POST /wishlist/toggle)
     */
    public function toggle()
    {
        if (headers_sent() === false) {
            header('Content-Type: application/json; charset=UTF-8');
        }

        // 1. Guard CSRF Interception Verification Layer
        if (!verifyCsrf()) {
            http_response_code(419);
            echo json_encode([
                'success' => false, 
                'message' => 'Security window expired. Please refresh page and try again.'
            ]);
            exit;
        }

        // 2. Extract Validated Sanitized Primitive Integer Input Parameters
        $productId = (int)($_POST['product_id'] ?? 0);
        $userId    = (int)($_SESSION['user']['id'] ?? 0);

        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Malformed item or parameter identification request.']);
            exit;
        }

        try {
            // Check state to dictate dynamic add vs remove logic loop pathing
            if ($userId > 0) {
                // Authenticated user DB logic
                if ($this->wishlist->isWishlisted($userId, $productId)) {
                    $this->wishlist->remove($userId, $productId);
                    $isWishlisted = false;
                } else {
                    $this->wishlist->add($userId, $productId);
                    $isWishlisted = true;
                }
            } else {
                // Guest user session logic
                if (!isset($_SESSION['guest_wishlist'])) {
                    $_SESSION['guest_wishlist'] = [];
                }
                
                $key = array_search($productId, $_SESSION['guest_wishlist']);
                if ($key !== false) {
                    unset($_SESSION['guest_wishlist'][$key]);
                    $_SESSION['guest_wishlist'] = array_values($_SESSION['guest_wishlist']); // re-index
                    $isWishlisted = false;
                } else {
                    $_SESSION['guest_wishlist'][] = $productId;
                    $isWishlisted = true;
                }
            }

            echo json_encode([
                'success' => true,
                'wishlisted' => $isWishlisted,
                'message' => $isWishlisted ? 'Product added to wishlist.' : 'Product removed from wishlist.'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Internal database runtime transactional exception error.',
                'debug_error' => $e->getMessage()
            ]);
        }
        exit;
    }
}