-- database_setup.sql
-- Database creation and seeding for PromoNest coupon portal

CREATE DATABASE IF NOT EXISTS `coupon` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `coupon`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'bi-tag',
  `description` VARCHAR(255) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ensure columns exist in categories if table already exists
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `icon` VARCHAR(50) DEFAULT 'bi-tag';
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `description` VARCHAR(255) DEFAULT '';

-- 3. Brands Table
CREATE TABLE IF NOT EXISTS `brands` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(180) NOT NULL UNIQUE,
  `logo` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `website_url` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `brands` ADD COLUMN IF NOT EXISTS `website_url` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `brands` ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) DEFAULT 0;

-- 4. Coupons Table
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `code` VARCHAR(100) DEFAULT NULL,
  `discount_text` VARCHAR(100) NOT NULL,
  `affiliate_url` TEXT NOT NULL,
  `expiry_date` DATE NOT NULL,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `verified` TINYINT(1) DEFAULT 1,
  `used_count` INT DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`brand_id`),
  INDEX (`category_id`),
  INDEX (`expiry_date`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `coupons` ADD COLUMN IF NOT EXISTS `description` TEXT DEFAULT NULL;

-- 5. Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread','read') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Admin (Password: admin123)
TRUNCATE TABLE `admins`;
INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$2iXTWllz8xC3O4mMVc5Ry.CL7mw.XAtg87J1/dmeYs.hr68348TAi');

-- Clear and Seed Categories
TRUNCATE TABLE `categories`;
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
(1, 'Fashion & Apparel', 'fashion-apparel', 'bi-bag-check', 'Verified clothing, sneakers, accessories & streetwear promo codes'),
(2, 'Electronics & Tech', 'electronics-tech', 'bi-laptop', 'Deals on smart devices, laptops, gadgets & computer hardware'),
(3, 'Travel & Hotels', 'travel-hotels', 'bi-airplane', 'Discount hotel rooms, flights, vacation stays & holiday packages'),
(4, 'Fitness & Activewear', 'fitness-activewear', 'bi-activity', 'Gym apparel, workout essentials & athletic conditioning equipment'),
(5, 'Home & Living', 'home-living', 'bi-house-door', 'Modern furniture, kitchen utensils, home decor & living essentials');

-- Clear and Seed 5 Real Brands
TRUNCATE TABLE `brands`;
INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `description`, `website_url`, `status`, `is_featured`) VALUES
(1, 'Nike', 'nike', 'nike.svg', 'Nike is the world leader in athletic footwear, apparel, and sports gear engineered to elevate athletic performance and everyday style.', 'https://www.nike.com', 'active', 1),
(2, 'Amazon', 'amazon', 'amazon.svg', 'Amazon is the global online retailer featuring millions of products across electronics, daily essentials, fashion, and cloud gadgets with fast delivery.', 'https://www.amazon.com', 'active', 1),
(3, 'Booking.com', 'booking-com', 'booking.svg', 'Booking.com connects millions of global travelers to hotels, luxury resorts, cozy holiday homes, and cheap flight bundles with flexible booking.', 'https://www.booking.com', 'active', 1),
(4, 'Gymshark', 'gymshark', 'gymshark.svg', 'Gymshark is a premier fitness brand designing high-performance conditioning apparel, seamless workout sets, and gym accessories for fitness enthusiasts.', 'https://www.gymshark.com', 'active', 1),
(5, 'IKEA', 'ikea', 'ikea.svg', 'IKEA is the world-renowned Swedish furniture brand offering smart, functional, and affordable home furnishing solutions and contemporary decor.', 'https://www.ikea.com', 'active', 1);

-- Clear and Seed 25 Coupons (5 for each brand, mix of codes & deals)
TRUNCATE TABLE `coupons`;

