<?php
// category.php - Individual Category Deals Page (/category/{slug})
require_once __DIR__ . '/connection.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// 1. Fetch Category by Slug
$stmtCat = $pdo->prepare("SELECT * FROM categories WHERE slug = :slug LIMIT 1");
$stmtCat->execute([':slug' => $slug]);
$category = $stmtCat->fetch();

// 404 Handler if Category not found
if (!$category) {
    http_response_code(404);
    $page_title = "Category Not Found - PromoNest";
    $page_desc  = "The requested category could not be found.";
    include __DIR__ . '/header.php';
    ?>
    <div class="container py-5 text-center">
        <div class="py-5">
            <i class="bi bi-tag text-muted" style="font-size: 4rem;"></i>
            <h1 class="h3 fw-bold mt-3">Category Not Found</h1>
            <p class="text-muted">We could not locate this shopping category. Discover all categories below.</p>
            <a href="<?= base_url('categories.php') ?>" class="btn btn-primary rounded-pill px-4 mt-2">
                Browse All Categories
            </a>
        </div>
    </div>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

// 2. Fetch Active Coupons in this Category
$stmtCoupons = $pdo->prepare("
    SELECT c.*, b.name AS brand_name, b.logo AS brand_logo, b.slug AS brand_slug 
    FROM coupons c 
    JOIN brands b ON c.brand_id = b.id 
    WHERE c.category_id = :cat_id 
      AND c.status = 'active' 
      AND b.status = 'active'
      AND c.expiry_date >= CURDATE() 
    ORDER BY c.verified DESC, (c.code IS NOT NULL) DESC, c.id DESC
");
$stmtCoupons->execute([':cat_id' => $category['id']]);
$coupons = $stmtCoupons->fetchAll();

// 3. Fetch Brands that belong to this category
$stmtBrands = $pdo->prepare("
    SELECT DISTINCT b.id, b.name, b.slug, b.logo 
    FROM brands b 
    JOIN coupons c ON b.id = c.brand_id 
    WHERE c.category_id = :cat_id AND b.status = 'active' AND c.status = 'active'
");
$stmtBrands->execute([':cat_id' => $category['id']]);
$categoryBrands = $stmtBrands->fetchAll();

// Counts
$codeCount = 0;
$dealCount = 0;
foreach ($coupons as $c) {
    if (!empty($c['code'])) {
        $codeCount++;
    } else {
        $dealCount++;
    }
}

// SEO Meta
$page_title = e($category['name']) . " Promo Codes & Discounts (" . date('F Y') . ") - PromoNest";
$page_desc  = "Browse the latest verified " . e($category['name']) . " coupons, sales, and promo codes. Hand-tested daily by the PromoNest team.";

include __DIR__ . '/header.php';
?>

<!-- Breadcrumbs -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('categories.php') ?>">Categories</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($category['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">

    <!-- Category Hero Header -->
    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="pn-category-icon m-0" style="width: 58px; height: 58px; font-size: 1.6rem;">
                <i class="bi <?= !empty($category['icon']) ? e($category['icon']) : 'bi-tag' ?>"></i>
            </div>
            <div>
                <h1 class="h3 fw-bold mb-1"><?= e($category['name']) ?> Promo Codes & Offers</h1>
                <p class="text-muted mb-0"><?= e($category['description']) ?></p>
            </div>
            <div class="ms-md-auto">
                <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2 rounded-pill">
                    <?= count($coupons) ?> Active Offers
                </span>
            </div>
        </div>

        <!-- Stores in this category -->
        <?php if (!empty($categoryBrands)): ?>
            <div class="mt-4 pt-3 border-top">
                <div class="small fw-bold text-muted text-uppercase mb-2">Featured Stores in this category:</div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php foreach ($categoryBrands as $b): ?>
                        <a href="<?= base_url('store.php?slug=' . urlencode($b['slug'])) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2">
                            <img src="<?= base_url('images/' . $b['logo']) ?>" alt="<?= e($b['name']) ?>" style="height: 16px; max-width: 40px; object-fit: contain;">
                            <span><?= e($b['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Coupons List Section -->
    <div class="row g-4">
        <div class="col-lg-8">
            
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h2 class="h5 fw-bold mb-0">Verified <?= e($category['name']) ?> Deals</h2>

                <!-- Filter Pills -->
                <div class="pn-filter-pills m-0">
                    <button type="button" class="pn-filter-pill active" data-filter="all">All Offers (<?= count($coupons) ?>)</button>
                    <button type="button" class="pn-filter-pill" data-filter="code">Codes (<?= $codeCount ?>)</button>
                    <button type="button" class="pn-filter-pill" data-filter="deal">Deals (<?= $dealCount ?>)</button>
                </div>
            </div>

            <?php if (!empty($coupons)): ?>
                <?php foreach ($coupons as $coupon): 
                    $hasCode = !empty($coupon['code']);
                    $cardType = $hasCode ? 'code' : 'deal';
                    $logoPath = base_url('images/' . $coupon['brand_logo']);
                ?>
                    <article class="pn-coupon-card" data-type="<?= $cardType ?>">
                        
                        <!-- Left: Discount Badge -->
                        <div class="pn-coupon-discount-box">
                            <div class="pn-coupon-discount-value"><?= e($coupon['discount_text']) ?></div>
                            <div class="pn-coupon-type-tag"><?= $hasCode ? 'Promo Code' : 'Special Deal' ?></div>
                        </div>

                        <!-- Middle: Details -->
                        <div class="pn-coupon-content">
                            
                            <div class="pn-coupon-meta">
                                <a href="<?= base_url('store.php?slug=' . urlencode($coupon['brand_slug'])) ?>" class="pn-coupon-brand-link">
                                    <?= e($coupon['brand_name']) ?>
                                </a>

                                <?php if ($coupon['verified']): ?>
                                    <span class="pn-verified-badge">
                                        <i class="bi bi-patch-check-fill"></i> Verified Today
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($coupon['used_count'])): ?>
                                    <span class="pn-used-counter">
                                        <i class="bi bi-fire text-danger"></i> <?= number_format($coupon['used_count']) ?> used
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="pn-coupon-title"><?= e($coupon['title']) ?></h3>

                            <?php if (!empty($coupon['description'])): ?>
                                <p class="pn-coupon-desc"><?= e($coupon['description']) ?></p>
                            <?php endif; ?>

                            <div class="pn-coupon-footer-info">
                                <span class="pn-coupon-expiry">
                                    <i class="bi bi-clock"></i> Valid until <?= date('M j, Y', strtotime($coupon['expiry_date'])) ?>
                                </span>
                            </div>

                        </div>

                        <!-- Right: Action Button -->
                        <div class="pn-coupon-action-box">
                            <?php if ($hasCode): ?>
                                <button type="button" 
                                        class="pn-btn-show-code" 
                                        onclick="openCouponModal('<?= e($coupon['code']) ?>', '<?= e($coupon['affiliate_url']) ?>', '<?= e(addslashes($coupon['title'])) ?>', '<?= e($coupon['discount_text']) ?>', '<?= e($logoPath) ?>', '<?= e(addslashes($coupon['brand_name'])) ?>')">
                                    <i class="bi bi-scissors me-1"></i> Show Code
                                </button>
                            <?php else: ?>
                                <a href="<?= e($coupon['affiliate_url']) ?>" 
                                   target="_blank" 
                                   rel="nofollow sponsored" 
                                   class="pn-btn-get-deal text-decoration-none">
                                    Get Deal <i class="bi bi-arrow-up-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info py-4 text-center">
                    <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                    No active coupons currently listed under <strong><?= e($category['name']) ?></strong>.
                </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar: Other Categories -->
        <div class="col-lg-4">
            <div class="pn-sidebar-card">
                <h3 class="pn-sidebar-title">All Categories</h3>
                <?php
                $stmtAllCats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                $allCats = $stmtAllCats->fetchAll();
                ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($allCats as $ac): ?>
                        <a href="<?= base_url('category.php?slug=' . urlencode($ac['slug'])) ?>" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark <?= ($ac['id'] == $category['id']) ? 'bg-primary-subtle text-primary fw-bold' : 'hover-bg' ?>">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi <?= !empty($ac['icon']) ? e($ac['icon']) : 'bi-tag' ?>"></i>
                                <span><?= e($ac['name']) ?></span>
                            </div>
                            <i class="bi bi-chevron-right small text-muted"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Savings Tip Card -->
            <div class="pn-sidebar-card bg-light">
                <h4 class="h6 fw-bold mb-2"><i class="bi bi-lightbulb text-warning me-1"></i> Smart Shopper Tip</h4>
                <p class="small text-muted mb-0">
                    Bookmark your favorite category page and subscribe to our deal newsletter so you are alerted the minute new seasonal discount codes are posted!
                </p>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>
