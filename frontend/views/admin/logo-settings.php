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
// REMOVED: $pageTitle = 'Logo Settings — Admin'; to prevent layout override issues
?>

<div class="p-4 w-100">
    <div class="admin-header" style="margin-bottom: 2rem;">
        <h1>Website Logo Configuration</h1>
        <p style="color: #666;">Upload and update the primary brand logo used across GroceryDash.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="flash flash-error" style="background:#fef2f2; color:#b91c1c; padding:1rem; border-radius:6px; margin-bottom:1.5rem; border: 1px solid #fca5a5;">
            <i class="bi bi-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="flash flash-success" style="background:#f0fdf4; color:#15803d; padding:1rem; border-radius:6px; margin-bottom:1.5rem; border: 1px solid #bbf7d0;">
            <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-4 p-4" style="background:#fff; border-radius:12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); max-width:600px;">
        <div class="current-logo-section" style="margin-bottom:2rem; text-align:center; padding:1.5rem; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;">
            <h6 style="margin-bottom:1rem; font-size:0.85rem; color:#6b7280; font-weight:700; text-transform:uppercase;">Active Logo Preview</h6>
            
            <?php 
                // Resolve URL routing correctly
                $logoUrl = (str_starts_with($currentLogo, 'http') || str_contains($currentLogo, '/grocery-shop/public')) 
                    ? $currentLogo 
                    : APP_URL . '/' . ltrim($currentLogo, '/');
            ?>
            <div class="p-3 bg-white rounded-3 shadow-sm d-inline-block border">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Current Logo" style="max-height:80px; object-fit:contain; max-width:100%;">
            </div>
        </div>

        <form method="POST" action="<?= APP_URL ?>/admin/settings/logo" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group" style="margin-bottom:1.5rem;">
                <label for="logo" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#374151;">Select New Logo Image</label>
                <input type="file" id="logo" name="logo" accept="image/png, image/jpeg, image/webp" class="form-control" style="width:100%;" required>
                <small style="color:#6b7280; display:block; margin-top:0.5rem;">
                    <i class="bi bi-info-circle"></i> Recommended formats: transparent <strong>PNG</strong> or <strong>WebP</strong>. Maximum file size: 2MB.
                </small>
            </div>

            <div class="actions" style="display:flex; gap:1rem; justify-content:flex-end; border-top: 1px solid #f3f4f6; margin-top: 1.5rem; padding-top: 1rem;">
                <a href="<?= APP_URL ?>/admin/dashboard" class="btn btn-light" style="padding:0.5rem 1.25rem; border:1px solid #d1d5db; border-radius:6px; text-decoration:none; color:#374151; background:#fff; font-weight:500;">Cancel</a>
                <button type="submit" class="btn btn-success" style="padding:0.5rem 1.25rem; background:#2c7a4d; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:500;">Upload Logo</button>
            </div>
        </form>
    </div>
</div>