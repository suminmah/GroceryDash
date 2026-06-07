<?php $pageTitle = 'Customers'; ?>

<div class="admin-header-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h1 class="main-title">Customers</h1>
        <p class="subtitle-context">Business-domain client listings, customer demographic records, and metrics.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/customers/new" class="btn btn-primary" style="background-color: #10b981; color: white; padding: 10px 18px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <span>➕</span> Register New Customer
    </a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>Customer ID</th>
            <th>Client Profile Name</th>
            <th>Linked Account Email</th>
            <th>Mobile Contact Phone</th>
            <th>System Entry Created</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($customers)): ?>
        <?php foreach ($customers as $c): ?>
            <tr>
                <td>#<?= $c['customer_id'] ?></td>
                <td><strong><?= e($c['name']) ?></strong></td>
                <td><?= e($c['email']) ?></td>
                <td><?= e($c['phone'] ?? '—') ?></td>
                <td><?= $c['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>