-- ============================================================
--  FreshCart Grocery Shop — Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS freshcart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE freshcart;

-- Users
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    phone       VARCHAR(20),
    password    VARCHAR(255) NOT NULL,
    role        ENUM('customer','admin') DEFAULT 'customer',
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Addresses
CREATE TABLE addresses (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    label       VARCHAR(50) DEFAULT 'Home',
    line1       VARCHAR(200) NOT NULL,
    line2       VARCHAR(200),
    city        VARCHAR(100) NOT NULL,
    pincode     VARCHAR(10) NOT NULL,
    is_default  TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Categories
CREATE TABLE categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(110) NOT NULL UNIQUE,
    image       VARCHAR(255),
    sort_order  INT DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1
);

-- Products
CREATE TABLE products (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED NOT NULL,
    name            VARCHAR(200) NOT NULL,
    slug            VARCHAR(220) NOT NULL UNIQUE,
    description     TEXT,
    price           DECIMAL(10,2) NOT NULL,
    sale_price      DECIMAL(10,2) DEFAULT NULL,
    unit            VARCHAR(30) DEFAULT '1 kg',
    stock           INT DEFAULT 0,
    image           VARCHAR(255),
    is_featured     TINYINT(1) DEFAULT 0,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Cart (session-based for guests, user-based for logged in)
CREATE TABLE cart (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED DEFAULT NULL,
    session_id  VARCHAR(100) DEFAULT NULL,
    product_id  INT UNSIGNED NOT NULL,
    quantity    INT DEFAULT 1,
    added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders
CREATE TABLE orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED DEFAULT NULL,
    order_number    VARCHAR(20) NOT NULL UNIQUE,
    status          ENUM('pending','confirmed','packed','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
    subtotal        DECIMAL(10,2) NOT NULL,
    delivery_fee    DECIMAL(10,2) DEFAULT 0.00,
    discount        DECIMAL(10,2) DEFAULT 0.00,
    total           DECIMAL(10,2) NOT NULL,
    payment_method  ENUM('cod','online','fonepay','esewa','khalti') DEFAULT 'cod',
    payment_status  ENUM('pending','paid','failed') DEFAULT 'pending',
    delivery_address TEXT NOT NULL,
    delivery_slot   VARCHAR(50),
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order Items
CREATE TABLE order_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED DEFAULT NULL,
    name        VARCHAR(200) NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    quantity    INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Reviews
CREATE TABLE reviews (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Coupons
CREATE TABLE coupons (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(30) NOT NULL UNIQUE,
    type            ENUM('flat','percent') DEFAULT 'flat',
    value           DECIMAL(10,2) NOT NULL,
    min_order       DECIMAL(10,2) DEFAULT 0,
    max_uses        INT DEFAULT NULL,
    used_count      INT DEFAULT 0,
    expires_at      DATE DEFAULT NULL,
    is_active       TINYINT(1) DEFAULT 1
);

-- ============================================================
-- Seed Data
-- ============================================================
INSERT INTO categories (name, slug, sort_order) VALUES
('Vegetables', 'vegetables', 1),
('Fruits', 'fruits', 2),
('Dairy & Eggs', 'dairy-eggs', 3),
('Bakery', 'bakery', 4),
('Meat & Fish', 'meat-fish', 5),
('Beverages', 'beverages', 6);

INSERT INTO products (category_id, name, slug, price, sale_price, unit, stock, is_featured) VALUES
(1, 'Fresh Tomatoes', 'fresh-tomatoes', 40.00, 32.00, '500g', 200, 1),
(1, 'Baby Spinach', 'baby-spinach', 35.00, NULL, '200g', 150, 0),
(2, 'Bananas', 'bananas', 50.00, 45.00, '1 dozen', 100, 1),
(2, 'Red Apples', 'red-apples', 120.00, NULL, '1 kg', 80, 1),
(3, 'Full Cream Milk', 'full-cream-milk', 65.00, NULL, '1 L', 300, 0),
(3, 'Farm Eggs', 'farm-eggs', 90.00, 80.00, '12 pcs', 200, 1),
(4, 'Whole Wheat Bread', 'whole-wheat-bread', 45.00, NULL, '400g', 60, 0),
(5, 'Chicken Breast', 'chicken-breast', 280.00, 250.00, '500g', 40, 1);

INSERT INTO coupons (code, type, value, min_order) VALUES
('FRESH10', 'percent', 10.00, 200.00),
('SAVE50', 'flat', 50.00, 500.00);
