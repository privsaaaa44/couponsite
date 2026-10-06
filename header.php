<?php
// header.php - Reusable Header Component
require_once __DIR__ . '/connection.php';

$page_title = $page_title ?? "PromoNest - Verified Coupons, Promo Codes & Discounts";
$page_desc = $page_desc ?? "Discover hand-tested coupons, promo codes, and daily deals for top online stores. Save money effortlessly with 100% verified discounts.";

$current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title><?= e($page_title) ?></title>
    <meta name="description" content="<?= e($page_desc) ?>">
    <meta name="robots" content="index, follow">

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- PromoNest Custom Mobile-First CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

    <!-- Header Navigation -->
    <header class="pn-navbar">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between py-2">
                
                <!-- Brand Logo -->
                <a href="<?= base_url() ?>" class="navbar-brand">
                    <img src="<?= base_url('images/logo.svg') ?>" alt="PromoNest" width="160" height="40">
                </a>

                <!-- Desktop Search Bar -->
                <div class="d-none d-lg-block flex-grow-1 mx-4" style="max-width: 420px;">
                    <form action="<?= base_url('search.php') ?>" method="GET" class="position-relative">
                        <div class="pn-search-input-wrap py-1 px-3">
                            <i class="bi bi-search text-muted me-2"></i>
                            <input type="text" name="query" class="pn-search-input py-1" placeholder="Search stores or deals" required>
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 py-1 ms-1">Search</button>
                        </div>
                    </form>
                </div>

                <!-- Desktop Nav Links -->
                <nav class="d-none d-md-flex align-items-center gap-1">
                    <a href="<?= base_url() ?>" class="pn-nav-link <?= ($current_script === 'index.php') ? 'active' : '' ?>">Home</a>
                    <a href="<?= base_url('shops.php') ?>" class="pn-nav-link <?= ($current_script === 'shops.php') ? 'active' : '' ?>">Stores</a>
                    <a href="<?= base_url('categories.php') ?>" class="pn-nav-link <?= ($current_script === 'categories.php') ? 'active' : '' ?>">Categories</a>
                    <a href="<?= base_url('about.php') ?>" class="pn-nav-link <?= ($current_script === 'about.php') ? 'active' : '' ?>">About</a>
                    <a href="<?= base_url('contact.php') ?>" class="pn-nav-link <?= ($current_script === 'contact.php') ? 'active' : '' ?>">Contact</a>
                </nav>

                <!-- Mobile Menu Button -->
                <div class="d-flex align-items-center gap-2 d-md-none">
                    <button class="pn-menu-toggle" id="pnMenuToggle" aria-label="Open mobile menu">
                        <i class="bi bi-list"></i>
                    </button>
                </div>

            </div>
        </div>
    </header>

    <!-- Mobile Navigation Drawer -->
    <div class="pn-mobile-drawer" id="pnMobileDrawer">
        <div class="pn-drawer-content">
            <div class="pn-drawer-header">
                <a href="<?= base_url() ?>" class="navbar-brand">
                    <img src="<?= base_url('images/logo.svg') ?>" alt="PromoNest" width="130" height="34">
                </a>
                <button type="button" class="btn-close" id="pnDrawerClose" aria-label="Close"></button>
            </div>

            <!-- Mobile Search Form -->
            <form action="<?= base_url('search.php') ?>" method="GET" class="mb-4">
                <div class="pn-search-input-wrap py-1 px-3">
                    <i class="bi bi-search text-muted me-2"></i>
                    <input type="text" name="query" class="pn-search-input py-1" placeholder="Search brands..." required>
                </div>
            </form>

            <nav class="pn-drawer-nav">
                <a href="<?= base_url() ?>" class="pn-drawer-link"><i class="bi bi-house-door me-2 text-primary"></i> Home</a>
                <a href="<?= base_url('shops.php') ?>" class="pn-drawer-link"><i class="bi bi-shop me-2 text-primary"></i> All Stores</a>
                <a href="<?= base_url('categories.php') ?>" class="pn-drawer-link"><i class="bi bi-grid me-2 text-primary"></i> Categories</a>
                <a href="<?= base_url('about.php') ?>" class="pn-drawer-link"><i class="bi bi-info-circle me-2 text-primary"></i> About Us</a>
                <a href="<?= base_url('contact.php') ?>" class="pn-drawer-link"><i class="bi bi-envelope me-2 text-primary"></i> Contact Support</a>
                <a href="<?= base_url('privacy.php') ?>" class="pn-drawer-link"><i class="bi bi-shield-check me-2 text-primary"></i> Privacy Policy</a>
            </nav>

            <div class="mt-auto pt-4 border-top">
                <a href="<?= base_url('admin/login.php') ?>" class="btn btn-outline-dark btn-sm w-100">
                    <i class="bi bi-person-lock me-1"></i> Staff Admin
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main>