<?php
// frontend/views/pages/register.php
$pageTitle = 'Create Account — GroceryDash';
if (!isset($error)) {
    $error = null;
}
require __DIR__ . '/../layouts/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h2>Create Account 🛒</h2>
    <p style="color:#777;margin-bottom:1.5rem">Join GroceryDash for faster checkout and order tracking.</p>

    <?php if ($error): ?>
      <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/register">
      <input type="hidden" name="_token" value="<?= csrfToken() ?>">

      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" class="input" placeholder="Your name" required autofocus>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="input" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label for="phone">Phone <small>(optional)</small></label>
        <input type="tel" id="phone" name="phone" class="input" placeholder="+977-98…">
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="input" placeholder="Min 8 characters" required minlength="8">
        </div>
        <div class="form-group">
          <label for="confirm">Confirm Password</label>
          <input type="password" id="confirm" name="confirm" class="input" placeholder="Repeat password" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Create Account</button>
    </form>

    <p style="text-align:center;margin-top:1rem">
      Already have an account? <a href="<?= APP_URL ?>/login">Sign in →</a>
    </p>
  </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
