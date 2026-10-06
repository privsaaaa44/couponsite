/**
 * PromoNest - Core Frontend JavaScript
 * Handles "Show Code" modal popup, 1-tap clipboard copying, new tab affiliate opening,
 * mobile navigation drawer, and instant coupon filtering.
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Navigation Drawer Toggle
    const menuToggle = document.getElementById('pnMenuToggle');
    const drawerClose = document.getElementById('pnDrawerClose');
    const mobileDrawer = document.getElementById('pnMobileDrawer');

    if (menuToggle && mobileDrawer) {
        menuToggle.addEventListener('click', function () {
            mobileDrawer.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (drawerClose && mobileDrawer) {
        drawerClose.addEventListener('click', function () {
            mobileDrawer.classList.remove('open');
            document.body.style.overflow = '';
        });
    }

    if (mobileDrawer) {
        mobileDrawer.addEventListener('click', function (e) {
            if (e.target === mobileDrawer) {
                mobileDrawer.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    }

    // 2. Setup Coupon Filter Pills (All / Codes Only / Deals Only)
    const filterPills = document.querySelectorAll('.pn-filter-pill');
    if (filterPills.length > 0) {
        filterPills.forEach(pill => {
            pill.addEventListener('click', function () {
                filterPills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                const filterType = this.getAttribute('data-filter');
                const couponCards = document.querySelectorAll('.pn-coupon-card');

                couponCards.forEach(card => {
                    const cardType = card.getAttribute('data-type'); // 'code' or 'deal'
                    if (filterType === 'all' || cardType === filterType) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }
});

/**
 * Open Coupon Modal & Launch Affiliate Link in New Tab
 * @param {string} code - The promo code
 * @param {string} affiliateUrl - The merchant/affiliate URL
 * @param {string} title - Coupon title
 * @param {string} discount - Discount amount/text
 * @param {string} logoUrl - Store logo image URL
 * @param {string} storeName - Store brand name
 */
function openCouponModal(code, affiliateUrl, title, discount, logoUrl, storeName) {
    const modal = document.getElementById('pnCouponModal');
    if (!modal) return;

    // 1. Open affiliate link in a new tab (with rel="nofollow sponsored" behavior)
    if (affiliateUrl && affiliateUrl !== '#') {
        window.open(affiliateUrl, '_blank', 'noopener,noreferrer');
    }

    // 2. Populate modal details
    const modalTitle = document.getElementById('pnModalTitle');
    const modalDiscount = document.getElementById('pnModalDiscount');
    const modalCode = document.getElementById('pnModalCode');
    const modalLogo = document.getElementById('pnModalLogo');
    const modalStoreBtn = document.getElementById('pnModalStoreBtn');
    const copyBtn = document.getElementById('pnCopyBtn');

    if (modalTitle) modalTitle.textContent = title || 'Special Promo Code';
    if (modalDiscount) modalDiscount.textContent = discount || 'Exclusive Offer';
    if (modalCode) modalCode.textContent = code || 'NOCODE';
    
    if (modalLogo) {
        if (logoUrl) {
            modalLogo.src = logoUrl;
            modalLogo.alt = storeName || 'Store';
            modalLogo.style.display = 'block';
        } else {
            modalLogo.style.display = 'none';
        }
    }

    if (modalStoreBtn) {
        modalStoreBtn.href = affiliateUrl || '#';
        modalStoreBtn.textContent = 'Go to ' + (storeName || 'Store') + ' ↗';
    }

    // Reset copy button state
    if (copyBtn) {
        copyBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Code';
        copyBtn.classList.remove('copied');
    }

    // 3. Show Modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

/**
 * Close Coupon Modal
 */
function closeCouponModal() {
    const modal = document.getElementById('pnCouponModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

/**
 * One-Tap Copy to Clipboard
 */
function copyCouponCode() {
    const codeEl = document.getElementById('pnModalCode');
    const copyBtn = document.getElementById('pnCopyBtn');
    if (!codeEl) return;

    const code = codeEl.textContent.trim();

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(() => {
            handleCopiedState(copyBtn);
        }).catch(() => {
            fallbackCopy(code, copyBtn);
        });
    } else {
        fallbackCopy(code, copyBtn);
    }
}

function fallbackCopy(text, button) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        handleCopiedState(button);
    } catch (err) {
        console.error('Copy fallback failed', err);
    }
    document.body.removeChild(textArea);
}

function handleCopiedState(button) {
    if (!button) return;
    button.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Copied!';
    button.classList.add('copied');

    // Visual notification
    setTimeout(() => {
        button.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Again';
    }, 2500);
}

/**
 * Quick User Feedback in Modal
 */
function recordModalFeedback(isWorking) {
    const wrap = document.getElementById('pnModalFeedback');
    if (wrap) {
        if (isWorking) {
            wrap.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-hand-thumbs-up-fill me-1"></i> Thanks! Marked as working.</span>';
        } else {
            wrap.innerHTML = '<span class="text-muted"><i class="bi bi-info-circle me-1"></i> Thank you! Our editors will re-test this code.</span>';
        }
    }
}
