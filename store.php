<?php
// store.php - Individual Brand / Store Page
require_once __DIR__ . '/connection.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// 1. Fetch Brand by slug
$stmtBrand = $pdo->prepare("SELECT * FROM brands WHERE slug = :slug AND status = 'active' LIMIT 1");
$stmtBrand->execute([':slug' => $slug]);
$brand = $stmtBrand->fetch();

// 404 Handler if store not found
if (!$brand) {
    http_response_code(404);
    $page_title = "Store Not Found - PromoNest";
    $page_desc  = "The requested store could not be found.";
    include __DIR__ . '/header.php';
    ?>
    <div class="container py-5 text-center">
        <div class="py-5">
            <i class="bi bi-shop-window text-muted" style="font-size: 4rem;"></i>
            <h1 class="h3 fw-bold mt-3">Store Not Found</h1>
            <p class="text-muted">We couldn't find the store you were looking for. Browse our active stores below.</p>
            <a href="<?= base_url('shops.php') ?>" class="btn btn-primary rounded-pill px-4 mt-2">
                Browse All Stores
            </a>
        </div>
    </div>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

// 2. Fetch Active Coupons for this brand (auto-hide expired)
$stmtCoupons = $pdo->prepare("
    SELECT c.*, cat.name AS category_name 
    FROM coupons c 
    JOIN categories cat ON c.category_id = cat.id 
    WHERE c.brand_id = :brand_id 
      AND c.status = 'active' 
      AND c.expiry_date >= CURDATE() 
    ORDER BY c.verified DESC, (c.code IS NOT NULL) DESC, c.id DESC
");
$stmtCoupons->execute([':brand_id' => $brand['id']]);
$coupons = $stmtCoupons->fetchAll();

// 3. Count codes vs deals
$codeCount = 0;
$dealCount = 0;
$topDiscount = '';
foreach ($coupons as $c) {
    if (!empty($c['code'])) {
        $codeCount++;
    } else {
        $dealCount++;
    }
    if (empty($topDiscount) && !empty($c['discount_text'])) {
        $topDiscount = $c['discount_text'];
    }
}

// 4. Fetch Other Popular Brands for sidebar
$stmtOtherBrands = $pdo->prepare("
    SELECT id, name, slug, logo 
    FROM brands 
    WHERE id != :brand_id AND status = 'active' 
    LIMIT 4
");
$stmtOtherBrands->execute([':brand_id' => $brand['id']]);
$otherBrands = $stmtOtherBrands->fetchAll();

// SEO Meta
$currentMonthYear = date('F Y');
$page_title = e($brand['name']) . " Promo Codes & Discounts (" . $currentMonthYear . ") - PromoNest";
$page_desc  = "Get verified " . e($brand['name']) . " promo codes, coupon vouchers, and exclusive savings for " . $currentMonthYear . ". Tested and verified daily.";

include __DIR__ . '/header.php';
?>

<!-- Breadcrumbs -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('shops.php') ?>">Stores</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($brand['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">

    <!-- Store Header Card -->
    <div class="pn-store-header-card">
        <div class="d-flex align-items-center gap-3 flex-wrap flex-sm-nowrap">
            <div class="pn-store-logo-box">
                <img src="<?= base_url('images/' . $brand['logo']) ?>" alt="<?= e($brand['name']) ?> Logo">
            </div>
            
            <div class="pn-store-info">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h1 class="pn-store-title mb-0"><?= e($brand['name']) ?> Promo Codes & Coupons</h1>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 small">
                        <i class="bi bi-patch-check-fill me-1"></i> Verified Merchant
                    </span>
                </div>

                <p class="pn-store-desc">
                    <?= e($brand['description']) ?>
                </p>

                <div class="pn-store-stats">
                    <span class="pn-store-stat-item"><b><?= count($coupons) ?></b> Total Offers</span>
                    <span class="text-muted">•</span>
                    <span class="pn-store-stat-item"><b><?= $codeCount ?></b> Promo Codes</span>
                    <span class="text-muted">•</span>
                    <span class="pn-store-stat-item">Best Discount: <b class="text-primary"><?= $topDiscount ?: 'Great Savings' ?></b></span>
                </div>
            </div>
        </div>

        <div class="align-self-start align-self-md-center">
            <a href="<?= e($brand['website_url'] ?: '#') ?>" target="_blank" rel="nofollow sponsored" class="pn-btn-visit-store">
                Visit <?= e($brand['name']) ?> Store <i class="bi bi-box-arrow-up-right ms-1"></i>
            </a>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Main Column: Coupon Cards -->
        <div class="col-lg-8">
            
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h2 class="h5 fw-bold mb-0">Active <?= e($brand['name']) ?> Deals & Vouchers</h2>

                <!-- Filter Pills -->
                <div class="pn-filter-pills m-0">
                    <button type="button" class="pn-filter-pill active" data-filter="all">All (<?= count($coupons) ?>)</button>
                    <button type="button" class="pn-filter-pill" data-filter="code">Codes (<?= $codeCount ?>)</button>
                    <button type="button" class="pn-filter-pill" data-filter="deal">Deals (<?= $dealCount ?>)</button>
                </div>
            </div>

            <?php if (!empty($coupons)): ?>
                <?php foreach ($coupons as $coupon): 
                    $hasCode = !empty($coupon['code']);
                    $cardType = $hasCode ? 'code' : 'deal';
                    $logoPath = base_url('images/' . $brand['logo']);
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
                                <span class="badge bg-light text-secondary"><?= e($coupon['category_name']) ?></span>

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
                                        onclick="openCouponModal('<?= e($coupon['code']) ?>', '<?= e($coupon['affiliate_url']) ?>', '<?= e(addslashes($coupon['title'])) ?>', '<?= e($coupon['discount_text']) ?>', '<?= e($logoPath) ?>', '<?= e(addslashes($brand['name'])) ?>')">
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
                    No active coupons currently available for <strong><?= e($brand['name']) ?></strong>. 
                    Check back soon as our team updates store codes daily!
                </div>
            <?php endif; ?>

            <!-- Brand FAQs Section -->
            <div class="mt-5 bg-white p-4 rounded-3 border">
                <h3 class="h5 fw-bold mb-3"><?= e($brand['name']) ?> Promo Codes & Savings FAQ</h3>
                
                <div class="accordion accordion-flush" id="brandFaqAccordion">
                    
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="faqHeading1">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                                How do I redeem a promo code on <?= e($brand['name']) ?>?
                            </button>
                        </h4>
                        <div id="faqCollapse1" class="accordion-collapse collapse" data-bs-parent="#brandFaqAccordion">
                            <div class="accordion-body text-muted small">
                                Click on the <strong>"Show Code"</strong> button above. Copy the revealed voucher code to your clipboard. When you proceed to the <?= e($brand['name']) ?> checkout page, locate the promo code or voucher input field, paste your code, and click apply to receive your instant discount.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h4 class="accordion-header" id="faqHeading2">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2">
                                Are the <?= e($brand['name']) ?> coupon codes on PromoNest verified?
                            </button>
                        </h4>
                        <div id="faqCollapse2" class="accordion-collapse collapse" data-bs-parent="#brandFaqAccordion">
                            <div class="accordion-body text-muted small">
                                Yes! Our dedicated team manually tests and verifies discounts every morning to ensure you only get working offers. We immediately retire expired or invalid promo codes.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h4 class="accordion-header" id="faqHeading3">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3">
                                Can I combine multiple promo codes on one purchase?
                            </button>
                        </h4>
                        <div id="faqCollapse3" class="accordion-collapse collapse" data-bs-parent="#brandFaqAccordion">
                            <div class="accordion-body text-muted small">
                                Most retailers permit one promotional code per order. However, many codes can be stacked alongside existing clearance or sale markdowns for maximum savings.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Sidebar Column: Instructions & Related Brands -->
        <div class="col-lg-4">
            
            <!-- How to use code card -->
            <div class="pn-sidebar-card">
                <h3 class="pn-sidebar-title">How to Use a <?= e($brand['name']) ?> Code</h3>
                
                <div class="pn-step-item">
                    <div class="pn-step-number">1</div>
                    <div class="pn-step-text">
                        <strong>Find your offer:</strong> Choose your favorite discount offer above and click <em>"Show Code"</em>.
                    </div>
                </div>

                <div class="pn-step-item">
                    <div class="pn-step-number">2</div>
                    <div class="pn-step-text">
                        <strong>Copy code:</strong> The code will pop up in a dialog. Tap the <em>"Copy Code"</em> button.
                    </div>
                </div>

                <div class="pn-step-item">
                    <div class="pn-step-number">3</div>
                    <div class="pn-step-text">
                        <strong>Shop the store:</strong> <?= e($brand['name']) ?> opens in a new tab. Add your items to your shopping cart.
                    </div>
                </div>

                <div class="pn-step-item">
                    <div class="pn-step-number">4</div>
                    <div class="pn-step-text">
                        <strong>Apply & save:</strong> Paste the coupon code into the checkout promotion box and enjoy your savings!
                    </div>
                </div>
            </div>

            <!-- Store Quick Info -->
            <div class="pn-sidebar-card">
                <h3 class="pn-sidebar-title">Store Summary</h3>
                <ul class="list-unstyled mb-0 small">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Store Name:</span>
                        <strong class="text-dark"><?= e($brand['name']) ?></strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-success bg-opacity-25 text-success">Verified Active</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Official Site:</span>
                        <a href="<?= e($brand['website_url'] ?: '#') ?>" target="_blank" rel="nofollow sponsored" class="text-primary">
                            Visit Site ↗
                        </a>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Updated:</span>
                        <span><?= date('M j, Y') ?></span>
                    </li>
                </ul>
            </div>

            <!-- Related Stores -->
            <?php if (!empty($otherBrands)): ?>
                <div class="pn-sidebar-card">
                    <h3 class="pn-sidebar-title">Other Popular Stores</h3>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($otherBrands as $ob): ?>
                            <a href="<?= base_url('store.php?slug=' . urlencode($ob['slug'])) ?>" class="d-flex align-items-center gap-3 text-decoration-none text-dark p-2 rounded hover-bg">
                                <div style="width: 50px; height: 35px; border: 1px solid var(--pn-border); border-radius: 6px; padding: 3px; display: flex; align-items: center; justify-content: center; background: #fff;">
                                    <img src="<?= base_url('images/' . $ob['logo']) ?>" alt="<?= e($ob['name']) ?>" style="max-height: 25px; max-width: 44px; object-fit: contain;">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small"><?= e($ob['name']) ?></div>
                                    <span class="text-muted extra-small">View promo codes & deals</span>
                                </div>
                                <i class="bi bi-chevron-right text-muted small"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>