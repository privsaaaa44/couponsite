<?php
// contact.php - Contact Us Page (Form saved to database)
require_once __DIR__ . '/connection.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($token)) {
        $error_msg = "Security token expired. Please refresh the page and try again.";
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $error_msg = "Please fill in all required fields (Name, Email, Message).";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = "Please provide a valid email address.";
        } else {
            try {
                // Try inserting with status
                $stmt = $pdo->prepare("
                    INSERT INTO contact_messages (name, email, subject, message, status) 
                    VALUES (:name, :email, :subject, :message, 'unread')
                ");
                $stmt->execute([
                    ':name'    => $name,
                    ':email'   => $email,
                    ':subject' => $subject ?: 'General Inquiry',
                    ':message' => $message,
                ]);

                $success_msg = "Thank you! Your message has been received. Our deal support team will review your inquiry and get back to you shortly.";
                $name = $email = $subject = $message = "";
            } catch (PDOException $e) {
                // Fallback insert without status column if schema differs
                try {
                    $stmtFallback = $pdo->prepare("
                        INSERT INTO contact_messages (name, email, subject, message) 
                        VALUES (:name, :email, :subject, :message)
                    ");
                    $stmtFallback->execute([
                        ':name'    => $name,
                        ':email'   => $email,
                        ':subject' => $subject ?: 'General Inquiry',
                        ':message' => $message,
                    ]);

                    $success_msg = "Thank you! Your message has been received. Our deal support team will review your inquiry and get back to you shortly.";
                    $name = $email = $subject = $message = "";
                } catch (PDOException $e2) {
                    $error_msg = "An error occurred while saving your message: " . $e2->getMessage();
                }
            }
        }
    }
}

$page_title = "Contact PromoNest - Support & Deal Submissions";
$page_desc  = "Have a question, feedback or want to submit a working promo code? Contact the PromoNest editorial team today.";

include __DIR__ . '/header.php';
?>

<!-- Breadcrumbs -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    
    <div class="text-center mb-5">
        <h1 class="pn-hero-title mb-2">Get in <span>Touch</span></h1>
        <p class="pn-hero-desc">Have a question, feedback, or a verified coupon you'd like to submit? We'd love to hear from you.</p>
    </div>

    <div class="row g-5 justify-content-center">
        
        <!-- Contact Form Column -->
        <div class="col-lg-7">
            <div class="bg-white border rounded-3 p-4 p-md-5 shadow-sm">
                
                <h2 class="h5 fw-bold mb-4">Send Us a Message</h2>

                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div><?= e($success_msg) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div><?= e($error_msg) ?></div>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('contact.php') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= e($name ?? '') ?>" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= e($email ?? '') ?>" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Subject</label>
                        <input type="text" name="subject" class="form-control" value="<?= e($subject ?? '') ?>" placeholder="Question regarding a store or promo code...">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Your Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Write your message here..." required><?= e($message ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-send me-1"></i> Send Message
                    </button>
                </form>

            </div>
        </div>

        <!-- Support Info Column -->
        <div class="col-lg-5">
            <div class="pn-sidebar-card mb-4">
                <h3 class="pn-sidebar-title">Contact Information</h3>
                
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="pn-trust-icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div>
                        <div class="fw-bold small">Editorial & Inquiries</div>
                        <div class="text-muted small">support@promonest.local</div>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="pn-trust-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div>
                        <div class="fw-bold small">Verification Hours</div>
                        <div class="text-muted small">Monday – Friday: 9am – 6pm EST</div>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="pn-trust-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold small">Response Guarantee</div>
                        <div class="text-muted small">We review all incoming inquiries within 24 business hours.</div>
                    </div>
                </div>
            </div>

            <!-- FAQ Card -->
            <div class="pn-sidebar-card bg-light">
                <h4 class="h6 fw-bold mb-3">Frequently Asked Questions</h4>
                
                <div class="mb-3">
                    <div class="fw-semibold small text-dark">Found an expired code?</div>
                    <div class="text-muted extra-small">You can report it directly using the thumbs-down button on any coupon card modal.</div>
                </div>

                <div>
                    <div class="fw-semibold small text-dark">Are you a merchant or store owner?</div>
                    <div class="text-muted extra-small">Submit your store discount codes using this contact form with subject "Merchant Partnership".</div>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>