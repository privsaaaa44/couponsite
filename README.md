# PromoNest - Modern Mobile-Friendly Coupon & Deals Portal

> **Original Brand**: **PromoNest**  
> **Tagline**: *Smart Savings, Verified Daily*  
> **Tech Stack**: Core PHP (v8.x), MySQL (PDO Prepared Statements), HTML5/CSS3 (Mobile-First Design System), Vanilla JavaScript (No heavy frameworks or page builders).

---

## 🌟 Overview & Key Distinctions

**PromoNest** is a mobile-first, high-conversion coupon and promo code directory built in pure core PHP and MySQL. Designed from the ground up with an original identity (Slate & Indigo with Emerald conversion accents), PromoNest delivers a fast, frictionless experience inspired by modern fintech and e-commerce leaders (like SimplyCodes and Linear) rather than cluttered, outdated legacy coupon portals.

### 5 Real Popular Brands Loaded (with 5 Verified Offers Each):
1. **Nike** (`/store/nike`): 5 offers (20% Off Shoes, 15% Member Discount, Up to 40% Clearance Deal, Free Shipping Deal, $10 Off Training).
2. **Amazon** (`/store/amazon`): 5 offers (15% Smart Home Code, Up to 50% Gold Box Deal, $10 App Voucher, 30-Day Prime Trial, 20% Kindle Plan).
3. **Booking.com** (`/store/booking-com`): 5 offers (15% Worldwide Hotel Code, 30% Getaway Deal, 20% Genius Tier 2 Code, Free Cancellation Deal, $25 Flight Bundle Voucher).
4. **Gymshark** (`/store/gymshark`): 5 offers (15% Activewear Code, 10% Student Discount Code, Up to 60% Conditioning Outlet Deal, Free Shipping Deal, $10 Off New Arrivals).
5. **IKEA** (`/store/ikea`): 5 offers ($25 IKEA Family Member Code, 30% Living Room Sale Deal, 15% Office Desks Code, Free Kitchen Planning Deal, 20% Cookware Voucher).

---

## 📱 Mobile-First UX (Tested at 360px, 768px & Desktop)

- **Thumb-Friendly Touch Targets**: All buttons, coupon cards, and links have minimum 44px–48px touch targets for mobile accessibility.
- **Zero Horizontal Scrolling**: Strictly audited viewport boundaries with fluid grids and responsive media queries.
- **Responsive Mobile Navigation Drawer**: Off-canvas navigation menu with built-in instant search bar.
- **Lightweight & Blazing Fast**: Vanilla CSS and native browser APIs (zero jQuery, zero bloated UI libraries, SVGs for crisp high-DPI rendering).

---

## 🚀 Public Site Features

1. **Homepage (`index.php`)**:
   - Live search box with instant redirection.
   - Quick trending store chips.
   - Featured store showcase with deal count badges.
   - Trending coupons grid with interactive filter tabs (**All**, **Promo Codes**, **Sales & Deals**).
   - Category shortcuts with deal counters.
   - Trust and verification guarantees.
   - Newsletter signup banner.

2. **Store / Brand Page (`/store/{slug}` / `store.php?slug={slug}`)**:
   - Store header with crisp SVG logo, merchant verification badge, description, and direct "Visit Store ↗" button (`rel="nofollow sponsored"`).
   - Real-time coupon filter pills (All, Codes, Deals).
   - Rich coupon cards with discount callout, title, terms, expiry date countdown, usage counter (`Used 2,410 times`), and verified badge.
   - **Interactive "Show Code" Button**:
     - Automatically launches the merchant's affiliate link in a new tab.
     - Displays an on-page modal popup containing the code with a **1-tap "Copy Code"** button.
     - Instant visual confirmation ("Copied to Clipboard!").
     - User feedback thumbs (👍 / 👎) for real-time verification.
   - **"Get Deal" Button**: Direct referral for sitewide sales and automatic discounts.
   - **"How to Use a Code"** 4-step visual guide.
   - Brand-specific FAQ accordion.
   - Related store suggestions.

3. **Category Pages (`/category/{slug}` / `category.php?slug={slug}` & `categories.php`)**:
   - Filter offers by department: Fashion & Apparel, Electronics & Tech, Travel & Hotels, Fitness & Activewear, Home & Living.
   - Category header with icons and store pills.
   - Full category directory in `categories.php`.

4. **Search Results Page (`search.php`)**:
   - Keyword matching across coupon titles, store names, discounts, and categories.
   - Matching store banner.
   - Helpful empty-state screen with recommended stores if no deals are found.

