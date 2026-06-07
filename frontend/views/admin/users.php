<?php $pageTitle = 'System Users'; ?>

<div class="admin-header-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h1 class="main-title">System Users</h1>
        <p class="subtitle-context">System-wide core authentication logins and authorization level tokens.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/customers/new" class="btn btn-primary" style="background-color: #10b981; color: white; padding: 10px 18px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <span>➕</span> Register New User Profile
    </a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Email Identity (Username)</th>
            <th>Authorization Level Role</th>
            <th>Status State</th>
            <th>Account Configured</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($users)): ?>
        <?php foreach ($users as $u): ?>
            <tr>
                <td>#<?= $u['id'] ?></td>
                <td><strong><?= e($u['email']) ?></strong></td>
                <td>
                    <span class="role-badge <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-customer' ?>">
                        <?= strtoupper(e($u['role'])) ?>
                    </span>
                </td>
                <td><?= $u['is_active'] ? '🟢 Active' : '🔴 Suspended' ?></td>
                <td><?= $u['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<style>
    .role-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
    .badge-admin { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .badge-customer { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
</style>