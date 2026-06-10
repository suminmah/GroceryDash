<?php
/**
 * @var array $category      Existing category data passed from the controller
 * @var array $parentOptions Available categories array to build parent dropdown options
 * @var array $errors         Validation errors passed from the controller
 */

$category = $category ?? [];
$parentOptions = $parentOptions ?? []; 
$errors = $errors ?? [];
$isEdit = !empty($category['id']);

$pageTitle = $isEdit ? 'Edit Category: ' . htmlspecialchars($category['name'] ?? '') : 'Create Category';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body class="bg-light">

<div class="container-fluid px-4 py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/grocery-shop/public/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/grocery-shop/public/admin/categories">Categories</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Edit' : 'Create' ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="/grocery-shop/public/admin/categories" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Category Parameters</h6>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($errors['global'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($errors['global']) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= $isEdit ? '/grocery-shop/public/admin/categories/edit?id=' . intval($category['id']) : '/grocery-shop/public/admin/categories/new' ?>" method="POST" novalidate>
                        
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="created_by" value="<?= htmlspecialchars($category['created_by'] ?? '') ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= intval($category['id']) ?>">
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                       id="categoryName" 
                                       name="name" 
                                       value="<?= htmlspecialchars($category['name'] ?? '') ?>" 
                                       required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="categorySlug" class="form-label">Slug</label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" 
                                       id="categorySlug" 
                                       name="slug" 
                                       value="<?= htmlspecialchars($category['slug'] ?? '') ?>">
                                <?php if (isset($errors['slug'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['slug']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="parentId" class="form-label">Parent Category</label>
                                <select class="form-select" id="parentId" name="parent_id">
                                    <option value="">None (Top-Level Category)</option>
                                    <?php foreach ($parentOptions as $option): ?>
                                        <?php if ($isEdit && $option['id'] == $category['id']) continue; ?>
                                        <option value="<?= $option['id'] ?>" <?= (isset($category['parent_id']) && $category['parent_id'] == $option['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($option['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="sortOrder" class="form-label">Display Sort Order</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="sortOrder" 
                                       name="sort_order" 
                                       value="<?= htmlspecialchars($category['sort_order'] ?? '0') ?>" 
                                       min="0">
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           role="switch" 
                                           id="categoryActive" 
                                           name="is_active" 
                                           value="1" 
                                           <?= (!isset($category['is_active']) || $category['is_active'] == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="categoryActive">Active and Available on Shop</label>
                                </div>
                            </div>
                        </div>

                        <div class="my-4 border-top"></div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/grocery-shop/public/admin/categories" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <?= $isEdit ? 'Save Structural Changes' : 'Create Category' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('categoryName');
    const slugInput = document.getElementById('categorySlug');
    let isSlugEditedManually = slugInput.value.trim() !== '';

    slugInput.addEventListener('input', function() {
        isSlugEditedManually = slugInput.value.trim() !== '';
    });

    nameInput.addEventListener('input', function () {
        if (!isSlugEditedManually) {
            slugInput.value = nameInput.value
                .toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }
    });
});
</script>
</body>
</html>