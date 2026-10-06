<?php
// admin/index.php - Admin Dashboard Overview
include __DIR__ . '/includes/admin_header.php';

// SQL Counters
$total_brands = (int)$pdo->query("SELECT COUNT(*) FROM brands")->fetchColumn();
$total_coupons = (int)$pdo->query("SELECT COUNT(*) FROM coupons")->fetchColumn();
$active_coupons = (int)$pdo->query("SELECT COUNT(*) FROM coupons WHERE status = 'active' AND expiry_date >= CURDATE()")->fetchColumn();
$expired_coupons = (int)$pdo->query("SELECT COUNT(*) FROM coupons WHERE expiry_date < CURDATE()")->fetchColumn();
$total_messages = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

// Fetch Recent 6 Coupons
$stmtRecent = $pdo->query("
    SELECT c.*, b.name AS brand_name 
    FROM coupons c 
    JOIN brands b ON c.brand_id = b.id 
    ORDER BY c.id DESC 
    LIMIT 6
");
$recentCoupons = $stmtRecent->fetchAll();

// Fetch Recent 5 Inquiries
$stmtMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 5");
$recentMessages = $stmtMessages->fetchAll();
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Dashboard Overview</h1>
        <p class="text-muted small mb-0">System metrics, active deals count, and recent coupon updates.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="coupons.php?action=add" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Coupon
        </a>
        <a href="brands.php?action=add" class="btn btn-outline-dark btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Store
        </a>
    </div>
</div>

<!-- Stat Cards Grid -->
<div class="row g-3 mb-4">
    
    <!-- Total Brands -->
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Total Brands</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $total_brands ?></div>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-shop"></i>
            </div>
        </div>
    </div>

    <!-- Total Coupons -->
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Total Coupons</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $total_coupons ?></div>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-ticket-perforated"></i>
            </div>
        </div>
    </div>

    <!-- Active Coupons -->
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Active Deals</div>
                <div class="fs-3 fw-bold text-success mt-1"><?= $active_coupons ?></div>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>

    <!-- Expired Coupons -->
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div>
                <div class="text-muted small fw-semibold">Expired Coupons</div>
                <div class="fs-3 fw-bold text-danger mt-1"><?= $expired_coupons ?></div>
            </div>
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>

</div>

<!-- Recent Activity Tables -->
<div class="row g-4">
    
    <!-- Left: Recent Coupons -->
    <div class="col-lg-8">
        <div class="bg-white border rounded-3 p-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 fw-bold mb-0">Recently Added Coupons</h2>
                <a href="coupons.php" class="text-primary small fw-semibold">View All Coupons &rarr;</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Store</th>
                            <th>Title & Discount</th>
                            <th>Code</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentCoupons)): ?>
                            <?php foreach ($recentCoupons as $rc): 
                                $isExpired = strtotime($rc['expiry_date']) < strtotime(date('Y-m-d'));
                            ?>
                                <tr>
                                    <td class="fw-bold"><?= e($rc['brand_name']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark small"><?= e($rc['title']) ?></div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary extra-small"><?= e($rc['discount_text']) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($rc['code'])): ?>
                                            <code><?= e($rc['code']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted small">No-code Deal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small <?= $isExpired ? 'text-danger fw-bold' : 'text-muted' ?>">
                                        <?= date('M j, Y', strtotime($rc['expiry_date'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($rc['status'] === 'active' && !$isExpired): ?>
                                            <span class="badge bg-success bg-opacity-25 text-success">Active</span>
                                        <?php elseif ($isExpired): ?>
                                            <span class="badge bg-danger bg-opacity-25 text-danger">Expired</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="coupons.php?action=edit&id=<?= $rc['id'] ?>" class="btn btn-sm btn-light text-primary py-0 px-2" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No coupons created yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Recent Inquiries -->
    <div class="col-lg-4">
        <div class="bg-white border rounded-3 p-4 shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 fw-bold mb-0">Recent Messages</h2>
                <a href="messages.php" class="text-primary small fw-semibold">View All &rarr;</a>
            </div>

            <?php if (!empty($recentMessages)): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($recentMessages as $msg): ?>
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong class="small text-dark"><?= e($msg['name']) ?></strong>
                                <span class="text-muted extra-small" style="font-size: 0.75rem;"><?= date('M j', strtotime($msg['created_at'])) ?></span>
                            </div>
                            <div class="text-primary small fw-semibold mb-1"><?= e($msg['subject']) ?></div>
                            <p class="text-muted extra-small mb-0" style="font-size: 0.8rem; line-height: 1.35;">
                                <?= e(mb_strimwidth($msg['message'], 0, 70, '...')) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted small">
                    No contact form messages yet.
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>