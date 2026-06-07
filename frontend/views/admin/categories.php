<?php
/**
 * Administrative Categories Management Workspace View
 * @var string|null $pageTitle
 * @var array $categories
 * @var int $totalPages
 * @var int $page
 */
$pageTitle = $pageTitle ?? 'Categories Management — GroceryDash';
$categories = $categories ?? [];
$totalPages = (int)($totalPages ?? 1);
$page = (int)($page ?? 1);
?>

<div class="admin-header-flex d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
    <div>
        <h1 class="main-title m-0 font-weight-bold">Categories</h1>
        <br>
        <p class="subtitle-context text-muted m-0">Organize and manage structural catalog divisions for store inventory items.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/categories/new" class="btn-premium-primary">
        <i class="bi bi-folder-plus"></i> Add Category
    </a>
</div>

<div class="table-container shadow-sm rounded bg-white overflow-hidden">
    <table class="admin-table w-100 m-0">
        <thead>
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 25%;">Category Name</th>
                <th style="width: 22%;">Slug Identifier</th>
                <th style="width: 12%;">Parent ID</th>
                <th style="width: 10%;">Sort Order</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 13%;" class="text-end">Action Track</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td>
                    <span class="text-secondary font-weight-bold">#<?= htmlspecialchars((string)$category['id'], ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td>
                    <strong class="customer-name-label"><?= htmlspecialchars((string)$category['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                </td>
                <td>
                    <code><?= htmlspecialchars((string)$category['slug'], ENT_QUOTES, 'UTF-8') ?></code>
                </td>
                <td>
                    <?php if (isset($category['parent_id']) && $category['parent_id'] != 0): ?>
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-diagram-2 small me-1"></i> #<?= htmlspecialchars((string)$category['parent_id'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php else: ?>
                        <span class="text-muted italic-subtext">Root Node</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="price-data-cell text-muted"><?= htmlspecialchars((string)($category['sort_order'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td>
                    <span class="status-badge <?= $category['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <i class="bi <?= $category['is_active'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i>
                        <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <div class="action-buttons d-flex align-items-center justify-content-end gap-2">
                        <a href="<?= APP_URL ?>/admin/categories/edit?id=<?= $category['id'] ?>" class="btn-edit">Edit
                        </a>

                        <form action="<?= APP_URL ?>/admin/categories/delete" 
                              method="POST" 
                              onsubmit="return confirm('⚠️ CRITICAL: Are you absolutely sure you want to permanently drop this category mapping entry?');" 
                              style="display:inline; margin:0; padding:0;">
                            <input type="hidden" name="id" value="<?= htmlspecialchars((string)$category['id'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <button type="submit" class="btn-delete" title="Purge Record">
                                <i class="bi bi-trash3-fill"></i> Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($categories)): ?>
            <tr>
                <td colspan="7" class="p-5 text-center text-muted">
                    <i class="bi bi-folder-x display-6 d-block mb-3 text-secondary"></i>
                    No relational configuration groups found in system metadata index blocks.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
<nav class="pagination-container d-flex justify-content-center mt-4" aria-label="Catalog pages tracking utility navigation">
    <div class="pagination shadow-sm">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-btn <?= ($page === $i) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</nav>
<?php endif; ?>