<?php
$pageTitle = 'Categories';
if (!isset($tree)) {
    $tree = [];
}
require __DIR__ . '/../layouts/header.php';
?>

<h1>Categories</h1>
<form method="POST" action="<?= APP_URL ?>/admin/categories">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="text" name="name" placeholder="Category Name" required>
    <select name="parent_id">
        <option value="">None (Root)</option>
        <?php foreach ($tree as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Add</button>
</form>
<ul>
<?php foreach ($tree as $cat): ?>
    <li><?= e($cat['name']) ?></li>
<?php endforeach; ?>
</ul>
<?php require __DIR__ . '/../layouts/footer.php'; ?>