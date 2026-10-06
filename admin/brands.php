<?php
// admin/brands.php - Manage Stores & Brands
include __DIR__ . '/includes/admin_header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$edit_brand = null;

// Helper function to create URL slugs
function create_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'brand-' . time());
}

// =========================================================================
// 1. HANDLE POST REQUESTS (ADD & EDIT)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = "Security token mismatch. Please try again.";
        header('Location: brands.php');
        exit;
    }

    $post_action = $_POST['post_action'] ?? '';

    // Handle Add & Update
    if ($post_action === 'save') {
        $brand_id    = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
        $name        = trim($_POST['name'] ?? '');
        $slug        = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $website_url = trim($_POST['website_url'] ?? '');
        $status      = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        if (empty($name)) {
            $error = "Brand name is required.";
        } else {
            if (empty($slug)) {
                $slug = create_slug($name);
            } else {
                $slug = create_slug($slug);
            }

            // Check slug uniqueness
            $checkSql = "SELECT id FROM brands WHERE slug = :slug" . ($brand_id ? " AND id != :id" : "");
            $checkParams = [':slug' => $slug];
            if ($brand_id) $checkParams[':id'] = $brand_id;
            
            $stmtCheck = $pdo->prepare($checkSql);
            $stmtCheck->execute($checkParams);
            if ($stmtCheck->fetch()) {
                $slug = $slug . '-' . time();
            }

            // Handle Logo File Upload
            $logo_filename = $_POST['existing_logo'] ?? 'default_store.svg';

            if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $fileTmp  = $_FILES['logo']['tmp_name'];
                $fileName = $_FILES['logo']['name'];
                $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed  = ['jpg', 'jpeg', 'png', 'svg', 'webp'];

                if (in_array($fileExt, $allowed)) {
                    $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '', $fileName);
                    
                    $targetPublic = __DIR__ . '/../../images/' . $newFileName;
                    $targetAdmin  = __DIR__ . '/uploads/' . $newFileName;

                    if (move_uploaded_file($fileTmp, $targetPublic)) {
                        copy($targetPublic, $targetAdmin);
                        $logo_filename = $newFileName;
                    }
                } else {
                    $error = "Invalid file extension. Please upload SVG, PNG, JPG, or WEBP.";
                }
            }

            if (empty($error)) {
                if ($brand_id) {
                    // Update
                    $stmt = $pdo->prepare("
                        UPDATE brands 
                        SET name = :name, slug = :slug, logo = :logo, description = :description, 
                            website_url = :website_url, status = :status, is_featured = :is_featured 
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':name'        => $name,
                        ':slug'        => $slug,
                        ':logo'        => $logo_filename,
                        ':description' => $description,
                        ':website_url' => $website_url,
                        ':status'      => $status,
                        ':is_featured' => $is_featured,
                        ':id'          => $brand_id
                    ]);
                    $_SESSION['flash_success'] = "Store \"{$name}\" updated successfully!";
                } else {
                    // Insert
                    $stmt = $pdo->prepare("
                        INSERT INTO brands (name, slug, logo, description, website_url, status, is_featured) 
                        VALUES (:name, :slug, :logo, :description, :website_url, :status, :is_featured)
                    ");
                    $stmt->execute([
                        ':name'        => $name,
                        ':slug'        => $slug,
                        ':logo'        => $logo_filename,
                        ':description' => $description,
                        ':website_url' => $website_url,
                        ':status'      => $status,
                        ':is_featured' => $is_featured
                    ]);
                    $_SESSION['flash_success'] = "New store \"{$name}\" created successfully!";
                }
                header('Location: brands.php');
                exit;
            }
        }
    }

    // Handle Delete
    if ($post_action === 'delete') {
        $delete_id = (int)($_POST['delete_id'] ?? 0);
        if ($delete_id > 0) {
            // First delete associated coupons
            $stmtC = $pdo->prepare("DELETE FROM coupons WHERE brand_id = :id");
            $stmtC->execute([':id' => $delete_id]);

            // Then delete brand
            $stmtB = $pdo->prepare("DELETE FROM brands WHERE id = :id");
            $stmtB->execute([':id' => $delete_id]);

            $_SESSION['flash_success'] = "Store and its associated coupons were deleted successfully.";
        }
        header('Location: brands.php');
        exit;
    }
}

// Fetch brand for edit if requested
if ($action === 'edit' && !empty($_GET['id'])) {
    $stmtE = $pdo->prepare("SELECT * FROM brands WHERE id = :id LIMIT 1");
    $stmtE->execute([':id' => (int)$_GET['id']]);
    $edit_brand = $stmtE->fetch();
    if (!$edit_brand) {
        $_SESSION['flash_error'] = "Store not found.";
        header('Location: brands.php');
        exit;
    }
}

