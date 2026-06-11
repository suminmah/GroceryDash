<?php
$category      = $category      ?? [];
$parentOptions = $parentOptions ?? [];
$errors        = $errors        ?? [];
$isEdit        = !empty($category['id']);
$pageTitle     = $isEdit
    ? 'Edit Category: ' . htmlspecialchars($category['name'] ?? '')
    : 'Create Category';
?>

<div class="admin-main">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <div>
            <div style="border-left:4px solid #198754; padding-left:0.75rem;">
                <h1 style="margin:0; font-size:1.5rem;">
                    <?= $isEdit ? 'Edit Category' : 'Create Category' ?>
                </h1>
            </div>
            <div style="color:#777; font-size:.85rem; margin-top:.25rem; margin-left:1rem;">
                Dashboard / Categories / <?= $isEdit ? 'Edit' : 'Create' ?>
            </div>
        </div>
        <a href="<?= APP_URL ?>/admin/categories" class="btn-secondary-sm">
            ← Back to List
        </a>
    </div>

    <div class="card" style="border-radius:8px; padding:2rem; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.08);">

        <?php if (!empty($errors['global'])): ?>
            <div class="flash flash-error"><?= htmlspecialchars($errors['global']) ?></div>
        <?php endif; ?>

        <form method="POST"
              action="<?= $isEdit
                ? APP_URL . '/admin/categories/' . (int)$category['id']
                : APP_URL . '/admin/categories' ?>">

            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
            <?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">

                <div class="form-group">
                    <label class="form-label">
                        Category Name <span style="color:#dc3545;">*</span>
                    </label>
                    <input type="text"
                           class="input <?= isset($errors['name']) ? 'input-error' : '' ?>"
                           id="categoryName"
                           name="name"
                           placeholder="e.g., Fresh Fruits"
                           value="<?= htmlspecialchars($category['name'] ?? '') ?>"
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <span style="color:#dc3545; font-size:.8rem;">
                            <?= htmlspecialchars($errors['name']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text"
                           class="input"
                           id="categorySlug"
                           name="slug"
                           placeholder="auto-generated"
                           value="<?= htmlspecialchars($category['slug'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Parent Category</label>
                    <select class="input" id="parentId" name="parent_id">
                        <option value="">None (Top-Level)</option>
                        <?php foreach ($parentOptions as $option): ?>
                            <?php if ($isEdit && $option['id'] == ($category['id'] ?? 0)) continue; ?>
                            <option value="<?= $option['id'] ?>"
                                <?= (isset($category['parent_id']) && $category['parent_id'] == $option['id'])
                                    ? 'selected' : '' ?>>
                                <?= htmlspecialchars($option['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number"
                           class="input"
                           name="sort_order"
                           value="<?= htmlspecialchars($category['sort_order'] ?? '0') ?>"
                           min="0">
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label style="display:flex; align-items:center; gap:.5rem; cursor:pointer;">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               <?= (!isset($category['is_active']) || $category['is_active'] == 1)
                                   ? 'checked' : '' ?>>
                        <span>Active and visible in shop</span>
                    </label>
                </div>

            </div>

            <div style="border-top:1px solid #eee; margin:1.5rem 0;"></div>

            <div style="display:flex; justify-content:flex-end; gap:.75rem;">
                <a href="<?= APP_URL ?>/admin/categories"
                   style="padding:.6rem 1.5rem; border-radius:6px; border:1px solid #ddd;
                          text-decoration:none; color:#555;">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Save Changes' : 'Create Category' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('categoryName');
    const slugInput = document.getElementById('categorySlug');
    let manualSlug  = slugInput.value.trim() !== '';

    slugInput.addEventListener('input', () => manualSlug = slugInput.value.trim() !== '');

    nameInput.addEventListener('input', function () {
        if (!manualSlug) {
            slugInput.value = nameInput.value
                .toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }
    });
});
</script>