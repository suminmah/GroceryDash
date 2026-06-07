<?php
/**
 * @var string|null $error
 * @var string|null $success
 */
?>
<div class="admin-container">
    <div class="admin-header-row">
        <div class="header-titles">
            <h1 class="main-title">Create User Account Profile</h1>
            <p class="subtitle-context">Provision internal administrative system profiles or client consumer access points directly.</p>
        </div>
        <a href="<?= APP_URL ?>/admin/customers" class="btn btn-secondary">
            ⬅️ Return to Customer Index
        </a>
    </div>

    <hr class="section-divider">

    <div class="form-card-container">
        <?php if (!empty($error)): ?>
            <div class="admin-alert alert-error">
                <span class="alert-icon">❌</span>
                <div class="alert-message"><strong>Execution Blocked:</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/admin/customers" method="POST" class="modern-admin-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="form-grid-two-col">
                <div class="form-group">
                    <label for="user-name" class="control-label">Full Profile Legal Name</label>
                    <input type="text" id="user-name" name="name" class="form-control" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label for="user-email" class="control-label">Electronic Email Address</label>
                    <input type="email" id="user-email" name="email" class="form-control" placeholder="johndoe@example.com" required>
                </div>
            </div>

            <div class="form-grid-two-col">
                <div class="form-group">
                    <label for="user-password" class="control-label">Access Passphrase Password</label>
                    <input type="password" id="user-password" name="password" class="form-control" placeholder="••••••••••••" required>
                    <small class="help-text">Password will be cryptographically compiled using industry-standard BCRYPT algorithms.</small>
                </div>

                <div class="form-group">
                    <label for="user-role" class="control-label">Account System Authorization Role</label>
                    <select id="user-role" name="role" class="modern-select">
                        <option value="customer" selected>👤 Client Consumer (Customer)</option>
                        <option value="admin">🔒 Administrative System Operator (Admin)</option>
                    </select>
                </div>
            </div>

            <div class="form-actions-row">
                <button type="submit" class="btn btn-primary btn-large">
                    💾 Commit Access Profile Record
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .form-card-container { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .form-grid-two-col { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .control-label { font-weight: 600; color: #374151; font-size: 14px; }
    .form-control, .modern-select { padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; width: 100%; box-sizing: border-box; }
    .form-control:focus, .modern-select:focus { border-color: #10b981; outline: none; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }
    .help-text { color: #6b7280; font-size: 11px; margin-top: 4px; }
    .form-actions-row { margin-top: 30px; display: flex; justify-content: flex-end; border-top: 1px solid #f3f4f6; padding-top: 20px; }
    .btn-large { padding: 12px 24px; font-weight: 600; cursor: pointer; }
    .admin-alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 14px; }
    .alert-error { background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
</style>