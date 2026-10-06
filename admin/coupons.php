<?php
// admin/coupons.php - Manage Coupons & Deals
include __DIR__ . '/includes/admin_header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$edit_coupon = null;

// =========================================================================
// 1. HANDLE POST REQUESTS (SAVE & DELETE)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = "Security token mismatch. Please try again.";
        header('Location: coupons.php');
        exit;
    }

    $post_action = $_POST['post_action'] ?? '';

    // Handle Save (Add / Edit)
    if ($post_action === 'save') {
        $coupon_id     = !empty($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : null;
        $brand_id      = (int)($_POST['brand_id'] ?? 0);
        $category_id   = (int)($_POST['category_id'] ?? 0);
        $title         = trim($_POST['title'] ?? '');
        $code          = trim($_POST['code'] ?? '');
        $discount_text = trim($_POST['discount_text'] ?? '');
        $affiliate_url = trim($_POST['affiliate_url'] ?? '');
        $expiry_date   = trim($_POST['expiry_date'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $status        = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $verified      = isset($_POST['verified']) ? 1 : 0;
        $used_count    = (int)($_POST['used_count'] ?? 0);

        // Validation
        if (empty($brand_id) || empty($category_id) || empty($title) || empty($discount_text) || empty($affiliate_url) || empty($expiry_date)) {
            $error = "Please fill in all required fields (Brand, Category, Title, Discount Text, Affiliate URL, Expiry Date).";
        } else {
            // Normalize empty code to NULL
            $code_val = !empty($code) ? $code : null;

            if ($coupon_id) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE coupons 
                    SET brand_id = :brand_id, category_id = :category_id, title = :title, 
                        code = :code, discount_text = :discount_text, affiliate_url = :affiliate_url, 
                        expiry_date = :expiry_date, status = :status, verified = :verified, 
                        used_count = :used_count, description = :description 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':brand_id'      => $brand_id,
                    ':category_id'   => $category_id,
                    ':title'         => $title,
                    ':code'          => $code_val,
                    ':discount_text' => $discount_text,
                    ':affiliate_url' => $affiliate_url,
                    ':expiry_date'   => $expiry_date,
                    ':status'        => $status,
                    ':verified'      => $verified,
                    ':used_count'    => $used_count,
                    ':description'   => $description,
                    ':id'            => $coupon_id
                ]);
                $_SESSION['flash_success'] = "Coupon \"{$title}\" updated successfully!";
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO coupons 
                    (brand_id, category_id, title, code, discount_text, affiliate_url, expiry_date, status, verified, used_count, description) 
                    VALUES 
                    (:brand_id, :category_id, :title, :code, :discount_text, :affiliate_url, :expiry_date, :status, :verified, :used_count, :description)
                ");
                $stmt->execute([
                    ':brand_id'      => $brand_id,
                    ':category_id'   => $category_id,
                    ':title'         => $title,
                    ':code'          => $code_val,
                    ':discount_text' => $discount_text,
                    ':affiliate_url' => $affiliate_url,
                    ':expiry_date'   => $expiry_date,
                    ':status'        => $status,
                    ':verified'      => $verified,
                    ':used_count'    => $used_count,
                    ':description'   => $description
                ]);
                $_SESSION['flash_success'] = "New coupon \"{$title}\" added successfully!";
            }
            header('Location: coupons.php');
            exit;
        }
    }

    // Handle Delete
    if ($post_action === 'delete') {
        $delete_id = (int)($_POST['delete_id'] ?? 0);
        if ($delete_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = :id");
            $stmt->execute([':id' => $delete_id]);
            $_SESSION['flash_success'] = "Coupon successfully deleted.";
        }
        header('Location: coupons.php');
        exit;
    }
}

// Fetch coupon for edit if requested
if ($action === 'edit' && !empty($_GET['id'])) {
    $stmtE = $pdo->prepare("SELECT * FROM coupons WHERE id = :id LIMIT 1");
    $stmtE->execute([':id' => (int)$_GET['id']]);
    $edit_coupon = $stmtE->fetch();
    if (!$edit_coupon) {
        $_SESSION['flash_error'] = "Coupon not found.";
        header('Location: coupons.php');
        exit;
    }
}

// Fetch all brands and categories for dropdowns
$allBrands = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll();
$allCategories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

