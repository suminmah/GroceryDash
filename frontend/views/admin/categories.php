<?php
$pageTitle = $pageTitle ?? 'Categories — Admin';
$categories = $categories ?? [];
$totalPages = $totalPages ?? 1;
$page = $page ?? 1;
?>

<div class="admin-main">
    <h1>Categories</h1>
    <!-- Changed to your category creation route -->
    <a href="<?= APP_URL ?>/admin/categories/new" class="btn-primary">+ Add Category</a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Parent ID</th>
            <th>Sort Order</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category): ?>
        <tr>
            <td><?= $category['id'] ?></td>
            <td><strong><?= e($category['name']) ?></strong></td>
            <td><code><?= e($category['slug']) ?></code></td>
            <td><?= $category['parent_id'] ?? '<span class="text-muted">Root</span>' ?></td>
            <td><?= $category['sort_order'] ?? 0 ?></td>
            <td>
                <span class="status-badge <?= $category['is_active'] ? 'status-active' : 'status-inactive' ?>">
                    <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </td>
            <td>
                <div class="action-buttons" style="display: flex; gap: 10px;">
                    <!-- Edit Button -->
                    <a href="<?= APP_URL ?>/admin/categories/<?= $category['id'] ?>/edit" class="btn-edit">Edit</a>

                    <!-- Delete Button -->
                    <form action="<?= APP_URL ?>/admin/categories/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <button type="submit" class="btn-delete" style="background:none; border:none; color:#dc3545; cursor:pointer; padding:0; font:inherit; text-decoration:underline;">
                            Delete
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        
        <?php if (empty($categories)): ?>
        <tr>
            <td colspan="7" style="text-align: center;">No categories found.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>" class="page-btn <?= ($page == $i) ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>