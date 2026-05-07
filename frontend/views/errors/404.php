<?php
// frontend/views/errors/404.php
$pageTitle = 'Page Not Found — FreshCart';
require __DIR__ . '/../layouts/header.php';
?>
<div class="container" style="text-align:center;padding:5rem 0">
  <div style="font-size:5rem">🥦</div>
  <h1 style="font-size:4rem;font-weight:700;color:#ddd">404</h1>
  <h2>Page not found</h2>
  <p style="color:#777">The page you're looking for might have moved or doesn't exist.</p>
  <a href="<?= APP_URL ?>/" class="btn btn-primary" style="margin-top:1rem">← Back to Home</a>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