-- NIKE (brand_id: 1, category_id: 1)
INSERT INTO `coupons` (`brand_id`, `category_id`, `title`, `code`, `discount_text`, `affiliate_url`, `expiry_date`, `status`, `verified`, `used_count`, `description`) VALUES
(1, 1, '20% Off Select Running Shoes & Training Gear', 'JUSTDOIT20', '20% OFF', 'https://www.nike.com', '2026-11-30', 'active', 1, 2410, 'Valid on select men and women running sneakers, training shorts, and activewear tops.'),
(1, 1, '15% Off Entire Order for Nike App & Online Members', 'MEMBERSHIP15', '15% OFF', 'https://www.nike.com', '2026-12-31', 'active', 1, 3820, 'Sign in with your free Nike Member profile to unlock an instant 15% discount at checkout.'),
(1, 1, 'Up to 40% Off End-of-Season Clearance & Outlet Sale', NULL, 'Up to 40% OFF', 'https://www.nike.com/sale', '2026-12-15', 'active', 1, 5120, 'Save up to 40% on over 1,500 markdowns including footwear, fleece hoodies, and jackets. No code required.'),
(1, 1, 'Free Standard Shipping on All Orders Over $50', NULL, 'FREE SHIPPING', 'https://www.nike.com', '2026-11-20', 'active', 1, 1290, 'Automatic free shipping applied in cart on all eligible merchandise totaling $50 or more.'),
(1, 1, '$10 Off $75+ on Fall Athletic Training Essentials', 'FALLTRAIN10', '$10 OFF', 'https://www.nike.com', '2026-11-15', 'active', 1, 960, 'Apply at checkout on training equipment and performance sportswear exceeding $75 subtotal.');

-- AMAZON (brand_id: 2, category_id: 2)
INSERT INTO `coupons` (`brand_id`, `category_id`, `title`, `code`, `discount_text`, `affiliate_url`, `expiry_date`, `status`, `verified`, `used_count`, `description`) VALUES
(2, 2, '15% Off Select Amazon Echo & Smart Home Devices', 'AMZSAVE15', '15% OFF', 'https://www.amazon.com', '2026-12-25', 'active', 1, 4110, 'Save 15% on Echo Dot, Echo Show, smart plugs, and smart home lighting kits.'),
(2, 2, 'Up to 50% Off Today\'s Gold Box & Lightning Deals', NULL, 'Up to 50% OFF', 'https://www.amazon.com/deals', '2026-12-31', 'active', 1, 7830, 'Deep daily savings on top tech brands, laptops, wireless noise-cancelling headphones, and gadgets.'),
(2, 2, '$10 Off First Purchase on the Amazon Shopping App', 'PRIME10NOW', '$10 OFF', 'https://www.amazon.com', '2026-11-28', 'active', 1, 2940, 'Valid on first in-app order of $25 or more on items shipped and sold directly by Amazon.'),
(2, 2, '30-Day Amazon Prime Free Trial with Fast 2-Day Delivery', NULL, 'FREE TRIAL', 'https://www.amazon.com/prime', '2027-01-01', 'active', 1, 6430, 'Unlock unlimited fast shipping, Prime Video entertainment, and exclusive prime-only discounts.'),
(2, 2, '20% Off Kindle Unlimited 6-Month Reading Plan', 'AMZREAD20', '20% OFF', 'https://www.amazon.com/kindle', '2026-11-30', 'active', 1, 1120, 'Instant 20% discount on 6-month subscription to over 4 million ebooks and audiobooks.');

-- BOOKING.COM (brand_id: 3, category_id: 3)
INSERT INTO `coupons` (`brand_id`, `category_id`, `title`, `code`, `discount_text`, `affiliate_url`, `expiry_date`, `status`, `verified`, `used_count`, `description`) VALUES
(3, 3, '15% Off Worldwide Hotel Stays with Getaway Deals', 'GETAWAY15', '15% OFF', 'https://www.booking.com', '2026-12-31', 'active', 1, 3250, 'Save 15% or more at thousands of participating hotels and beach resorts around the world.'),
(3, 3, 'Up to 30% Off Seasonal City Breaks & Weekend Getaways', NULL, 'Up to 30% OFF', 'https://www.booking.com/deals', '2026-12-10', 'active', 1, 4890, 'Special seasonal rate reductions automatically applied on verified accommodations.'),
(3, 3, '20% Off Select Hotels with Genius Loyalty Tier 2', 'GENIUS20', '20% OFF', 'https://www.booking.com', '2026-11-25', 'active', 1, 2130, 'Enjoy 20% off plus free breakfast and priority room upgrades at select partner properties.'),
(3, 3, 'Free Cancellation on Select Vacation Homes & Hotels', NULL, 'FREE CANCEL', 'https://www.booking.com', '2027-01-15', 'active', 1, 3400, 'Stay flexible with zero cancellation fees up to 24-48 hours before check-in on designated listings.'),
(3, 3, '$25 Off Flight + Hotel Vacation Package Bookings', 'FLYSTAY10', '$25 OFF', 'https://www.booking.com', '2026-12-05', 'active', 1, 890, 'Combine round-trip airfare and hotel reservation to receive an extra $25 instant package rebate.');