// Fetch list of brands
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$sql = "
    SELECT b.*, COUNT(c.id) as coupon_count 
    FROM brands b 
    LEFT JOIN coupons c ON b.id = c.brand_id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (b.name LIKE :search OR b.slug LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if (!empty($status_filter)) {
    $sql .= " AND b.status = :status";
    $params[':status'] = $status_filter;
}

$sql .= " GROUP BY b.id ORDER BY b.is_featured DESC, b.name ASC";
$stmtList = $pdo->prepare($sql);
$stmtList->execute($params);
$brands = $stmtList->fetchAll();
?>

<!-- Action Bar -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Stores & Brands Management</h1>
        <p class="text-muted small mb-0">Create, edit, or remove partner stores and upload logos.</p>
    </div>
    <div>
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <a href="brands.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Stores
            </a>
        <?php else: ?>
            <a href="brands.php?action=add" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Add New Store
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
    <div class="bg-white border rounded-3 p-4 shadow-sm" style="max-width: 800px;">
        <h2 class="h5 fw-bold mb-4 text-dark">
            <?= ($action === 'edit') ? 'Edit Store: ' . e($edit_brand['name']) : 'Create New Store' ?>
        </h2>

        <form action="brands.php" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="post_action" value="save">
            <input type="hidden" name="brand_id" value="<?= e($edit_brand['id'] ?? '') ?>">
            <input type="hidden" name="existing_logo" value="<?= e($edit_brand['logo'] ?? '') ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Store / Brand Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= e($edit_brand['name'] ?? '') ?>" placeholder="e.g. Nike" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">URL Slug (leave blank to auto-generate)</label>
                    <input type="text" name="slug" class="form-control" value="<?= e($edit_brand['slug'] ?? '') ?>" placeholder="e.g. nike">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Official Website / Affiliate Landing URL</label>
                <input type="url" name="website_url" class="form-control" value="<?= e($edit_brand['website_url'] ?? '') ?>" placeholder="https://www.nike.com">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Store Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief summary of store products and discount offerings..."><?= e($edit_brand['description'] ?? '') ?></textarea>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Brand Logo (SVG, PNG, JPG, WEBP)</label>
                    <input type="file" name="logo" class="form-control" accept=".svg,.png,.jpg,.jpeg,.webp">
                    <?php if (!empty($edit_brand['logo'])): ?>
                        <div class="mt-2 p-2 bg-light border rounded d-inline-flex align-items-center gap-2">
                            <span class="small text-muted">Current:</span>
                            <img src="<?= base_url('images/' . $edit_brand['logo']) ?>" alt="Logo" style="height: 30px; max-width: 90px; object-fit: contain;">
                            <span class="small text-muted">(<?= e($edit_brand['logo']) ?>)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (($edit_brand['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (($edit_brand['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-center mt-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" <?= (!empty($edit_brand['is_featured'])) ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-semibold" for="is_featured">
                            Featured on Homepage
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-save me-1"></i> Save Store
                </button>
                <a href="brands.php" class="btn btn-outline-secondary rounded-pill px-3">Cancel</a>
            </div>
        </form>
    </div>

<!-- =========================================================================
     LIST VIEW (DEFAULT)
     ========================================================================= -->
<?php else: ?>

    <!-- Filter Bar -->
    <div class="bg-white border rounded-3 p-3 mb-4 shadow-sm">
        <form action="brands.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-4">
                <input type="text" name="search" class="form-control form-control-sm" value="<?= e($search) ?>" placeholder="Search by name or slug...">
            </div>
            <div class="col-md-4 col-lg-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($status_filter === 'active') ? 'selected' : '' ?>>Active Only</option>
                    <option value="inactive" <?= ($status_filter === 'inactive') ? 'selected' : '' ?>>Inactive Only</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary px-3">Filter</button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="brands.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Brands Table -->
    <div class="bg-white border rounded-3 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">Logo</th>
                        <th>Store Name</th>
                        <th>Slug & URL</th>
                        <th>Coupons</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($brands)): ?>
                        <?php foreach ($brands as $b): ?>
                            <tr>
                                <td>
                                    <div style="width: 54px; height: 36px; border: 1px solid #E2E8F0; border-radius: 6px; padding: 2px; display: flex; align-items: center; justify-content: center; background: #fff;">
                                        <img src="<?= base_url('images/' . $b['logo']) ?>" alt="<?= e($b['name']) ?>" style="max-height: 28px; max-width: 48px; object-fit: contain;">
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-dark"><?= e($b['name']) ?></strong>
                                    <div class="text-muted extra-small" style="font-size: 0.8rem;"><?= e(mb_strimwidth($b['description'], 0, 45, '...')) ?></div>
                                </td>
                                <td>
                                    <code>/store/<?= e($b['slug']) ?></code>
                                    <div>
                                        <a href="<?= base_url('store.php?slug=' . urlencode($b['slug'])) ?>" target="_blank" class="extra-small text-muted">
                                            View Page ↗
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <?= $b['coupon_count'] ?> Deals
                                    </span>
                                </td>
                                <td>
                                    <?php if ($b['is_featured']): ?>
                                        <span class="badge bg-warning bg-opacity-25 text-dark"><i class="bi bi-star-fill text-warning"></i> Featured</span>
                                    <?php else: ?>
                                        <span class="text-muted small">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'active'): ?>
                                        <span class="badge bg-success bg-opacity-25 text-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="brands.php?action=edit&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit Store">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!-- Delete Button Trigger -->
                                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="confirmDelete(<?= $b['id'] ?>, '<?= e(addslashes($b['name'])) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No brands found matching your criteria.
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
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Store Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteBrandName">this store</strong>?</p>
                    <p class="text-danger small mb-0"><strong>Warning:</strong> Deleting this store will also permanently remove all coupon codes and deals associated with it.</p>
                </div>
                <div class="modal-footer">
                    <form action="brands.php" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="post_action" value="delete">
                        <input type="hidden" name="delete_id" id="deleteIdInput" value="">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete Store</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(id, name) {
        document.getElementById('deleteIdInput').value = id;
        document.getElementById('deleteBrandName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
    </script>

<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>