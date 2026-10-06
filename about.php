<?php
// about.php - About PromoNest Page
require_once __DIR__ . '/connection.php';

$page_title = "About PromoNest - The Smarter Way to Save Online";
$page_desc  = "Learn about PromoNest, our coupon verification methodology, our editorial guidelines, and how we help shoppers save on every online purchase.";

include __DIR__ . '/header.php';
?>

<!-- Breadcrumbs -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">About Us</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    
    <!-- Hero / Headline -->
    <div class="text-center mb-5" style="max-width: 780px; margin-left: auto; margin-right: auto;">
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill mb-2 fw-semibold">Our Mission</span>
        <h1 class="pn-hero-title mb-3">Ending the Frustration of <span>Broken Coupon Codes</span></h1>
        <p class="pn-hero-desc">
            We founded PromoNest with a simple belief: online coupon sites should actually help you save money, not send you down rabbit holes of expired codes and click-bait ads.
        </p>
    </div>

    <!-- Stats Banner -->
    <div class="row g-3 mb-5 text-center">
        <div class="col-6 col-md-3">
            <div class="bg-white border rounded-3 p-4 shadow-sm">
                <div class="h2 fw-bold text-primary mb-1">100%</div>
                <div class="text-muted small">Hand-Verified Codes</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white border rounded-3 p-4 shadow-sm">
                <div class="h2 fw-bold text-success mb-1">25+</div>
                <div class="text-muted small">Daily Store Deals</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white border rounded-3 p-4 shadow-sm">
                <div class="h2 fw-bold text-info mb-1">\$1.4M+</div>
                <div class="text-muted small">Shopper Savings</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white border rounded-3 p-4 shadow-sm">
                <div class="h2 fw-bold text-warning mb-1">0</div>
                <div class="text-muted small">Spammy Popups</div>
            </div>
        </div>
    </div>

    <!-- Story & Process Section -->
    <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6">
            <h2 class="h3 fw-bold mb-3">How We Verify Every Single Offer</h2>
            <p class="text-muted">
                Unlike automated scraper sites that fill pages with defunct coupons, our editorial team manually tests promo codes by adding products to cart, applying codes at checkout, and confirming real discounts before publishing.
            </p>
            <ul class="list-unstyled d-flex flex-column gap-3 mt-4">
                <li class="d-flex align-items-start gap-3">
                    <div class="pn-trust-icon" style="width: 36px; height: 36px; font-size: 1rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <strong class="text-dark d-block">Cart-Level Validation</strong>
                        <span class="text-muted small">We test minimum purchase thresholds, item exclusions, and member prerequisites.</span>
                    </div>
                </li>
                <li class="d-flex align-items-start gap-3">
                    <div class="pn-trust-icon" style="width: 36px; height: 36px; font-size: 1rem;">
                        <i class="bi bi-calendar2-check"></i>
                    </div>
                    <div>
                        <strong class="text-dark d-block">Automatic Expiration Hiding</strong>
                        <span class="text-muted small">The second a promotional code passes its expiration date, our system retires it automatically.</span>
                    </div>
                </li>
                <li class="d-flex align-items-start gap-3">
                    <div class="pn-trust-icon" style="width: 36px; height: 36px; font-size: 1rem;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <strong class="text-dark d-block">Real Community Feedback</strong>
                        <span class="text-muted small">Our one-tap feedback system allows shoppers to confirm what worked in real-time.</span>
                    </div>
                </li>
            </ul>
        </div>

        <div class="col-lg-6">
            <div class="bg-white border rounded-3 p-4 p-md-5 shadow-sm">
                <h3 class="h5 fw-bold mb-3">Our Core Principles</h3>
                <div class="mb-4">
                    <h4 class="h6 fw-bold text-primary mb-1">1. Shopper-First Integrity</h4>
                    <p class="text-muted small mb-0">We prioritize verified working coupons over revenue opportunities. If a code doesn't work, we don't display it.</p>
                </div>
                <div class="mb-4">
                    <h4 class="h6 fw-bold text-primary mb-1">2. Transparent Affiliate Disclosures</h4>
                    <p class="text-muted small mb-0">We disclose affiliate partnerships clearly. When you save money using our links, we may receive a commission at no extra cost to you.</p>
                </div>
                <div>
                    <h4 class="h6 fw-bold text-primary mb-1">3. Mobile-First Speed</h4>
                    <p class="text-muted small mb-0">No bloated scripts or tracking cookies that slow down your checkout. Clean, instant, and frictionless.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to action -->
    <div class="bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 p-5 text-center">
        <h3 class="h4 fw-bold mb-2">Ready to Start Saving?</h3>
        <p class="text-muted small mb-4">Browse our verified brands or search for discounts today.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= base_url('shops.php') ?>" class="btn btn-primary rounded-pill px-4">Browse Stores</a>
            <a href="<?= base_url('contact.php') ?>" class="btn btn-outline-primary rounded-pill px-4">Contact Team</a>
        </div>
    </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>