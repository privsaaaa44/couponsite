<?php
// privacy.php - Privacy Policy & Affiliate Disclosure
require_once __DIR__ . '/connection.php';

$page_title = "Privacy Policy & Affiliate Disclosure - PromoNest";
$page_desc  = "Read PromoNest's privacy policy and FTC-compliant affiliate disclosure. Learn how we handle user data and maintain commercial transparency.";

include __DIR__ . '/header.php';
?>

<!-- Breadcrumbs -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5" style="max-width: 860px;">
    
    <div class="bg-white border rounded-3 p-4 p-md-5 shadow-sm">
        
        <h1 class="h3 fw-bold mb-2">Privacy Policy & Affiliate Disclosure</h1>
        <p class="text-muted small mb-4">Last Updated: <?= date('F j, Y') ?></p>
        <hr class="mb-4">

        <!-- Affiliate Disclosure Highlight Box -->
        <div class="alert alert-info border-info mb-4">
            <h4 class="h6 fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i> FTC Affiliate Disclosure</h4>
            <p class="small mb-0">
                PromoNest is a participant in affiliate marketing programs. When you click on coupon buttons (such as "Show Code" or "Get Deal") and complete a purchase on a merchant's website, we may earn an affiliate commission at zero additional cost to you. In compliance with search engine guidelines, all affiliate links are tagged with <code>rel="nofollow sponsored"</code>.
            </p>
        </div>

        <section class="mb-4">
            <h2 class="h5 fw-bold mb-2">1. Overview</h2>
            <p class="text-muted small">
                PromoNest ("we", "our", or "us") is dedicated to protecting your online privacy. This Privacy Policy describes how we collect, use, and safeguard personal information when you browse our website, search for discount vouchers, or contact our customer support.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="h5 fw-bold mb-2">2. Information We Collect</h2>
            <p class="text-muted small">
                We believe in data minimization. We do not require visitors to register for an account to browse deals or copy coupon codes. The only personal data we collect is:
            </p>
            <ul class="text-muted small">
                <li><strong>Contact Form Information:</strong> When you send us an inquiry via our Contact Us form, we collect your name, email address, message subject, and content to reply to your inquiry.</li>
                <li><strong>Anonymous Usage Data:</strong> Standard server logs, page views, browser type, device type, and timestamp information to monitor website performance and security.</li>
            </ul>
        </section>

        <section class="mb-4">
            <h2 class="h5 fw-bold mb-2">3. Cookies & Tracking Technologies</h2>
            <p class="text-muted small">
                PromoNest may utilize minimal, essential cookies for session management and CSRF security verification. When you navigate to partner retailer websites, those third-party merchants may drop tracking cookies on their domain to verify that the referral originated from PromoNest for commission accounting purposes.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="h5 fw-bold mb-2">4. Third-Party Links</h2>
            <p class="text-muted small">
                Our site features links to third-party merchant sites (including Nike, Amazon, Booking.com, Gymshark, and IKEA). We have no direct control over the privacy practices, pricing, or terms of third-party websites. We encourage you to review their respective privacy policies when completing transactions.
            </p>
        </section>

        <section class="mb-4">
            <h2 class="h5 fw-bold mb-2">5. Data Protection & Security</h2>
            <p class="text-muted small">
                All communications and queries are secured using modern encryption standards. We do not sell, rent, or trade your contact details with external marketers or advertising brokers.
            </p>
        </section>

        <section>
            <h2 class="h5 fw-bold mb-2">6. Contact Our Privacy Officer</h2>
            <p class="text-muted small mb-0">
                If you have questions regarding our privacy practices or wish to request the removal of any submitted messages, please contact us at <a href="<?= base_url('contact.php') ?>">privacy@promonest.local</a>.
            </p>
        </section>

    </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>