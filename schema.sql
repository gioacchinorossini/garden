-- ==========================================
-- IDLE LAND FOR COMMUNITY GARDENING SYSTEM
-- DATABASE SCHEMA (MySQL 8 compatible)
-- ==========================================

CREATE DATABASE IF NOT EXISTS `idle_land_garden` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `idle_land_garden`;

-- Disable foreign key checks during creation to avoid order issues
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'landowner', 'gardener') NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: lands
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lands`;
CREATE TABLE `lands` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `landowner_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `address` VARCHAR(255) NOT NULL,
  `latitude` DECIMAL(10, 8) NOT NULL,
  `longitude` DECIMAL(11, 8) NOT NULL,
  `total_area` DECIMAL(10, 2) NOT NULL COMMENT 'in square meters',
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `rejection_reason` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_lands_landowner` FOREIGN KEY (`landowner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: land_images
-- --------------------------------------------------------
DROP TABLE IF EXISTS `land_images`;
CREATE TABLE `land_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `land_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_images_land` FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: plots
-- --------------------------------------------------------
DROP TABLE IF EXISTS `plots`;
CREATE TABLE `plots` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `land_id` INT NOT NULL,
  `plot_number` VARCHAR(50) NOT NULL,
  `area` DECIMAL(10, 2) NOT NULL COMMENT 'in square meters',
  `status` ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_plots_land` FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: requests
-- --------------------------------------------------------
DROP TABLE IF EXISTS `requests`;
CREATE TABLE `requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gardener_id` INT NOT NULL,
  `land_id` INT NOT NULL,
  `plot_id` INT DEFAULT NULL,
  `purpose` TEXT NOT NULL,
  `requested_duration` VARCHAR(50) NOT NULL COMMENT 'e.g., 6 months, 1 year',
  `status` ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
  `response_notes` TEXT DEFAULT NULL,
  `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `responded_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_requests_gardener` FOREIGN KEY (`gardener_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_requests_land` FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_requests_plot` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: schedules
-- --------------------------------------------------------
DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `land_id` INT NOT NULL,
  `gardener_id` INT DEFAULT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME NOT NULL,
  `task_type` ENUM('planting', 'watering', 'weeding', 'harvesting', 'meeting', 'other') DEFAULT 'other',
  `status` ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_schedules_land` FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_schedules_gardener` FOREIGN KEY (`gardener_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: harvests
-- --------------------------------------------------------
DROP TABLE IF EXISTS `harvests`;
CREATE TABLE `harvests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gardener_id` INT NOT NULL,
  `land_id` INT NOT NULL,
  `plot_id` INT DEFAULT NULL,
  `crop_name` VARCHAR(100) NOT NULL,
  `quantity` DECIMAL(10, 2) NOT NULL,
  `unit` VARCHAR(20) DEFAULT 'kg',
  `harvest_date` DATE NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_harvests_gardener` FOREIGN KEY (`gardener_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_harvests_land` FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_harvests_plot` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Seed Data for Initial Testing
-- Passwords are set to bcrypt hashes of 'password123'
-- --------------------------------------------------------

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `status`) VALUES
(1, 'System Administrator', 'admin@garden.com', '$2y$10$tZ1.Ksz/u1N6r7FvhX5pfeP6yHly/3B.6t.M2hWq2U/H/t3s340a6', 'admin', '09123456789', 'active'),
(2, 'John Landowner', 'landowner@garden.com', '$2y$10$tZ1.Ksz/u1N6r7FvhX5pfeP6yHly/3B.6t.M2hWq2U/H/t3s340a6', 'landowner', '09223456789', 'active'),
(3, 'Mary Gardener', 'gardener@garden.com', '$2y$10$tZ1.Ksz/u1N6r7FvhX5pfeP6yHly/3B.6t.M2hWq2U/H/t3s340a6', 'gardener', '09333456789', 'active');