5. **Static Pages**:
   - **About Us (`about.php`)**: Our mission, cart-level verification methodology, and transparency metrics.
   - **Contact Us (`contact.php`)**: Form with CSRF protection, input validation, and database storage in `contact_messages`.
   - **Privacy Policy (`privacy.php`)**: Full privacy notice and FTC-compliant affiliate disclosure explaining `rel="nofollow sponsored"`.

6. **SEO Foundations**:
   - Dynamic titles and meta descriptions tailored to each store and category.
   - Clean semantic `<h1>` to `<h3>` heading structure.
   - Dynamic XML sitemap (`/sitemap.xml` / `sitemap.php`).

---

## 🛠️ Admin Control Center (`/admin`)

- **Secure Session Authentication**:
  - Hashed passwords using `password_hash()` and `password_verify()`.
  - Session fixation protection with `session_regenerate_id(true)`.
  - Unauthorized access blocked on all `/admin/*` pages.
- **Admin Dashboard (`admin/index.php`)**:
  - Real-time metric cards: Total Brands, Total Coupons, Active Coupons, Expired Coupons.
  - Recent coupons table with quick-action links.
  - Recent contact inquiries preview.
- **Store & Brand Management (`admin/brands.php`)**:
  - Add, edit, delete stores.
  - Logo file upload supporting SVG, PNG, JPG, and WEBP.
  - Automatic URL slug generator.
  - Homepage featured store toggle.
  - Delete confirmation modal dialog.
- **Coupons & Deals Management (`admin/coupons.php`)**:
  - Add, edit, delete coupons.
  - Fields: Brand dropdown, Category dropdown, Title, Code (optional for deals), Discount text, Affiliate URL, Expiry date, Status (Active/Inactive), Verified toggle, Usage count, Description/terms.
  - Search and filter by brand and by status.
  - Delete confirmation modal dialog.
- **Category Management (`admin/categories.php`)**:
  - Add, edit, delete shopping categories with Bootstrap icon picker.
  - Foreign key safeguards preventing accidental deletion of categories containing active deals.
- **Inquiries Inbox (`admin/messages.php`)**:
  - Review, mark as read, or delete messages sent via the contact form.
- **Mobile-Responsive Admin Interface**:
  - Fully usable on smartphones with slide-out off-canvas navigation.

---

## 🔒 Security Best Practices

1. **Prepared Statements**: 100% of SQL queries utilize PDO prepared statements with parameter binding to prevent SQL injection.
2. **XSS Prevention**: All dynamic output passed through `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` via the `e()` helper.
3. **CSRF Protection**: Cryptographically secure tokens (`bin2hex(random_bytes(32))`) validated on every state-changing POST request.
4. **Rel Sponsored Links**: All external retailer links enforce `rel="nofollow sponsored" target="_blank"`.

---

## 💻 Installation & Setup Guide

### 1. Requirements
- XAMPP / WAMP / LAMP with PHP 8.0+ and MySQL 5.7+ / MariaDB.
- Apache `mod_rewrite` enabled.

### 2. Database Setup
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin/`) or MySQL CLI.
2. Create a database named `coupon`:
   ```sql
   CREATE DATABASE coupon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import the provided `database.sql` file:
   - In phpMyAdmin: Select `coupon` database -> Click **Import** -> Choose `database.sql` -> Click **Go**.
   - Or via MySQL command line:
     ```bash
     mysql -u root coupon < database.sql
     ```

### 3. Application Configuration
1. Place the project folder into your server root:
   - For XAMPP: `C:\xampp\htdocs\couponsitesproject\`
2. Check `connection.php` settings (default XAMPP credentials already pre-configured):
   ```php
   $host = 'localhost';
   $dbname = 'coupon';
   $username = 'root';
   $password = '';
   ```

### 4. Admin Credentials
- **Admin Login URL**: `http://localhost/couponsitesproject/admin/login.php`
- **Username**: `admin`
- **Password**: `admin123`

### 5. Accessing the Site
- **Homepage**: `http://localhost/couponsitesproject/`
- **Stores Directory**: `http://localhost/couponsitesproject/shops.php`
- **Nike Store Page**: `http://localhost/couponsitesproject/store/nike` (or `store.php?slug=nike`)
- **Categories Directory**: `http://localhost/couponsitesproject/categories.php`
- **Fashion Category**: `http://localhost/couponsitesproject/category/fashion-apparel`
- **Search**: `http://localhost/couponsitesproject/search.php?query=nike`
- **Contact Form**: `http://localhost/couponsitesproject/contact.php`
- **Dynamic XML Sitemap**: `http://localhost/couponsitesproject/sitemap.xml`
