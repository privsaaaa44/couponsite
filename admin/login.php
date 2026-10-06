<?php
// admin/login.php - Administrator Secure Login
require_once __DIR__ . '/../../connection.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = "Security token mismatch. Please try again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!empty($username) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Regenerate session id to protect against session fixation
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['flash_success'] = "Welcome back, " . $admin['username'] . "!";

                header('Location: index.php');
                exit;
            } else {
                $error = "Invalid username or password. Please try again.";
            }
        } else {
            $error = "Please provide both username and password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PromoNest</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background-color: #0F172A;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #FFFFFF;
            border-radius: 16px;
            padding: 36px 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .btn-brand {
            background-color: #4F46E5;
            color: #FFFFFF;
            font-weight: 600;
            border: none;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn-brand:hover {
            background-color: #4338CA;
            color: #FFFFFF;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <a href="<?= base_url() ?>" class="d-inline-block mb-2 text-decoration-none">
            <span class="fs-3 fw-bold text-dark"><i class="bi bi-tag-fill text-primary"></i> Promo<span class="text-primary">Nest</span></span>
        </a>
        <h1 class="h5 fw-bold text-dark mb-1">Admin Control Center</h1>
        <p class="text-muted small">Sign in to manage stores, coupons, and deals</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-circle-fill"></i>
            <div><?= e($error) ?></div>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                <input type="text" name="username" class="form-control" placeholder="admin" required autocomplete="username">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>
        </div>

        <button type="submit" class="btn btn-brand w-100">
            Sign In to Dashboard <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <div class="mt-4 pt-3 border-top text-center">
        <span class="badge bg-light text-muted border p-2">
            Demo credentials: <strong>admin</strong> / <strong>admin123</strong>
        </span>
    </div>

    <div class="text-center mt-3">
        <a href="<?= base_url() ?>" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Public Website
        </a>
    </div>
</div>

</body>
</html>