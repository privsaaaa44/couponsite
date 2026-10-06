<?php
// admin/categories.php - Manage Categories
include __DIR__ . '/includes/admin_header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$edit_category = null;

// Helper to create slugs
function create_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'cat-' . time());
}

// =========================================================================
// 1. HANDLE POST REQUESTS (SAVE & DELETE)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = "Security token mismatch. Please try again.";
        header('Location: categories.php');
        exit;
    }

    $post_action = $_POST['post_action'] ?? '';

    // Handle Save (Add / Edit)
    if ($post_action === 'save') {
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $name        = trim($_POST['name'] ?? '');
        $slug        = trim($_POST['slug'] ?? '');
        $icon        = trim($_POST['icon'] ?? 'bi-tag');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $error = "Category name is required.";
        } else {
            if (empty($slug)) {
                $slug = create_slug($name);
            } else {
                $slug = create_slug($slug);
            }

            // Check slug uniqueness
            $checkSql = "SELECT id FROM categories WHERE slug = :slug" . ($category_id ? " AND id != :id" : "");
            $checkParams = [':slug' => $slug];
            if ($category_id) $checkParams[':id'] = $category_id;
            
            $stmtCheck = $pdo->prepare($checkSql);
            $stmtCheck->execute($checkParams);
            if ($stmtCheck->fetch()) {
                $slug = $slug . '-' . time();
            }

            if ($category_id) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE categories 
                    SET name = :name, slug = :slug, icon = :icon, description = :description 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':name'        => $name,
                    ':slug'        => $slug,
                    ':icon'        => $icon,
                    ':description' => $description,
                    ':id'          => $category_id
                ]);
                $_SESSION['flash_success'] = "Category \"{$name}\" updated successfully!";
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO categories (name, slug, icon, description) 
                    VALUES (:name, :slug, :icon, :description)
                ");
                $stmt->execute([
                    ':name'        => $name,
                    ':slug'        => $slug,
                    ':icon'        => $icon,
                    ':description' => $description
                ]);
                $_SESSION['flash_success'] = "New category \"{$name}\" created successfully!";
            }
            header('Location: categories.php');
            exit;
        }
    }

    // Handle Delete
    if ($post_action === 'delete') {
        $delete_id = (int)($_POST['delete_id'] ?? 0);
        if ($delete_id > 0) {
            // Check if coupons exist in this category
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM coupons WHERE category_id = :id");
            $stmtCount->execute([':id' => $delete_id]);
            $hasCoupons = $stmtCount->fetchColumn();

            if ($hasCoupons > 0) {
                $_SESSION['flash_error'] = "Cannot delete category: It contains {$hasCoupons} active coupons. Reassign or delete those coupons first.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
                $stmt->execute([':id' => $delete_id]);
                $_SESSION['flash_success'] = "Category successfully deleted.";
            }
        }
        header('Location: categories.php');
        exit;
    }
}

// Fetch category for edit if requested
if ($action === 'edit' && !empty($_GET['id'])) {
    $stmtE = $pdo->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
    $stmtE->execute([':id' => (int)$_GET['id']]);
    $edit_category = $stmtE->fetch();
    if (!$edit_category) {
        $_SESSION['flash_error'] = "Category not found.";
        header('Location: categories.php');
        exit;
    }
}

// Fetch all categories with coupon count
$stmtList = $pdo->query("
    SELECT cat.*, COUNT(c.id) AS coupon_count 
    FROM categories cat 
    LEFT JOIN coupons c ON cat.id = c.category_id 
    GROUP BY cat.id 
    ORDER BY cat.name ASC
");
$categories = $stmtList->fetchAll();
?>

<!-- Action Bar -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Categories Management</h1>
        <p class="text-muted small mb-0">Organize promo codes and discounts by shopper departments.</p>
    </div>
    <div>
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <a href="categories.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Categories
            </a>
        <?php else: ?>
            <a href="categories.php?action=add" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Add New Category
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger py-2 small mb-3"><?= e($error) ?></div>
<?php endif; ?>

<!-- =========================================================================
     FORM VIEW (ADD OR EDIT)
     ========================================================================= -->
<?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="bg-white border rounded-3 p-4 shadow-sm" style="max-width: 700px;">
        <h2 class="h5 fw-bold mb-4 text-dark">
            <?= ($action === 'edit') ? 'Edit Category: ' . e($edit_category['name']) : 'Create New Category' ?>
        </h2>

        <form action="categories.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="post_action" value="save">
            <input type="hidden" name="category_id" value="<?= e($edit_category['id'] ?? '') ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= e($edit_category['name'] ?? '') ?>" placeholder="e.g. Fashion & Apparel" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">URL Slug (leave blank to auto-generate)</label>
                    <input type="text" name="slug" class="form-control" value="<?= e($edit_category['slug'] ?? '') ?>" placeholder="e.g. fashion-apparel">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Bootstrap Icon Class</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi <?= !empty($edit_category['icon']) ? e($edit_category['icon']) : 'bi-tag' ?>"></i></span>
                    <input type="text" name="icon" class="form-control" value="<?= e($edit_category['icon'] ?? 'bi-tag') ?>" placeholder="e.g. bi-bag-check, bi-laptop, bi-airplane">
                </div>
                <div class="extra-small text-muted mt-1" style="font-size: 0.75rem;">
                    Examples: <code>bi-bag-check</code>, <code>bi-laptop</code>, <code>bi-airplane</code>, <code>bi-activity</code>, <code>bi-house-door</code>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief summary of deals in this category..."><?= e($edit_category['description'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-save me-1"></i> Save Category
                </button>
                <a href="categories.php" class="btn btn-outline-secondary rounded-pill px-3">Cancel</a>
            </div>
        </form>
    </div>

<!-- =========================================================================
     LIST VIEW (DEFAULT)
     ========================================================================= -->
<?php else: ?>

    <div class="bg-white border rounded-3 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Icon</th>
                        <th>Category Name</th>
                        <th>URL Slug</th>
                        <th>Description</th>
                        <th>Total Deals</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <div class="pn-trust-icon" style="width: 38px; height: 38px; font-size: 1.1rem;">
                                        <i class="bi <?= !empty($cat['icon']) ? e($cat['icon']) : 'bi-tag' ?>"></i>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-dark"><?= e($cat['name']) ?></strong>
                                </td>
                                <td>
                                    <code>/category/<?= e($cat['slug']) ?></code>
                                </td>
                                <td class="text-muted small">
                                    <?= e($cat['description']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <?= $cat['coupon_count'] ?> Coupons
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="categories.php?action=edit&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit Category">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!-- Delete Button Trigger -->
                                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="confirmDelete(<?= $cat['id'] ?>, '<?= e(addslashes($cat['name'])) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No categories created yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Category Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete category <strong id="deleteCatName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <form action="categories.php" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="post_action" value="delete">
                        <input type="hidden" name="delete_id" id="deleteIdInput" value="">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(id, name) {
        document.getElementById('deleteIdInput').value = id;
        document.getElementById('deleteCatName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
    </script>

<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>