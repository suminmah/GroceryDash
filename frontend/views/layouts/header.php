<?php
// frontend/views/layouts/header.php
// Usage: require with $pageTitle set in the calling view.

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Past date

require_once __DIR__ . '/../../../backend/models/Category.php';

$pageTitle  = $pageTitle  ?? 'GroceryDash — Fresh Grocery Delivered Fast';
$cartCount  = cartCount();
$categories = $categories ?? (new Category())->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="Order fresh groceries online and get them delivered in 30 minutes. Best prices, widest selection.">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
</head>
<body>

<!-- ═══ TOPBAR ═══════════════════════════════════════════ -->
<div class="topbar">
  <span>🚚 Free delivery on orders above <?= formatPrice(FREE_DELIVERY_THRESHOLD) ?></span>
  <span>📞 +977-1-4000000</span>
</div>

<!-- ═══ HEADER ════════════════════════════════════════════ -->
<header class="site-header">
  <div class="container header-inner">

    <!-- Logo -->
    <a href="<?= APP_URL ?>/" class="logo">
      <span class="logo-icon">🛒</span>
      <span class="logo-text">Grocery<strong>Dash</strong></span>
    </a>

    <!-- Search -->
    <form class="search-form" action="<?= APP_URL ?>/search" method="GET" role="search">
      <select name="category" class="search-cat" aria-label="Category">
        <option value="">All</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <input
        type="search"
        name="q"
        class="search-input"
        placeholder="Search fresh groceries…"
        value="<?= e($_GET['q'] ?? '') ?>"
        autocomplete="off"
        aria-label="Search">
      <button type="submit" class="search-btn" aria-label="Search">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>
    </form>

    <!-- Header actions -->
    <nav class="header-actions">
      <?php if (isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/account/orders" class="action-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span><?= e(explode(' ', $_SESSION['user']['name'])[0]) ?></span>
        </a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login" class="action-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" align="right"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Sign In</span>
        </a>
      <?php endif; ?>

      <a href="<?= APP_URL ?>/cart" class="action-btn cart-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" align="right"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span>Cart</span>
        <span class="cart-badge" id="cartBadge" <?= $cartCount === 0 ? 'style="display:"' : '' ?>>
          <?= $cartCount ?>
        </span>
      </a>
    </nav>
  </div>

  <!-- ─── Nav bar ─── -->
  <nav class="main-nav" aria-label="Main navigation">
    <div class="container nav-inner">
      <button class="nav-toggle" id="navToggle" aria-label="Menu">☰ Categories</button>
      <ul class="nav-links" id="navLinks">
        <li><a href="<?= APP_URL ?>/"       class="nav-link">Home</a></li>
        <li><a href="<?= APP_URL ?>/shop"   class="nav-link">Shop</a></li>
        <li><a href="<?= APP_URL ?>/offers" class="nav-link">Offers</a></li>
        <li class="has-dropdown">
          <a href="#" class="nav-link">Categories ▾</a>
          <ul class="dropdown">
            <?php foreach ($categories as $cat): ?>
              <li>
                <a href="<?= APP_URL ?>/shop?category=<?= e($cat['slug']) ?>">
                  <?= e($cat['name']) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li><a href="<?= APP_URL ?>/delivery" class="nav-link">Delivery</a></li>
        <li><a href="<?= APP_URL ?>/about"    class="nav-link">About</a></li>
        <li><a href="<?= APP_URL ?>/help"     class="nav-link">Help</a></li>
      </ul>
    </div>
  </nav>
</header>

<!-- Flash messages -->
<?php if ($msg = flash('success')): ?>
  <div class="flash flash-success" role="alert"><?= e($msg) ?> <button onclick="this.parentElement.remove()">✕</button></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
  <div class="flash flash-error" role="alert"><?= e($msg) ?> <button onclick="this.parentElement.remove()">✕</button></div>
<?php endif; ?>

<main id="main-content">
