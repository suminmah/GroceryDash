<?php $pageTitle = 'System Users'; ?>

<div class="admin-header-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h1 class="main-title">System Users</h1>
        <p class="subtitle-context">User Description</p>
    </div>
    <a href="<?= APP_URL ?>/admin/customers/new" class="btn btn-primary" style="background-color: #10b981; color: white; padding: 10px 18px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <span></span> Register New User Profile
    </a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Configured Date</th>
            <th style="text-align: right; padding-right: 1.5rem;">Actions</th>
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
                <td>
                    <span class="status-indicator <?= $u['is_active'] ? 'status-active' : 'status-suspended' ?>">
                        <?= $u['is_active'] ? '🟢 Active' : '🔴 Suspended' ?>
                    </span>
                </td>
                <td><?= $u['created_at'] ?></td>
                <td style="text-align: right; padding-right: 1.5rem;">
                    <form method="POST" action="<?= APP_URL ?>/admin/users/toggle-status" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                        <input type="hidden" name="current_status" value="<?= (int)$u['is_active'] ?>">
                        
                        <?php if ($u['is_active']): ?>
                            <button type="submit" class="btn-action-status btn-suspend" title="Suspend User Account">
                                Suspend
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn-action-status btn-activate" title="Activate User Account">
                                Activate
                            </button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<style>
    /* Badge Rules */
    .role-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
    .badge-admin { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .badge-customer { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }

    /* Custom Row Isolation Action Buttons */
    .btn-action-status {
        padding: 0.35rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.15s ease-in-out;
    }

    /* Suspend State Configuration (Muted Warning Red) */
    .btn-suspend {
        background-color: #fff5f5;
        color: #e53e3e;
        border-color: #fed7d7;
    }
    .btn-suspend:hover {
        background-color: #e53e3e;
        color: #ffffff;
        border-color: #e53e3e;
    }

    /* Activate State Configuration (Clean Emerald Green) */
    .btn-activate {
        background-color: #f0fdf4;
        color: #16a34a;
        border-color: #bbf7d0;
    }
    .btn-activate:hover {
        background-color: #16a34a;
        color: #ffffff;
        border-color: #16a34a;
    }
    
    /* Clean status text layout padding spacing tracking */
    .status-indicator {
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>