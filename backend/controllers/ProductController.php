<?php
// backend/controllers/ProductController.php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../helpers/functions.php';

class ProductController {
    private Product  $product;
    private Category $category;

    public function __construct() {
        $this->product  = new Product();
        $this->category = new Category();
    }

    /** GET / — Homepage */
    public function home() {
        $featured   = $this->product->getFeatured(8);
        $categories = $this->category->getAll();
        require __DIR__ . '/../../frontend/views/pages/home.php';
    }

    /** GET /shop — Product listing */
    public function shop() {
        $filters = [
            'category'  => $_GET['category']  ?? '',
            'search'    => $_GET['search']    ?? '',
            'sort'      => $_GET['sort']      ?? '',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
        ];
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $products   = $this->product->getAll($filters, $page);
        $total      = $this->product->count($filters);
        $totalPages = (int) ceil($total / PRODUCTS_PER_PAGE);
        $categories = $this->category->getAll();
        require __DIR__ . '/../../frontend/views/pages/shop.php';
    }

    /** GET /product/{slug} — Product detail */
    public function detail(string $slug) {
        $product = $this->product->findBySlug($slug);
        if (!$product) {
            http_response_code(404);
            require __DIR__ . '/../../frontend/views/errors/404.php';
            return;
        }
        $related = $this->product->getRelated(
            (int) $product['category_id'],
            (int) $product['id']
        );
        require __DIR__ . '/../../frontend/views/pages/product.php';
    }

    /** GET /search */
    public function search() {
        $query    = trim($_GET['q'] ?? '');
        $products = $query ? $this->product->search($query) : [];
        require __DIR__ . '/../../frontend/views/pages/search.php';
    }

    /** GET /offers */
    public function offers() {
        $filters    = ['sort' => 'popular'];
        $products   = $this->product->getAll($filters);
        $categories = $this->category->getAll();
        require __DIR__ . '/../../frontend/views/pages/offers.php';
    }
}
