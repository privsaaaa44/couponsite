<?php
// admin/includes/admin_header.php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../connection.php';

$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION['admin_username'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PromoNest Admin Control Panel</title>

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --pn-admin-dark: #0F172A;
            --pn-admin-sidebar: #1E293B;
            --pn-primary: #4F46E5;
            --pn-emerald: #10B981;
        }

        body {
            background-color: #F8FAFC;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            min-height: 100vh;
        }

        .admin-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        #adminSidebar {
            min-width: 250px;
            max-width: 250px;
            background: var(--pn-admin-sidebar);
            color: #E2E8F0;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            z-index: 1050;
        }

        .sidebar-brand {
            padding: 20px;
            background: var(--pn-admin-dark);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-brand a {
            color: #FFFFFF;
            font-weight: 800;
            font-size: 1.15rem;
            text-decoration: none;
            letter-spacing: -0.02em;
        }

        .sidebar-brand a span {
            color: #818CF8;
        }

        .sidebar-nav {
            padding: 16px 0;
            list-style: none;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #94A3B8;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .sidebar-nav li a:hover,
        .sidebar-nav li a.active {
            color: #FFFFFF;
            background: rgba(255, 255, 255, 0.06);
            border-left-color: var(--pn-primary);
        }

        .sidebar-nav li a i {
            font-size: 1.15rem;
            width: 22px;
            text-align: center;
        }

        #adminContent {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .admin-topbar {
            background: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .admin-page-body {
            padding: 24px;
            flex: 1 1 auto;
        }

        .stat-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* Mobile sidebar */
        @media (max-width: 768px) {
            #adminSidebar {
                position: fixed;
                top: 0;
                left: -260px;
                height: 100vh;
                box-shadow: 4px 0 16px rgba(0,0,0,0.2);
            }
            #adminSidebar.show-mobile {
                left: 0;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(15, 23, 42, 0.5);
                z-index: 1040;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="admin-wrapper">
    
    <!-- Sidebar -->
    <nav id="adminSidebar">
        <div class="sidebar-brand">
            <a href="index.php">
                <i class="bi bi-tag-fill text-primary me-1"></i> Promo<span>Nest</span>
            </a>
            <button class="btn btn-sm btn-link text-white d-md-none p-0" onclick="toggleSidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <ul class="sidebar-nav">
            <li>
                <a href="index.php" class="<?= ($current_page === 'index.php') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="brands.php" class="<?= ($current_page === 'brands.php') ? 'active' : '' ?>">
                    <i class="bi bi-shop"></i> Stores & Brands
                </a>
            </li>
            <li>
                <a href="coupons.php" class="<?= ($current_page === 'coupons.php') ? 'active' : '' ?>">
                    <i class="bi bi-ticket-perforated"></i> Coupons & Deals
                </a>
            </li>
            <li>
                <a href="categories.php" class="<?= ($current_page === 'categories.php') ? 'active' : '' ?>">
                    <i class="bi bi-grid"></i> Categories
                </a>
            </li>
            <li>
                <a href="messages.php" class="<?= ($current_page === 'messages.php') ? 'active' : '' ?>">
                    <i class="bi bi-envelope"></i> Inquiries
                </a>
            </li>
        </ul>

        <div class="mt-auto p-3 border-top border-secondary border-opacity-25">
            <a href="logout.php" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Are you sure you want to log out of the admin panel?');">
                <i class="bi bi-box-arrow-right me-1"></i> Sign Out
            </a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div id="adminContent">
        
        <!-- Topbar -->
        <div class="admin-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm d-md-none" onclick="toggleSidebar()">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="fw-bold text-dark">
                    Administrator Workspace
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="../index.php" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="bi bi-globe me-1"></i> View Live Site
                </a>
                <span class="small text-muted d-none d-sm-inline">
                    Logged in as: <strong><?= e($admin_name) ?></strong>
                </span>
            </div>
        </div>

        <!-- Content Body -->
        <div class="admin-page-body">
            
            <!-- Flash Message Banner -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= e($_SESSION['flash_success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($_SESSION['flash_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>