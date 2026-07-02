<?php
// frontend/views/layouts/header.php
// Usage: require with $pageTitle set in the calling view.

if (!isset($settings)) {
    require_once __DIR__ . '/../../../backend/models/Setting.php';
    $settings = new Setting(Database::connect());
}

// Fetch the saved asset path string from your database
$savedLogo = $settings->get('site_logo');
$headerLogoUrl = null;

if (!empty($savedLogo)) {
    // Format the URL path string so it resolves cleanly on your local subdirectory
    $headerLogoUrl = (str_starts_with($savedLogo, 'http') || str_contains($savedLogo, '/grocery-shop/public')) 
        ? $savedLogo 
        : APP_URL . '/' . ltrim($savedLogo, '/');
}

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
  
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/user-order.css?v=<?= time() ?>">
  <link rel="stylesheet" href="/grocery-shop/public/assets/css/admin.css?v=<?= time() ?>">
  
  <aria-preconnect href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">

  <style>
    .profile-dropdown-wrapper {
      position: relative;
      display: inline-block;
    }
    .profile-dropdown-menu {
      display: none;
      position: absolute;
      top: 115%;
      right: 0;
      background: #ffffff;
      min-width: 190px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border-radius: 8px;
      border: 1px solid #eef1f4;
      padding: 0.5rem 0;
      z-index: 9999;
    }
    .profile-dropdown-menu.show {
      display: block;
      animation: navDropdownFade 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dropdown-group-header {
      padding: 0.4rem 1rem;
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #99a2ab;
      font-weight: 700;
      border-bottom: 1px solid #f8f9fa;
      margin-bottom: 0.25rem;
    }
    .profile-dropdown-item {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.65rem 1rem;
      color: #333d47;
      text-decoration: none;
      font-size: 0.9rem;
      font-family: 'DM Sans', sans-serif;
      transition: all 0.2s ease;
    }
    .profile-dropdown-item:hover {
      background-color: #f4f6f8;
      color: #198754;
      padding-left: 1.2rem;
    }
    .profile-dropdown-item.logout-link {
      color: #dc3545;
      border-top: 1px solid #f1f3f5;
      margin-top: 0.25rem;
    }
    .profile-dropdown-item.logout-link:hover {
      background-color: #fff5f5;
      color: #bd2130;
    }
    .caret-icon {
      font-size: 0.65rem;
      margin-left: 0.2rem;
      transition: transform 0.2s ease;
      color: #88929d;
    }
    .profile-dropdown-wrapper.active .caret-icon {
      transform: rotate(180deg);
    }
    @keyframes navDropdownFade {
      from { opacity: 0; transform: translateY(4px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<header class="site-header">
  <div class="header-inner">

    <a href="<?= APP_URL ?>/" class="logo">
      <?php if (!empty($headerLogoUrl)): ?>
        <img src="<?= htmlspecialchars($headerLogoUrl) ?>" alt="GroceryDash Logo">
      <?php else: ?>
        <span style="font-size: 1.6rem; margin-right: 0.5rem;">🛒</span>
        <span style="color: #1c2d22; font-family: var(--font-head); font-size: 1.5rem; font-weight: 600; white-space: nowrap;">
          Grocery<strong style="color: var(--green); font-weight: 700;">Dash</strong>
        </span>
      <?php endif; ?>
    </a>

    <form class="search-form" action="<?= APP_URL ?>/search" method="GET" role="search">
      <select name="category" class="search-cat" aria-label="Category">
        <option value="">All Categories</option>
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

    <div class="header-actions">
      
      <?php if (isLoggedIn()): ?>
        <div class="profile-dropdown-wrapper" id="profileDropdownGroup">
          <a href="#" class="action-btn" id="profileMenuTrigger" role="button" aria-haspopup="true" aria-expanded="false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>
              <?= htmlspecialchars(explode(' ', $_SESSION['user']['name'] ?? 'User')[0], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <span style="font-size: 0.65rem; margin-left: 2px;">▼</span>
          </a>
          
          <div class="profile-dropdown-menu" id="profileDropdownContainer">
            <div class="dropdown-group-header">Account Management</div>
            <a href="<?= APP_URL ?>/account/orders" class="profile-dropdown-item">📦 My Orders</a>
            <a href="<?= APP_URL ?>/cart" class="profile-dropdown-item">🛒 My Cart</a>
            <a href="<?= APP_URL ?>/account/wishlist" class="profile-dropdown-item">❤️ My Wishlist</a>
            <a href="<?= APP_URL ?>/logout" class="profile-dropdown-item logout-link">🚪 Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login" class="action-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Sign In</span>
        </a>
      <?php endif; ?>

      <a href="<?= APP_URL ?>/cart" class="action-btn cart-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"/>
          <circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <span>Cart</span>
        <span class="cart-badge" id="cartBadge" <?= $cartCount === 0 ? 'style="display:none;"' : '' ?>>
          <?= $cartCount ?>
        </span>
      </a>

      <a href="<?= APP_URL ?>/account/wishlist" class="action-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <span>Wishlist</span>
      </a>

    </div>
  </div>

  <nav class="main-nav" aria-label="Main navigation">
    <div class="nav-inner">
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

<?php if ($msg = flash('success')): ?>
  <div class="flash flash-success" role="alert"><?= e($msg) ?> <button onclick="this.parentElement.remove()">✕</button></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
  <div class="flash flash-error" role="alert"><?= e($msg) ?> <button onclick="this.parentElement.remove()">✕</button></div>
<?php endif; ?>

<main id="main-content">

<script>
document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('profileMenuTrigger');
    const menu = document.getElementById('profileDropdownContainer');
    const wrapper = document.getElementById('profileDropdownGroup');

    if (!trigger || !menu) return;

    trigger.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        const isExpanded = menu.classList.contains('show');
        menu.classList.toggle('show', !isExpanded);
        wrapper.classList.toggle('active', !isExpanded);
        trigger.setAttribute('aria-expanded', !isExpanded);
    });

    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            menu.classList.remove('show');
            wrapper.classList.remove('active');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });
});
</script>