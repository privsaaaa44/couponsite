<?php
// footer.php - Reusable Footer Component
?>
    </main>

    <!-- Global Coupon Code Reveal Modal -->
    <div class="pn-modal-backdrop" id="pnCouponModal" role="dialog" aria-modal="true" aria-labelledby="pnModalTitle">
        <div class="pn-modal-box">
            <button type="button" class="pn-modal-close" onclick="closeCouponModal()" aria-label="Close modal">
                <i class="bi bi-x-lg"></i>
            </button>

            <!-- Store Logo -->
            <img src="" id="pnModalLogo" class="pn-modal-store-logo" alt="Store Logo" style="display:none;">

            <!-- Discount Highlight Tag -->
            <div class="pn-modal-discount" id="pnModalDiscount">Special Offer</div>

            <!-- Title -->
            <h3 class="pn-modal-title" id="pnModalTitle">Coupon Code</h3>

            <!-- Code display with 1-tap copy -->
            <div class="pn-code-display-wrap">
                <span class="pn-code-string" id="pnModalCode">PROMO10</span>
                <button type="button" class="pn-btn-copy-code" id="pnCopyBtn" onclick="copyCouponCode()">
                    <i class="bi bi-clipboard me-1"></i> Copy Code
                </button>
            </div>

            <p class="pn-modal-instructions">
                Paste this code in the promo box during checkout on the merchant site.
            </p>

            <!-- Open Store Affiliate Link -->
            <a href="#" id="pnModalStoreBtn" target="_blank" rel="nofollow sponsored" class="pn-btn-modal-affiliate text-decoration-none">
                Continue to Store ↗
            </a>

            <!-- Feedback -->
            <div class="pn-modal-feedback" id="pnModalFeedback">
                <span>Did this code work?</span>
                <button type="button" class="pn-feedback-btn" onclick="recordModalFeedback(true)">👍 Yes</button>
                <button type="button" class="pn-feedback-btn" onclick="recordModalFeedback(false)">👎 No</button>
            </div>
        </div>
    </div>

    <!-- Site Footer -->
    <footer class="pn-footer">
        <div class="container">
            <div class="row g-4">
                
                <!-- Col 1: Brand Info -->
                <div class="col-lg-4 col-md-6">
                    <a href="<?= base_url() ?>" class="d-inline-block mb-3">
                        <img src="<?= base_url('images/logo.svg') ?>" alt="PromoNest" width="160" height="40" style="filter: brightness(0) invert(1);">
                    </a>
                    <p class="pn-footer-brand-desc">
                        PromoNest is your go-to coupon destination for hand-verified promo codes, exclusive discounts, and verified daily offers from the world's most trusted brands.
                    </p>
                    <div class="d-flex align-items-center gap-2 mt-3 text-white">
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1">
                            <i class="bi bi-patch-check-fill me-1"></i> 100% Tested Daily
                        </span>
                        <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 px-2 py-1">
                            <i class="bi bi-shield-lock-fill me-1"></i> Zero Spam
                        </span>
                    </div>
                </div>

                <!-- Col 2: Top Stores -->
                <div class="col-lg-3 col-md-6">
                    <h5>Popular Stores</h5>
                    <ul class="pn-footer-links">
                        <li><a href="<?= base_url('store.php?slug=nike') ?>">Nike Promo Codes</a></li>
                        <li><a href="<?= base_url('store.php?slug=amazon') ?>">Amazon Discount Deals</a></li>
                        <li><a href="<?= base_url('store.php?slug=booking-com') ?>">Booking.com Hotel Coupons</a></li>
                        <li><a href="<?= base_url('store.php?slug=gymshark') ?>">Gymshark Workout Offers</a></li>
                        <li><a href="<?= base_url('store.php?slug=ikea') ?>">IKEA Home Discounts</a></li>
                    </ul>
                </div>

                <!-- Col 3: Browse Categories -->
                <div class="col-lg-2 col-md-6">
                    <h5>Categories</h5>
                    <ul class="pn-footer-links">
                        <li><a href="<?= base_url('category.php?slug=fashion-apparel') ?>">Fashion & Apparel</a></li>
                        <li><a href="<?= base_url('category.php?slug=electronics-tech') ?>">Electronics & Tech</a></li>
                        <li><a href="<?= base_url('category.php?slug=travel-hotels') ?>">Travel & Hotels</a></li>
                        <li><a href="<?= base_url('category.php?slug=fitness-activewear') ?>">Fitness & Gym</a></li>
                        <li><a href="<?= base_url('category.php?slug=home-living') ?>">Home & Living</a></li>
                    </ul>
                </div>

                <!-- Col 4: Quick Links -->
                <div class="col-lg-3 col-md-6">
                    <h5>Company & Legal</h5>
                    <ul class="pn-footer-links">
                        <li><a href="<?= base_url('about.php') ?>">About PromoNest</a></li>
                        <li><a href="<?= base_url('contact.php') ?>">Contact & Support</a></li>
                        <li><a href="<?= base_url('privacy.php') ?>">Privacy Policy</a></li>
                        <li><a href="<?= base_url('sitemap.xml') ?>">XML Sitemap</a></li>
                        <li><a href="<?= base_url('admin/login.php') ?>"><i class="bi bi-lock me-1"></i> Admin Access</a></li>
                    </ul>
                </div>

            </div>

            <!-- Affiliate Disclosure -->
            <div class="pn-footer-disclosure">
                <strong>Affiliate Disclosure:</strong> PromoNest is reader-supported. When you click through links or use coupon codes on our website, we may earn an affiliate commission at no extra cost to you. All merchant links are tagged with <code>rel="nofollow sponsored"</code> in compliance with FTC guidelines and search engine best practices. All registered trademarks and logos belong to their respective brand owners.
            </div>

            <!-- Footer Bottom -->
            <div class="pn-footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> <strong>PromoNest</strong>. All rights reserved.
                </div>
                <div class="d-flex gap-3">
                    <a href="<?= base_url('privacy.php') ?>" class="text-secondary text-decoration-none">Privacy Policy</a>
                    <a href="<?= base_url('contact.php') ?>" class="text-secondary text-decoration-none">Contact Us</a>
                    <a href="<?= base_url('sitemap.xml') ?>" class="text-secondary text-decoration-none">Sitemap</a>
                </div>
            </div>

        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PromoNest Main JS -->
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>