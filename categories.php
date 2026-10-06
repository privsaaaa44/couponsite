<?php
// categories.php - Browse All Categories Directory
require_once __DIR__ . '/connection.php';

$page_title = "Browse All Coupon Categories - PromoNest";
$page_desc  = "Discover verified promo codes and discounts organized by category. From Fashion to Tech, Travel, Fitness, and Home decor.";

include __DIR__ . '/header.php';

// Fetch all categories with deal counts
$stmt = $pdo->query("
    SELECT cat.*, COUNT(c.id) AS coupon_count 
    FROM categories cat 
    LEFT JOIN coupons c ON cat.id = c.category_id AND c.status = 'active' AND c.expiry_date >= CURDATE()
    GROUP BY cat.id 
    ORDER BY cat.name ASC
");
$categories = $stmt->fetchAll();
?>

<!-- Breadcrumbs -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">All Categories</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    
    <div class="text-center mb-5">
        <h1 class="pn-hero-title mb-2">Explore <span>Coupon Categories</span></h1>
        <p class="pn-hero-desc">Find tested discounts and vouchers categorized by your favorite shopping interests.</p>
    </div>

    <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-lg-3">
        <?php foreach ($categories as $cat): 
            // Fetch sample brands for each category
            $stmtB = $pdo->prepare("
                SELECT DISTINCT b.name, b.slug 
                FROM brands b 
                JOIN coupons c ON b.id = c.brand_id 
                WHERE c.category_id = :cid AND b.status = 'active' 
                LIMIT 3
            ");
            $stmtB->execute([':cid' => $cat['id']]);
            $sampleBrands = $stmtB->fetchAll();
        ?>
            <div class="col">
                <div class="pn-category-card text-start align-items-stretch p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="pn-category-icon m-0">
                            <i class="bi <?= !empty($cat['icon']) ? e($cat['icon']) : 'bi-tag' ?>"></i>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1">
                            <?= $cat['coupon_count'] ?> Offers
                        </span>
                    </div>

                    <h2 class="h5 fw-bold mb-2">
                        <a href="<?= base_url('category.php?slug=' . urlencode($cat['slug'])) ?>" class="text-dark text-decoration-none">
                            <?= e($cat['name']) ?>
                        </a>
                    </h2>

                    <p class="text-muted small mb-3 flex-grow-1">
                        <?= e($cat['description']) ?>
                    </p>

                    <?php if (!empty($sampleBrands)): ?>
                        <div class="pt-2 border-top small">
                            <span class="text-muted">Top brands:</span>
                            <?php foreach ($sampleBrands as $sb): ?>
                                <a href="<?= base_url('store.php?slug=' . urlencode($sb['slug'])) ?>" class="badge bg-light text-dark text-decoration-none border me-1">
                                    <?= e($sb['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3 pt-2">
                        <a href="<?= base_url('category.php?slug=' . urlencode($cat['slug'])) ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold">
                            View All Deals <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>