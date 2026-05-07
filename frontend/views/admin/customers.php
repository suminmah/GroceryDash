<?php $pageTitle = 'Customers'; 
require __DIR__ . '/../layouts/header.php'; ?>

<h1>Customers</h1>
<table class="admin-table">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th></tr></thead>
    <tbody>
    <?php if(isset($customers)): ?>
    <?php foreach ($customers as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= e($c['name']) ?></td>
            <td><?= e($c['email']) ?></td>
            <td><?= e($c['phone'] ?? '') ?></td>
            <td><?= $c['created_at'] ?></td>
        </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/footer.php'; ?>