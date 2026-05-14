<?php
// frontend/views/pages/login.php
$pageTitle = 'Sign In — GroceryDash';
$error = null;
require __DIR__ . '/../layouts/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h2>Welcome back 👋</h2>
    <p style="color:#777;margin-bottom:1.5rem">Sign in to your GroceryDash account</p>

    <?php if ($error): ?>
      <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/login">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="redirect" value="<?= e($_GET['redirect'] ?? APP_URL . '/account/orders') ?>">

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="input" placeholder="you@example.com" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="input" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn btn-primary btn-full" style="margin-top:1rem">Sign In</button>
    </form>

    <p style="text-align:center;margin-top:1rem">
      Don't have an account? <a href="<?= APP_URL ?>/register">Create one →</a>
    </p>
  </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
