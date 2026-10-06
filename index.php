<?php
// index.php - PromoNest Homepage
require_once __DIR__ . '/connection.php';

$page_title = "PromoNest - Hand-Verified Coupon Codes, Discounts & Deals";
$page_desc  = "Save money online with 100% verified coupon codes, discounts, and exclusive offers for Nike, Amazon, Booking.com, Gymshark, IKEA and more. Tested daily.";

include __DIR__ . '/header.php';

// 1. Fetch Featured Brands with active coupon count
$stmtBrands = $pdo->prepare("
    SELECT b.*, COUNT(c.id) AS coupon_count 
    FROM brands b 
    LEFT JOIN coupons c ON b.id = c.brand_id AND c.status = 'active' AND c.expiry_date >= CURDATE()
    WHERE b.status = 'active' 
    GROUP BY b.id 
    ORDER BY b.is_featured DESC, b.name ASC
");
$stmtBrands->execute();
$featuredBrands = $stmtBrands->fetchAll();

// 2. Fetch Trending Active Coupons (auto-hiding expired ones)
$stmtCoupons = $pdo->prepare("
    SELECT c.*, b.name AS brand_name, b.logo AS brand_logo, b.slug AS brand_slug, cat.name AS category_name 
    FROM coupons c 
    JOIN brands b ON c.brand_id = b.id 
    JOIN categories cat ON c.category_id = cat.id 
    WHERE c.expiry_date >= CURDATE() AND c.status = 'active' AND b.status = 'active'
    ORDER BY c.verified DESC, c.used_count DESC, c.id DESC 
    LIMIT 12
");
$stmtCoupons->execute();
$trendingCoupons = $stmtCoupons->fetchAll();

// 3. Fetch Categories with deal counts
$stmtCats = $pdo->prepare("
    SELECT cat.*, COUNT(c.id) AS deal_count 
    FROM categories cat 
    LEFT JOIN coupons c ON cat.id = c.category_id AND c.status = 'active' AND c.expiry_date >= CURDATE()
    GROUP BY cat.id 
    ORDER BY cat.name ASC
");
$stmtCats->execute();
$categories = $stmtCats->fetchAll();
?>

<!-- 1. Hero & Search Section -->
<section class="pn-hero">
    <div class="container text-center">
        
        <div class="pn-hero-badge">
            <i class="bi bi-stars text-warning"></i>
            <span>Over 25+ Hand-Verified Codes Updated Today</span>
        </div>

        <h1 class="pn-hero-title">
            Smart Savings, Verified Daily.<br>
            <span>Coupons That Actually Work.</span>
        </h1>

        <p class="pn-hero-desc">
            Stop wasting time with expired promo codes. Browse guaranteed working discounts, exclusive promo codes, and daily sales across top brands.
        </p>

        <!-- Search Bar -->
        <form action="<?= base_url('search.php') ?>" method="GET" class="pn-search-form">
            <div class="pn-search-input-wrap">
                <i class="bi bi-search text-muted fs-5 me-2"></i>
                <input type="text" name="query" class="pn-search-input" placeholder="Search by store or deal (e.g. Nike, 20% off, hotels)..." required autocomplete="off">
                <button type="submit" class="pn-search-submit">
                    Find Deals <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>

        <!-- Quick Store Chips -->
        <div class="pn-quick-chips">
            <span class="pn-chip-label">Trending Stores:</span>
            <?php foreach ($featuredBrands as $b): ?>
                <a href="<?= base_url('store.php?slug=' . urlencode($b['slug'])) ?>" class="pn-chip">
                    <?= e($b['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- 2. Featured Stores Section -->
<section class="py-5">
    <div class="container">
        
        <div class="pn-section-header">
            <div>
                <h2 class="pn-section-title">Top Featured Stores</h2>
                <p class="pn-section-subtitle">Verified savings from the world's most trusted brands</p>
            </div>
            <a href="<?= base_url('shops.php') ?>" class="text-primary fw-semibold small">
                View All Stores <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <div class="row g-3 row-cols-2 row-cols-sm-3 row-cols-md-5">
            <?php foreach ($featuredBrands as $brand): ?>
                <div class="col">
                    <a href="<?= base_url('store.php?slug=' . urlencode($brand['slug'])) ?>" class="text-decoration-none d-block h-100">
                        <div class="pn-brand-card">
                            <div class="pn-brand-logo-wrap">
                                <img src="<?= base_url('images/' . $brand['logo']) ?>" alt="<?= e($brand['name']) ?>" loading="lazy">
                            </div>
                            <h3 class="pn-brand-name"><?= e($brand['name']) ?></h3>
                            <span class="pn-brand-count"><?= $brand['coupon_count'] ?> Offers</span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- 3. Trending Active Coupons Section -->
<section class="py-4 bg-white border-top border-bottom">
    <div class="container">
        
        <div class="pn-section-header">
            <div>
                <h2 class="pn-section-title">Trending Verified Coupons</h2>
                <p class="pn-section-subtitle">Top discounts hand-tested by our deal specialists</p>
            </div>

            <!-- Filter Pills -->
            <div class="pn-filter-pills m-0">
                <button type="button" class="pn-filter-pill active" data-filter="all">All Offers (<?= count($trendingCoupons) ?>)</button>
                <button type="button" class="pn-filter-pill" data-filter="code">Promo Codes</button>
                <button type="button" class="pn-filter-pill" data-filter="deal">Sales & Deals</button>
            </div>
        </div>

        <!-- Coupon Cards List -->
        <div class="row g-3">
            <div class="col-12">
                <?php if (!empty($trendingCoupons)): ?>
                    <?php foreach ($trendingCoupons as $coupon): 
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

                            <!-- Middle: Coupon Information -->
                            <div class="pn-coupon-content">
                                
                                <div class="pn-coupon-meta">
                                    <a href="<?= base_url('store.php?slug=' . urlencode($coupon['brand_slug'])) ?>" class="pn-coupon-brand-link">
                                        <?= e($coupon['brand_name']) ?>
                                    </a>
                                    <span class="text-muted">•</span>
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
                    <div class="alert alert-info text-center py-4">
                        No active coupons found at the moment. Please check back shortly!
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- 4. Category Shortcuts Section -->
<section class="py-5">
    <div class="container">
        
        <div class="pn-section-header">
            <div>
                <h2 class="pn-section-title">Explore by Category</h2>
                <p class="pn-section-subtitle">Find deals tailored to what you love shopping for</p>
            </div>
            <a href="<?= base_url('categories.php') ?>" class="text-primary fw-semibold small">
                All Categories <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5">
            <?php foreach ($categories as $cat): ?>
                <div class="col">
                    <a href="<?= base_url('category.php?slug=' . urlencode($cat['slug'])) ?>" class="text-decoration-none d-block h-100">
                        <div class="pn-category-card">
                            <div class="pn-category-icon">
                                <i class="bi <?= !empty($cat['icon']) ? e($cat['icon']) : 'bi-tag' ?>"></i>
                            </div>
                            <h3 class="pn-category-title"><?= e($cat['name']) ?></h3>
                            <p class="pn-category-desc mb-2"><?= e($cat['description']) ?></p>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 small">
                                <?= $cat['deal_count'] ?> Active Deals
                            </span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- 5. Trust / Value Proposition Section -->
<section class="pn-trust-section">
    <div class="container">
        <div class="row g-4">
            
            <div class="col-md-4">
                <div class="pn-trust-item">
                    <div class="pn-trust-icon">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <div>
                        <h4 class="pn-trust-title">100% Tested Daily</h4>
                        <p class="pn-trust-text">Every promo code on PromoNest is real-time verified by shopping experts. No fake or spammy click-bait coupons.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="pn-trust-item">
                    <div class="pn-trust-icon">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <div>
                        <h4 class="pn-trust-title">One-Tap Code Copying</h4>
                        <p class="pn-trust-text">Instant code copying with automatic merchant store redirection saves you time and ensures hassle-free savings.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="pn-trust-item">
                    <div class="pn-trust-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h4 class="pn-trust-title">Free & Independent</h4>
                        <p class="pn-trust-text">PromoNest is 100% free to use. We maintain full editorial transparency with compliant affiliate disclosures.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 6. Newsletter Banner -->
<section class="container">
    <div class="pn-newsletter-card">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h3 class="pn-newsletter-title">Never Miss a Flash Sale</h3>
                <p class="pn-newsletter-desc">Subscribe to receive curated weekly promo codes, secret discount drops, and member-only vouchers straight to your inbox.</p>
            </div>
            <div class="col-lg-5">
                <form action="<?= base_url('contact.php') ?>" method="GET" class="pn-newsletter-form">
                    <input type="email" name="email" class="pn-newsletter-input" placeholder="Enter your email address..." required>
                    <button type="submit" class="pn-newsletter-btn">
                        Subscribe Free
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>