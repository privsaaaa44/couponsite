<?php
// shops.php - All Stores / Brands Directory
require_once __DIR__ . '/connection.php';

$page_title = "All Stores & Brands - PromoNest";
$page_desc  = "Browse all stores offering verified coupon codes and discounts on PromoNest. Nike, Amazon, Booking.com, Gymshark, IKEA and more.";

include __DIR__ . '/header.php';

// Fetch all active brands with coupon counts
$stmt = $pdo->query("
    SELECT b.*, COUNT(c.id) AS coupon_count 
    FROM brands b 
    LEFT JOIN coupons c ON b.id = c.brand_id AND c.status = 'active' AND c.expiry_date >= CURDATE()
    WHERE b.status = 'active' 
    GROUP BY b.id 
    ORDER BY b.name ASC
");
$brands = $stmt->fetchAll();
?>

<!-- Breadcrumbs -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">All Stores</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    
    <div class="text-center mb-5">
        <h1 class="pn-hero-title mb-2">Browse <span>All Stores</span></h1>
        <p class="pn-hero-desc">Discover top global brands with verified promo codes and daily deals.</p>
    </div>

    <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5">
        <?php foreach ($brands as $brand): ?>
            <div class="col">
                <div class="pn-brand-card p-4 h-100">
                    <div class="pn-brand-logo-wrap" style="height: 70px;">
                        <img src="<?= base_url('images/' . $brand['logo']) ?>" alt="<?= e($brand['name']) ?>" loading="lazy">
                    </div>

                    <h2 class="pn-brand-name h6 fw-bold mb-2"><?= e($brand['name']) ?></h2>
                    
                    <p class="text-muted extra-small mb-3 text-center" style="font-size: 0.8rem; line-height: 1.4;">
                        <?= e(mb_strimwidth($brand['description'], 0, 75, '...')) ?>
                    </p>

                    <span class="pn-brand-count mb-3"><?= $brand['coupon_count'] ?> Active Deals</span>

                    <div class="mt-auto w-100">
                        <a href="<?= base_url('store.php?slug=' . urlencode($brand['slug'])) ?>" class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold">
                            View Offers <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>