-- GYMSHARK (brand_id: 4, category_id: 4)
INSERT INTO `coupons` (`brand_id`, `category_id`, `title`, `code`, `discount_text`, `affiliate_url`, `expiry_date`, `status`, `verified`, `used_count`, `description`) VALUES
(4, 4, '15% Off All Leggings, Seamless Tops & Active Sets', 'LIFT15', '15% OFF', 'https://www.gymshark.com', '2026-12-20', 'active', 1, 2780, 'Valid on Gymshark Adapt, Vital Seamless, and Apex gym collections with code at checkout.'),
(4, 4, '10% Verified Student & Graduate Discount on Every Order', 'STUDENT10', '10% OFF', 'https://www.gymshark.com', '2026-12-31', 'active', 1, 1940, 'Verify your student or youth status to access 10% off your full cart. Works sitewide.'),
(4, 4, 'Up to 60% Off Gymshark Conditioning Outlet & End of Line', NULL, 'Up to 60% OFF', 'https://www.gymshark.com/outlet', '2026-11-30', 'active', 1, 6210, 'Massive price cuts on high-performance sports bras, lifting shorts, hoodies, and training bags.'),
(4, 4, 'Free Tracked Standard Delivery on Orders Over $75', NULL, 'FREE SHIPPING', 'https://www.gymshark.com', '2026-12-15', 'active', 1, 1670, 'Fast tracked doorstep shipping is complimentary on all orders of $75 or above.'),
(4, 4, '$10 Off $80+ on New Seasonal Lifting Essentials', 'POWERFIT10', '$10 OFF', 'https://www.gymshark.com', '2026-11-10', 'active', 1, 1150, 'Spend $80 on new arrivals and lifting accessories to unlock an instant $10 markdown.');

-- IKEA (brand_id: 5, category_id: 5)
INSERT INTO `coupons` (`brand_id`, `category_id`, `title`, `code`, `discount_text`, `affiliate_url`, `expiry_date`, `status`, `verified`, `used_count`, `description`) VALUES
(5, 5, '$25 Off $150+ Home Furnishing for IKEA Family Members', 'IKEAFAM25', '$25 OFF', 'https://www.ikea.com', '2026-12-28', 'active', 1, 3670, 'Exclusive discount code for registered IKEA Family members on living, bedroom & storage items.'),
(5, 5, 'Up to 30% Off Living Room Furniture, Sofas & Storage', NULL, 'Up to 30% OFF', 'https://www.ikea.com/deals', '2026-12-12', 'active', 1, 4120, 'Seasonal price drops on BILLY bookcases, KALLAX cubes, sectional couches, and coffee tables.'),
(5, 5, '15% Off Ergonomic Standing Desks & Workspace Seating', 'DESKSPACE15', '15% OFF', 'https://www.ikea.com', '2026-11-30', 'active', 1, 1830, 'Save 15% on home office desks, ergonomic chairs, cable organizers, and monitor risers.'),
(5, 5, 'Free 3D Kitchen Planning Session & 10% Cabinetry Rebate', NULL, '10% REBATE', 'https://www.ikea.com/kitchen', '2026-12-31', 'active', 1, 940, 'Complimentary custom kitchen design consultation plus 10% credit back on custom kitchen units.'),
(5, 5, '20% Off Select Cookware, Glassware & Food Containers', 'KITCHEN20', '20% OFF', 'https://www.ikea.com', '2026-11-18', 'active', 1, 2420, 'Take 20% off frying pans, pots, glass food storage containers, and kitchen utensils.');

SET FOREIGN_KEY_CHECKS = 1;
