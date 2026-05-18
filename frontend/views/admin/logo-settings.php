<?php
// frontend/views/admin/logo-settings.php
/**
 * @var string $currentLogo Provided by AdminController::logoForm()
 * @var string|null $error   Provided by AdminController::logoForm()
 * @var string|null $success Provided by AdminController::logoForm()
 */

// Fallback logic to satisfy static analysis and prevent runtime notices
if (!isset($currentLogo)) {
    $currentLogo = defined('APP_URL') ? APP_URL . '/assets/images/logo.png' : '/assets/images/logo.png';
}

$pageTitle = 'Logo Settings — Admin';
?>

<main class="admin-main">
    <div class="admin-header" style="margin-bottom: 2rem;">
        <h1>Website Logo Configuration</h1>
        <p style="color: #666;">Upload and update the primary brand logo used across GroceryDash.</p>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($error)): ?>
        <div class="flash flash-error" style="background:#fef2f2; color:#b91c1c; padding:1rem; border-radius:6px; margin-bottom:1.5rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="flash flash-success" style="background:#f0fdf4; color:#15803d; padding:1rem; border-radius:6px; margin-bottom:1.5rem;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background:#fff; padding:2rem; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1); max-width:600px;">
        <!-- Live Current Logo Preview Section -->
        <div class="current-logo-section" style="margin-bottom:2rem; text-align:center; padding:1.5rem; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px;">
            <h3 style="margin-bottom:1rem; font-size:1rem; color:#374151;">Active Logo Preview</h3>
            
            <?php 
                // If the path already has http/https or the full project subfolder, use it directly.
                // Otherwise, safely glue APP_URL to the front of it.
                $logoUrl = (str_starts_with($currentLogo, 'http') || str_contains($currentLogo, '/grocery-shop/public')) 
                    ? $currentLogo 
                    : APP_URL . '/' . ltrim($currentLogo, '/');
            ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Current Logo" style="max-height:80px; object-fit:contain; max-width:100%;">
        </div>

        <!-- Configuration Upload Form -->
        <form method="POST" action="<?= APP_URL ?>/admin/settings/logo" enctype="multipart/form-data">
            <!-- Security CSRF token matches your application structure -->
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group" style="margin-bottom:1.5rem;">
                <label for="logo" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#374151;">Select New Logo Image</label>
                <input type="file" id="logo" name="logo" accept="image/*" class="input" style="width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:4px;" required>
                <small style="color:#6b7280; display:block; margin-top:0.5rem;">Recommended formats: transparent PNG or WebP. Maximum file size: 2MB.</small>
            </div>

            <div class="actions" style="display:flex; gap:1rem; justify-content:flex-end;">
                <a href="<?= APP_URL ?>/admin/dashboard" class="btn" style="padding:0.5rem 1rem; border:1px solid #d1d5db; border-radius:4px; text-decoration:none; color:#374151; background:#fff;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem; background:#059669; color:#fff; border:none; border-radius:4px; cursor:pointer;">Upload Logo</button>
            </div>
        </form>
    </div>
</main>