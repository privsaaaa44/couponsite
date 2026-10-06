<?php
// search.php - Search Results Page
require_once __DIR__ . '/connection.php';

$query = trim($_GET['query'] ?? $_GET['q'] ?? '');

$page_title = !empty($query) 
    ? "Search Results for \"" . e($query) . "\" - PromoNest" 
    : "Search Coupons & Promo Codes - PromoNest";
$page_desc = "Find verified discount coupons, promo codes, and sales matching your search on PromoNest.";

include __DIR__ . '/header.php';

$matchingBrands = [];
$coupons = [];

if (!empty($query)) {
    $searchTerm = '%' . $query . '%';

    // 1. Search matching brands
    $stmtB = $pdo->prepare("
        SELECT b.*, COUNT(c.id) as coupon_count 
        FROM brands b 
        LEFT JOIN coupons c ON b.id = c.brand_id AND c.status = 'active' AND c.expiry_date >= CURDATE()
        WHERE b.status = 'active' AND (b.name LIKE ? OR b.description LIKE ?)
        GROUP BY b.id
    ");
    $stmtB->execute([$searchTerm, $searchTerm]);
    $matchingBrands = $stmtB->fetchAll();

    // 2. Search matching active coupons
    $stmtC = $pdo->prepare("
        SELECT c.*, b.name AS brand_name, b.logo AS brand_logo, b.slug AS brand_slug, cat.name AS category_name 
        FROM coupons c 
        JOIN brands b ON c.brand_id = b.id 
        JOIN categories cat ON c.category_id = cat.id 
        WHERE (c.title LIKE ? OR b.name LIKE ? OR c.discount_text LIKE ? OR cat.name LIKE ?) 
          AND c.status = 'active' 
          AND b.status = 'active'
          AND c.expiry_date >= CURDATE() 
        ORDER BY c.verified DESC, (c.code IS NOT NULL) DESC, c.id DESC
    ");
    $stmtC->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $coupons = $stmtC->fetchAll();
}

$codeCount = 0;
$dealCount = 0;
foreach ($coupons as $c) {
    if (!empty($c['code'])) {
        $codeCount++;
    } else {
        $dealCount++;
    }
}
?>

<!-- Breadcrumbs -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Search Results</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">

    <!-- Search Form Header -->
    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm text-center">
        <h1 class="h3 fw-bold mb-2">
            <?php if (!empty($query)): ?>
                Search Results for <span class="text-primary">"<?= e($query) ?>"</span>
            <?php else: ?>
                Search Promo Codes & Deals
            <?php endif; ?>
        </h1>
        <p class="text-muted small mb-3">
            Found <strong><?= count($coupons) ?></strong> coupons and <strong><?= count($matchingBrands) ?></strong> matching stores.
        </p>

        <!-- Inline Search Input -->
        <form action="<?= base_url('search.php') ?>" method="GET" class="pn-search-form">
            <div class="pn-search-input-wrap">
                <i class="bi bi-search text-muted fs-5 me-2"></i>
                <input type="text" name="query" class="pn-search-input" value="<?= e($query) ?>" placeholder="Search by brand, category or deal..." required>
                <button type="submit" class="pn-search-submit">Search</button>
            </div>
        </form>
    </div>

    <!-- Matching Brands Callout -->
    <?php if (!empty($matchingBrands)): ?>
        <div class="mb-4">
            <h2 class="h5 fw-bold mb-3">Matching Stores</h2>
            <div class="row g-3 row-cols-2 row-cols-md-4">
                <?php foreach ($matchingBrands as $b): ?>
                    <div class="col">
                        <a href="<?= base_url('store.php?slug=' . urlencode($b['slug'])) ?>" class="text-decoration-none d-block h-100">
                            <div class="pn-brand-card p-3">
                                <div class="pn-brand-logo-wrap" style="height: 50px;">
                                    <img src="<?= base_url('images/' . $b['logo']) ?>" alt="<?= e($b['name']) ?>">
                                </div>
                                <h3 class="pn-brand-name h6 mb-1"><?= e($b['name']) ?></h3>
                                <span class="pn-brand-count"><?= $b['coupon_count'] ?> Active Deals</span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Coupons Results Section -->
    <div class="row g-4">
        <div class="col-lg-8">
            
            <?php if (!empty($coupons)): ?>
                
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <h2 class="h5 fw-bold mb-0">Matching Offers (<?= count($coupons) ?>)</h2>

                    <!-- Filter Pills -->
                    <div class="pn-filter-pills m-0">
                        <button type="button" class="pn-filter-pill active" data-filter="all">All (<?= count($coupons) ?>)</button>
                        <button type="button" class="pn-filter-pill" data-filter="code">Codes (<?= $codeCount ?>)</button>
                        <button type="button" class="pn-filter-pill" data-filter="deal">Deals (<?= $dealCount ?>)</button>
                    </div>
                </div>

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
                                <span class="text-muted">•</span>
                                <span class="badge bg-light text-secondary"><?= e($coupon['category_name']) ?></span>

                                <?php if ($coupon['verified']): ?>
                                    <span class="pn-verified-badge">
                                        <i class="bi bi-patch-check-fill"></i> Verified
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

                <!-- Empty State -->
                <div class="bg-white border rounded-3 p-5 text-center shadow-sm">
                    <i class="bi bi-search text-muted" style="font-size: 3.5rem;"></i>
                    <h3 class="h5 fw-bold mt-3">No matching coupons found</h3>
                    <p class="text-muted small mb-4">
                        We couldn't find any active coupons matching "<strong><?= e($query) ?></strong>". Try searching for brand names like <em>Nike, Amazon, Booking, Gymshark, or IKEA</em>.
                    </p>
                    <a href="<?= base_url('shops.php') ?>" class="btn btn-primary rounded-pill px-4">
                        Browse All Brands
                    </a>
                </div>

            <?php endif; ?>

        </div>

        <!-- Sidebar: Popular Stores & Categories -->
        <div class="col-lg-4">
            
            <div class="pn-sidebar-card">
                <h3 class="pn-sidebar-title">Popular Stores</h3>
                <?php
                $stmtPopular = $pdo->query("SELECT name, slug, logo FROM brands WHERE status = 'active' LIMIT 5");
                $popStores = $stmtPopular->fetchAll();
                ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($popStores as $ps): ?>
                        <a href="<?= base_url('store.php?slug=' . urlencode($ps['slug'])) ?>" class="d-flex align-items-center gap-3 p-2 rounded text-decoration-none text-dark hover-bg">
                            <div style="width: 44px; height: 30px; border: 1px solid var(--pn-border); border-radius: 6px; padding: 2px; display: flex; align-items: center; justify-content: center; background: #fff;">
                                <img src="<?= base_url('images/' . $ps['logo']) ?>" alt="<?= e($ps['name']) ?>" style="max-height: 22px; max-width: 38px; object-fit: contain;">
                            </div>
                            <span class="fw-semibold small flex-grow-1"><?= e($ps['name']) ?></span>
                            <i class="bi bi-chevron-right small text-muted"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pn-sidebar-card bg-light">
                <h4 class="h6 fw-bold mb-2"><i class="bi bi-shield-check text-success me-1"></i> PromoNest Guarantee</h4>
                <p class="small text-muted mb-0">
                    If any coupon doesn't work, give it a quick thumbs-down in the popup so our editors can review and update it within hours.
                </p>
            </div>

        </div>
    </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>