// =========================================================================
// 2. SEARCH & FILTER IN COUPON LIST
// =========================================================================
$search        = trim($_GET['search'] ?? '');
$brand_filter  = (int)($_GET['brand_id'] ?? 0);
$status_filter = trim($_GET['status'] ?? '');

$sql = "
    SELECT c.*, b.name AS brand_name, cat.name AS category_name 
    FROM coupons c 
    JOIN brands b ON c.brand_id = b.id 
    JOIN categories cat ON c.category_id = cat.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (c.title LIKE :s OR c.code LIKE :s OR c.discount_text LIKE :s)";
    $params[':s'] = '%' . $search . '%';
}
if (!empty($brand_filter)) {
    $sql .= " AND c.brand_id = :bid";
    $params[':bid'] = $brand_filter;
}
if (!empty($status_filter)) {
    $sql .= " AND c.status = :status";
    $params[':status'] = $status_filter;
}

$sql .= " ORDER BY c.id DESC";
$stmtList = $pdo->prepare($sql);
$stmtList->execute($params);
$coupons = $stmtList->fetchAll();
?>

<!-- Action Bar -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Coupons & Deals Management</h1>
        <p class="text-muted small mb-0">Publish promo codes, set discounts, manage expiration dates and affiliate links.</p>
    </div>
    <div>
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <a href="coupons.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Coupons
            </a>
        <?php else: ?>
            <a href="coupons.php?action=add" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Add New Coupon
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
    <div class="bg-white border rounded-3 p-4 shadow-sm" style="max-width: 860px;">
        <h2 class="h5 fw-bold mb-4 text-dark">
            <?= ($action === 'edit') ? 'Edit Coupon: ' . e($edit_coupon['title']) : 'Create New Coupon / Deal' ?>
        </h2>

        <form action="coupons.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="post_action" value="save">
            <input type="hidden" name="coupon_id" value="<?= e($edit_coupon['id'] ?? '') ?>">

            <div class="row g-3 mb-3">
                <!-- Brand Selection -->
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Store / Brand <span class="text-danger">*</span></label>
                    <select name="brand_id" class="form-select" required>
                        <option value="">Select Brand...</option>
                        <?php foreach ($allBrands as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= (($edit_coupon['brand_id'] ?? '') == $b['id']) ? 'selected' : '' ?>>
                                <?= e($b['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Category Selection -->
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category...</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($edit_coupon['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Title -->
            <div class="mb-3">
                <label class="form-label small fw-semibold">Coupon Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= e($edit_coupon['title'] ?? '') ?>" placeholder="e.g. 20% Off Select Running Shoes & Training Apparel" required>
            </div>

            <div class="row g-3 mb-3">
                <!-- Promo Code (Optional) -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Promo Code (Leave blank for deals)</label>
                    <input type="text" name="code" class="form-control" value="<?= e($edit_coupon['code'] ?? '') ?>" placeholder="e.g. JUSTDOIT20">
                    <span class="extra-small text-muted" style="font-size: 0.75rem;">If empty, displays "Get Deal" button.</span>
                </div>

                <!-- Discount Text -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Discount Highlight <span class="text-danger">*</span></label>
                    <input type="text" name="discount_text" class="form-control" value="<?= e($edit_coupon['discount_text'] ?? '') ?>" placeholder="e.g. 20% OFF, $15 OFF, FREE SHIPPING" required>
                </div>

                <!-- Expiry Date -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Expiry Date <span class="text-danger">*</span></label>
                    <input type="date" name="expiry_date" class="form-control" value="<?= e($edit_coupon['expiry_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>" required>
                </div>
            </div>

            <!-- Affiliate URL -->
            <div class="mb-3">
                <label class="form-label small fw-semibold">Affiliate Landing URL <span class="text-danger">*</span></label>
                <input type="url" name="affiliate_url" class="form-control" value="<?= e($edit_coupon['affiliate_url'] ?? '') ?>" placeholder="https://www.nike.com/deal" required>
            </div>

            <!-- Description / Terms -->
            <div class="mb-3">
                <label class="form-label small fw-semibold">Offer Details / Terms & Conditions</label>
                <textarea name="description" class="form-control" rows="2" placeholder="e.g. Valid on all full-price items when spending $75 or more..."><?= e($edit_coupon['description'] ?? '') ?></textarea>
            </div>

            <div class="row g-3 mb-4">
                <!-- Status -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (($edit_coupon['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (($edit_coupon['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <!-- Used Count -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Usage Counter</label>
                    <input type="number" name="used_count" class="form-control" value="<?= (int)($edit_coupon['used_count'] ?? 100) ?>" min="0">
                </div>

                <!-- Verified Checkbox -->
                <div class="col-md-4 d-flex align-items-center mt-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="verified" id="verified" value="1" <?= (!isset($edit_coupon) || !empty($edit_coupon['verified'])) ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-semibold" for="verified">
                            <i class="bi bi-patch-check-fill text-success"></i> Verified by Editor
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-save me-1"></i> Save Coupon
                </button>
                <a href="coupons.php" class="btn btn-outline-secondary rounded-pill px-3">Cancel</a>
            </div>
        </form>
    </div>

<!-- =========================================================================
     LIST VIEW (DEFAULT)
     ========================================================================= -->
<?php else: ?>

    <!-- Filter Bar -->
    <div class="bg-white border rounded-3 p-3 mb-4 shadow-sm">
        <form action="coupons.php" method="GET" class="row g-2 align-items-center">
            
            <!-- Keyword search -->
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" value="<?= e($search) ?>" placeholder="Search title, code, or discount...">
            </div>

            <!-- Brand Filter -->
            <div class="col-md-3">
                <select name="brand_id" class="form-select form-select-sm">
                    <option value="">All Brands</option>
                    <?php foreach ($allBrands as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= ($brand_filter == $b['id']) ? 'selected' : '' ?>>
                            <?= e($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($status_filter === 'active') ? 'selected' : '' ?>>Active Only</option>
                    <option value="inactive" <?= ($status_filter === 'inactive') ? 'selected' : '' ?>>Inactive Only</option>
                </select>
            </div>

            <!-- Submit & Reset -->
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary px-3">Filter</button>
                <?php if (!empty($search) || !empty($brand_filter) || !empty($status_filter)): ?>
                    <a href="coupons.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white border rounded-3 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Store</th>
                        <th>Title & Category</th>
                        <th>Type / Code</th>
                        <th>Discount</th>
                        <th>Expiry Date</th>
                        <th>Verified</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($coupons)): ?>
                        <?php foreach ($coupons as $c): 
                            $isExpired = strtotime($c['expiry_date']) < strtotime(date('Y-m-d'));
                        ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= e($c['brand_name']) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark small" style="max-width: 280px;"><?= e($c['title']) ?></div>
                                    <span class="badge bg-light text-secondary border extra-small"><?= e($c['category_name']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($c['code'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace">
                                            <i class="bi bi-scissors me-1"></i><?= e($c['code']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-lightning-fill me-1"></i>Deal
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-primary small"><?= e($c['discount_text']) ?></strong>
                                </td>
                                <td class="small <?= $isExpired ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= date('M j, Y', strtotime($c['expiry_date'])) ?>
                                    <?php if ($isExpired): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger ms-1">Expired</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($c['verified']): ?>
                                        <i class="bi bi-patch-check-fill text-success fs-5" title="Verified"></i>
                                    <?php else: ?>
                                        <i class="bi bi-dash-circle text-muted fs-5" title="Unverified"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($c['status'] === 'active' && !$isExpired): ?>
                                        <span class="badge bg-success bg-opacity-25 text-success">Active</span>
                                    <?php elseif ($isExpired): ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger">Expired</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="coupons.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit Coupon">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!-- Delete Button Trigger -->
                                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="confirmDelete(<?= $c['id'] ?>, '<?= e(addslashes($c['title'])) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No coupons found matching your criteria.
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
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Coupon Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to permanently delete this coupon offer?</p>
                    <p class="text-dark small mb-0"><strong id="deleteCouponTitle"></strong></p>
                </div>
                <div class="modal-footer">
                    <form action="coupons.php" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="post_action" value="delete">
                        <input type="hidden" name="delete_id" id="deleteIdInput" value="">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete Coupon</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(id, title) {
        document.getElementById('deleteIdInput').value = id;
        document.getElementById('deleteCouponTitle').textContent = title;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
    </script>

<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>