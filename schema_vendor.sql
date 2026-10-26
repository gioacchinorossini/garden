-- ========================================================
-- STREET FOOD & VENDOR MANAGEMENT SYSTEM (STREETSPOT)
-- DATABASE SCHEMA (MySQL 8 Compatible)
-- Standard Terminology: Property Owner, Vendor, Customer, Admin
-- ========================================================

CREATE DATABASE IF NOT EXISTS `streetfood_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `streetfood_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. Table: users
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'property_owner', 'vendor', 'customer') NOT NULL DEFAULT 'customer',
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar_url` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Table: properties (Food parks, commercial lots, outdoor markets)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `properties`;
CREATE TABLE `properties` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `owner_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL COMMENT 'e.g. Mercato Food Park, Downtown Night Stalls',
  `property_type` ENUM('food_park', 'commercial_lot', 'night_market', 'sidewalk_spot', 'private_lot') NOT NULL DEFAULT 'food_park',
  `permit_no` VARCHAR(100) DEFAULT NULL COMMENT 'Barangay or Mayor business permit',
  `permit_document_path` VARCHAR(255) DEFAULT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `address` VARCHAR(255) NOT NULL,
  `latitude` DECIMAL(10, 8) NOT NULL,
  `longitude` DECIMAL(11, 8) NOT NULL,
  `total_area` DECIMAL(10, 2) NOT NULL COMMENT 'in square meters',
  `amenities` TEXT DEFAULT NULL COMMENT 'e.g. electricity, water, seating, waste_disposal, parking',
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `rejection_reason` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_properties_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Table: property_images
-- --------------------------------------------------------
DROP TABLE IF EXISTS `property_images`;
CREATE TABLE `property_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `property_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_images_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Table: stalls (Individual rental booths/spots inside a property)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `stalls`;
CREATE TABLE `stalls` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `property_id` INT NOT NULL,
  `stall_number` VARCHAR(50) NOT NULL COMMENT 'e.g., Stall A-01, Cart Spot 3',
  `area` DECIMAL(10, 2) NOT NULL COMMENT 'in sqm',
  `rental_rate` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `rate_period` ENUM('daily', 'weekly', 'monthly') DEFAULT 'monthly',
  `status` ENUM('available', 'occupied', 'reserved', 'maintenance') DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_stalls_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Table: vendor_profiles
-- --------------------------------------------------------
DROP TABLE IF EXISTS `vendor_profiles`;
CREATE TABLE `vendor_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT NOT NULL UNIQUE,
  `brand_name` VARCHAR(120) NOT NULL COMMENT 'e.g. Maria Grill & Street Bites',
  `cuisine_category` VARCHAR(80) NOT NULL DEFAULT 'Street Food' COMMENT 'e.g. Skewers, Fried Snacks, Drinks',
  `bio` TEXT DEFAULT NULL,
  `sanitary_permit_no` VARCHAR(100) DEFAULT NULL,
  `sanitary_permit_path` VARCHAR(255) DEFAULT NULL,
  `is_permit_verified` TINYINT(1) DEFAULT 0,
  `logo_path` VARCHAR(255) DEFAULT NULL,
  `banner_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_profile_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Table: stall_applications (Vendor rental requests)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `stall_applications`;
CREATE TABLE `stall_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT NOT NULL,
  `property_id` INT NOT NULL,
  `stall_id` INT DEFAULT NULL,
  `food_concept` TEXT NOT NULL COMMENT 'Description of food offerings and cart setup',
  `requested_duration` VARCHAR(50) NOT NULL COMMENT 'e.g., 1 month, 3 months, 6 months',
  `start_date` DATE DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
  `response_notes` TEXT DEFAULT NULL,
  `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `responded_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_app_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_stall` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Table: menu_items (Street food offerings)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT NOT NULL,
  `item_name` VARCHAR(120) NOT NULL,
  `category` VARCHAR(60) NOT NULL DEFAULT 'Street Food',
  `price` DECIMAL(10, 2) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `is_available` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_menu_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Table: vendor_schedules (Operating hours)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `vendor_schedules`;
CREATE TABLE `vendor_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT NOT NULL,
  `property_id` INT NOT NULL,
  `stall_id` INT DEFAULT NULL,
  `title` VARCHAR(150) NOT NULL,
  `operating_day` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Daily') DEFAULT 'Daily',
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_sched_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sched_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sched_stall` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 9. Table: vendor_sales_logs
-- --------------------------------------------------------
DROP TABLE IF EXISTS `vendor_sales_logs`;
CREATE TABLE `vendor_sales_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT NOT NULL,
  `stall_id` INT DEFAULT NULL,
  `log_date` DATE NOT NULL,
  `item_name` VARCHAR(100) NOT NULL,
  `quantity_sold` INT NOT NULL,
  `total_revenue` DECIMAL(10, 2) NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_sales_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sales_stall` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 10. Table: reviews
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `vendor_id` INT NOT NULL,
  `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `comment` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reviews_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Initial Seed Data
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `status`) VALUES
(1, 'System Administrator', 'admin@streetbites.com', '$2y$10$tZ1.Ksz/u1N6r7FvhX5pfeP6yHly/3B.6t.M2hWq2U/H/t3s340a6', 'admin', '09123456789', 'active'),
(2, 'Juan Property Owner', 'owner@streetbites.com', '$2y$10$tZ1.Ksz/u1N6r7FvhX5pfeP6yHly/3B.6t.M2hWq2U/H/t3s340a6', 'property_owner', '09223456789', 'active'),
(3, 'Maria Street Vendor', 'vendor@streetbites.com', '$2y$10$tZ1.Ksz/u1N6r7FvhX5pfeP6yHly/3B.6t.M2hWq2U/H/t3s340a6', 'vendor', '09333456789', 'active'),
(4, 'Pedro Foodie Customer', 'customer@streetbites.com', '$2y$10$tZ1.Ksz/u1N6r7FvhX5pfeP6yHly/3B.6t.M2hWq2U/H/t3s340a6', 'customer', '09443456789', 'active')
ON DUPLICATE KEY UPDATE name=VALUES(name), role=VALUES(role);
