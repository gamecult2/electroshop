-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 25, 2025 at 10:49 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qwenshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_user_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_user_id` (`admin_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backups`
--

DROP TABLE IF EXISTS `backups`;
CREATE TABLE IF NOT EXISTS `backups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `type` enum('database','files','full') NOT NULL,
  `size_mb` decimal(8,2) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `desktop_image` varchar(500) NOT NULL,
  `mobile_image` varchar(500) DEFAULT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `link_target` enum('_self','_blank') DEFAULT '_self',
  `position` enum('homepage_hero','category_top','sidebar','footer','popup') DEFAULT 'homepage_hero',
  `priority` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `desktop_image`, `mobile_image`, `link_url`, `link_target`, `position`, `priority`, `is_active`, `starts_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'Summer Sale', '/img/banners/summer-sale-desktop.jpg', '/img/banners/summer-sale-mobile.jpg', '/products', '_self', 'homepage_hero', 1, 1, NULL, NULL, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(2, 'New Arrivals', '/img/banners/new-arrivals-desktop.jpg', '/img/banners/new-arrivals-mobile.jpg', '/products?new=true', '_self', 'category_top', 2, 1, NULL, NULL, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(3, 'Summer Sale', '/img/banners/summer-sale-desktop.jpg', '/img/banners/summer-sale-mobile.jpg', '/products', '_self', 'homepage_hero', 1, 1, NULL, NULL, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(4, 'New Arrivals', '/img/banners/new-arrivals-desktop.jpg', '/img/banners/new-arrivals-mobile.jpg', '/products?new=true', '_self', 'category_top', 2, 1, NULL, NULL, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(5, 'Summer Sale', '/img/banners/summer-sale-desktop.jpg', '/img/banners/summer-sale-mobile.jpg', '/products', '_self', 'homepage_hero', 1, 1, NULL, NULL, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(6, 'New Arrivals', '/img/banners/new-arrivals-desktop.jpg', '/img/banners/new-arrivals-mobile.jpg', '/products?new=true', '_self', 'category_top', 2, 1, NULL, NULL, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(7, 'Summer Sale', '/img/banners/summer-sale-desktop.jpg', '/img/banners/summer-sale-mobile.jpg', '/products', '_self', 'homepage_hero', 1, 1, NULL, NULL, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(8, 'New Arrivals', '/img/banners/new-arrivals-desktop.jpg', '/img/banners/new-arrivals-mobile.jpg', '/products?new=true', '_self', 'category_top', 2, 1, NULL, NULL, '2025-12-12 12:57:30', '2025-12-12 12:57:30');

-- --------------------------------------------------------

--
-- Table structure for table `banner_clicks`
--

DROP TABLE IF EXISTS `banner_clicks`;
CREATE TABLE IF NOT EXISTS `banner_clicks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `banner_id` int NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `clicked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `banner_id` (`banner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blocked_ips`
--

DROP TABLE IF EXISTS `blocked_ips`;
CREATE TABLE IF NOT EXISTS `blocked_ips` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `blocked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `logo_url` varchar(500) DEFAULT NULL,
  `website_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `description`, `logo_url`, `website_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Samsung', 'Leading technology brand', NULL, NULL, 1, '2025-12-12 10:54:01', '2025-12-12 10:54:01'),
(2, 'HP', 'Global computer manufacturer', NULL, NULL, 1, '2025-12-12 10:54:01', '2025-12-12 10:54:01'),
(3, 'Lenovo', 'Computing solutions provider', NULL, NULL, 1, '2025-12-12 10:54:01', '2025-12-12 10:54:01'),
(4, 'Asus', '', NULL, NULL, 1, '2025-12-12 10:54:01', '2025-12-15 11:12:31'),
(5, 'MSI', 'Computer hardware manufacturer', NULL, NULL, 1, '2025-12-12 10:54:01', '2025-12-12 10:54:01'),
(6, 'Xiaomi', 'Chinese electronics company', NULL, NULL, 1, '2025-12-12 10:54:01', '2025-12-12 10:54:01');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) NOT NULL,
  `name_fr` varchar(255) NOT NULL,
  `description_en` text,
  `description_fr` text,
  `parent_id` int DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name_en`, `name_fr`, `description_en`, `description_fr`, `parent_id`, `image_url`, `icon_class`, `slug`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', 'Électronique', '', '', NULL, NULL, '', 'electronics', 1, 1, '2025-12-12 10:54:01', '2025-12-22 05:17:21'),
(2, 'PC Components', 'Composants PC', '', '', NULL, NULL, '', 'pc-components', 1, 2, '2025-12-12 10:54:01', '2025-12-22 05:17:21'),
(3, 'Gaming', 'Gaming', '', '', NULL, NULL, '', 'gaming', 1, 3, '2025-12-12 10:54:01', '2025-12-22 05:17:21'),
(4, 'Home Appliances', 'Électroménager', '', '', NULL, NULL, '', 'home-appliances', 1, 4, '2025-12-12 10:54:01', '2025-12-22 05:17:21'),
(5, 'Accessories', 'Accessoires', '', '', NULL, NULL, '', 'accessories', 1, 5, '2025-12-12 10:54:01', '2025-12-22 05:17:21'),
(6, 'Smartphones', 'Smartphones', '', '', NULL, NULL, '', 'smartphones', 1, 6, '2025-12-12 10:54:01', '2025-12-22 05:17:21'),
(7, 'Networking', 'Réseaux', '', '', NULL, NULL, '', 'networking', 1, 7, '2025-12-12 10:54:01', '2025-12-22 05:17:21'),
(8, 'Audio', 'Audio', '', '', NULL, NULL, '', 'audio', 1, 8, '2025-12-12 10:54:01', '2025-12-22 05:17:21'),
(9, 'Gadgets', 'Gadgets', '', '', NULL, NULL, '', 'gadgets', 1, 9, '2025-12-12 10:54:01', '2025-12-22 05:17:21'),
(10, 'Televisions', 'Téléviseurs', '', '', 1, NULL, '', 'televisions', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(11, 'Cameras and Camcorders', 'Appareils photo and Caméscopes', 'Cameras', '', 1, NULL, '', 'cameras', 1, 0, '2025-12-13 15:59:55', '2025-12-22 05:13:22'),
(12, 'Drones', 'Drones', '', '', 1, NULL, '', 'drones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(13, 'Wearables', 'Objets connectés portables', '', '', 1, NULL, '', 'wearables', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(14, 'E-Readers & Tablets', 'Liseuses & Tablettes', '', '', 1, NULL, '', 'ereaders-tablets', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(15, 'Processors (CPUs)', 'Processeurs (CPU)', '', '', 2, NULL, '', 'processors', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(16, 'Graphics Cards (GPUs)', 'Cartes graphiques (GPU)', '', '', 2, NULL, '', 'graphics-cards', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(17, 'Motherboards', 'Cartes mères', '', '', 2, NULL, '', 'motherboards', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(18, 'RAM & Memory', 'Mémoire vive (RAM)', '', '', 2, NULL, '', 'ram', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(19, 'Storage (SSD/HDD)', 'Stockage (SSD/HDD)', '', '', 2, NULL, '', 'storage', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(20, 'Power Supplies', 'Alimentations', '', '', 2, NULL, '', 'power-supplies', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(21, 'PC Cases', 'Boîtiers PC', '', '', 2, NULL, '', 'pc-cases', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(22, 'Cooling Systems', 'Systèmes de refroidissement', '', '', 2, NULL, '', 'cooling', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(23, 'Consoles', 'Consoles', '', '', 3, NULL, '', 'consoles', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(24, 'Video Games', 'Jeux vidéo', '', '', 3, NULL, '', 'video-games', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(25, 'Gaming Peripherals', 'Périphériques gaming', '', '', 3, NULL, '', 'gaming-peripherals', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(26, 'VR & AR Gear', 'Équipement VR & AR', '', '', 3, NULL, '', 'vr-ar', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(27, 'Gaming Chairs & Desks', 'Chaises & bureaux gaming', '', '', 3, NULL, '', 'gaming-furniture', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(28, 'Kitchen Appliances', 'Appareils de cuisine', '', '', 4, NULL, '', 'kitchen-appliances', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(29, 'Laundry Appliances', 'Appareils de buanderie', '', '', 4, NULL, '', 'laundry', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(30, 'Climate Control', 'Climatisation & chauffage', '', '', 4, NULL, '', 'climate-control', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(31, 'Vacuum Cleaners', 'Aspirateurs', '', '', 4, NULL, '', 'vacuum-cleaners', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(32, 'Small Appliances', 'Petits appareils', '', '', 4, NULL, '', 'small-appliances', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(33, 'Cables & Adapters', 'Câbles & adaptateurs', '', '', 5, NULL, '', 'cables-adapters', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(34, 'Chargers & Power Banks', 'Chargeurs & batteries externes', '', '', 5, NULL, '', 'chargers-powerbanks', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(35, 'Cases & Covers', 'Housses & coques', '', '', 5, NULL, '', 'cases-covers', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(36, 'Screen Protectors', 'Protections d\'écran', '', '', 5, NULL, '', 'screen-protectors', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(37, 'Styluses & Pens', 'Stylets & stylos', '', '', 5, NULL, '', 'styluses', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(38, 'Android Phones', 'Téléphones Android', '', '', 6, NULL, '', 'android-phones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(39, 'iOS Devices', 'Appareils iOS', '', '', 6, NULL, '', 'ios-devices', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(40, 'Feature Phones', 'Téléphones basiques', '', '', 6, NULL, '', 'feature-phones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(41, 'Refurbished Phones', 'Téléphones reconditionnés', '', '', 6, NULL, '', 'refurbished-phones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(42, 'Phone Accessories', 'Accessoires smartphones', '', '', 6, NULL, '', 'phone-accessories', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(43, 'Routers & Modems', 'Routeurs & Modems', '', '', 7, NULL, '', 'routers-modems', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(44, 'Switches', 'Commutateurs (Switches)', '', '', 7, NULL, '', 'switches', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(45, 'Wi-Fi Extenders', 'Amplificateurs Wi-Fi', '', '', 7, NULL, '', 'wifi-extenders', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(46, 'Network Cables', 'Câbles réseau', '', '', 7, NULL, '', 'network-cables', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(47, 'NAS & Servers', 'NAS & Serveurs', '', '', 7, NULL, '', 'nas-servers', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(48, 'Headphones', 'Casques audio', '', '', 8, NULL, '', 'headphones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(49, 'Earbuds', 'Écouteurs intra-auriculaires', '', '', 8, NULL, '', 'earbuds', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(50, 'Speakers', 'Enceintes', '', '', 8, NULL, '', 'speakers', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(51, 'Soundbars', 'Barres de son', '', '', 8, NULL, '', 'soundbars', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(52, 'DJ & Studio Equipment', 'Équipement DJ & studio', '', '', 8, NULL, '', 'dj-studio', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(53, 'Smart Home Devices', 'Objets domotiques', '', '', 9, NULL, '', 'smart-home', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(54, 'Fitness Trackers', 'Traceurs d\'activité', '', '', 9, NULL, '', 'fitness-trackers', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(55, 'Novelty Tech', 'Gadgets insolites', '', '', 9, NULL, '', 'novelty-tech', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(56, 'Portable Projectors', 'Projecteurs portables', '', '', 9, NULL, '', 'portable-projectors', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(57, 'Digital Accessories', 'Accessoires numériques', '', '', 9, NULL, '', 'digital-gadgets', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55');

-- --------------------------------------------------------

--
-- Table structure for table `categories_backup_20251222_051721`
--

DROP TABLE IF EXISTS `categories_backup_20251222_051721`;
CREATE TABLE IF NOT EXISTS `categories_backup_20251222_051721` (
  `id` int NOT NULL DEFAULT '0',
  `name_en` varchar(255) NOT NULL,
  `name_fr` varchar(255) NOT NULL,
  `description_en` text,
  `description_fr` text,
  `parent_id` int DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories_backup_20251222_051721`
--

INSERT INTO `categories_backup_20251222_051721` (`id`, `name_en`, `name_fr`, `description_en`, `description_fr`, `parent_id`, `image_url`, `icon_class`, `slug`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', 'Électronique', '', '', NULL, NULL, '', 'electronics', 1, 1, '2025-12-12 10:54:01', '2025-12-15 11:06:57'),
(2, 'PC Components', 'Composants PC', '', '', NULL, NULL, '', 'pc-components', 1, 2, '2025-12-12 10:54:01', '2025-12-15 11:06:57'),
(3, 'Gaming', 'Gaming', '', '', NULL, NULL, '', 'gaming', 1, 3, '2025-12-12 10:54:01', '2025-12-15 11:06:57'),
(4, 'Home Appliances', 'Électroménager', '', '', NULL, NULL, '', 'home-appliances', 1, 4, '2025-12-12 10:54:01', '2025-12-15 11:06:57'),
(5, 'Accessories', 'Accessoires', '', '', NULL, NULL, '', 'accessories', 1, 5, '2025-12-12 10:54:01', '2025-12-15 11:06:57'),
(6, 'Smartphones', 'Smartphones', '', '', NULL, NULL, '', 'smartphones', 1, 6, '2025-12-12 10:54:01', '2025-12-15 11:06:57'),
(7, 'Networking', 'Réseaux', '', '', NULL, NULL, '', 'networking', 1, 7, '2025-12-12 10:54:01', '2025-12-15 11:06:57'),
(8, 'Audio', 'Audio', '', '', NULL, NULL, '', 'audio', 1, 8, '2025-12-12 10:54:01', '2025-12-15 11:06:57'),
(9, 'Gadgets', 'Gadgets', '', '', NULL, NULL, '', 'gadgets', 1, 9, '2025-12-12 10:54:01', '2025-12-15 11:06:57'),
(10, 'Televisions', 'Téléviseurs', '', '', 1, NULL, '', 'televisions', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(11, 'Cameras and Camcorders', 'Appareils photo and Caméscopes', 'Cameras', '', 1, NULL, '', 'cameras', 1, 0, '2025-12-13 15:59:55', '2025-12-22 05:13:22'),
(12, 'Drones', 'Drones', '', '', 1, NULL, '', 'drones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(13, 'Wearables', 'Objets connectés portables', '', '', 1, NULL, '', 'wearables', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(14, 'E-Readers & Tablets', 'Liseuses & Tablettes', '', '', 1, NULL, '', 'ereaders-tablets', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(15, 'Processors (CPUs)', 'Processeurs (CPU)', '', '', 2, NULL, '', 'processors', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(16, 'Graphics Cards (GPUs)', 'Cartes graphiques (GPU)', '', '', 2, NULL, '', 'graphics-cards', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(17, 'Motherboards', 'Cartes mères', '', '', 2, NULL, '', 'motherboards', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(18, 'RAM & Memory', 'Mémoire vive (RAM)', '', '', 2, NULL, '', 'ram', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(19, 'Storage (SSD/HDD)', 'Stockage (SSD/HDD)', '', '', 2, NULL, '', 'storage', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(20, 'Power Supplies', 'Alimentations', '', '', 2, NULL, '', 'power-supplies', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(21, 'PC Cases', 'Boîtiers PC', '', '', 2, NULL, '', 'pc-cases', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(22, 'Cooling Systems', 'Systèmes de refroidissement', '', '', 2, NULL, '', 'cooling', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(23, 'Consoles', 'Consoles', '', '', 3, NULL, '', 'consoles', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(24, 'Video Games', 'Jeux vidéo', '', '', 3, NULL, '', 'video-games', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(25, 'Gaming Peripherals', 'Périphériques gaming', '', '', 3, NULL, '', 'gaming-peripherals', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(26, 'VR & AR Gear', 'Équipement VR & AR', '', '', 3, NULL, '', 'vr-ar', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(27, 'Gaming Chairs & Desks', 'Chaises & bureaux gaming', '', '', 3, NULL, '', 'gaming-furniture', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(28, 'Kitchen Appliances', 'Appareils de cuisine', '', '', 4, NULL, '', 'kitchen-appliances', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(29, 'Laundry Appliances', 'Appareils de buanderie', '', '', 4, NULL, '', 'laundry', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(30, 'Climate Control', 'Climatisation & chauffage', '', '', 4, NULL, '', 'climate-control', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(31, 'Vacuum Cleaners', 'Aspirateurs', '', '', 4, NULL, '', 'vacuum-cleaners', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(32, 'Small Appliances', 'Petits appareils', '', '', 4, NULL, '', 'small-appliances', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(33, 'Cables & Adapters', 'Câbles & adaptateurs', '', '', 5, NULL, '', 'cables-adapters', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(34, 'Chargers & Power Banks', 'Chargeurs & batteries externes', '', '', 5, NULL, '', 'chargers-powerbanks', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(35, 'Cases & Covers', 'Housses & coques', '', '', 5, NULL, '', 'cases-covers', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(36, 'Screen Protectors', 'Protections d\'écran', '', '', 5, NULL, '', 'screen-protectors', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(37, 'Styluses & Pens', 'Stylets & stylos', '', '', 5, NULL, '', 'styluses', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(38, 'Android Phones', 'Téléphones Android', '', '', 6, NULL, '', 'android-phones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(39, 'iOS Devices', 'Appareils iOS', '', '', 6, NULL, '', 'ios-devices', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(40, 'Feature Phones', 'Téléphones basiques', '', '', 6, NULL, '', 'feature-phones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(41, 'Refurbished Phones', 'Téléphones reconditionnés', '', '', 6, NULL, '', 'refurbished-phones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(42, 'Phone Accessories', 'Accessoires smartphones', '', '', 6, NULL, '', 'phone-accessories', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(43, 'Routers & Modems', 'Routeurs & Modems', '', '', 7, NULL, '', 'routers-modems', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(44, 'Switches', 'Commutateurs (Switches)', '', '', 7, NULL, '', 'switches', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(45, 'Wi-Fi Extenders', 'Amplificateurs Wi-Fi', '', '', 7, NULL, '', 'wifi-extenders', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(46, 'Network Cables', 'Câbles réseau', '', '', 7, NULL, '', 'network-cables', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(47, 'NAS & Servers', 'NAS & Serveurs', '', '', 7, NULL, '', 'nas-servers', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(48, 'Headphones', 'Casques audio', '', '', 8, NULL, '', 'headphones', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(49, 'Earbuds', 'Écouteurs intra-auriculaires', '', '', 8, NULL, '', 'earbuds', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(50, 'Speakers', 'Enceintes', '', '', 8, NULL, '', 'speakers', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(51, 'Soundbars', 'Barres de son', '', '', 8, NULL, '', 'soundbars', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(52, 'DJ & Studio Equipment', 'Équipement DJ & studio', '', '', 8, NULL, '', 'dj-studio', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(53, 'Smart Home Devices', 'Objets domotiques', '', '', 9, NULL, '', 'smart-home', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(54, 'Fitness Trackers', 'Traceurs d\'activité', '', '', 9, NULL, '', 'fitness-trackers', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(55, 'Novelty Tech', 'Gadgets insolites', '', '', 9, NULL, '', 'novelty-tech', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(56, 'Portable Projectors', 'Projecteurs portables', '', '', 9, NULL, '', 'portable-projectors', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55'),
(57, 'Digital Accessories', 'Accessoires numériques', '', '', 9, NULL, '', 'digital-gadgets', 1, 0, '2025-12-13 15:59:55', '2025-12-13 15:59:55');

-- --------------------------------------------------------

--
-- Table structure for table `category_abbreviations`
--

DROP TABLE IF EXISTS `category_abbreviations`;
CREATE TABLE IF NOT EXISTS `category_abbreviations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `abbreviation` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category` (`category_id`),
  UNIQUE KEY `unique_abbreviation` (`abbreviation`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category_abbreviations`
--

INSERT INTO `category_abbreviations` (`id`, `category_id`, `abbreviation`, `created_at`, `updated_at`) VALUES
(1, 1, 'ELEC', '2025-12-19 08:01:28', '2025-12-19 08:01:28'),
(2, 2, 'PCCP', '2025-12-19 08:01:28', '2025-12-19 08:01:28'),
(3, 3, 'GAME', '2025-12-19 08:01:28', '2025-12-19 08:01:28'),
(4, 4, 'HOME', '2025-12-19 08:01:28', '2025-12-19 08:01:28'),
(5, 5, 'ACCS', '2025-12-19 08:01:28', '2025-12-19 08:01:28'),
(6, 6, 'PHON', '2025-12-19 08:01:28', '2025-12-19 08:01:28'),
(7, 7, 'NETW', '2025-12-19 08:01:28', '2025-12-19 08:01:28'),
(8, 8, 'AUDI', '2025-12-19 08:01:28', '2025-12-19 08:01:28'),
(9, 9, 'GADG', '2025-12-19 08:01:28', '2025-12-19 08:01:28'),
(10, 11, 'CAC', '2025-12-19 08:20:45', '2025-12-19 08:20:45'),
(11, 14, 'ET', '2025-12-21 13:36:34', '2025-12-21 13:36:34'),
(12, 23, 'CONS', '2025-12-25 04:32:04', '2025-12-25 04:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_resolved` tinyint(1) DEFAULT '0',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` int DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `resolved_by` (`resolved_by`),
  KEY `idx_contact_messages_resolved` (`is_resolved`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_pages`
--

DROP TABLE IF EXISTS `content_pages`;
CREATE TABLE IF NOT EXISTS `content_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title_en` varchar(255) NOT NULL,
  `title_fr` varchar(255) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `content_en` longtext,
  `content_fr` longtext,
  `meta_title_en` varchar(255) DEFAULT NULL,
  `meta_title_fr` varchar(255) DEFAULT NULL,
  `meta_description_en` text,
  `meta_description_fr` text,
  `is_published` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_fr` varchar(255) NOT NULL,
  `description_en` text,
  `description_fr` text,
  `discount_type` enum('percentage','fixed_amount') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `minimum_order_amount` decimal(10,2) DEFAULT '0.00',
  `usage_limit` int DEFAULT NULL,
  `used_count` int DEFAULT '0',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` enum('percentage','fixed_amount','free_shipping') DEFAULT 'percentage',
  `usage_limit_per_user` int DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_coupons_code` (`code`),
  KEY `idx_coupons_dates` (`starts_at`,`expires_at`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `name_en`, `name_fr`, `description_en`, `description_fr`, `discount_type`, `discount_value`, `minimum_order_amount`, `usage_limit`, `used_count`, `valid_from`, `valid_until`, `is_active`, `created_at`, `updated_at`, `type`, `usage_limit_per_user`, `starts_at`, `expires_at`) VALUES
(5, '8HBV9', '', '', NULL, NULL, 'percentage', 10.00, 0.00, 100, 2, '2025-12-10', '2025-12-31', 1, '2025-12-24 09:00:45', '2025-12-24 14:26:45', 'percentage', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupon_categories`
--

DROP TABLE IF EXISTS `coupon_categories`;
CREATE TABLE IF NOT EXISTS `coupon_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `coupon_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_coupon_category` (`coupon_id`,`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_products`
--

DROP TABLE IF EXISTS `coupon_products`;
CREATE TABLE IF NOT EXISTS `coupon_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `coupon_id` int NOT NULL,
  `product_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_coupon_product` (`coupon_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

DROP TABLE IF EXISTS `coupon_usage`;
CREATE TABLE IF NOT EXISTS `coupon_usage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `coupon_id` int NOT NULL,
  `order_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_coupon_order` (`coupon_id`,`order_id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `coupon_usage`
--

INSERT INTO `coupon_usage` (`id`, `coupon_id`, `order_id`, `user_id`, `used_at`) VALUES
(1, 5, 23, 1, '2025-12-24 10:30:30'),
(2, 5, 24, 1, '2025-12-24 14:26:45');

-- --------------------------------------------------------

--
-- Table structure for table `couriers`
--

DROP TABLE IF EXISTS `couriers`;
CREATE TABLE IF NOT EXISTS `couriers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `tracking_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `couriers`
--

INSERT INTO `couriers` (`id`, `name`, `tracking_url`, `is_active`) VALUES
(1, 'asd', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_banned` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `two_fa_enabled` tinyint(1) DEFAULT '0',
  `two_fa_secret` varchar(255) DEFAULT NULL,
  `two_fa_backup_codes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `email`, `password`, `first_name`, `last_name`, `phone`, `date_of_birth`, `gender`, `email_verified`, `verification_token`, `reset_token`, `reset_token_expires`, `is_active`, `is_banned`, `created_at`, `updated_at`, `two_fa_enabled`, `two_fa_secret`, `two_fa_backup_codes`) VALUES
(1, 'djerradabderrahim@gmail.com', '$2y$10$6AH.u1LrjujJZNnqcLv9CuSuZR3cE5XTnaxxkdRqmtKA8FnJbSRDW', 'Abderrahim', 'DJERRAD', '13091435037', '2025-12-08', 'male', 1, '19aaaf2a6b8f06c04f5058354639909919ab7976e5f7fe5b43a6fdb78391dfba', NULL, NULL, 1, 0, '2025-12-12 13:26:08', '2025-12-22 05:30:31', 0, NULL, NULL),
(2, 'djerradabderrahim@outlook.fr', '$2y$10$8we0MgLjbdiphFOJKg3Ty.krJiZGNYBu1hF7luCz5MapbBq4tIE4y', 'Abderrahim', 'DJERRAD', '13091435037', '2025-12-15', 'male', 0, '2a599df4fc55b85bb44c490335a3e6abc03e73b4722a2550d6a27db6c7528ada', NULL, NULL, 1, 0, '2025-12-13 05:20:09', '2025-12-22 05:30:28', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `data_requests`
--

DROP TABLE IF EXISTS `data_requests`;
CREATE TABLE IF NOT EXISTS `data_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` enum('export','delete') NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `requested_data` json DEFAULT NULL,
  `exported_file` varchar(500) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `admin_note` text,
  `processed_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `processed_by` (`processed_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_zones`
--

DROP TABLE IF EXISTS `delivery_zones`;
CREATE TABLE IF NOT EXISTS `delivery_zones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `wilaya` varchar(100) NOT NULL,
  `delivery_cost` decimal(10,2) DEFAULT '0.00',
  `estimated_days` int DEFAULT '7',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `question_en` text NOT NULL,
  `question_fr` text,
  `answer_en` text NOT NULL,
  `answer_fr` text,
  `view_count` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `category_id`, `question_en`, `question_fr`, `answer_en`, `answer_fr`, `view_count`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'How do I place an order?', 'Comment puis-je passer une commande ?', 'Simply browse our products, add items to cart, and proceed to checkout. Follow the prompts to provide shipping and payment information.', 'Parcourez simplement nos produits, ajoutez des articles au panier et passez à la caisse. Suivez les invites pour fournir les informations de livraison et de paiement.', 0, 1, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(2, 2, 'What are the shipping costs?', 'Quels sont les frais de port ?', 'Shipping costs vary by location. We offer free shipping on orders over 10,000 DA. You can calculate shipping costs in the cart.', 'Les frais de port varient selon le lieu. Nous proposons la livraison gratuite pour les commandes supérieures à 10 000 DA. Vous pouvez calculer les frais de port dans le panier.', 0, 1, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(3, 3, 'What is your return policy?', 'Quelle est votre politique de retour ?', 'We offer a 30-day return policy. Items must be in original condition. Contact us to initiate a return request.', 'Nous proposons une politique de retour de 30 jours. Les articles doivent être dans leur état d\'origine. Contactez-nous pour initier une demande de retour.', 0, 1, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(4, 4, 'Which payment methods do you accept?', 'Quels modes de paiement acceptez-vous ?', 'We accept CIB Card, Edahabia, BaridiMob, Cash on Delivery (COD), and Bank Transfer.', 'Nous acceptons la carte CIB, Edahabia, BaridiMob, le paiement en espèces à la livraison (COD) et le virement bancaire.', 0, 1, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(5, 1, 'How do I place an order?', 'Comment puis-je passer une commande ?', 'Simply browse our products, add items to cart, and proceed to checkout. Follow the prompts to provide shipping and payment information.', 'Parcourez simplement nos produits, ajoutez des articles au panier et passez à la caisse. Suivez les invites pour fournir les informations de livraison et de paiement.', 0, 1, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(6, 2, 'What are the shipping costs?', 'Quels sont les frais de port ?', 'Shipping costs vary by location. We offer free shipping on orders over 10,000 DA. You can calculate shipping costs in the cart.', 'Les frais de port varient selon le lieu. Nous proposons la livraison gratuite pour les commandes supérieures à 10 000 DA. Vous pouvez calculer les frais de port dans le panier.', 0, 1, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(7, 3, 'What is your return policy?', 'Quelle est votre politique de retour ?', 'We offer a 30-day return policy. Items must be in original condition. Contact us to initiate a return request.', 'Nous proposons une politique de retour de 30 jours. Les articles doivent être dans leur état d\'origine. Contactez-nous pour initier une demande de retour.', 0, 1, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(8, 4, 'Which payment methods do you accept?', 'Quels modes de paiement acceptez-vous ?', 'We accept CIB Card, Edahabia, BaridiMob, Cash on Delivery (COD), and Bank Transfer.', 'Nous acceptons la carte CIB, Edahabia, BaridiMob, le paiement en espèces à la livraison (COD) et le virement bancaire.', 0, 1, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(9, 1, 'How do I place an order?', 'Comment puis-je passer une commande ?', 'Simply browse our products, add items to cart, and proceed to checkout. Follow the prompts to provide shipping and payment information.', 'Parcourez simplement nos produits, ajoutez des articles au panier et passez à la caisse. Suivez les invites pour fournir les informations de livraison et de paiement.', 0, 1, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(10, 2, 'What are the shipping costs?', 'Quels sont les frais de port ?', 'Shipping costs vary by location. We offer free shipping on orders over 10,000 DA. You can calculate shipping costs in the cart.', 'Les frais de port varient selon le lieu. Nous proposons la livraison gratuite pour les commandes supérieures à 10 000 DA. Vous pouvez calculer les frais de port dans le panier.', 0, 1, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(11, 3, 'What is your return policy?', 'Quelle est votre politique de retour ?', 'We offer a 30-day return policy. Items must be in original condition. Contact us to initiate a return request.', 'Nous proposons une politique de retour de 30 jours. Les articles doivent être dans leur état d\'origine. Contactez-nous pour initier une demande de retour.', 0, 1, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(12, 4, 'Which payment methods do you accept?', 'Quels modes de paiement acceptez-vous ?', 'We accept CIB Card, Edahabia, BaridiMob, Cash on Delivery (COD), and Bank Transfer.', 'Nous acceptons la carte CIB, Edahabia, BaridiMob, le paiement en espèces à la livraison (COD) et le virement bancaire.', 0, 1, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(13, 1, 'How do I place an order?', 'Comment puis-je passer une commande ?', 'Simply browse our products, add items to cart, and proceed to checkout. Follow the prompts to provide shipping and payment information.', 'Parcourez simplement nos produits, ajoutez des articles au panier et passez à la caisse. Suivez les invites pour fournir les informations de livraison et de paiement.', 0, 1, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(14, 2, 'What are the shipping costs?', 'Quels sont les frais de port ?', 'Shipping costs vary by location. We offer free shipping on orders over 10,000 DA. You can calculate shipping costs in the cart.', 'Les frais de port varient selon le lieu. Nous proposons la livraison gratuite pour les commandes supérieures à 10 000 DA. Vous pouvez calculer les frais de port dans le panier.', 0, 1, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(15, 3, 'What is your return policy?', 'Quelle est votre politique de retour ?', 'We offer a 30-day return policy. Items must be in original condition. Contact us to initiate a return request.', 'Nous proposons une politique de retour de 30 jours. Les articles doivent être dans leur état d\'origine. Contactez-nous pour initier une demande de retour.', 0, 1, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(16, 4, 'Which payment methods do you accept?', 'Quels modes de paiement acceptez-vous ?', 'We accept CIB Card, Edahabia, BaridiMob, Cash on Delivery (COD), and Bank Transfer.', 'Nous acceptons la carte CIB, Edahabia, BaridiMob, le paiement en espèces à la livraison (COD) et le virement bancaire.', 0, 1, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30');

-- --------------------------------------------------------

--
-- Table structure for table `faq_categories`
--

DROP TABLE IF EXISTS `faq_categories`;
CREATE TABLE IF NOT EXISTS `faq_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) NOT NULL,
  `name_fr` varchar(255) NOT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `faq_categories`
--

INSERT INTO `faq_categories` (`id`, `name_en`, `name_fr`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Ordering', 'Commande', 1, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(2, 'Shipping', 'Livraison', 2, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(3, 'Returns', 'Retours', 3, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(4, 'Payment', 'Paiement', 4, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(5, 'Account', 'Compte', 5, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(6, 'Ordering', 'Commande', 1, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(7, 'Shipping', 'Livraison', 2, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(8, 'Returns', 'Retours', 3, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(9, 'Payment', 'Paiement', 4, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(10, 'Account', 'Compte', 5, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(11, 'Ordering', 'Commande', 1, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(12, 'Shipping', 'Livraison', 2, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(13, 'Returns', 'Retours', 3, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(14, 'Payment', 'Paiement', 4, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(15, 'Account', 'Compte', 5, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(16, 'Ordering', 'Commande', 1, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(17, 'Shipping', 'Livraison', 2, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(18, 'Returns', 'Retours', 3, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(19, 'Payment', 'Paiement', 4, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(20, 'Account', 'Compte', 5, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30');

-- --------------------------------------------------------

--
-- Table structure for table `flash_sales`
--

DROP TABLE IF EXISTS `flash_sales`;
CREATE TABLE IF NOT EXISTS `flash_sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_flash_sales_product` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `flash_sales`
--

INSERT INTO `flash_sales` (`id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(1, 94, 5.00, '2025-12-01 15:07:00', '2025-12-02 15:07:00', 1, '2025-12-13 07:07:23'),
(2, 421, 10.00, '2025-12-14 13:53:00', '2025-12-31 13:53:00', 1, '2025-12-15 05:53:41');

-- --------------------------------------------------------

--
-- Table structure for table `media_upload_log`
--

DROP TABLE IF EXISTS `media_upload_log`;
CREATE TABLE IF NOT EXISTS `media_upload_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int NOT NULL,
  `media_type` enum('image','video') NOT NULL,
  `upload_status` enum('success','failed') NOT NULL,
  `error_message` text,
  `uploaded_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `media_upload_log`
--

INSERT INTO `media_upload_log` (`id`, `product_id`, `file_name`, `file_path`, `file_size`, `media_type`, `upload_status`, `error_message`, `uploaded_by`, `created_at`) VALUES
(1, 421, 'vid_1766131913_694508c94b26b.mp4', 'uploads/medias/products/421/vid_1766131913_694508c94b26b.mp4', 65752, 'video', 'success', NULL, NULL, '2025-12-19 08:11:53'),
(2, 421, 'vid_1766131951_694508ef26aac.mp4', 'uploads/medias/products/421/vid_1766131951_694508ef26aac.mp4', 65752, 'video', 'success', NULL, NULL, '2025-12-19 08:12:31'),
(3, 421, 'vid_1766131962_694508fa0b738.mp4', 'uploads/medias/products/421/vid_1766131962_694508fa0b738.mp4', 65752, 'video', 'success', NULL, NULL, '2025-12-19 08:12:42'),
(4, 421, 'vid_1766131998_6945091ebb15c.mp4', 'uploads/medias/products/421/vid_1766131998_6945091ebb15c.mp4', 65752, 'video', 'success', NULL, NULL, '2025-12-19 08:13:18'),
(5, 421, 'vid_1766209619_6946385394b99.mp4', 'uploads/medias/products/421/vid_1766209619_6946385394b99.mp4', 65752, 'video', 'success', NULL, NULL, '2025-12-20 05:46:59'),
(6, 421, 'vid_1766234989_69469b6d3dc7b.mp4', 'uploads/medias/products/421/vid_1766234989_69469b6d3dc7b.mp4', 65752, 'video', 'success', NULL, NULL, '2025-12-20 12:49:49'),
(7, 421, 'vid_1766235220_69469c548c36c.mp4', 'uploads/medias/products/421/vid_1766235220_69469c548c36c.mp4', 65752, 'video', 'success', NULL, NULL, '2025-12-20 12:53:40'),
(8, 421, 'img_1766235635_69469df3c872d.jpg', 'uploads/medias/products/421/img_1766235635_69469df3c872d.jpg', 1808784, 'image', 'success', NULL, NULL, '2025-12-20 13:00:35'),
(9, 421, 'img_1766235647_69469dff41b91.jpg', 'uploads/medias/products/421/img_1766235647_69469dff41b91.jpg', 2677624, 'image', 'success', NULL, NULL, '2025-12-20 13:00:47'),
(10, 421, 'img_1766235664_69469e1039350.jpg', 'uploads/medias/products/421/img_1766235664_69469e1039350.jpg', 2754016, 'image', 'success', NULL, NULL, '2025-12-20 13:01:04'),
(11, 421, 'img_1766235674_69469e1a023e9.jpg', 'uploads/medias/products/421/img_1766235674_69469e1a023e9.jpg', 2754016, 'image', 'success', NULL, NULL, '2025-12-20 13:01:14'),
(12, 421, 'img_1766235744_69469e6056082.jpg', 'uploads/medias/products/421/img_1766235744_69469e6056082.jpg', 2754016, 'image', 'success', NULL, NULL, '2025-12-20 13:02:24'),
(13, 421, 'img_1766238639_6946a9afdd00f.jpg', 'uploads/medias/products/421/img_1766238639_6946a9afdd00f.jpg', 2754016, 'image', 'success', NULL, NULL, '2025-12-20 13:50:39'),
(14, 421, 'img_1766328406_69480856f135c.jpg', 'uploads/medias/products/421/img_1766328406_69480856f135c.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-21 14:46:46'),
(15, 421, 'img_1766328426_6948086a94d1d.jpg', 'uploads/medias/products/421/img_1766328426_6948086a94d1d.jpg', 38416, 'image', 'success', NULL, NULL, '2025-12-21 14:47:06'),
(16, 421, 'img_1766328478_6948089e6958c.jpg', 'uploads/medias/products/421/img_1766328478_6948089e6958c.jpg', 30284, 'image', 'success', NULL, NULL, '2025-12-21 14:47:58'),
(17, 421, 'img_1766328478_6948089e69be6.jpg', 'uploads/medias/products/421/img_1766328478_6948089e69be6.jpg', 66784, 'image', 'success', NULL, NULL, '2025-12-21 14:47:58'),
(18, 421, 'img_1766328485_694808a5440ac.jpg', 'uploads/medias/products/421/img_1766328485_694808a5440ac.jpg', 30284, 'image', 'success', NULL, NULL, '2025-12-21 14:48:05'),
(19, 421, 'img_1766328485_694808a544880.jpg', 'uploads/medias/products/421/img_1766328485_694808a544880.jpg', 66784, 'image', 'success', NULL, NULL, '2025-12-21 14:48:05'),
(20, 423, 'img_1766373903_6948ba0f29163.jpg', 'uploads/medias/products/423/img_1766373903_6948ba0f29163.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-22 03:25:03'),
(21, 423, 'vid_1766374019_6948ba83aee7d.mp4', 'uploads/medias/products/423/vid_1766374019_6948ba83aee7d.mp4', 65752, 'video', 'success', NULL, NULL, '2025-12-22 03:26:59'),
(22, 421, 'vid_1766374435_6948bc2307b40.mp4', 'uploads/medias/products/421/vid_1766374435_6948bc2307b40.mp4', 65752, 'video', 'success', NULL, NULL, '2025-12-22 03:33:55'),
(23, 421, 'img_1766374718_6948bd3e285c4.jpg', 'uploads/medias/products/421/img_1766374718_6948bd3e285c4.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-22 03:38:38'),
(24, 421, 'img_1766374751_6948bd5f47648.jpg', 'uploads/medias/products/421/img_1766374751_6948bd5f47648.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-22 03:39:11'),
(25, 421, 'img_1766374752_6948bd602819a.jpg', 'uploads/medias/products/421/img_1766374752_6948bd602819a.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-22 03:39:12'),
(26, 421, 'img_1766374752_6948bd60d6e1c.jpg', 'uploads/medias/products/421/img_1766374752_6948bd60d6e1c.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-22 03:39:12'),
(27, 421, 'img_1766374753_6948bd61abae6.jpg', 'uploads/medias/products/421/img_1766374753_6948bd61abae6.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-22 03:39:13'),
(28, 421, 'img_1766558934_694b8cd6aca82.jpg', 'uploads/medias/products/421/img_1766558934_694b8cd6aca82.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:48:54'),
(29, 421, 'img_1766558942_694b8cded0b07.jpg', 'uploads/medias/products/421/img_1766558942_694b8cded0b07.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:49:02'),
(30, 421, 'img_1766558946_694b8ce2ace08.jpg', 'uploads/medias/products/421/img_1766558946_694b8ce2ace08.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:49:06'),
(31, 421, 'img_1766558951_694b8ce75865d.jpg', 'uploads/medias/products/421/img_1766558951_694b8ce75865d.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:49:11'),
(32, 421, 'img_1766558953_694b8ce9820b5.jpg', 'uploads/medias/products/421/img_1766558953_694b8ce9820b5.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:49:13'),
(33, 421, 'img_1766558956_694b8cec4a921.jpg', 'uploads/medias/products/421/img_1766558956_694b8cec4a921.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:49:16'),
(34, 421, 'img_1766558962_694b8cf2c2dbf.jpg', 'uploads/medias/products/421/img_1766558962_694b8cf2c2dbf.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:49:22'),
(35, 421, 'img_1766558965_694b8cf5d1353.jpg', 'uploads/medias/products/421/img_1766558965_694b8cf5d1353.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:49:25'),
(36, 421, 'img_1766558968_694b8cf8373ed.jpg', 'uploads/medias/products/421/img_1766558968_694b8cf8373ed.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:49:28'),
(37, 421, 'img_1766558970_694b8cfac80b2.jpg', 'uploads/medias/products/421/img_1766558970_694b8cfac80b2.jpg', 45625, 'image', 'success', NULL, NULL, '2025-12-24 06:49:30'),
(38, 421, 'img_1766559549_694b8f3deb384.jpg', 'uploads/medias/products/421/img_1766559549_694b8f3deb384.jpg', 66784, 'image', 'success', NULL, NULL, '2025-12-24 06:59:09'),
(39, 421, 'img_1766559561_694b8f49deb70.jpg', 'uploads/medias/products/421/img_1766559561_694b8f49deb70.jpg', 38416, 'image', 'success', NULL, NULL, '2025-12-24 06:59:21'),
(40, 421, 'img_1766559567_694b8f4fc2b59.jpg', 'uploads/medias/products/421/img_1766559567_694b8f4fc2b59.jpg', 30284, 'image', 'success', NULL, NULL, '2025-12-24 06:59:27'),
(41, 424, 'img_1766637124_694cbe44af8d2.jpg', 'uploads/medias/products/424/img_1766637124_694cbe44af8d2.jpg', 46033, 'image', 'success', NULL, NULL, '2025-12-25 04:32:04'),
(42, 424, 'img_1766637124_694cbe44b268c.jpg', 'uploads/medias/products/424/img_1766637124_694cbe44b268c.jpg', 237694, 'image', 'success', NULL, NULL, '2025-12-25 04:32:04'),
(43, 424, 'img_1766637124_694cbe44b2a90.jpg', 'uploads/medias/products/424/img_1766637124_694cbe44b2a90.jpg', 256585, 'image', 'success', NULL, NULL, '2025-12-25 04:32:04'),
(44, 424, 'img_1766637124_694cbe44b2f58.jpg', 'uploads/medias/products/424/img_1766637124_694cbe44b2f58.jpg', 73518, 'image', 'success', NULL, NULL, '2025-12-25 04:32:04'),
(45, 424, 'img_1766637124_694cbe44b32cc.jpg', 'uploads/medias/products/424/img_1766637124_694cbe44b32cc.jpg', 87624, 'image', 'success', NULL, NULL, '2025-12-25 04:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_campaigns`
--

DROP TABLE IF EXISTS `newsletter_campaigns`;
CREATE TABLE IF NOT EXISTS `newsletter_campaigns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `sent_count` int DEFAULT '0',
  `opened_count` int DEFAULT '0',
  `clicked_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_logs`
--

DROP TABLE IF EXISTS `newsletter_logs`;
CREATE TABLE IF NOT EXISTS `newsletter_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subscriber_id` int DEFAULT NULL,
  `campaign_id` int DEFAULT NULL,
  `type` enum('sent','opened','clicked') NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriber_id` (`subscriber_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `is_confirmed` tinyint(1) DEFAULT '0',
  `confirmation_token` varchar(255) DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_newsletter_subscriber_email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT '0.00',
  `tax_amount` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'DZD',
  `billing_address` json DEFAULT NULL,
  `shipping_address` json DEFAULT NULL,
  `delivery_option` enum('home_delivery','pickup_point','express','standard') DEFAULT 'standard',
  `wilaya` varchar(100) DEFAULT NULL,
  `daira` varchar(100) DEFAULT NULL,
  `commune` varchar(100) DEFAULT NULL,
  `delivery_notes` text,
  `payment_method` enum('cod','bank_transfer','baridimob','edahabia','cib') DEFAULT NULL,
  `payment_gateway_response` json DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_reason` text,
  `admin_notes` text,
  `internal_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `courier_name` varchar(100) DEFAULT NULL,
  `tracking_url` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_orders_user` (`customer_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_date` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `customer_name`, `customer_email`, `status`, `payment_status`, `subtotal`, `shipping_cost`, `tax_amount`, `discount_amount`, `total_amount`, `currency`, `billing_address`, `shipping_address`, `delivery_option`, `wilaya`, `daira`, `commune`, `delivery_notes`, `payment_method`, `payment_gateway_response`, `transaction_id`, `tracking_number`, `estimated_delivery`, `delivered_at`, `cancelled_at`, `cancelled_reason`, `admin_notes`, `internal_notes`, `created_at`, `updated_at`, `courier_name`, `tracking_url`) VALUES
(22, 'QWS-C908BCE3', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 12500.00, 500.00, 0.00, 0.00, 13000.00, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-24 10:03:00', '2025-12-24 10:03:00', NULL, NULL),
(21, 'QWS-56B3F9C6', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 6000.00, 500.00, 0.00, 0.00, 6500.00, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-24 09:02:32', '2025-12-24 09:02:32', NULL, NULL),
(20, 'QWS-4975BFED', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 5700.00, 500.00, 0.00, 0.00, 6200.00, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-24 08:26:39', '2025-12-24 08:26:39', NULL, NULL),
(19, 'QWS-70B98FBC', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 11700.00, 500.00, 0.00, 0.00, 12200.00, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-24 05:56:30', '2025-12-24 05:56:30', NULL, NULL),
(18, 'QWS-F7E4D95C', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 6000.00, 500.00, 0.00, 0.00, 6500.00, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-24 05:22:06', '2025-12-24 05:22:06', NULL, NULL),
(17, 'QWS-511716EE', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 516974.08, 500.00, 0.00, 0.00, 517474.08, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-24 05:15:57', '2025-12-24 05:15:57', NULL, NULL),
(16, 'QWS-1B6936CF', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 6000.00, 500.00, 0.00, 0.00, 6500.00, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-22 08:11:19', '2025-12-22 08:11:19', NULL, NULL),
(15, 'QWS-CB499EFF', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'delivered', 'pending', 5700.00, 500.00, 0.00, 0.00, 6200.00, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-22 03:50:25', '2025-12-22 09:54:28', NULL, NULL),
(14, 'QWS-06711D16', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 137153.28, 500.00, 0.00, 0.00, 137653.28, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-22 03:49:37', '2025-12-22 03:49:37', NULL, NULL),
(13, 'QWS-A5BB5045', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 35700.00, 500.00, 0.00, 0.00, 36200.00, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 17:22:25', '2025-12-21 17:22:25', NULL, NULL),
(12, 'QWS-3FB48E57', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 900.90, 500.00, 0.00, 0.00, 1400.90, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 12:55:15', '2025-12-21 12:55:15', NULL, NULL),
(23, 'QWS-9695B377', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 6000.00, 500.00, 0.00, 600.00, 5900.00, 'DZD', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Setif\", \"wilaya\": \"Setif\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-19 23:03:02\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Setif', 'Setif', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-24 10:30:30', '2025-12-24 10:30:30', NULL, NULL),
(24, 'QWS-396588C6', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'pending', 'pending', 169408.32, 500.00, 0.00, 16940.83, 152967.49, 'DZD', '{\"id\": 1, \"daira\": \"Alger\", \"wilaya\": \"Alger\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-24 19:32:50\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Alger\", \"wilaya\": \"Alger\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-24 19:32:50\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Alger', 'Alger', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-24 14:26:45', '2025-12-24 14:26:45', NULL, NULL),
(25, 'QWS-A1AFE39C', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'cancelled', 'refunded', 250500.00, 500.00, 0.00, 0.00, 251000.00, 'DZD', '{\"id\": 1, \"daira\": \"Alger\", \"wilaya\": \"Alger\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-24 19:32:50\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Alger\", \"wilaya\": \"Alger\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-24 19:32:50\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Alger', 'Alger', 'Yangpu District', '', 'cod', NULL, NULL, '13246789', '2025-12-10', NULL, NULL, NULL, NULL, NULL, '2025-12-24 14:31:51', '2025-12-25 06:39:41', NULL, NULL),
(26, 'QWS-2709E129', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'cancelled', 'pending', 280500.00, 500.00, 0.00, 0.00, 281000.00, 'DZD', '{\"id\": 1, \"daira\": \"Alger\", \"wilaya\": \"Alger\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-24 19:32:50\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Alger\", \"wilaya\": \"Alger\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-24 19:32:50\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Alger', 'Alger', 'Yangpu District', '', 'cod', NULL, NULL, NULL, NULL, NULL, '2025-12-25 05:48:29', 'Other', NULL, NULL, '2025-12-24 14:47:48', '2025-12-25 05:49:01', NULL, NULL),
(27, 'QWS-C51A3153', 1, 'Abderrahim DJERRAD', 'djerradabderrahim@gmail.com', 'confirmed', 'pending', 3500.00, 500.00, 0.00, 0.00, 4000.00, 'DZD', '{\"id\": 1, \"daira\": \"Alger\", \"wilaya\": \"Alger\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-24 19:32:50\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', '{\"id\": 1, \"daira\": \"Alger\", \"wilaya\": \"Alger\", \"commune\": \"Yangpu District\", \"created_at\": \"2025-12-19 20:12:40\", \"is_default\": 1, \"updated_at\": \"2025-12-24 19:32:50\", \"customer_id\": 1, \"postal_code\": \"210000\", \"phone_number\": \"13091435037\", \"street_address\": \"Siping Road 2065 Building 6 apartment 405\", \"apartment_suite\": \"\"}', 'standard', 'Alger', 'Alger', 'Yangpu District', '', 'cod', NULL, NULL, '', '2025-12-17', NULL, NULL, NULL, 'TEST Admin Notes (Visible to Customer)', 'TEST Admin Notes Internal Comments (Staff Only)', '2025-12-25 04:36:58', '2025-12-25 07:55:27', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `variant_id` int DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `quantity` int NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `variant_id` (`variant_id`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `product_name`, `product_sku`, `quantity`, `price_at_purchase`, `total_price`) VALUES
(1, 1, 418, NULL, 'Digital Accessories Model 2', NULL, 1, 109633.76, 109633.76),
(2, 1, 420, NULL, 'Digital Accessories Model 4', NULL, 2, 131153.28, 262306.56),
(3, 1, 421, 174, 'AYANEO Pocket AIR Mini', NULL, 1, 900.00, 900.00),
(4, 1, 421, 172, 'AYANEO Pocket AIR Mini', NULL, 1, 900.00, 900.00),
(5, 1, 421, 177, 'AYANEO Pocket AIR Mini', NULL, 1, 900.00, 900.00),
(6, 1, 421, NULL, 'AYANEO Pocket AIR Mini', NULL, 7, 900.00, 6300.00),
(7, 2, 421, 389, 'AYANEO Pocket AIR Mini', NULL, 1, 900.90, 900.90),
(8, 3, 421, 393, 'AYANEO Pocket AIR Mini', NULL, 1, 900.90, 900.90),
(9, 4, 421, 393, 'AYANEO Pocket AIR Mini', NULL, 1, 900.90, 900.90),
(10, 5, 420, NULL, 'Digital Accessories Model 4', NULL, 1, 131153.28, 131153.28),
(11, 6, 420, NULL, 'Digital Accessories Model 4', NULL, 1, 131153.28, 131153.28),
(12, 7, 420, NULL, 'Digital Accessories Model 4', NULL, 1, 131153.28, 131153.28),
(13, 8, 420, NULL, 'Digital Accessories Model 4', NULL, 1, 131153.28, 131153.28),
(14, 9, 421, NULL, 'AYANEO Pocket AIR Mini', NULL, 1, 900.90, 900.90),
(15, 10, 420, NULL, 'Digital Accessories Model 4', NULL, 1, 131153.28, 131153.28),
(16, 11, 191, NULL, 'GoPro Hero 12 Black', NULL, 1, 37799.16, 37799.16),
(17, 11, 421, 389, 'AYANEO Pocket AIR Mini', NULL, 1, 900.90, 900.90),
(18, 12, 421, 386, 'AYANEO Pocket AIR Mini', NULL, 1, 900.90, 900.90),
(19, 13, 421, 49, 'AYANEO Pocket AIR Mini', NULL, 5, 6000.00, 30000.00),
(20, 13, 421, NULL, 'AYANEO Pocket AIR Mini', NULL, 1, 5700.00, 5700.00),
(21, 14, 421, 47, 'AYANEO Pocket AIR Mini', NULL, 1, 6000.00, 6000.00),
(22, 14, 420, NULL, 'Digital Accessories Model 4', NULL, 1, 131153.28, 131153.28),
(23, 15, 421, NULL, 'AYANEO Pocket AIR Mini', NULL, 1, 5700.00, 5700.00),
(24, 16, 421, 85, 'AYANEO Pocket AIR Mini', NULL, 1, 6000.00, 6000.00),
(25, 17, 418, NULL, 'Digital Accessories Model 2', NULL, 2, 109633.76, 219267.52),
(26, 17, 420, NULL, 'Digital Accessories Model 4', NULL, 2, 131153.28, 262306.56),
(27, 17, 421, NULL, 'AYANEO Pocket AIR Mini', NULL, 2, 5700.00, 11400.00),
(28, 17, 421, 88, 'AYANEO Pocket AIR Mini', NULL, 3, 6000.00, 18000.00),
(29, 17, 421, 89, 'AYANEO Pocket AIR Mini', NULL, 1, 6000.00, 6000.00),
(30, 18, 421, 89, 'AYANEO Pocket AIR Mini', NULL, 1, 6000.00, 6000.00),
(31, 19, 421, 89, 'AYANEO Pocket AIR Mini', NULL, 1, 6000.00, 6000.00),
(32, 19, 421, NULL, 'AYANEO Pocket AIR Mini', NULL, 1, 5700.00, 5700.00),
(33, 20, 421, NULL, 'AYANEO Pocket AIR Mini', NULL, 1, 5700.00, 5700.00),
(34, 21, 421, 89, 'AYANEO Pocket AIR Mini', NULL, 1, 6000.00, 6000.00),
(35, 22, 421, 95, 'AYANEO Pocket AIR Mini', NULL, 1, 6500.00, 6500.00),
(36, 22, 421, 89, 'AYANEO Pocket AIR Mini', NULL, 1, 6000.00, 6000.00),
(37, 23, 421, 101, 'AYANEO Pocket AIR Mini', NULL, 1, 6000.00, 6000.00),
(38, 24, 417, NULL, 'Digital Accessories Model 1', NULL, 2, 79004.16, 158008.32),
(39, 24, 421, NULL, 'AYANEO Pocket AIR Mini', NULL, 2, 5700.00, 11400.00),
(40, 25, 421, 101, 'AYANEO Pocket AIR Mini', NULL, 18, 6000.00, 108000.00),
(41, 25, 421, NULL, 'AYANEO Pocket AIR Mini', NULL, 25, 5700.00, 142500.00),
(42, 26, 421, 106, 'AYANEO Pocket AIR Mini', NULL, 51, 5500.00, 280500.00),
(43, 27, 424, NULL, 'ANBERNIC RG 35XXPro', NULL, 1, 3500.00, 3500.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
  `admin_user_id` int DEFAULT NULL,
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_user_id` (`admin_user_id`),
  KEY `idx_order_status_history_order` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `admin_user_id`, `note`, `created_at`) VALUES
(1, 27, 'confirmed', 1, '', '2025-12-25 05:17:03'),
(2, 27, 'pending', 1, '', '2025-12-25 05:17:09'),
(3, 27, 'pending', 1, '', '2025-12-25 05:17:34'),
(4, 27, 'pending', 1, '', '2025-12-25 05:17:35'),
(5, 27, 'pending', 1, '', '2025-12-25 05:17:36'),
(6, 27, 'pending', 1, '', '2025-12-25 05:17:38'),
(7, 27, 'pending', 1, '', '2025-12-25 05:17:39'),
(8, 27, 'pending', 1, '', '2025-12-25 05:17:39'),
(9, 27, 'pending', 1, '', '2025-12-25 05:17:42'),
(10, 27, 'pending', 1, '', '2025-12-25 05:17:55'),
(11, 27, 'pending', 1, '', '2025-12-25 05:17:56'),
(12, 27, 'pending', 1, '', '2025-12-25 05:17:57'),
(13, 27, 'pending', 1, '', '2025-12-25 05:17:57'),
(14, 27, 'confirmed', 1, '', '2025-12-25 05:18:02'),
(15, 27, 'confirmed', 1, '', '2025-12-25 05:18:04'),
(16, 27, 'processing', 1, '', '2025-12-25 05:18:06'),
(17, 27, 'processing', 1, '', '2025-12-25 05:18:44'),
(18, 27, 'processing', 1, '', '2025-12-25 05:18:45'),
(19, 27, 'processing', 1, '', '2025-12-25 05:18:48'),
(20, 27, 'processing', 1, '', '2025-12-25 05:18:55'),
(21, 27, 'processing', 1, '', '2025-12-25 05:18:56'),
(22, 27, 'processing', 1, '', '2025-12-25 05:18:56'),
(23, 27, 'processing', 1, '', '2025-12-25 05:18:57'),
(24, 27, 'processing', 1, '', '2025-12-25 05:19:35'),
(25, 27, 'processing', 1, '', '2025-12-25 05:19:36'),
(26, 27, 'processing', 1, '', '2025-12-25 05:19:36'),
(27, 27, 'processing', 1, '', '2025-12-25 05:19:39'),
(28, 27, 'processing', 1, '', '2025-12-25 05:19:39'),
(29, 27, 'processing', 1, '', '2025-12-25 05:19:40'),
(30, 27, 'processing', 1, '', '2025-12-25 05:19:40'),
(31, 27, 'processing', 1, '', '2025-12-25 05:19:40'),
(32, 27, 'processing', 1, '', '2025-12-25 05:19:40'),
(33, 27, 'processing', 1, '', '2025-12-25 05:19:41'),
(34, 27, 'processing', 1, '', '2025-12-25 05:19:41'),
(35, 27, 'processing', 1, '', '2025-12-25 05:19:41'),
(36, 27, 'processing', 1, '', '2025-12-25 05:19:41'),
(37, 27, 'processing', 1, '', '2025-12-25 05:19:41'),
(38, 27, 'processing', 1, '', '2025-12-25 05:19:42'),
(39, 27, 'processing', 1, '', '2025-12-25 05:19:42'),
(40, 27, 'processing', 1, '', '2025-12-25 05:19:42'),
(41, 27, 'processing', 1, '', '2025-12-25 05:19:42'),
(42, 27, 'processing', 1, '', '2025-12-25 05:20:44'),
(43, 27, 'shipped', 1, '', '2025-12-25 05:22:12'),
(44, 27, 'returned', 1, '', '2025-12-25 05:41:01'),
(45, 27, 'pending', 1, '', '2025-12-25 05:42:20'),
(46, 27, 'confirmed', 1, '', '2025-12-25 05:42:41'),
(47, 27, 'pending', 1, '', '2025-12-25 05:44:12'),
(48, 27, 'confirmed', 1, '', '2025-12-25 05:44:14'),
(49, 27, 'processing', 1, '', '2025-12-25 05:44:16'),
(50, 27, 'shipped', 1, '', '2025-12-25 05:44:17'),
(51, 27, 'delivered', 1, '', '2025-12-25 05:44:19'),
(52, 27, 'cancelled', 1, '', '2025-12-25 05:44:21'),
(53, 27, 'returned', 1, '', '2025-12-25 05:44:23'),
(54, 27, 'pending', 1, '', '2025-12-25 05:44:26'),
(55, 27, 'pending', 1, '', '2025-12-25 05:44:28'),
(56, 27, 'pending', 1, '', '2025-12-25 05:44:40'),
(57, 27, 'pending', 1, '', '2025-12-25 05:44:41'),
(58, 27, 'pending', 1, '', '2025-12-25 05:44:41'),
(59, 27, 'pending', 1, '', '2025-12-25 05:44:42'),
(60, 27, 'pending', 1, '', '2025-12-25 05:44:42'),
(61, 27, 'pending', 1, '', '2025-12-25 05:44:42'),
(62, 27, 'confirmed', 1, '', '2025-12-25 05:46:19'),
(63, 26, 'cancelled', NULL, 'Other', '2025-12-25 05:48:29'),
(64, 26, 'delivered', 1, '', '2025-12-25 05:48:59'),
(65, 26, 'cancelled', 1, '', '2025-12-25 05:49:01'),
(66, 25, 'cancelled', 1, '', '2025-12-25 06:27:25'),
(67, 25, 'cancelled', 1, '', '2025-12-25 06:27:29'),
(68, 27, 'cancelled', 1, '', '2025-12-25 06:29:39'),
(69, 27, 'cancelled', 1, '', '2025-12-25 06:29:41'),
(70, 27, 'cancelled', 1, '', '2025-12-25 06:29:42'),
(71, 27, 'cancelled', 1, '', '2025-12-25 06:29:42'),
(72, 27, 'cancelled', 1, '', '2025-12-25 06:29:49'),
(73, 27, 'cancelled', 1, '', '2025-12-25 06:29:50'),
(74, 27, 'confirmed', 1, '', '2025-12-25 06:29:54'),
(75, 27, 'processing', 1, '', '2025-12-25 06:29:56'),
(76, 27, 'shipped', 1, '', '2025-12-25 06:29:57'),
(77, 27, 'delivered', 1, '', '2025-12-25 06:30:00'),
(78, 27, 'cancelled', 1, '', '2025-12-25 06:30:04'),
(79, 27, 'returned', 1, '', '2025-12-25 06:30:05'),
(80, 27, 'processing', 1, '', '2025-12-25 06:30:09'),
(81, 27, 'processing', 1, '', '2025-12-25 06:30:48'),
(82, 27, 'processing', 1, '', '2025-12-25 06:30:49'),
(83, 27, 'pending', 1, '', '2025-12-25 06:36:53'),
(84, 27, 'pending', 1, '', '2025-12-25 06:37:40'),
(85, 27, 'confirmed', 1, '', '2025-12-25 07:55:22'),
(86, 27, 'delivered', 1, '', '2025-12-25 07:55:25'),
(87, 27, 'confirmed', 1, '', '2025-12-25 07:55:27');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title_en` varchar(255) NOT NULL,
  `title_fr` varchar(255) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `content_en` longtext NOT NULL,
  `content_fr` longtext,
  `meta_title_en` varchar(255) DEFAULT NULL,
  `meta_title_fr` varchar(255) DEFAULT NULL,
  `meta_description_en` text,
  `meta_description_fr` text,
  `meta_keywords` text,
  `is_published` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title_en`, `title_fr`, `slug`, `content_en`, `content_fr`, `meta_title_en`, `meta_title_fr`, `meta_description_en`, `meta_description_fr`, `meta_keywords`, `is_published`, `created_at`, `updated_at`) VALUES
(4, 'Text', 'Text', 'text', '', '', NULL, NULL, NULL, NULL, NULL, 1, '2025-12-14 16:46:14', '2025-12-14 16:46:14');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) NOT NULL,
  `name_fr` varchar(255) NOT NULL,
  `description_en` text,
  `description_fr` text,
  `technical_specs_en` json DEFAULT NULL,
  `technical_specs_fr` json DEFAULT NULL,
  `short_description_en` varchar(500) DEFAULT NULL,
  `short_description_fr` varchar(500) DEFAULT NULL,
  `category_id` int NOT NULL,
  `brand_id` int DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `final_price` decimal(10,2) GENERATED ALWAYS AS ((`price` * (1 - (`discount_percentage` / 100)))) STORED,
  `stock_quantity` int DEFAULT '0',
  `min_stock_quantity` int DEFAULT '5',
  `sku` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_new_arrival` tinyint(1) DEFAULT '0',
  `is_best_seller` tinyint(1) DEFAULT '0',
  `views_count` int DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `rating_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `low_stock_threshold` int DEFAULT '5',
  `flash_sale_badge` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_brand` (`brand_id`),
  KEY `idx_products_sku` (`sku`),
  KEY `idx_products_price` (`final_price`),
  KEY `idx_products_rating` (`rating`),
  KEY `idx_products_active` (`is_active`),
  KEY `idx_products_featured` (`is_featured`),
  KEY `idx_products_new_arrival` (`is_new_arrival`),
  KEY `idx_products_best_seller` (`is_best_seller`),
  KEY `idx_products_stock` (`stock_quantity`),
  KEY `idx_products_low_stock` (`stock_quantity`,`low_stock_threshold`),
  KEY `idx_products_sku_unique` (`sku`)
) ENGINE=MyISAM AUTO_INCREMENT=425 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name_en`, `name_fr`, `description_en`, `description_fr`, `technical_specs_en`, `technical_specs_fr`, `short_description_en`, `short_description_fr`, `category_id`, `brand_id`, `price`, `discount_percentage`, `stock_quantity`, `min_stock_quantity`, `sku`, `barcode`, `weight`, `dimensions`, `video_url`, `is_active`, `is_featured`, `is_new_arrival`, `is_best_seller`, `views_count`, `rating`, `rating_count`, `created_at`, `updated_at`, `low_stock_threshold`, `flash_sale_badge`) VALUES
(421, 'AYANEO Pocket AIR Mini', 'AYANEO Pocket AIR Mini', '<p><img src=\"uploads/medias/products/421/9b8619251a19057cff70779273e95aa6.jpg\" style=\"width: 100%;\"></p><p></p>', '<p>AYANEO Pocket AIR Mini Retro Android Handheld Game Console - MTK Helio G90T Octa-Core.</p>', '[]', '[]', 'AYANEO Pocket AIR Mini Retro Android Handheld Game Console - MTK Helio G90T Octa-Core.', 'AYANEO Pocket AIR Mini Retro Android Handheld Game Console - MTK Helio G90T Octa-Core.', 11, NULL, 6000.00, 0.00, -1, 5, 'DIG-057-005', '', 0.50, '32x24x45', 'uploads/medias/products/421/vid_1766374435_6948bc2307b40.mp4', 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-24 14:47:48', 5, 0),
(420, 'Digital Accessories Model 4', 'Digital Accessories Modèle 4', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', 57, NULL, 136618.00, 4.00, 33, 5, 'DIG-057-004', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-24 08:55:43', 5, 0),
(418, 'Digital Accessories Model 2', 'Digital Accessories Modèle 2', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', 57, NULL, 123184.00, 11.00, 16, 5, 'DIG-057-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-24 05:15:57', 5, 0),
(417, 'Digital Accessories Model 1', 'Digital Accessories Modèle 1', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', 57, NULL, 109728.00, 28.00, 98, 5, 'DIG-057-001', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-24 14:26:45', 5, 0),
(415, 'Portable Projectors Model 4', 'Portable Projectors Modèle 4', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 64986.00, 11.00, 34, 5, 'POR-056-004', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-22 11:49:19', 5, 0),
(416, 'Portable Projectors Model 5', 'Portable Projectors Modèle 5', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 46819.00, 10.00, 15, 5, 'POR-056-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:22', 5, 0),
(414, 'Portable Projectors Model 3', 'Portable Projectors Modèle 3', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 30065.00, 4.00, 90, 5, 'POR-056-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(413, 'Portable Projectors Model 2', 'Portable Projectors Modèle 2', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 41662.00, 23.00, 40, 5, 'POR-056-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(411, 'Novelty Tech Model 5', 'Novelty Tech Modèle 5', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 145191.00, 4.00, 71, 5, 'NOV-055-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(412, 'Portable Projectors Model 1', 'Portable Projectors Modèle 1', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 41117.00, 18.00, 42, 5, 'POR-056-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:25', 5, 0),
(410, 'Novelty Tech Model 4', 'Novelty Tech Modèle 4', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 176260.00, 3.00, 15, 5, 'NOV-055-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:25', 5, 0),
(409, 'Novelty Tech Model 3', 'Novelty Tech Modèle 3', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 154609.00, 14.00, 57, 5, 'NOV-055-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(407, 'Novelty Tech Model 1', 'Novelty Tech Modèle 1', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 149862.00, 8.00, 38, 5, 'NOV-055-001', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:55', 5, 0),
(408, 'Novelty Tech Model 2', 'Novelty Tech Modèle 2', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 131144.00, 3.00, 65, 5, 'NOV-055-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(406, 'Fitness Trackers Model 5', 'Fitness Trackers Modèle 5', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 91516.00, 20.00, 25, 5, 'FIT-054-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(405, 'Fitness Trackers Model 4', 'Fitness Trackers Modèle 4', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 56075.00, 19.00, 37, 5, 'FIT-054-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(403, 'Fitness Trackers Model 2', 'Fitness Trackers Modèle 2', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 49199.00, 14.00, 7, 5, 'FIT-054-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(404, 'Fitness Trackers Model 3', 'Fitness Trackers Modèle 3', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 57675.00, 30.00, 50, 5, 'FIT-054-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(402, 'Fitness Trackers Model 1', 'Fitness Trackers Modèle 1', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 52993.00, 18.00, 63, 5, 'FIT-054-001', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:56', 5, 0),
(401, 'Smart Home Devices Model 5', 'Smart Home Devices Modèle 5', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 44237.00, 0.00, 34, 5, 'SMA-053-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(399, 'Smart Home Devices Model 3', 'Smart Home Devices Modèle 3', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 54633.00, 4.00, 68, 5, 'SMA-053-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(400, 'Smart Home Devices Model 4', 'Smart Home Devices Modèle 4', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 22706.00, 9.00, 39, 5, 'SMA-053-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(398, 'Smart Home Devices Model 2', 'Smart Home Devices Modèle 2', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 32948.00, 8.00, 73, 5, 'SMA-053-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(396, 'DJ & Studio Equipment Model 5', 'DJ & Studio Equipment Modèle 5', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 13266.00, 21.00, 43, 5, 'DJ--052-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(397, 'Smart Home Devices Model 1', 'Smart Home Devices Modèle 1', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 28619.00, 9.00, 42, 5, 'SMA-053-001', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:03:00', 5, 0),
(395, 'DJ & Studio Equipment Model 4', 'DJ & Studio Equipment Modèle 4', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 8075.00, 26.00, 26, 5, 'DJ--052-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(394, 'DJ & Studio Equipment Model 3', 'DJ & Studio Equipment Modèle 3', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 3065.00, 28.00, 38, 5, 'DJ--052-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(392, 'DJ & Studio Equipment Model 1', 'DJ & Studio Equipment Modèle 1', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 13314.00, 25.00, 24, 5, 'DJ--052-001', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:59', 5, 0),
(393, 'DJ & Studio Equipment Model 2', 'DJ & Studio Equipment Modèle 2', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 29321.00, 9.00, 24, 5, 'DJ--052-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(391, 'Soundbars Model 5', 'Soundbars Modèle 5', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 101528.00, 12.00, 61, 5, 'SOU-051-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(389, 'Soundbars Model 3', 'Soundbars Modèle 3', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 104793.00, 23.00, 28, 5, 'SOU-051-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(390, 'Soundbars Model 4', 'Soundbars Modèle 4', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 94368.00, 21.00, 60, 5, 'SOU-051-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(387, 'Soundbars Model 1', 'Soundbars Modèle 1', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 77206.00, 6.00, 46, 5, 'SOU-051-001', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:03:02', 5, 0),
(388, 'Soundbars Model 2', 'Soundbars Modèle 2', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 73804.00, 2.00, 79, 5, 'SOU-051-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(386, 'Speakers Model 5', 'Speakers Modèle 5', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 1876.00, 11.00, 100, 5, 'SPE-050-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(384, 'Speakers Model 3', 'Speakers Modèle 3', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 15260.00, 27.00, 81, 5, 'SPE-050-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(385, 'Speakers Model 4', 'Speakers Modèle 4', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 32881.00, 11.00, 13, 5, 'SPE-050-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(383, 'Speakers Model 2', 'Speakers Modèle 2', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 22791.00, 12.00, 68, 5, 'SPE-050-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(381, 'Earbuds Model 5', 'Earbuds Modèle 5', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 131137.00, 24.00, 79, 5, 'EAR-049-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(382, 'Speakers Model 1', 'Speakers Modèle 1', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 31089.00, 7.00, 69, 5, 'SPE-050-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(380, 'Earbuds Model 4', 'Earbuds Modèle 4', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 74848.00, 18.00, 47, 5, 'EAR-049-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(378, 'Earbuds Model 2', 'Earbuds Modèle 2', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 91272.00, 30.00, 42, 5, 'EAR-049-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(379, 'Earbuds Model 3', 'Earbuds Modèle 3', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 72838.00, 11.00, 16, 5, 'EAR-049-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(376, 'JBL Tune 770NC', 'JBL Tune 770NC', 'Budget ANC headphones', 'Casque ANC économique', '[]', '[]', 'Budget ANC headphones', 'Casque ANC économique', 48, NULL, 14999.00, 21.00, 85, 5, 'HEA-048-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(377, 'Earbuds Model 1', 'Earbuds Modèle 1', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 87728.00, 17.00, 8, 5, 'EAR-049-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(374, 'Apple AirPods Max', 'Apple AirPods Max', 'Spatial audio excellence', 'Excellence audio spatial', '[]', '[]', 'Spatial audio excellence', 'Excellence audio spatial', 48, NULL, 59999.00, 16.00, 21, 5, 'HEA-048-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(375, 'Sennheiser Momentum 4', 'Sennheiser Momentum 4', 'Audiophile wireless headphones', 'Casque sans fil audiophile', '[]', '[]', 'Audiophile wireless headphones', 'Casque sans fil audiophile', 48, NULL, 34999.00, 17.00, 54, 5, 'HEA-048-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(372, 'Sony WH-1000XM5', 'Sony WH-1000XM5', 'Industry-leading noise cancellation', 'Réduction de bruit leader du marché', '[]', '[]', 'Industry-leading noise cancellation', 'Réduction de bruit leader du marché', 48, NULL, 39999.00, 9.00, 84, 5, 'HEA-048-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(373, 'Bose QuietComfort Ultra', 'Bose QuietComfort Ultra', 'Premium comfort and ANC', 'Confort premium et ANC', '[]', '[]', 'Premium comfort and ANC', 'Confort premium et ANC', 48, NULL, 44999.00, 4.00, 81, 5, 'HEA-048-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(370, 'NAS & Servers Model 4', 'NAS & Servers Modèle 4', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 105748.00, 28.00, 14, 5, 'NAS-047-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(371, 'NAS & Servers Model 5', 'NAS & Servers Modèle 5', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 52434.00, 22.00, 11, 5, 'NAS-047-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(369, 'NAS & Servers Model 3', 'NAS & Servers Modèle 3', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 93315.00, 1.00, 65, 5, 'NAS-047-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(368, 'NAS & Servers Model 2', 'NAS & Servers Modèle 2', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 66868.00, 15.00, 96, 5, 'NAS-047-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(366, 'Network Cables Model 5', 'Network Cables Modèle 5', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 143963.00, 11.00, 92, 5, 'NET-046-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(367, 'NAS & Servers Model 1', 'NAS & Servers Modèle 1', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 72730.00, 17.00, 78, 5, 'NAS-047-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(365, 'Network Cables Model 4', 'Network Cables Modèle 4', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 156864.00, 16.00, 28, 5, 'NET-046-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(363, 'Network Cables Model 2', 'Network Cables Modèle 2', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 153166.00, 2.00, 71, 5, 'NET-046-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(364, 'Network Cables Model 3', 'Network Cables Modèle 3', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 165094.00, 12.00, 15, 5, 'NET-046-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(362, 'Network Cables Model 1', 'Network Cables Modèle 1', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 148358.00, 6.00, 53, 5, 'NET-046-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(360, 'Wi-Fi Extenders Model 4', 'Wi-Fi Extenders Modèle 4', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 72317.00, 18.00, 89, 5, 'WIF-045-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(361, 'Wi-Fi Extenders Model 5', 'Wi-Fi Extenders Modèle 5', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 68906.00, 26.00, 53, 5, 'WIF-045-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(359, 'Wi-Fi Extenders Model 3', 'Wi-Fi Extenders Modèle 3', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 67602.00, 7.00, 5, 5, 'WIF-045-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(358, 'Wi-Fi Extenders Model 2', 'Wi-Fi Extenders Modèle 2', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 48259.00, 25.00, 91, 5, 'WIF-045-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(356, 'Switches Model 5', 'Switches Modèle 5', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 89084.00, 0.00, 74, 5, 'SWI-044-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(357, 'Wi-Fi Extenders Model 1', 'Wi-Fi Extenders Modèle 1', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 53252.00, 8.00, 45, 5, 'WIF-045-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(355, 'Switches Model 4', 'Switches Modèle 4', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 111972.00, 20.00, 60, 5, 'SWI-044-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(353, 'Switches Model 2', 'Switches Modèle 2', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 70536.00, 27.00, 89, 5, 'SWI-044-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(354, 'Switches Model 3', 'Switches Modèle 3', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 90729.00, 7.00, 51, 5, 'SWI-044-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(352, 'Switches Model 1', 'Switches Modèle 1', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 72717.00, 15.00, 67, 5, 'SWI-044-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(350, 'Routers & Modems Model 4', 'Routers & Modems Modèle 4', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 160329.00, 6.00, 94, 5, 'ROU-043-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(351, 'Routers & Modems Model 5', 'Routers & Modems Modèle 5', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 120397.00, 25.00, 97, 5, 'ROU-043-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(349, 'Routers & Modems Model 3', 'Routers & Modems Modèle 3', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 167989.00, 3.00, 48, 5, 'ROU-043-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(348, 'Routers & Modems Model 2', 'Routers & Modems Modèle 2', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 160227.00, 10.00, 95, 5, 'ROU-043-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(346, 'Phone Accessories Model 5', 'Phone Accessories Modèle 5', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 96897.00, 0.00, 79, 5, 'PHO-042-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(347, 'Routers & Modems Model 1', 'Routers & Modems Modèle 1', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 138951.00, 7.00, 8, 5, 'ROU-043-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(345, 'Phone Accessories Model 4', 'Phone Accessories Modèle 4', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 97768.00, 23.00, 65, 5, 'PHO-042-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(344, 'Phone Accessories Model 3', 'Phone Accessories Modèle 3', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 95802.00, 26.00, 53, 5, 'PHO-042-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(342, 'Phone Accessories Model 1', 'Phone Accessories Modèle 1', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 111654.00, 9.00, 82, 5, 'PHO-042-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(343, 'Phone Accessories Model 2', 'Phone Accessories Modèle 2', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 108012.00, 0.00, 87, 5, 'PHO-042-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(341, 'Refurbished Phones Model 5', 'Refurbished Phones Modèle 5', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 60323.00, 2.00, 29, 5, 'REF-041-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(340, 'Refurbished Phones Model 4', 'Refurbished Phones Modèle 4', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 64882.00, 10.00, 59, 5, 'REF-041-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(339, 'Refurbished Phones Model 3', 'Refurbished Phones Modèle 3', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 70964.00, 7.00, 27, 5, 'REF-041-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(337, 'Refurbished Phones Model 1', 'Refurbished Phones Modèle 1', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 73197.00, 13.00, 38, 5, 'REF-041-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(338, 'Refurbished Phones Model 2', 'Refurbished Phones Modèle 2', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 60856.00, 12.00, 65, 5, 'REF-041-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(336, 'Feature Phones Model 5', 'Feature Phones Modèle 5', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 112837.00, 3.00, 23, 5, 'FEA-040-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(335, 'Feature Phones Model 4', 'Feature Phones Modèle 4', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 91510.00, 15.00, 8, 5, 'FEA-040-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(333, 'Feature Phones Model 2', 'Feature Phones Modèle 2', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 118406.00, 12.00, 93, 5, 'FEA-040-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(334, 'Feature Phones Model 3', 'Feature Phones Modèle 3', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 122362.00, 6.00, 38, 5, 'FEA-040-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(332, 'Feature Phones Model 1', 'Feature Phones Modèle 1', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 110614.00, 9.00, 19, 5, 'FEA-040-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(331, 'iOS Devices Model 5', 'iOS Devices Modèle 5', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 50157.00, 3.00, 25, 5, 'IOS-039-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(329, 'iOS Devices Model 3', 'iOS Devices Modèle 3', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 61471.00, 30.00, 66, 5, 'IOS-039-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(330, 'iOS Devices Model 4', 'iOS Devices Modèle 4', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 36250.00, 21.00, 15, 5, 'IOS-039-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(328, 'iOS Devices Model 2', 'iOS Devices Modèle 2', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 54744.00, 30.00, 98, 5, 'IOS-039-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(326, 'Samsung Galaxy A54', 'Samsung Galaxy A54', 'Mid-range with great features', 'Milieu de gamme avec bonnes fonctionnalités', '[]', '[]', 'Mid-range with great features', 'Milieu de gamme avec bonnes fonctionnalités', 38, NULL, 44999.00, 15.00, 68, 5, 'AND-038-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(327, 'iOS Devices Model 1', 'iOS Devices Modèle 1', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 51325.00, 1.00, 69, 5, 'IOS-039-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(324, 'OnePlus 12', 'OnePlus 12', 'Fast charging flagship', 'Modèle phare charge rapide', '[]', '[]', 'Fast charging flagship', 'Modèle phare charge rapide', 38, NULL, 89999.00, 30.00, 26, 5, 'AND-038-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(325, 'Xiaomi 14 Pro', 'Xiaomi 14 Pro', 'Premium camera phone', 'Téléphone photo premium', '[]', '[]', 'Premium camera phone', 'Téléphone photo premium', 38, NULL, 79999.00, 28.00, 23, 5, 'AND-038-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(322, 'Samsung Galaxy S24 Ultra', 'Samsung Galaxy S24 Ultra', 'Flagship with S Pen', 'Modèle phare avec S Pen', '[]', '[]', 'Flagship with S Pen', 'Modèle phare avec S Pen', 38, NULL, 149999.00, 27.00, 67, 5, 'AND-038-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(323, 'Google Pixel 8 Pro', 'Google Pixel 8 Pro', 'Pure Android experience', 'Expérience Android pure', '[]', '[]', 'Pure Android experience', 'Expérience Android pure', 38, NULL, 119999.00, 14.00, 58, 5, 'AND-038-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(321, 'Styluses & Pens Model 5', 'Styluses & Pens Modèle 5', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 182900.00, 30.00, 33, 5, 'STY-037-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(319, 'Styluses & Pens Model 3', 'Styluses & Pens Modèle 3', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 168861.00, 19.00, 26, 5, 'STY-037-003', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-15 15:27:24', 5, 0),
(320, 'Styluses & Pens Model 4', 'Styluses & Pens Modèle 4', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 173719.00, 30.00, 47, 5, 'STY-037-004', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-15 15:27:23', 5, 0),
(318, 'Styluses & Pens Model 2', 'Styluses & Pens Modèle 2', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 155623.00, 11.00, 47, 5, 'STY-037-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(317, 'Styluses & Pens Model 1', 'Styluses & Pens Modèle 1', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 151672.00, 12.00, 60, 5, 'STY-037-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(315, 'Screen Protectors Model 4', 'Screen Protectors Modèle 4', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 151577.00, 6.00, 67, 5, 'SCR-036-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(316, 'Screen Protectors Model 5', 'Screen Protectors Modèle 5', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 145997.00, 0.00, 97, 5, 'SCR-036-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(314, 'Screen Protectors Model 3', 'Screen Protectors Modèle 3', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 146355.00, 12.00, 64, 5, 'SCR-036-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(313, 'Screen Protectors Model 2', 'Screen Protectors Modèle 2', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 132517.00, 28.00, 53, 5, 'SCR-036-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(311, 'Cases & Covers Model 5', 'Cases & Covers Modèle 5', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 25618.00, 25.00, 58, 5, 'CAS-035-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(312, 'Screen Protectors Model 1', 'Screen Protectors Modèle 1', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 128442.00, 3.00, 62, 5, 'SCR-036-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0);
INSERT INTO `products` (`id`, `name_en`, `name_fr`, `description_en`, `description_fr`, `technical_specs_en`, `technical_specs_fr`, `short_description_en`, `short_description_fr`, `category_id`, `brand_id`, `price`, `discount_percentage`, `stock_quantity`, `min_stock_quantity`, `sku`, `barcode`, `weight`, `dimensions`, `video_url`, `is_active`, `is_featured`, `is_new_arrival`, `is_best_seller`, `views_count`, `rating`, `rating_count`, `created_at`, `updated_at`, `low_stock_threshold`, `flash_sale_badge`) VALUES
(310, 'Cases & Covers Model 4', 'Cases & Covers Modèle 4', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 17996.00, 8.00, 24, 5, 'CAS-035-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(308, 'Cases & Covers Model 2', 'Cases & Covers Modèle 2', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 18682.00, 6.00, 58, 5, 'CAS-035-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(309, 'Cases & Covers Model 3', 'Cases & Covers Modèle 3', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 40218.00, 10.00, 85, 5, 'CAS-035-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(307, 'Cases & Covers Model 1', 'Cases & Covers Modèle 1', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 34713.00, 12.00, 37, 5, 'CAS-035-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(306, 'Chargers & Power Banks Model 5', 'Chargers & Power Banks Modèle 5', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 70367.00, 8.00, 71, 5, 'CHA-034-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(304, 'Chargers & Power Banks Model 3', 'Chargers & Power Banks Modèle 3', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 82726.00, 19.00, 13, 5, 'CHA-034-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(305, 'Chargers & Power Banks Model 4', 'Chargers & Power Banks Modèle 4', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 79822.00, 29.00, 88, 5, 'CHA-034-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(303, 'Chargers & Power Banks Model 2', 'Chargers & Power Banks Modèle 2', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 112164.00, 20.00, 68, 5, 'CHA-034-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(302, 'Chargers & Power Banks Model 1', 'Chargers & Power Banks Modèle 1', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 87549.00, 29.00, 96, 5, 'CHA-034-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(300, 'Cables & Adapters Model 4', 'Cables & Adapters Modèle 4', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 75223.00, 12.00, 95, 5, 'CAB-033-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(301, 'Cables & Adapters Model 5', 'Cables & Adapters Modèle 5', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 61467.00, 18.00, 87, 5, 'CAB-033-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(299, 'Cables & Adapters Model 3', 'Cables & Adapters Modèle 3', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 64264.00, 0.00, 28, 5, 'CAB-033-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(297, 'Cables & Adapters Model 1', 'Cables & Adapters Modèle 1', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 66221.00, 8.00, 58, 5, 'CAB-033-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(298, 'Cables & Adapters Model 2', 'Cables & Adapters Modèle 2', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 85481.00, 1.00, 20, 5, 'CAB-033-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(296, 'Small Appliances Model 5', 'Small Appliances Modèle 5', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, -658.00, 29.00, 90, 5, 'SMA-032-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(295, 'Small Appliances Model 4', 'Small Appliances Modèle 4', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, 53948.00, 10.00, 36, 5, 'SMA-032-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(293, 'Small Appliances Model 2', 'Small Appliances Modèle 2', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, 14412.00, 11.00, 6, 5, 'SMA-032-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(294, 'Small Appliances Model 3', 'Small Appliances Modèle 3', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, 14788.00, 30.00, 62, 5, 'SMA-032-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(292, 'Small Appliances Model 1', 'Small Appliances Modèle 1', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, 14791.00, 26.00, 62, 5, 'SMA-032-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(290, 'Vacuum Cleaners Model 4', 'Vacuum Cleaners Modèle 4', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 171649.00, 8.00, 94, 5, 'VAC-031-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(291, 'Vacuum Cleaners Model 5', 'Vacuum Cleaners Modèle 5', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 128483.00, 18.00, 12, 5, 'VAC-031-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(289, 'Vacuum Cleaners Model 3', 'Vacuum Cleaners Modèle 3', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 126482.00, 15.00, 30, 5, 'VAC-031-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(288, 'Vacuum Cleaners Model 2', 'Vacuum Cleaners Modèle 2', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 143679.00, 10.00, 81, 5, 'VAC-031-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(286, 'Climate Control Model 5', 'Climate Control Modèle 5', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 62116.00, 25.00, 93, 5, 'CLI-030-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(287, 'Vacuum Cleaners Model 1', 'Vacuum Cleaners Modèle 1', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 149999.00, 15.00, 48, 5, 'VAC-031-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(285, 'Climate Control Model 4', 'Climate Control Modèle 4', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 60479.00, 2.00, 84, 5, 'CLI-030-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(283, 'Climate Control Model 2', 'Climate Control Modèle 2', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 61019.00, 23.00, 62, 5, 'CLI-030-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(284, 'Climate Control Model 3', 'Climate Control Modèle 3', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 66902.00, 13.00, 78, 5, 'CLI-030-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(282, 'Climate Control Model 1', 'Climate Control Modèle 1', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 47142.00, 22.00, 72, 5, 'CLI-030-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(281, 'Laundry Appliances Model 5', 'Laundry Appliances Modèle 5', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 101739.00, 21.00, 22, 5, 'LAU-029-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(280, 'Laundry Appliances Model 4', 'Laundry Appliances Modèle 4', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 74289.00, 9.00, 22, 5, 'LAU-029-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(278, 'Laundry Appliances Model 2', 'Laundry Appliances Modèle 2', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 58905.00, 0.00, 22, 5, 'LAU-029-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(279, 'Laundry Appliances Model 3', 'Laundry Appliances Modèle 3', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 54619.00, 5.00, 45, 5, 'LAU-029-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(277, 'Laundry Appliances Model 1', 'Laundry Appliances Modèle 1', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 63424.00, 21.00, 67, 5, 'LAU-029-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(276, 'Kitchen Appliances Model 5', 'Kitchen Appliances Modèle 5', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 35429.00, 20.00, 12, 5, 'KIT-028-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(275, 'Kitchen Appliances Model 4', 'Kitchen Appliances Modèle 4', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 63475.00, 4.00, 95, 5, 'KIT-028-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(273, 'Kitchen Appliances Model 2', 'Kitchen Appliances Modèle 2', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 45571.00, 28.00, 34, 5, 'KIT-028-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(274, 'Kitchen Appliances Model 3', 'Kitchen Appliances Modèle 3', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 60321.00, 14.00, 53, 5, 'KIT-028-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(272, 'Kitchen Appliances Model 1', 'Kitchen Appliances Modèle 1', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 32508.00, 6.00, 24, 5, 'KIT-028-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(271, 'Gaming Chairs & Desks Model 5', 'Gaming Chairs & Desks Modèle 5', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 73493.00, 3.00, 31, 5, 'GAM-027-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(269, 'Gaming Chairs & Desks Model 3', 'Gaming Chairs & Desks Modèle 3', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 47311.00, 25.00, 54, 5, 'GAM-027-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(270, 'Gaming Chairs & Desks Model 4', 'Gaming Chairs & Desks Modèle 4', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 48554.00, 23.00, 93, 5, 'GAM-027-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(268, 'Gaming Chairs & Desks Model 2', 'Gaming Chairs & Desks Modèle 2', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 69042.00, 13.00, 51, 5, 'GAM-027-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(266, 'VR & AR Gear Model 5', 'VR & AR Gear Modèle 5', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 50562.00, 13.00, 24, 5, 'VR--026-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(267, 'Gaming Chairs & Desks Model 1', 'Gaming Chairs & Desks Modèle 1', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 58413.00, 20.00, 69, 5, 'GAM-027-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(265, 'VR & AR Gear Model 4', 'VR & AR Gear Modèle 4', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 62499.00, 6.00, 91, 5, 'VR--026-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(263, 'VR & AR Gear Model 2', 'VR & AR Gear Modèle 2', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 66703.00, 4.00, 31, 5, 'VR--026-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(264, 'VR & AR Gear Model 3', 'VR & AR Gear Modèle 3', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 83302.00, 1.00, 93, 5, 'VR--026-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(262, 'VR & AR Gear Model 1', 'VR & AR Gear Modèle 1', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 55667.00, 14.00, 69, 5, 'VR--026-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(260, 'Gaming Peripherals Model 4', 'Gaming Peripherals Modèle 4', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 47718.00, 25.00, 55, 5, 'GAM-025-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(261, 'Gaming Peripherals Model 5', 'Gaming Peripherals Modèle 5', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 24435.00, 28.00, 34, 5, 'GAM-025-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(259, 'Gaming Peripherals Model 3', 'Gaming Peripherals Modèle 3', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 46589.00, 0.00, 64, 5, 'GAM-025-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(258, 'Gaming Peripherals Model 2', 'Gaming Peripherals Modèle 2', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 21318.00, 8.00, 75, 5, 'GAM-025-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(256, 'Video Games Model 5', 'Video Games Modèle 5', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 153600.00, 13.00, 37, 5, 'VID-024-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(257, 'Gaming Peripherals Model 1', 'Gaming Peripherals Modèle 1', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 32706.00, 16.00, 46, 5, 'GAM-025-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(255, 'Video Games Model 4', 'Video Games Modèle 4', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 152347.00, 0.00, 10, 5, 'VID-024-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(253, 'Video Games Model 2', 'Video Games Modèle 2', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 136143.00, 7.00, 73, 5, 'VID-024-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(254, 'Video Games Model 3', 'Video Games Modèle 3', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 136370.00, 13.00, 62, 5, 'VID-024-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(252, 'Video Games Model 1', 'Video Games Modèle 1', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 136927.00, 22.00, 58, 5, 'VID-024-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(250, 'Consoles Model 4', 'Consoles Modèle 4', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 75107.00, 21.00, 61, 5, 'CON-023-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(251, 'Consoles Model 5', 'Consoles Modèle 5', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 66737.00, 28.00, 28, 5, 'CON-023-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(249, 'Consoles Model 3', 'Consoles Modèle 3', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 88606.00, 29.00, 43, 5, 'CON-023-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(247, 'Consoles Model 1', 'Consoles Modèle 1', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 76871.00, 30.00, 36, 5, 'CON-023-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(248, 'Consoles Model 2', 'Consoles Modèle 2', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 96967.00, 24.00, 43, 5, 'CON-023-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(246, 'Cooling Systems Model 5', 'Cooling Systems Modèle 5', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 25005.00, 29.00, 76, 5, 'COO-022-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(244, 'Cooling Systems Model 3', 'Cooling Systems Modèle 3', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 41315.00, 17.00, 20, 5, 'COO-022-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(245, 'Cooling Systems Model 4', 'Cooling Systems Modèle 4', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 26486.00, 5.00, 98, 5, 'COO-022-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(243, 'Cooling Systems Model 2', 'Cooling Systems Modèle 2', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 55054.00, 12.00, 72, 5, 'COO-022-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(241, 'PC Cases Model 5', 'PC Cases Modèle 5', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 134353.00, 8.00, 88, 5, 'PC--021-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(242, 'Cooling Systems Model 1', 'Cooling Systems Modèle 1', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 41120.00, 8.00, 8, 5, 'COO-022-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(240, 'PC Cases Model 4', 'PC Cases Modèle 4', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 160755.00, 8.00, 11, 5, 'PC--021-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(238, 'PC Cases Model 2', 'PC Cases Modèle 2', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 128523.00, 9.00, 37, 5, 'PC--021-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(239, 'PC Cases Model 3', 'PC Cases Modèle 3', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 158416.00, 1.00, 75, 5, 'PC--021-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(237, 'PC Cases Model 1', 'PC Cases Modèle 1', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 138832.00, 16.00, 41, 5, 'PC--021-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(235, 'Power Supplies Model 4', 'Power Supplies Modèle 4', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 85729.00, 22.00, 11, 5, 'POW-020-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(236, 'Power Supplies Model 5', 'Power Supplies Modèle 5', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 74347.00, 26.00, 43, 5, 'POW-020-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(234, 'Power Supplies Model 3', 'Power Supplies Modèle 3', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 51599.00, 11.00, 100, 5, 'POW-020-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(233, 'Power Supplies Model 2', 'Power Supplies Modèle 2', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 68309.00, 3.00, 54, 5, 'POW-020-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(231, 'Storage (SSD/HDD) Model 5', 'Storage (SSD/HDD) Modèle 5', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 59868.00, 30.00, 54, 5, 'STO-019-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(232, 'Power Supplies Model 1', 'Power Supplies Modèle 1', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 46672.00, 28.00, 21, 5, 'POW-020-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(230, 'Storage (SSD/HDD) Model 4', 'Storage (SSD/HDD) Modèle 4', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 22584.00, 17.00, 72, 5, 'STO-019-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(228, 'Storage (SSD/HDD) Model 2', 'Storage (SSD/HDD) Modèle 2', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 30758.00, 3.00, 52, 5, 'STO-019-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(229, 'Storage (SSD/HDD) Model 3', 'Storage (SSD/HDD) Modèle 3', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 38415.00, 2.00, 56, 5, 'STO-019-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(227, 'Storage (SSD/HDD) Model 1', 'Storage (SSD/HDD) Modèle 1', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 36653.00, 27.00, 76, 5, 'STO-019-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(226, 'RAM & Memory Model 5', 'RAM & Memory Modèle 5', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 19027.00, 3.00, 57, 5, 'RAM-018-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(224, 'RAM & Memory Model 3', 'RAM & Memory Modèle 3', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 32031.00, 28.00, 64, 5, 'RAM-018-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(225, 'RAM & Memory Model 4', 'RAM & Memory Modèle 4', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 60772.00, 23.00, 82, 5, 'RAM-018-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(223, 'RAM & Memory Model 2', 'RAM & Memory Modèle 2', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 38722.00, 13.00, 23, 5, 'RAM-018-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(221, 'Motherboards Model 5', 'Motherboards Modèle 5', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 83657.00, 23.00, 81, 5, 'MOT-017-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(222, 'RAM & Memory Model 1', 'RAM & Memory Modèle 1', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 38183.00, 9.00, 60, 5, 'RAM-018-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(220, 'Motherboards Model 4', 'Motherboards Modèle 4', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 76344.00, 8.00, 94, 5, 'MOT-017-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(219, 'Motherboards Model 3', 'Motherboards Modèle 3', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 54324.00, 11.00, 65, 5, 'MOT-017-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(217, 'Motherboards Model 1', 'Motherboards Modèle 1', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 61624.00, 18.00, 23, 5, 'MOT-017-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(218, 'Motherboards Model 2', 'Motherboards Modèle 2', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 48440.00, 8.00, 61, 5, 'MOT-017-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(215, 'AMD RX 7800 XT', 'AMD RX 7800 XT', 'Excellent 1440p performance', 'Excellente performance 1440p', '[]', '[]', 'Excellent 1440p performance', 'Excellente performance 1440p', 16, NULL, 64999.00, 20.00, 48, 5, 'GRA-016-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(216, 'NVIDIA RTX 4060 Ti', 'NVIDIA RTX 4060 Ti', 'Mainstream gaming GPU', 'GPU gaming grand public', '[]', '[]', 'Mainstream gaming GPU', 'GPU gaming grand public', 16, NULL, 49999.00, 8.00, 55, 5, 'GRA-016-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(213, 'AMD Radeon RX 7900 XTX', 'AMD Radeon RX 7900 XTX', 'High-end RDNA 3 GPU', 'GPU RDNA 3 haut de gamme', '[]', '[]', 'High-end RDNA 3 GPU', 'GPU RDNA 3 haut de gamme', 16, NULL, 129999.00, 13.00, 75, 5, 'GRA-016-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(214, 'NVIDIA RTX 4070 Ti', 'NVIDIA RTX 4070 Ti', 'Premium 1440p gaming', 'Gaming 1440p premium', '[]', '[]', 'Premium 1440p gaming', 'Gaming 1440p premium', 16, NULL, 89999.00, 6.00, 57, 5, 'GRA-016-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(211, 'Intel Core i5-14600K', 'Intel Core i5-14600K', 'Excellent mid-range processor', 'Excellent processeur milieu de gamme', '[]', '[]', 'Excellent mid-range processor', 'Excellent processeur milieu de gamme', 15, NULL, 29999.00, 26.00, 15, 5, 'PRO-015-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(212, 'NVIDIA RTX 4090 24GB', 'NVIDIA RTX 4090 24GB', 'Ultimate gaming graphics card', 'Carte graphique gaming ultime', '[]', '[]', 'Ultimate gaming graphics card', 'Carte graphique gaming ultime', 16, NULL, 199999.00, 12.00, 56, 5, 'GRA-016-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(209, 'Intel Core i7-14700K', 'Intel Core i7-14700K', 'High-performance gaming CPU', 'CPU gaming haute performance', '[]', '[]', 'High-performance gaming CPU', 'CPU gaming haute performance', 15, NULL, 44999.00, 8.00, 80, 5, 'PRO-015-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(210, 'AMD Ryzen 7 7800X3D', 'AMD Ryzen 7 7800X3D', 'Gaming-optimized with 3D V-Cache', 'Optimisé gaming avec 3D V-Cache', '[]', '[]', 'Gaming-optimized with 3D V-Cache', 'Optimisé gaming avec 3D V-Cache', 15, NULL, 49999.00, 5.00, 88, 5, 'PRO-015-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(207, 'Intel Core i9-14900K', 'Processeur Intel Core i9-14900K', '24-core flagship processor', 'Processeur phare 24 cœurs', '[]', '[]', '24-core flagship processor', 'Processeur phare 24 cœurs', 15, NULL, 64999.00, 16.00, 80, 5, 'PRO-015-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(208, 'AMD Ryzen 9 7950X', 'AMD Ryzen 9 7950X', '16-core Zen 4 processor', 'Processeur Zen 4 16 cœurs', '[]', '[]', '16-core Zen 4 processor', 'Processeur Zen 4 16 cœurs', 15, NULL, 69999.00, 15.00, 43, 5, 'PRO-015-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(206, 'E-Readers & Tablets Model 5', 'E-Readers & Tablets Modèle 5', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 69344.00, 23.00, 43, 5, 'ERE-014-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(205, 'E-Readers & Tablets Model 4', 'E-Readers & Tablets Modèle 4', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 43732.00, 26.00, 40, 5, 'ERE-014-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(203, 'E-Readers & Tablets Model 2', 'E-Readers & Tablets Modèle 2', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 30824.00, 12.00, 82, 5, 'ERE-014-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(204, 'E-Readers & Tablets Model 3', 'E-Readers & Tablets Modèle 3', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 22156.00, 21.00, 43, 5, 'ERE-014-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(202, 'E-Readers & Tablets Model 1', 'E-Readers & Tablets Modèle 1', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 28955.00, 17.00, 26, 5, 'ERE-014-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0);
INSERT INTO `products` (`id`, `name_en`, `name_fr`, `description_en`, `description_fr`, `technical_specs_en`, `technical_specs_fr`, `short_description_en`, `short_description_fr`, `category_id`, `brand_id`, `price`, `discount_percentage`, `stock_quantity`, `min_stock_quantity`, `sku`, `barcode`, `weight`, `dimensions`, `video_url`, `is_active`, `is_featured`, `is_new_arrival`, `is_best_seller`, `views_count`, `rating`, `rating_count`, `created_at`, `updated_at`, `low_stock_threshold`, `flash_sale_badge`) VALUES
(201, 'Wearables Model 5', 'Wearables Modèle 5', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 150930.00, 9.00, 91, 5, 'WEA-013-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(199, 'Wearables Model 3', 'Wearables Modèle 3', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 170016.00, 11.00, 39, 5, 'WEA-013-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(200, 'Wearables Model 4', 'Wearables Modèle 4', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 138547.00, 10.00, 93, 5, 'WEA-013-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(198, 'Wearables Model 2', 'Wearables Modèle 2', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 160371.00, 11.00, 10, 5, 'WEA-013-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(196, 'Drones Model 5', 'Drones Modèle 5', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 111518.00, 6.00, 82, 5, 'DRO-012-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(197, 'Wearables Model 1', 'Wearables Modèle 1', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 159046.00, 29.00, 64, 5, 'WEA-013-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(195, 'Drones Model 4', 'Drones Modèle 4', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 129124.00, 26.00, 15, 5, 'DRO-012-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(193, 'Drones Model 2', 'Drones Modèle 2', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 113490.00, 15.00, 30, 5, 'DRO-012-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(194, 'Drones Model 3', 'Drones Modèle 3', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 86324.00, 12.00, 91, 5, 'DRO-012-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(191, 'GoPro Hero 12 Black', 'GoPro Hero 12 Black', 'Action camera with 5.3K video', 'Caméra d\'action avec vidéo 5.3K', '[]', '[]', 'Action camera with 5.3K video', 'Caméra d\'action avec vidéo 5.3K', 11, NULL, 44999.00, 16.00, 33, 5, 'CAM-011-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-21 12:39:42', 5, 0),
(192, 'Drones Model 1', 'Drones Modèle 1', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 92345.00, 13.00, 56, 5, 'DRO-012-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(190, 'Fujifilm X-T4', 'Fujifilm X-T4', 'APS-C flagship with IBIS', 'Modèle phare APS-C avec stabilisation', '[]', '[]', 'APS-C flagship with IBIS', 'Modèle phare APS-C avec stabilisation', 11, NULL, 169999.00, 18.00, 33, 5, 'CAM-011-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(188, 'Sony A7 IV Body', 'Sony A7 IV Boîtier', 'Versatile hybrid camera', 'Appareil photo hybride polyvalent', '[]', '[]', 'Versatile hybrid camera', 'Appareil photo hybride polyvalent', 11, NULL, 279999.00, 27.00, 74, 5, 'CAM-011-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(189, 'Nikon Z6 II Kit', 'Kit Nikon Z6 II', 'Advanced mirrorless with dual processors', 'Sans miroir avancé avec double processeur', '[]', '[]', 'Advanced mirrorless with dual processors', 'Sans miroir avancé avec double processeur', 11, NULL, 189999.00, 19.00, 100, 5, 'CAM-011-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(186, 'Hisense 32\" HD Ready TV', 'TV Hisense HD Ready 32\"', 'Compact HD television', 'Télévision HD compacte', '[]', '[]', 'Compact HD television', 'Télévision HD compacte', 10, NULL, 24999.00, 23.00, 53, 5, 'TEL-010-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(187, 'Canon EOS R6 Mirrorless', 'Canon EOS R6 Sans Miroir', 'Professional full-frame mirrorless camera', 'Appareil photo sans miroir plein format professionnel', '[]', '[]', 'Professional full-frame mirrorless camera', 'Appareil photo sans miroir plein format professionnel', 11, NULL, 249999.00, 20.00, 84, 5, 'CAM-011-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(184, 'Sony Bravia 50\" LED 4K', 'TV Sony Bravia LED 4K 50\"', 'Excellent picture quality with HDR', 'Excellente qualité d\'image avec HDR', '[]', '[]', 'Excellent picture quality with HDR', 'Excellente qualité d\'image avec HDR', 10, NULL, 69999.00, 30.00, 9, 5, 'TEL-010-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(185, 'TCL 43\" Android TV', 'TV TCL Android 43\"', 'Budget-friendly smart TV', 'TV intelligente économique', '[]', '[]', 'Budget-friendly smart TV', 'TV intelligente économique', 10, NULL, 39999.00, 28.00, 81, 5, 'TEL-010-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(182, 'Samsung 55\" QLED 4K Smart TV', 'TV Samsung QLED 4K 55\"', 'High-end QLED display with quantum dot technology', 'Écran QLED haut de gamme avec technologie quantum dot', '[]', '[]', 'High-end QLED display with quantum dot technology', 'Écran QLED haut de gamme avec technologie quantum dot', 10, NULL, 89999.00, 24.00, 84, 5, 'TEL-010-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(183, 'LG 65\" OLED C3 Smart TV', 'TV LG OLED C3 65\"', 'Premium OLED with perfect blacks', 'OLED premium avec noirs parfaits', '[]', '[]', 'Premium OLED with perfect blacks', 'OLED premium avec noirs parfaits', 10, NULL, 129999.00, 25.00, 93, 5, 'TEL-010-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(424, 'ANBERNIC RG 35XXPro', '', '<p><img src=\"uploads/medias/editor/c8ffe9a587b126f152ed3d89a146b445.webp\" style=\"width: 100%;\"><br></p>', NULL, '[]', NULL, 'ANBERNIC RG35XX Pro Handheld Game Console 2025 Portable Retro Gaming Console', NULL, 23, NULL, 3500.00, 0.00, 9, 5, 'GAME-CONS-0001', NULL, 0.00, '', NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-25 04:32:04', '2025-12-25 04:36:58', 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `products_backup_20251222_051721`
--

DROP TABLE IF EXISTS `products_backup_20251222_051721`;
CREATE TABLE IF NOT EXISTS `products_backup_20251222_051721` (
  `id` int NOT NULL DEFAULT '0',
  `name_en` varchar(255) NOT NULL,
  `name_fr` varchar(255) NOT NULL,
  `description_en` text,
  `description_fr` text,
  `technical_specs_en` json DEFAULT NULL,
  `technical_specs_fr` json DEFAULT NULL,
  `short_description_en` varchar(500) DEFAULT NULL,
  `short_description_fr` varchar(500) DEFAULT NULL,
  `category_id` int NOT NULL,
  `brand_id` int DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `final_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int DEFAULT '0',
  `min_stock_quantity` int DEFAULT '5',
  `sku` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_new_arrival` tinyint(1) DEFAULT '0',
  `is_best_seller` tinyint(1) DEFAULT '0',
  `views_count` int DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `rating_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `low_stock_threshold` int DEFAULT '5',
  `flash_sale_badge` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products_backup_20251222_051721`
--

INSERT INTO `products_backup_20251222_051721` (`id`, `name_en`, `name_fr`, `description_en`, `description_fr`, `technical_specs_en`, `technical_specs_fr`, `short_description_en`, `short_description_fr`, `category_id`, `brand_id`, `price`, `discount_percentage`, `final_price`, `stock_quantity`, `min_stock_quantity`, `sku`, `barcode`, `weight`, `dimensions`, `video_url`, `is_active`, `is_featured`, `is_new_arrival`, `is_best_seller`, `views_count`, `rating`, `rating_count`, `created_at`, `updated_at`, `low_stock_threshold`, `flash_sale_badge`) VALUES
(421, 'AYANEO Pocket AIR Mini', 'AYANEO Pocket AIR Mini', '<p><img src=\"uploads/medias/products/421/9b8619251a19057cff70779273e95aa6.jpg\" style=\"width: 100%;\"><b><u>AYANEO Pocket AIR Mini Retro Android Handheld Game Console - MTK Helio G90T Octa-Core.</u></b></p><p><b><u><br></u></b></p><p></p>', '<p>AYANEO Pocket AIR Mini Retro Android Handheld Game Console - MTK Helio G90T Octa-Core.</p>', '[]', '[]', 'AYANEO Pocket AIR Mini Retro Android Handheld Game Console - MTK Helio G90T Octa-Core.', 'AYANEO Pocket AIR Mini Retro Android Handheld Game Console - MTK Helio G90T Octa-Core.', 11, NULL, 6000.00, 5.00, 5700.00, 42, 5, 'DIG-057-005', '', 0.50, '32x24x45', 'uploads/medias/products/421/vid_1766374435_6948bc2307b40.mp4', 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-22 03:50:25', 5, 0),
(420, 'Digital Accessories Model 4', 'Digital Accessories Modèle 4', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', 57, NULL, 136618.00, 4.00, 131153.28, 35, 5, 'DIG-057-004', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-22 03:49:37', 5, 0),
(418, 'Digital Accessories Model 2', 'Digital Accessories Modèle 2', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', 57, NULL, 123184.00, 11.00, 109633.76, 18, 5, 'DIG-057-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-21 04:01:57', 5, 0),
(417, 'Digital Accessories Model 1', 'Digital Accessories Modèle 1', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Digital Accessories with advanced features', 'Digital Accessories de haute qualité avec fonctionnalités avancées', 57, NULL, 109728.00, 28.00, 79004.16, 100, 5, 'DIG-057-001', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:03:12', 5, 0),
(415, 'Portable Projectors Model 4', 'Portable Projectors Modèle 4', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 64986.00, 11.00, 57837.54, 34, 5, 'POR-056-004', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:03:10', 5, 0),
(416, 'Portable Projectors Model 5', 'Portable Projectors Modèle 5', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 46819.00, 10.00, 42137.10, 15, 5, 'POR-056-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:22', 5, 0),
(414, 'Portable Projectors Model 3', 'Portable Projectors Modèle 3', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 30065.00, 4.00, 28862.40, 90, 5, 'POR-056-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(413, 'Portable Projectors Model 2', 'Portable Projectors Modèle 2', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 41662.00, 23.00, 32079.74, 40, 5, 'POR-056-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(411, 'Novelty Tech Model 5', 'Novelty Tech Modèle 5', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 145191.00, 4.00, 139383.36, 71, 5, 'NOV-055-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(412, 'Portable Projectors Model 1', 'Portable Projectors Modèle 1', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Portable Projectors with advanced features', 'Portable Projectors de haute qualité avec fonctionnalités avancées', 56, NULL, 41117.00, 18.00, 33715.94, 42, 5, 'POR-056-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:25', 5, 0),
(410, 'Novelty Tech Model 4', 'Novelty Tech Modèle 4', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 176260.00, 3.00, 170972.20, 15, 5, 'NOV-055-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:25', 5, 0),
(409, 'Novelty Tech Model 3', 'Novelty Tech Modèle 3', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 154609.00, 14.00, 132963.74, 57, 5, 'NOV-055-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(407, 'Novelty Tech Model 1', 'Novelty Tech Modèle 1', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 149862.00, 8.00, 137873.04, 38, 5, 'NOV-055-001', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:55', 5, 0),
(408, 'Novelty Tech Model 2', 'Novelty Tech Modèle 2', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Novelty Tech with advanced features', 'Novelty Tech de haute qualité avec fonctionnalités avancées', 55, NULL, 131144.00, 3.00, 127209.68, 65, 5, 'NOV-055-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(406, 'Fitness Trackers Model 5', 'Fitness Trackers Modèle 5', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 91516.00, 20.00, 73212.80, 25, 5, 'FIT-054-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(405, 'Fitness Trackers Model 4', 'Fitness Trackers Modèle 4', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 56075.00, 19.00, 45420.75, 37, 5, 'FIT-054-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(403, 'Fitness Trackers Model 2', 'Fitness Trackers Modèle 2', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 49199.00, 14.00, 42311.14, 7, 5, 'FIT-054-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(404, 'Fitness Trackers Model 3', 'Fitness Trackers Modèle 3', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 57675.00, 30.00, 40372.50, 50, 5, 'FIT-054-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(402, 'Fitness Trackers Model 1', 'Fitness Trackers Modèle 1', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Fitness Trackers with advanced features', 'Fitness Trackers de haute qualité avec fonctionnalités avancées', 54, NULL, 52993.00, 18.00, 43454.26, 63, 5, 'FIT-054-001', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:56', 5, 0),
(401, 'Smart Home Devices Model 5', 'Smart Home Devices Modèle 5', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 44237.00, 0.00, 44237.00, 34, 5, 'SMA-053-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(399, 'Smart Home Devices Model 3', 'Smart Home Devices Modèle 3', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 54633.00, 4.00, 52447.68, 68, 5, 'SMA-053-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(400, 'Smart Home Devices Model 4', 'Smart Home Devices Modèle 4', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 22706.00, 9.00, 20662.46, 39, 5, 'SMA-053-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(398, 'Smart Home Devices Model 2', 'Smart Home Devices Modèle 2', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 32948.00, 8.00, 30312.16, 73, 5, 'SMA-053-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(396, 'DJ & Studio Equipment Model 5', 'DJ & Studio Equipment Modèle 5', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 13266.00, 21.00, 10480.14, 43, 5, 'DJ--052-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(397, 'Smart Home Devices Model 1', 'Smart Home Devices Modèle 1', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Smart Home Devices with advanced features', 'Smart Home Devices de haute qualité avec fonctionnalités avancées', 53, NULL, 28619.00, 9.00, 26043.29, 42, 5, 'SMA-053-001', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:03:00', 5, 0),
(395, 'DJ & Studio Equipment Model 4', 'DJ & Studio Equipment Modèle 4', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 8075.00, 26.00, 5975.50, 26, 5, 'DJ--052-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(394, 'DJ & Studio Equipment Model 3', 'DJ & Studio Equipment Modèle 3', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 3065.00, 28.00, 2206.80, 38, 5, 'DJ--052-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(392, 'DJ & Studio Equipment Model 1', 'DJ & Studio Equipment Modèle 1', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 13314.00, 25.00, 9985.50, 24, 5, 'DJ--052-001', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:02:59', 5, 0),
(393, 'DJ & Studio Equipment Model 2', 'DJ & Studio Equipment Modèle 2', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality DJ & Studio Equipment with advanced features', 'DJ & Studio Equipment de haute qualité avec fonctionnalités avancées', 52, NULL, 29321.00, 9.00, 26682.11, 24, 5, 'DJ--052-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(391, 'Soundbars Model 5', 'Soundbars Modèle 5', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 101528.00, 12.00, 89344.64, 61, 5, 'SOU-051-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(389, 'Soundbars Model 3', 'Soundbars Modèle 3', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 104793.00, 23.00, 80690.61, 28, 5, 'SOU-051-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(390, 'Soundbars Model 4', 'Soundbars Modèle 4', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 94368.00, 21.00, 74550.72, 60, 5, 'SOU-051-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(387, 'Soundbars Model 1', 'Soundbars Modèle 1', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 77206.00, 6.00, 72573.64, 46, 5, 'SOU-051-001', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-19 05:03:02', 5, 0),
(388, 'Soundbars Model 2', 'Soundbars Modèle 2', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Soundbars with advanced features', 'Soundbars de haute qualité avec fonctionnalités avancées', 51, NULL, 73804.00, 2.00, 72327.92, 79, 5, 'SOU-051-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(386, 'Speakers Model 5', 'Speakers Modèle 5', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 1876.00, 11.00, 1669.64, 100, 5, 'SPE-050-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(384, 'Speakers Model 3', 'Speakers Modèle 3', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 15260.00, 27.00, 11139.80, 81, 5, 'SPE-050-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(385, 'Speakers Model 4', 'Speakers Modèle 4', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 32881.00, 11.00, 29264.09, 13, 5, 'SPE-050-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(383, 'Speakers Model 2', 'Speakers Modèle 2', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 22791.00, 12.00, 20056.08, 68, 5, 'SPE-050-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(381, 'Earbuds Model 5', 'Earbuds Modèle 5', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 131137.00, 24.00, 99664.12, 79, 5, 'EAR-049-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(382, 'Speakers Model 1', 'Speakers Modèle 1', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Speakers with advanced features', 'Speakers de haute qualité avec fonctionnalités avancées', 50, NULL, 31089.00, 7.00, 28912.77, 69, 5, 'SPE-050-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(380, 'Earbuds Model 4', 'Earbuds Modèle 4', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 74848.00, 18.00, 61375.36, 47, 5, 'EAR-049-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(378, 'Earbuds Model 2', 'Earbuds Modèle 2', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 91272.00, 30.00, 63890.40, 42, 5, 'EAR-049-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(379, 'Earbuds Model 3', 'Earbuds Modèle 3', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 72838.00, 11.00, 64825.82, 16, 5, 'EAR-049-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(376, 'JBL Tune 770NC', 'JBL Tune 770NC', 'Budget ANC headphones', 'Casque ANC économique', '[]', '[]', 'Budget ANC headphones', 'Casque ANC économique', 48, NULL, 14999.00, 21.00, 11849.21, 85, 5, 'HEA-048-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(377, 'Earbuds Model 1', 'Earbuds Modèle 1', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Earbuds with advanced features', 'Earbuds de haute qualité avec fonctionnalités avancées', 49, NULL, 87728.00, 17.00, 72814.24, 8, 5, 'EAR-049-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(374, 'Apple AirPods Max', 'Apple AirPods Max', 'Spatial audio excellence', 'Excellence audio spatial', '[]', '[]', 'Spatial audio excellence', 'Excellence audio spatial', 48, NULL, 59999.00, 16.00, 50399.16, 21, 5, 'HEA-048-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(375, 'Sennheiser Momentum 4', 'Sennheiser Momentum 4', 'Audiophile wireless headphones', 'Casque sans fil audiophile', '[]', '[]', 'Audiophile wireless headphones', 'Casque sans fil audiophile', 48, NULL, 34999.00, 17.00, 29049.17, 54, 5, 'HEA-048-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(372, 'Sony WH-1000XM5', 'Sony WH-1000XM5', 'Industry-leading noise cancellation', 'Réduction de bruit leader du marché', '[]', '[]', 'Industry-leading noise cancellation', 'Réduction de bruit leader du marché', 48, NULL, 39999.00, 9.00, 36399.09, 84, 5, 'HEA-048-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(373, 'Bose QuietComfort Ultra', 'Bose QuietComfort Ultra', 'Premium comfort and ANC', 'Confort premium et ANC', '[]', '[]', 'Premium comfort and ANC', 'Confort premium et ANC', 48, NULL, 44999.00, 4.00, 43199.04, 81, 5, 'HEA-048-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(370, 'NAS & Servers Model 4', 'NAS & Servers Modèle 4', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 105748.00, 28.00, 76138.56, 14, 5, 'NAS-047-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(371, 'NAS & Servers Model 5', 'NAS & Servers Modèle 5', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 52434.00, 22.00, 40898.52, 11, 5, 'NAS-047-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(369, 'NAS & Servers Model 3', 'NAS & Servers Modèle 3', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 93315.00, 1.00, 92381.85, 65, 5, 'NAS-047-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(368, 'NAS & Servers Model 2', 'NAS & Servers Modèle 2', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 66868.00, 15.00, 56837.80, 96, 5, 'NAS-047-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(366, 'Network Cables Model 5', 'Network Cables Modèle 5', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 143963.00, 11.00, 128127.07, 92, 5, 'NET-046-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(367, 'NAS & Servers Model 1', 'NAS & Servers Modèle 1', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality NAS & Servers with advanced features', 'NAS & Servers de haute qualité avec fonctionnalités avancées', 47, NULL, 72730.00, 17.00, 60365.90, 78, 5, 'NAS-047-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(365, 'Network Cables Model 4', 'Network Cables Modèle 4', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 156864.00, 16.00, 131765.76, 28, 5, 'NET-046-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(363, 'Network Cables Model 2', 'Network Cables Modèle 2', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 153166.00, 2.00, 150102.68, 71, 5, 'NET-046-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(364, 'Network Cables Model 3', 'Network Cables Modèle 3', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 165094.00, 12.00, 145282.72, 15, 5, 'NET-046-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(362, 'Network Cables Model 1', 'Network Cables Modèle 1', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Network Cables with advanced features', 'Network Cables de haute qualité avec fonctionnalités avancées', 46, NULL, 148358.00, 6.00, 139456.52, 53, 5, 'NET-046-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(360, 'Wi-Fi Extenders Model 4', 'Wi-Fi Extenders Modèle 4', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 72317.00, 18.00, 59299.94, 89, 5, 'WIF-045-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(361, 'Wi-Fi Extenders Model 5', 'Wi-Fi Extenders Modèle 5', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 68906.00, 26.00, 50990.44, 53, 5, 'WIF-045-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(359, 'Wi-Fi Extenders Model 3', 'Wi-Fi Extenders Modèle 3', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 67602.00, 7.00, 62869.86, 5, 5, 'WIF-045-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(358, 'Wi-Fi Extenders Model 2', 'Wi-Fi Extenders Modèle 2', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 48259.00, 25.00, 36194.25, 91, 5, 'WIF-045-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(356, 'Switches Model 5', 'Switches Modèle 5', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 89084.00, 0.00, 89084.00, 74, 5, 'SWI-044-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(357, 'Wi-Fi Extenders Model 1', 'Wi-Fi Extenders Modèle 1', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wi-Fi Extenders with advanced features', 'Wi-Fi Extenders de haute qualité avec fonctionnalités avancées', 45, NULL, 53252.00, 8.00, 48991.84, 45, 5, 'WIF-045-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(355, 'Switches Model 4', 'Switches Modèle 4', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 111972.00, 20.00, 89577.60, 60, 5, 'SWI-044-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(353, 'Switches Model 2', 'Switches Modèle 2', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 70536.00, 27.00, 51491.28, 89, 5, 'SWI-044-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(354, 'Switches Model 3', 'Switches Modèle 3', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 90729.00, 7.00, 84377.97, 51, 5, 'SWI-044-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(352, 'Switches Model 1', 'Switches Modèle 1', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Switches with advanced features', 'Switches de haute qualité avec fonctionnalités avancées', 44, NULL, 72717.00, 15.00, 61809.45, 67, 5, 'SWI-044-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(350, 'Routers & Modems Model 4', 'Routers & Modems Modèle 4', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 160329.00, 6.00, 150709.26, 94, 5, 'ROU-043-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(351, 'Routers & Modems Model 5', 'Routers & Modems Modèle 5', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 120397.00, 25.00, 90297.75, 97, 5, 'ROU-043-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(349, 'Routers & Modems Model 3', 'Routers & Modems Modèle 3', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 167989.00, 3.00, 162949.33, 48, 5, 'ROU-043-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(348, 'Routers & Modems Model 2', 'Routers & Modems Modèle 2', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 160227.00, 10.00, 144204.30, 95, 5, 'ROU-043-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(346, 'Phone Accessories Model 5', 'Phone Accessories Modèle 5', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 96897.00, 0.00, 96897.00, 79, 5, 'PHO-042-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(347, 'Routers & Modems Model 1', 'Routers & Modems Modèle 1', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Routers & Modems with advanced features', 'Routers & Modems de haute qualité avec fonctionnalités avancées', 43, NULL, 138951.00, 7.00, 129224.43, 8, 5, 'ROU-043-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(345, 'Phone Accessories Model 4', 'Phone Accessories Modèle 4', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 97768.00, 23.00, 75281.36, 65, 5, 'PHO-042-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(344, 'Phone Accessories Model 3', 'Phone Accessories Modèle 3', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 95802.00, 26.00, 70893.48, 53, 5, 'PHO-042-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(342, 'Phone Accessories Model 1', 'Phone Accessories Modèle 1', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 111654.00, 9.00, 101605.14, 82, 5, 'PHO-042-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(343, 'Phone Accessories Model 2', 'Phone Accessories Modèle 2', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Phone Accessories with advanced features', 'Phone Accessories de haute qualité avec fonctionnalités avancées', 42, NULL, 108012.00, 0.00, 108012.00, 87, 5, 'PHO-042-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(341, 'Refurbished Phones Model 5', 'Refurbished Phones Modèle 5', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 60323.00, 2.00, 59116.54, 29, 5, 'REF-041-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(340, 'Refurbished Phones Model 4', 'Refurbished Phones Modèle 4', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 64882.00, 10.00, 58393.80, 59, 5, 'REF-041-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(339, 'Refurbished Phones Model 3', 'Refurbished Phones Modèle 3', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 70964.00, 7.00, 65996.52, 27, 5, 'REF-041-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(337, 'Refurbished Phones Model 1', 'Refurbished Phones Modèle 1', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 73197.00, 13.00, 63681.39, 38, 5, 'REF-041-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(338, 'Refurbished Phones Model 2', 'Refurbished Phones Modèle 2', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Refurbished Phones with advanced features', 'Refurbished Phones de haute qualité avec fonctionnalités avancées', 41, NULL, 60856.00, 12.00, 53553.28, 65, 5, 'REF-041-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(336, 'Feature Phones Model 5', 'Feature Phones Modèle 5', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 112837.00, 3.00, 109451.89, 23, 5, 'FEA-040-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(335, 'Feature Phones Model 4', 'Feature Phones Modèle 4', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 91510.00, 15.00, 77783.50, 8, 5, 'FEA-040-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(333, 'Feature Phones Model 2', 'Feature Phones Modèle 2', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 118406.00, 12.00, 104197.28, 93, 5, 'FEA-040-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(334, 'Feature Phones Model 3', 'Feature Phones Modèle 3', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 122362.00, 6.00, 115020.28, 38, 5, 'FEA-040-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(332, 'Feature Phones Model 1', 'Feature Phones Modèle 1', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Feature Phones with advanced features', 'Feature Phones de haute qualité avec fonctionnalités avancées', 40, NULL, 110614.00, 9.00, 100658.74, 19, 5, 'FEA-040-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(331, 'iOS Devices Model 5', 'iOS Devices Modèle 5', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 50157.00, 3.00, 48652.29, 25, 5, 'IOS-039-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(329, 'iOS Devices Model 3', 'iOS Devices Modèle 3', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 61471.00, 30.00, 43029.70, 66, 5, 'IOS-039-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(330, 'iOS Devices Model 4', 'iOS Devices Modèle 4', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 36250.00, 21.00, 28637.50, 15, 5, 'IOS-039-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(328, 'iOS Devices Model 2', 'iOS Devices Modèle 2', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 54744.00, 30.00, 38320.80, 98, 5, 'IOS-039-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(326, 'Samsung Galaxy A54', 'Samsung Galaxy A54', 'Mid-range with great features', 'Milieu de gamme avec bonnes fonctionnalités', '[]', '[]', 'Mid-range with great features', 'Milieu de gamme avec bonnes fonctionnalités', 38, NULL, 44999.00, 15.00, 38249.15, 68, 5, 'AND-038-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(327, 'iOS Devices Model 1', 'iOS Devices Modèle 1', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality iOS Devices with advanced features', 'iOS Devices de haute qualité avec fonctionnalités avancées', 39, NULL, 51325.00, 1.00, 50811.75, 69, 5, 'IOS-039-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(324, 'OnePlus 12', 'OnePlus 12', 'Fast charging flagship', 'Modèle phare charge rapide', '[]', '[]', 'Fast charging flagship', 'Modèle phare charge rapide', 38, NULL, 89999.00, 30.00, 62999.30, 26, 5, 'AND-038-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(325, 'Xiaomi 14 Pro', 'Xiaomi 14 Pro', 'Premium camera phone', 'Téléphone photo premium', '[]', '[]', 'Premium camera phone', 'Téléphone photo premium', 38, NULL, 79999.00, 28.00, 57599.28, 23, 5, 'AND-038-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(322, 'Samsung Galaxy S24 Ultra', 'Samsung Galaxy S24 Ultra', 'Flagship with S Pen', 'Modèle phare avec S Pen', '[]', '[]', 'Flagship with S Pen', 'Modèle phare avec S Pen', 38, NULL, 149999.00, 27.00, 109499.27, 67, 5, 'AND-038-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(323, 'Google Pixel 8 Pro', 'Google Pixel 8 Pro', 'Pure Android experience', 'Expérience Android pure', '[]', '[]', 'Pure Android experience', 'Expérience Android pure', 38, NULL, 119999.00, 14.00, 103199.14, 58, 5, 'AND-038-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(321, 'Styluses & Pens Model 5', 'Styluses & Pens Modèle 5', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 182900.00, 30.00, 128030.00, 33, 5, 'STY-037-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(319, 'Styluses & Pens Model 3', 'Styluses & Pens Modèle 3', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 168861.00, 19.00, 136777.41, 26, 5, 'STY-037-003', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-15 15:27:24', 5, 0),
(320, 'Styluses & Pens Model 4', 'Styluses & Pens Modèle 4', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 173719.00, 30.00, 121603.30, 47, 5, 'STY-037-004', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-15 15:27:23', 5, 0),
(318, 'Styluses & Pens Model 2', 'Styluses & Pens Modèle 2', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 155623.00, 11.00, 138504.47, 47, 5, 'STY-037-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(317, 'Styluses & Pens Model 1', 'Styluses & Pens Modèle 1', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Styluses & Pens with advanced features', 'Styluses & Pens de haute qualité avec fonctionnalités avancées', 37, NULL, 151672.00, 12.00, 133471.36, 60, 5, 'STY-037-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(315, 'Screen Protectors Model 4', 'Screen Protectors Modèle 4', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 151577.00, 6.00, 142482.38, 67, 5, 'SCR-036-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(316, 'Screen Protectors Model 5', 'Screen Protectors Modèle 5', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 145997.00, 0.00, 145997.00, 97, 5, 'SCR-036-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(314, 'Screen Protectors Model 3', 'Screen Protectors Modèle 3', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 146355.00, 12.00, 128792.40, 64, 5, 'SCR-036-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0);
INSERT INTO `products_backup_20251222_051721` (`id`, `name_en`, `name_fr`, `description_en`, `description_fr`, `technical_specs_en`, `technical_specs_fr`, `short_description_en`, `short_description_fr`, `category_id`, `brand_id`, `price`, `discount_percentage`, `final_price`, `stock_quantity`, `min_stock_quantity`, `sku`, `barcode`, `weight`, `dimensions`, `video_url`, `is_active`, `is_featured`, `is_new_arrival`, `is_best_seller`, `views_count`, `rating`, `rating_count`, `created_at`, `updated_at`, `low_stock_threshold`, `flash_sale_badge`) VALUES
(313, 'Screen Protectors Model 2', 'Screen Protectors Modèle 2', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 132517.00, 28.00, 95412.24, 53, 5, 'SCR-036-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(311, 'Cases & Covers Model 5', 'Cases & Covers Modèle 5', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 25618.00, 25.00, 19213.50, 58, 5, 'CAS-035-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(312, 'Screen Protectors Model 1', 'Screen Protectors Modèle 1', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Screen Protectors with advanced features', 'Screen Protectors de haute qualité avec fonctionnalités avancées', 36, NULL, 128442.00, 3.00, 124588.74, 62, 5, 'SCR-036-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(310, 'Cases & Covers Model 4', 'Cases & Covers Modèle 4', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 17996.00, 8.00, 16556.32, 24, 5, 'CAS-035-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(308, 'Cases & Covers Model 2', 'Cases & Covers Modèle 2', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 18682.00, 6.00, 17561.08, 58, 5, 'CAS-035-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(309, 'Cases & Covers Model 3', 'Cases & Covers Modèle 3', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 40218.00, 10.00, 36196.20, 85, 5, 'CAS-035-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(307, 'Cases & Covers Model 1', 'Cases & Covers Modèle 1', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cases & Covers with advanced features', 'Cases & Covers de haute qualité avec fonctionnalités avancées', 35, NULL, 34713.00, 12.00, 30547.44, 37, 5, 'CAS-035-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(306, 'Chargers & Power Banks Model 5', 'Chargers & Power Banks Modèle 5', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 70367.00, 8.00, 64737.64, 71, 5, 'CHA-034-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(304, 'Chargers & Power Banks Model 3', 'Chargers & Power Banks Modèle 3', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 82726.00, 19.00, 67008.06, 13, 5, 'CHA-034-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(305, 'Chargers & Power Banks Model 4', 'Chargers & Power Banks Modèle 4', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 79822.00, 29.00, 56673.62, 88, 5, 'CHA-034-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(303, 'Chargers & Power Banks Model 2', 'Chargers & Power Banks Modèle 2', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 112164.00, 20.00, 89731.20, 68, 5, 'CHA-034-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(302, 'Chargers & Power Banks Model 1', 'Chargers & Power Banks Modèle 1', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Chargers & Power Banks with advanced features', 'Chargers & Power Banks de haute qualité avec fonctionnalités avancées', 34, NULL, 87549.00, 29.00, 62159.79, 96, 5, 'CHA-034-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(300, 'Cables & Adapters Model 4', 'Cables & Adapters Modèle 4', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 75223.00, 12.00, 66196.24, 95, 5, 'CAB-033-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(301, 'Cables & Adapters Model 5', 'Cables & Adapters Modèle 5', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 61467.00, 18.00, 50402.94, 87, 5, 'CAB-033-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(299, 'Cables & Adapters Model 3', 'Cables & Adapters Modèle 3', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 64264.00, 0.00, 64264.00, 28, 5, 'CAB-033-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(297, 'Cables & Adapters Model 1', 'Cables & Adapters Modèle 1', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 66221.00, 8.00, 60923.32, 58, 5, 'CAB-033-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(298, 'Cables & Adapters Model 2', 'Cables & Adapters Modèle 2', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cables & Adapters with advanced features', 'Cables & Adapters de haute qualité avec fonctionnalités avancées', 33, NULL, 85481.00, 1.00, 84626.19, 20, 5, 'CAB-033-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(296, 'Small Appliances Model 5', 'Small Appliances Modèle 5', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, -658.00, 29.00, -467.18, 90, 5, 'SMA-032-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(295, 'Small Appliances Model 4', 'Small Appliances Modèle 4', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, 53948.00, 10.00, 48553.20, 36, 5, 'SMA-032-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(293, 'Small Appliances Model 2', 'Small Appliances Modèle 2', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, 14412.00, 11.00, 12826.68, 6, 5, 'SMA-032-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(294, 'Small Appliances Model 3', 'Small Appliances Modèle 3', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, 14788.00, 30.00, 10351.60, 62, 5, 'SMA-032-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(292, 'Small Appliances Model 1', 'Small Appliances Modèle 1', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Small Appliances with advanced features', 'Small Appliances de haute qualité avec fonctionnalités avancées', 32, NULL, 14791.00, 26.00, 10945.34, 62, 5, 'SMA-032-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(290, 'Vacuum Cleaners Model 4', 'Vacuum Cleaners Modèle 4', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 171649.00, 8.00, 157917.08, 94, 5, 'VAC-031-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(291, 'Vacuum Cleaners Model 5', 'Vacuum Cleaners Modèle 5', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 128483.00, 18.00, 105356.06, 12, 5, 'VAC-031-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(289, 'Vacuum Cleaners Model 3', 'Vacuum Cleaners Modèle 3', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 126482.00, 15.00, 107509.70, 30, 5, 'VAC-031-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(288, 'Vacuum Cleaners Model 2', 'Vacuum Cleaners Modèle 2', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 143679.00, 10.00, 129311.10, 81, 5, 'VAC-031-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(286, 'Climate Control Model 5', 'Climate Control Modèle 5', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 62116.00, 25.00, 46587.00, 93, 5, 'CLI-030-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(287, 'Vacuum Cleaners Model 1', 'Vacuum Cleaners Modèle 1', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Vacuum Cleaners with advanced features', 'Vacuum Cleaners de haute qualité avec fonctionnalités avancées', 31, NULL, 149999.00, 15.00, 127499.15, 48, 5, 'VAC-031-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(285, 'Climate Control Model 4', 'Climate Control Modèle 4', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 60479.00, 2.00, 59269.42, 84, 5, 'CLI-030-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(283, 'Climate Control Model 2', 'Climate Control Modèle 2', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 61019.00, 23.00, 46984.63, 62, 5, 'CLI-030-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(284, 'Climate Control Model 3', 'Climate Control Modèle 3', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 66902.00, 13.00, 58204.74, 78, 5, 'CLI-030-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(282, 'Climate Control Model 1', 'Climate Control Modèle 1', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Climate Control with advanced features', 'Climate Control de haute qualité avec fonctionnalités avancées', 30, NULL, 47142.00, 22.00, 36770.76, 72, 5, 'CLI-030-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(281, 'Laundry Appliances Model 5', 'Laundry Appliances Modèle 5', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 101739.00, 21.00, 80373.81, 22, 5, 'LAU-029-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(280, 'Laundry Appliances Model 4', 'Laundry Appliances Modèle 4', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 74289.00, 9.00, 67602.99, 22, 5, 'LAU-029-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(278, 'Laundry Appliances Model 2', 'Laundry Appliances Modèle 2', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 58905.00, 0.00, 58905.00, 22, 5, 'LAU-029-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(279, 'Laundry Appliances Model 3', 'Laundry Appliances Modèle 3', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 54619.00, 5.00, 51888.05, 45, 5, 'LAU-029-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(277, 'Laundry Appliances Model 1', 'Laundry Appliances Modèle 1', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Laundry Appliances with advanced features', 'Laundry Appliances de haute qualité avec fonctionnalités avancées', 29, NULL, 63424.00, 21.00, 50104.96, 67, 5, 'LAU-029-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(276, 'Kitchen Appliances Model 5', 'Kitchen Appliances Modèle 5', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 35429.00, 20.00, 28343.20, 12, 5, 'KIT-028-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(275, 'Kitchen Appliances Model 4', 'Kitchen Appliances Modèle 4', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 63475.00, 4.00, 60936.00, 95, 5, 'KIT-028-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(273, 'Kitchen Appliances Model 2', 'Kitchen Appliances Modèle 2', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 45571.00, 28.00, 32811.12, 34, 5, 'KIT-028-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(274, 'Kitchen Appliances Model 3', 'Kitchen Appliances Modèle 3', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 60321.00, 14.00, 51876.06, 53, 5, 'KIT-028-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(272, 'Kitchen Appliances Model 1', 'Kitchen Appliances Modèle 1', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Kitchen Appliances with advanced features', 'Kitchen Appliances de haute qualité avec fonctionnalités avancées', 28, NULL, 32508.00, 6.00, 30557.52, 24, 5, 'KIT-028-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(271, 'Gaming Chairs & Desks Model 5', 'Gaming Chairs & Desks Modèle 5', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 73493.00, 3.00, 71288.21, 31, 5, 'GAM-027-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(269, 'Gaming Chairs & Desks Model 3', 'Gaming Chairs & Desks Modèle 3', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 47311.00, 25.00, 35483.25, 54, 5, 'GAM-027-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(270, 'Gaming Chairs & Desks Model 4', 'Gaming Chairs & Desks Modèle 4', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 48554.00, 23.00, 37386.58, 93, 5, 'GAM-027-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(268, 'Gaming Chairs & Desks Model 2', 'Gaming Chairs & Desks Modèle 2', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 69042.00, 13.00, 60066.54, 51, 5, 'GAM-027-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(266, 'VR & AR Gear Model 5', 'VR & AR Gear Modèle 5', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 50562.00, 13.00, 43988.94, 24, 5, 'VR--026-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(267, 'Gaming Chairs & Desks Model 1', 'Gaming Chairs & Desks Modèle 1', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Chairs & Desks with advanced features', 'Gaming Chairs & Desks de haute qualité avec fonctionnalités avancées', 27, NULL, 58413.00, 20.00, 46730.40, 69, 5, 'GAM-027-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(265, 'VR & AR Gear Model 4', 'VR & AR Gear Modèle 4', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 62499.00, 6.00, 58749.06, 91, 5, 'VR--026-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(263, 'VR & AR Gear Model 2', 'VR & AR Gear Modèle 2', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 66703.00, 4.00, 64034.88, 31, 5, 'VR--026-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(264, 'VR & AR Gear Model 3', 'VR & AR Gear Modèle 3', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 83302.00, 1.00, 82468.98, 93, 5, 'VR--026-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(262, 'VR & AR Gear Model 1', 'VR & AR Gear Modèle 1', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality VR & AR Gear with advanced features', 'VR & AR Gear de haute qualité avec fonctionnalités avancées', 26, NULL, 55667.00, 14.00, 47873.62, 69, 5, 'VR--026-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(260, 'Gaming Peripherals Model 4', 'Gaming Peripherals Modèle 4', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 47718.00, 25.00, 35788.50, 55, 5, 'GAM-025-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(261, 'Gaming Peripherals Model 5', 'Gaming Peripherals Modèle 5', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 24435.00, 28.00, 17593.20, 34, 5, 'GAM-025-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(259, 'Gaming Peripherals Model 3', 'Gaming Peripherals Modèle 3', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 46589.00, 0.00, 46589.00, 64, 5, 'GAM-025-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(258, 'Gaming Peripherals Model 2', 'Gaming Peripherals Modèle 2', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 21318.00, 8.00, 19612.56, 75, 5, 'GAM-025-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(256, 'Video Games Model 5', 'Video Games Modèle 5', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 153600.00, 13.00, 133632.00, 37, 5, 'VID-024-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(257, 'Gaming Peripherals Model 1', 'Gaming Peripherals Modèle 1', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Gaming Peripherals with advanced features', 'Gaming Peripherals de haute qualité avec fonctionnalités avancées', 25, NULL, 32706.00, 16.00, 27473.04, 46, 5, 'GAM-025-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(255, 'Video Games Model 4', 'Video Games Modèle 4', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 152347.00, 0.00, 152347.00, 10, 5, 'VID-024-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(253, 'Video Games Model 2', 'Video Games Modèle 2', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 136143.00, 7.00, 126612.99, 73, 5, 'VID-024-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(254, 'Video Games Model 3', 'Video Games Modèle 3', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 136370.00, 13.00, 118641.90, 62, 5, 'VID-024-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(252, 'Video Games Model 1', 'Video Games Modèle 1', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Video Games with advanced features', 'Video Games de haute qualité avec fonctionnalités avancées', 24, NULL, 136927.00, 22.00, 106803.06, 58, 5, 'VID-024-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(250, 'Consoles Model 4', 'Consoles Modèle 4', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 75107.00, 21.00, 59334.53, 61, 5, 'CON-023-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(251, 'Consoles Model 5', 'Consoles Modèle 5', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 66737.00, 28.00, 48050.64, 28, 5, 'CON-023-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(249, 'Consoles Model 3', 'Consoles Modèle 3', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 88606.00, 29.00, 62910.26, 43, 5, 'CON-023-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(247, 'Consoles Model 1', 'Consoles Modèle 1', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 76871.00, 30.00, 53809.70, 36, 5, 'CON-023-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(248, 'Consoles Model 2', 'Consoles Modèle 2', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Consoles with advanced features', 'Consoles de haute qualité avec fonctionnalités avancées', 23, NULL, 96967.00, 24.00, 73694.92, 43, 5, 'CON-023-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(246, 'Cooling Systems Model 5', 'Cooling Systems Modèle 5', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 25005.00, 29.00, 17753.55, 76, 5, 'COO-022-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(244, 'Cooling Systems Model 3', 'Cooling Systems Modèle 3', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 41315.00, 17.00, 34291.45, 20, 5, 'COO-022-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(245, 'Cooling Systems Model 4', 'Cooling Systems Modèle 4', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 26486.00, 5.00, 25161.70, 98, 5, 'COO-022-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(243, 'Cooling Systems Model 2', 'Cooling Systems Modèle 2', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 55054.00, 12.00, 48447.52, 72, 5, 'COO-022-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(241, 'PC Cases Model 5', 'PC Cases Modèle 5', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 134353.00, 8.00, 123604.76, 88, 5, 'PC--021-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(242, 'Cooling Systems Model 1', 'Cooling Systems Modèle 1', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Cooling Systems with advanced features', 'Cooling Systems de haute qualité avec fonctionnalités avancées', 22, NULL, 41120.00, 8.00, 37830.40, 8, 5, 'COO-022-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(240, 'PC Cases Model 4', 'PC Cases Modèle 4', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 160755.00, 8.00, 147894.60, 11, 5, 'PC--021-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(238, 'PC Cases Model 2', 'PC Cases Modèle 2', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 128523.00, 9.00, 116955.93, 37, 5, 'PC--021-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(239, 'PC Cases Model 3', 'PC Cases Modèle 3', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 158416.00, 1.00, 156831.84, 75, 5, 'PC--021-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(237, 'PC Cases Model 1', 'PC Cases Modèle 1', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality PC Cases with advanced features', 'PC Cases de haute qualité avec fonctionnalités avancées', 21, NULL, 138832.00, 16.00, 116618.88, 41, 5, 'PC--021-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(235, 'Power Supplies Model 4', 'Power Supplies Modèle 4', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 85729.00, 22.00, 66868.62, 11, 5, 'POW-020-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(236, 'Power Supplies Model 5', 'Power Supplies Modèle 5', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 74347.00, 26.00, 55016.78, 43, 5, 'POW-020-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(234, 'Power Supplies Model 3', 'Power Supplies Modèle 3', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 51599.00, 11.00, 45923.11, 100, 5, 'POW-020-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(233, 'Power Supplies Model 2', 'Power Supplies Modèle 2', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 68309.00, 3.00, 66259.73, 54, 5, 'POW-020-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(231, 'Storage (SSD/HDD) Model 5', 'Storage (SSD/HDD) Modèle 5', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 59868.00, 30.00, 41907.60, 54, 5, 'STO-019-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(232, 'Power Supplies Model 1', 'Power Supplies Modèle 1', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Power Supplies with advanced features', 'Power Supplies de haute qualité avec fonctionnalités avancées', 20, NULL, 46672.00, 28.00, 33603.84, 21, 5, 'POW-020-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(230, 'Storage (SSD/HDD) Model 4', 'Storage (SSD/HDD) Modèle 4', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 22584.00, 17.00, 18744.72, 72, 5, 'STO-019-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(228, 'Storage (SSD/HDD) Model 2', 'Storage (SSD/HDD) Modèle 2', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 30758.00, 3.00, 29835.26, 52, 5, 'STO-019-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(229, 'Storage (SSD/HDD) Model 3', 'Storage (SSD/HDD) Modèle 3', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 38415.00, 2.00, 37646.70, 56, 5, 'STO-019-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(227, 'Storage (SSD/HDD) Model 1', 'Storage (SSD/HDD) Modèle 1', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Storage (SSD/HDD) with advanced features', 'Storage (SSD/HDD) de haute qualité avec fonctionnalités avancées', 19, NULL, 36653.00, 27.00, 26756.69, 76, 5, 'STO-019-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(226, 'RAM & Memory Model 5', 'RAM & Memory Modèle 5', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 19027.00, 3.00, 18456.19, 57, 5, 'RAM-018-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(224, 'RAM & Memory Model 3', 'RAM & Memory Modèle 3', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 32031.00, 28.00, 23062.32, 64, 5, 'RAM-018-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(225, 'RAM & Memory Model 4', 'RAM & Memory Modèle 4', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 60772.00, 23.00, 46794.44, 82, 5, 'RAM-018-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(223, 'RAM & Memory Model 2', 'RAM & Memory Modèle 2', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 38722.00, 13.00, 33688.14, 23, 5, 'RAM-018-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(221, 'Motherboards Model 5', 'Motherboards Modèle 5', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 83657.00, 23.00, 64415.89, 81, 5, 'MOT-017-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(222, 'RAM & Memory Model 1', 'RAM & Memory Modèle 1', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality RAM & Memory with advanced features', 'RAM & Memory de haute qualité avec fonctionnalités avancées', 18, NULL, 38183.00, 9.00, 34746.53, 60, 5, 'RAM-018-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(220, 'Motherboards Model 4', 'Motherboards Modèle 4', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 76344.00, 8.00, 70236.48, 94, 5, 'MOT-017-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(219, 'Motherboards Model 3', 'Motherboards Modèle 3', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 54324.00, 11.00, 48348.36, 65, 5, 'MOT-017-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(217, 'Motherboards Model 1', 'Motherboards Modèle 1', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 61624.00, 18.00, 50531.68, 23, 5, 'MOT-017-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(218, 'Motherboards Model 2', 'Motherboards Modèle 2', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Motherboards with advanced features', 'Motherboards de haute qualité avec fonctionnalités avancées', 17, NULL, 48440.00, 8.00, 44564.80, 61, 5, 'MOT-017-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(215, 'AMD RX 7800 XT', 'AMD RX 7800 XT', 'Excellent 1440p performance', 'Excellente performance 1440p', '[]', '[]', 'Excellent 1440p performance', 'Excellente performance 1440p', 16, NULL, 64999.00, 20.00, 51999.20, 48, 5, 'GRA-016-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(216, 'NVIDIA RTX 4060 Ti', 'NVIDIA RTX 4060 Ti', 'Mainstream gaming GPU', 'GPU gaming grand public', '[]', '[]', 'Mainstream gaming GPU', 'GPU gaming grand public', 16, NULL, 49999.00, 8.00, 45999.08, 55, 5, 'GRA-016-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(213, 'AMD Radeon RX 7900 XTX', 'AMD Radeon RX 7900 XTX', 'High-end RDNA 3 GPU', 'GPU RDNA 3 haut de gamme', '[]', '[]', 'High-end RDNA 3 GPU', 'GPU RDNA 3 haut de gamme', 16, NULL, 129999.00, 13.00, 113099.13, 75, 5, 'GRA-016-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(214, 'NVIDIA RTX 4070 Ti', 'NVIDIA RTX 4070 Ti', 'Premium 1440p gaming', 'Gaming 1440p premium', '[]', '[]', 'Premium 1440p gaming', 'Gaming 1440p premium', 16, NULL, 89999.00, 6.00, 84599.06, 57, 5, 'GRA-016-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(211, 'Intel Core i5-14600K', 'Intel Core i5-14600K', 'Excellent mid-range processor', 'Excellent processeur milieu de gamme', '[]', '[]', 'Excellent mid-range processor', 'Excellent processeur milieu de gamme', 15, NULL, 29999.00, 26.00, 22199.26, 15, 5, 'PRO-015-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(212, 'NVIDIA RTX 4090 24GB', 'NVIDIA RTX 4090 24GB', 'Ultimate gaming graphics card', 'Carte graphique gaming ultime', '[]', '[]', 'Ultimate gaming graphics card', 'Carte graphique gaming ultime', 16, NULL, 199999.00, 12.00, 175999.12, 56, 5, 'GRA-016-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(209, 'Intel Core i7-14700K', 'Intel Core i7-14700K', 'High-performance gaming CPU', 'CPU gaming haute performance', '[]', '[]', 'High-performance gaming CPU', 'CPU gaming haute performance', 15, NULL, 44999.00, 8.00, 41399.08, 80, 5, 'PRO-015-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(210, 'AMD Ryzen 7 7800X3D', 'AMD Ryzen 7 7800X3D', 'Gaming-optimized with 3D V-Cache', 'Optimisé gaming avec 3D V-Cache', '[]', '[]', 'Gaming-optimized with 3D V-Cache', 'Optimisé gaming avec 3D V-Cache', 15, NULL, 49999.00, 5.00, 47499.05, 88, 5, 'PRO-015-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(207, 'Intel Core i9-14900K', 'Processeur Intel Core i9-14900K', '24-core flagship processor', 'Processeur phare 24 cœurs', '[]', '[]', '24-core flagship processor', 'Processeur phare 24 cœurs', 15, NULL, 64999.00, 16.00, 54599.16, 80, 5, 'PRO-015-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(208, 'AMD Ryzen 9 7950X', 'AMD Ryzen 9 7950X', '16-core Zen 4 processor', 'Processeur Zen 4 16 cœurs', '[]', '[]', '16-core Zen 4 processor', 'Processeur Zen 4 16 cœurs', 15, NULL, 69999.00, 15.00, 59499.15, 43, 5, 'PRO-015-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0);
INSERT INTO `products_backup_20251222_051721` (`id`, `name_en`, `name_fr`, `description_en`, `description_fr`, `technical_specs_en`, `technical_specs_fr`, `short_description_en`, `short_description_fr`, `category_id`, `brand_id`, `price`, `discount_percentage`, `final_price`, `stock_quantity`, `min_stock_quantity`, `sku`, `barcode`, `weight`, `dimensions`, `video_url`, `is_active`, `is_featured`, `is_new_arrival`, `is_best_seller`, `views_count`, `rating`, `rating_count`, `created_at`, `updated_at`, `low_stock_threshold`, `flash_sale_badge`) VALUES
(206, 'E-Readers & Tablets Model 5', 'E-Readers & Tablets Modèle 5', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 69344.00, 23.00, 53394.88, 43, 5, 'ERE-014-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(205, 'E-Readers & Tablets Model 4', 'E-Readers & Tablets Modèle 4', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 43732.00, 26.00, 32361.68, 40, 5, 'ERE-014-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(203, 'E-Readers & Tablets Model 2', 'E-Readers & Tablets Modèle 2', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 30824.00, 12.00, 27125.12, 82, 5, 'ERE-014-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(204, 'E-Readers & Tablets Model 3', 'E-Readers & Tablets Modèle 3', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 22156.00, 21.00, 17503.24, 43, 5, 'ERE-014-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(202, 'E-Readers & Tablets Model 1', 'E-Readers & Tablets Modèle 1', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality E-Readers & Tablets with advanced features', 'E-Readers & Tablets de haute qualité avec fonctionnalités avancées', 14, NULL, 28955.00, 17.00, 24032.65, 26, 5, 'ERE-014-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(201, 'Wearables Model 5', 'Wearables Modèle 5', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 150930.00, 9.00, 137346.30, 91, 5, 'WEA-013-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(199, 'Wearables Model 3', 'Wearables Modèle 3', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 170016.00, 11.00, 151314.24, 39, 5, 'WEA-013-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(200, 'Wearables Model 4', 'Wearables Modèle 4', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 138547.00, 10.00, 124692.30, 93, 5, 'WEA-013-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(198, 'Wearables Model 2', 'Wearables Modèle 2', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 160371.00, 11.00, 142730.19, 10, 5, 'WEA-013-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(196, 'Drones Model 5', 'Drones Modèle 5', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 111518.00, 6.00, 104826.92, 82, 5, 'DRO-012-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(197, 'Wearables Model 1', 'Wearables Modèle 1', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Wearables with advanced features', 'Wearables de haute qualité avec fonctionnalités avancées', 13, NULL, 159046.00, 29.00, 112922.66, 64, 5, 'WEA-013-001', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(195, 'Drones Model 4', 'Drones Modèle 4', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 129124.00, 26.00, 95551.76, 15, 5, 'DRO-012-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(193, 'Drones Model 2', 'Drones Modèle 2', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 113490.00, 15.00, 96466.50, 30, 5, 'DRO-012-002', NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(194, 'Drones Model 3', 'Drones Modèle 3', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 86324.00, 12.00, 75965.12, 91, 5, 'DRO-012-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(191, 'GoPro Hero 12 Black', 'GoPro Hero 12 Black', 'Action camera with 5.3K video', 'Caméra d\'action avec vidéo 5.3K', '[]', '[]', 'Action camera with 5.3K video', 'Caméra d\'action avec vidéo 5.3K', 11, NULL, 44999.00, 16.00, 37799.16, 33, 5, 'CAM-011-005', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-21 12:39:42', 5, 0),
(192, 'Drones Model 1', 'Drones Modèle 1', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', '[]', '[]', 'High-quality Drones with advanced features', 'Drones de haute qualité avec fonctionnalités avancées', 12, NULL, 92345.00, 13.00, 80340.15, 56, 5, 'DRO-012-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(190, 'Fujifilm X-T4', 'Fujifilm X-T4', 'APS-C flagship with IBIS', 'Modèle phare APS-C avec stabilisation', '[]', '[]', 'APS-C flagship with IBIS', 'Modèle phare APS-C avec stabilisation', 11, NULL, 169999.00, 18.00, 139399.18, 33, 5, 'CAM-011-004', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(188, 'Sony A7 IV Body', 'Sony A7 IV Boîtier', 'Versatile hybrid camera', 'Appareil photo hybride polyvalent', '[]', '[]', 'Versatile hybrid camera', 'Appareil photo hybride polyvalent', 11, NULL, 279999.00, 27.00, 204399.27, 74, 5, 'CAM-011-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(189, 'Nikon Z6 II Kit', 'Kit Nikon Z6 II', 'Advanced mirrorless with dual processors', 'Sans miroir avancé avec double processeur', '[]', '[]', 'Advanced mirrorless with dual processors', 'Sans miroir avancé avec double processeur', 11, NULL, 189999.00, 19.00, 153899.19, 100, 5, 'CAM-011-003', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(186, 'Hisense 32\" HD Ready TV', 'TV Hisense HD Ready 32\"', 'Compact HD television', 'Télévision HD compacte', '[]', '[]', 'Compact HD television', 'Télévision HD compacte', 10, NULL, 24999.00, 23.00, 19249.23, 53, 5, 'TEL-010-005', NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(187, 'Canon EOS R6 Mirrorless', 'Canon EOS R6 Sans Miroir', 'Professional full-frame mirrorless camera', 'Appareil photo sans miroir plein format professionnel', '[]', '[]', 'Professional full-frame mirrorless camera', 'Appareil photo sans miroir plein format professionnel', 11, NULL, 249999.00, 20.00, 199999.20, 84, 5, 'CAM-011-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(184, 'Sony Bravia 50\" LED 4K', 'TV Sony Bravia LED 4K 50\"', 'Excellent picture quality with HDR', 'Excellente qualité d\'image avec HDR', '[]', '[]', 'Excellent picture quality with HDR', 'Excellente qualité d\'image avec HDR', 10, NULL, 69999.00, 30.00, 48999.30, 9, 5, 'TEL-010-003', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(185, 'TCL 43\" Android TV', 'TV TCL Android 43\"', 'Budget-friendly smart TV', 'TV intelligente économique', '[]', '[]', 'Budget-friendly smart TV', 'TV intelligente économique', 10, NULL, 39999.00, 28.00, 28799.28, 81, 5, 'TEL-010-004', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(182, 'Samsung 55\" QLED 4K Smart TV', 'TV Samsung QLED 4K 55\"', 'High-end QLED display with quantum dot technology', 'Écran QLED haut de gamme avec technologie quantum dot', '[]', '[]', 'High-end QLED display with quantum dot technology', 'Écran QLED haut de gamme avec technologie quantum dot', 10, NULL, 89999.00, 24.00, 68399.24, 84, 5, 'TEL-010-001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0),
(183, 'LG 65\" OLED C3 Smart TV', 'TV LG OLED C3 65\"', 'Premium OLED with perfect blacks', 'OLED premium avec noirs parfaits', '[]', '[]', 'Premium OLED with perfect blacks', 'OLED premium avec noirs parfaits', 10, NULL, 129999.00, 25.00, 97499.25, 93, 5, 'TEL-010-002', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0.00, 0, '2025-12-13 16:16:52', '2025-12-13 16:16:52', 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_comparison_sessions`
--

DROP TABLE IF EXISTS `product_comparison_sessions`;
CREATE TABLE IF NOT EXISTS `product_comparison_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `product_ids` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `media_type` enum('image','video') DEFAULT 'image',
  `file_size` int DEFAULT '0',
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=232 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `media_type`, `file_size`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 2, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 1', 1, 0, '2025-12-13 06:39:35'),
(2, 3, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 2', 1, 0, '2025-12-13 06:39:35'),
(3, 4, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 3', 1, 0, '2025-12-13 06:39:35'),
(4, 5, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 4', 1, 0, '2025-12-13 06:39:35'),
(5, 6, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 5', 1, 0, '2025-12-13 06:39:35'),
(6, 7, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 6', 1, 0, '2025-12-13 06:39:35'),
(7, 8, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 7', 1, 0, '2025-12-13 06:39:35'),
(8, 9, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 8', 1, 0, '2025-12-13 06:39:35'),
(9, 10, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 9', 1, 0, '2025-12-13 06:39:35'),
(10, 11, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 10', 1, 0, '2025-12-13 06:39:35'),
(11, 12, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 1', 1, 0, '2025-12-13 06:39:35'),
(12, 13, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 2', 1, 0, '2025-12-13 06:39:35'),
(13, 14, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 3', 1, 0, '2025-12-13 06:39:35'),
(14, 15, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 4', 1, 0, '2025-12-13 06:39:35'),
(15, 16, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 5', 1, 0, '2025-12-13 06:39:35'),
(16, 17, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 6', 1, 0, '2025-12-13 06:39:35'),
(17, 18, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 7', 1, 0, '2025-12-13 06:39:35'),
(18, 19, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 8', 1, 0, '2025-12-13 06:39:35'),
(19, 20, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 9', 1, 0, '2025-12-13 06:39:35'),
(20, 21, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 10', 1, 0, '2025-12-13 06:39:35'),
(21, 22, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 1', 1, 0, '2025-12-13 06:39:35'),
(22, 23, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 2', 1, 0, '2025-12-13 06:39:35'),
(23, 24, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 3', 1, 0, '2025-12-13 06:39:35'),
(24, 25, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 4', 1, 0, '2025-12-13 06:39:35'),
(25, 26, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 5', 1, 0, '2025-12-13 06:39:35'),
(26, 27, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 6', 1, 0, '2025-12-13 06:39:35'),
(27, 28, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 7', 1, 0, '2025-12-13 06:39:35'),
(28, 29, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 8', 1, 0, '2025-12-13 06:39:35'),
(29, 30, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 9', 1, 0, '2025-12-13 06:39:35'),
(30, 31, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 10', 1, 0, '2025-12-13 06:39:35'),
(31, 32, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 1', 1, 0, '2025-12-13 06:39:35'),
(32, 33, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 2', 1, 0, '2025-12-13 06:39:35'),
(33, 34, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 3', 1, 0, '2025-12-13 06:39:35'),
(34, 35, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 4', 1, 0, '2025-12-13 06:39:35'),
(35, 36, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 5', 1, 0, '2025-12-13 06:39:35'),
(36, 37, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 6', 1, 0, '2025-12-13 06:39:35'),
(37, 38, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 7', 1, 0, '2025-12-13 06:39:35'),
(38, 39, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 8', 1, 0, '2025-12-13 06:39:35'),
(39, 40, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 9', 1, 0, '2025-12-13 06:39:35'),
(40, 41, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 10', 1, 0, '2025-12-13 06:39:35'),
(41, 42, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 1', 1, 0, '2025-12-13 06:39:35'),
(42, 43, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 2', 1, 0, '2025-12-13 06:39:35'),
(43, 44, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 3', 1, 0, '2025-12-13 06:39:35'),
(44, 45, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 4', 1, 0, '2025-12-13 06:39:35'),
(45, 46, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 5', 1, 0, '2025-12-13 06:39:35'),
(46, 47, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 6', 1, 0, '2025-12-13 06:39:35'),
(47, 48, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 7', 1, 0, '2025-12-13 06:39:35'),
(48, 49, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 8', 1, 0, '2025-12-13 06:39:35'),
(49, 50, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 9', 1, 0, '2025-12-13 06:39:35'),
(50, 51, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 10', 1, 0, '2025-12-13 06:39:35'),
(51, 52, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 1', 1, 0, '2025-12-13 06:39:35'),
(52, 53, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 2', 1, 0, '2025-12-13 06:39:35'),
(53, 54, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 3', 1, 0, '2025-12-13 06:39:35'),
(54, 55, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 4', 1, 0, '2025-12-13 06:39:35'),
(55, 56, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 5', 1, 0, '2025-12-13 06:39:35'),
(56, 57, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 6', 1, 0, '2025-12-13 06:39:35'),
(57, 58, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 7', 1, 0, '2025-12-13 06:39:35'),
(58, 59, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 8', 1, 0, '2025-12-13 06:39:35'),
(59, 60, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 9', 1, 0, '2025-12-13 06:39:35'),
(60, 61, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 10', 1, 0, '2025-12-13 06:39:35'),
(61, 62, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 1', 1, 0, '2025-12-13 06:39:35'),
(62, 63, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 2', 1, 0, '2025-12-13 06:39:35'),
(63, 64, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 3', 1, 0, '2025-12-13 06:39:35'),
(64, 65, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 4', 1, 0, '2025-12-13 06:39:35'),
(65, 66, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 5', 1, 0, '2025-12-13 06:39:35'),
(66, 67, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 6', 1, 0, '2025-12-13 06:39:35'),
(67, 68, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 7', 1, 0, '2025-12-13 06:39:35'),
(68, 69, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 8', 1, 0, '2025-12-13 06:39:35'),
(69, 70, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 9', 1, 0, '2025-12-13 06:39:35'),
(70, 71, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 10', 1, 0, '2025-12-13 06:39:35'),
(71, 72, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 1', 1, 0, '2025-12-13 06:39:35'),
(72, 73, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 2', 1, 0, '2025-12-13 06:39:35'),
(73, 74, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 3', 1, 0, '2025-12-13 06:39:35'),
(74, 75, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 4', 1, 0, '2025-12-13 06:39:35'),
(75, 76, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 5', 1, 0, '2025-12-13 06:39:35'),
(76, 77, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 6', 1, 0, '2025-12-13 06:39:35'),
(77, 78, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 7', 1, 0, '2025-12-13 06:39:35'),
(78, 79, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 8', 1, 0, '2025-12-13 06:39:35'),
(79, 80, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 9', 1, 0, '2025-12-13 06:39:35'),
(80, 81, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 10', 1, 0, '2025-12-13 06:39:35'),
(81, 82, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 1', 1, 0, '2025-12-13 06:39:35'),
(82, 83, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 2', 1, 0, '2025-12-13 06:39:35'),
(83, 84, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 3', 1, 0, '2025-12-13 06:39:35'),
(84, 85, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 4', 1, 0, '2025-12-13 06:39:35'),
(85, 86, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 5', 1, 0, '2025-12-13 06:39:35'),
(86, 87, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 6', 1, 0, '2025-12-13 06:39:35'),
(87, 88, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 7', 1, 0, '2025-12-13 06:39:35'),
(88, 89, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 8', 1, 0, '2025-12-13 06:39:35'),
(89, 90, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 9', 1, 0, '2025-12-13 06:39:35'),
(90, 91, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 10', 1, 0, '2025-12-13 06:39:35'),
(91, 92, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 1', 1, 0, '2025-12-13 06:39:58'),
(92, 93, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 2', 1, 0, '2025-12-13 06:39:58'),
(93, 94, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 3', 1, 0, '2025-12-13 06:39:58'),
(94, 95, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 4', 1, 0, '2025-12-13 06:39:58'),
(95, 96, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 5', 1, 0, '2025-12-13 06:39:58'),
(96, 97, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 6', 1, 0, '2025-12-13 06:39:58'),
(97, 98, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 7', 1, 0, '2025-12-13 06:39:58'),
(98, 99, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 8', 1, 0, '2025-12-13 06:39:58'),
(99, 100, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 9', 1, 0, '2025-12-13 06:39:58'),
(100, 101, 'img/product-placeholder.jpg', 'image', 0, 'Electronics Product 10', 1, 0, '2025-12-13 06:39:58'),
(101, 102, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 1', 1, 0, '2025-12-13 06:39:58'),
(102, 103, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 2', 1, 0, '2025-12-13 06:39:58'),
(103, 104, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 3', 1, 0, '2025-12-13 06:39:58'),
(104, 105, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 4', 1, 0, '2025-12-13 06:39:58'),
(105, 106, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 5', 1, 0, '2025-12-13 06:39:58'),
(106, 107, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 6', 1, 0, '2025-12-13 06:39:58'),
(107, 108, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 7', 1, 0, '2025-12-13 06:39:58'),
(108, 109, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 8', 1, 0, '2025-12-13 06:39:58'),
(109, 110, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 9', 1, 0, '2025-12-13 06:39:58'),
(110, 111, 'img/product-placeholder.jpg', 'image', 0, 'PC Components Product 10', 1, 0, '2025-12-13 06:39:58'),
(111, 112, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 1', 1, 0, '2025-12-13 06:39:58'),
(112, 113, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 2', 1, 0, '2025-12-13 06:39:58'),
(113, 114, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 3', 1, 0, '2025-12-13 06:39:58'),
(114, 115, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 4', 1, 0, '2025-12-13 06:39:58'),
(115, 116, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 5', 1, 0, '2025-12-13 06:39:58'),
(116, 117, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 6', 1, 0, '2025-12-13 06:39:58'),
(117, 118, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 7', 1, 0, '2025-12-13 06:39:58'),
(118, 119, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 8', 1, 0, '2025-12-13 06:39:58'),
(119, 120, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 9', 1, 0, '2025-12-13 06:39:58'),
(120, 121, 'img/product-placeholder.jpg', 'image', 0, 'Gaming Product 10', 1, 0, '2025-12-13 06:39:58'),
(121, 122, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 1', 1, 0, '2025-12-13 06:39:58'),
(122, 123, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 2', 1, 0, '2025-12-13 06:39:58'),
(123, 124, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 3', 1, 0, '2025-12-13 06:39:58'),
(124, 125, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 4', 1, 0, '2025-12-13 06:39:58'),
(125, 126, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 5', 1, 0, '2025-12-13 06:39:58'),
(126, 127, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 6', 1, 0, '2025-12-13 06:39:58'),
(127, 128, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 7', 1, 0, '2025-12-13 06:39:58'),
(128, 129, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 8', 1, 0, '2025-12-13 06:39:58'),
(129, 130, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 9', 1, 0, '2025-12-13 06:39:58'),
(130, 131, 'img/product-placeholder.jpg', 'image', 0, 'Home Appliances Product 10', 1, 0, '2025-12-13 06:39:58'),
(131, 132, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 1', 1, 0, '2025-12-13 06:39:58'),
(132, 133, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 2', 1, 0, '2025-12-13 06:39:58'),
(133, 134, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 3', 1, 0, '2025-12-13 06:39:58'),
(134, 135, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 4', 1, 0, '2025-12-13 06:39:58'),
(135, 136, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 5', 1, 0, '2025-12-13 06:39:58'),
(136, 137, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 6', 1, 0, '2025-12-13 06:39:58'),
(137, 138, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 7', 1, 0, '2025-12-13 06:39:58'),
(138, 139, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 8', 1, 0, '2025-12-13 06:39:58'),
(139, 140, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 9', 1, 0, '2025-12-13 06:39:58'),
(140, 141, 'img/product-placeholder.jpg', 'image', 0, 'Accessories Product 10', 1, 0, '2025-12-13 06:39:58'),
(141, 142, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 1', 1, 0, '2025-12-13 06:39:58'),
(142, 143, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 2', 1, 0, '2025-12-13 06:39:58'),
(143, 144, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 3', 1, 0, '2025-12-13 06:39:58'),
(144, 145, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 4', 1, 0, '2025-12-13 06:39:58'),
(145, 146, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 5', 1, 0, '2025-12-13 06:39:58'),
(146, 147, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 6', 1, 0, '2025-12-13 06:39:58'),
(147, 148, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 7', 1, 0, '2025-12-13 06:39:58'),
(148, 149, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 8', 1, 0, '2025-12-13 06:39:58'),
(149, 150, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 9', 1, 0, '2025-12-13 06:39:58'),
(150, 151, 'img/product-placeholder.jpg', 'image', 0, 'Smartphones Product 10', 1, 0, '2025-12-13 06:39:58'),
(151, 152, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 1', 1, 0, '2025-12-13 06:39:58'),
(152, 153, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 2', 1, 0, '2025-12-13 06:39:58'),
(153, 154, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 3', 1, 0, '2025-12-13 06:39:58'),
(154, 155, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 4', 1, 0, '2025-12-13 06:39:58'),
(155, 156, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 5', 1, 0, '2025-12-13 06:39:58'),
(156, 157, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 6', 1, 0, '2025-12-13 06:39:58'),
(157, 158, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 7', 1, 0, '2025-12-13 06:39:58'),
(158, 159, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 8', 1, 0, '2025-12-13 06:39:58'),
(159, 160, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 9', 1, 0, '2025-12-13 06:39:58'),
(160, 161, 'img/product-placeholder.jpg', 'image', 0, 'Networking Product 10', 1, 0, '2025-12-13 06:39:58'),
(161, 162, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 1', 1, 0, '2025-12-13 06:39:58'),
(162, 163, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 2', 1, 0, '2025-12-13 06:39:58'),
(163, 164, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 3', 1, 0, '2025-12-13 06:39:58'),
(164, 165, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 4', 1, 0, '2025-12-13 06:39:58'),
(165, 166, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 5', 1, 0, '2025-12-13 06:39:58'),
(166, 167, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 6', 1, 0, '2025-12-13 06:39:58'),
(167, 168, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 7', 1, 0, '2025-12-13 06:39:58'),
(168, 169, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 8', 1, 0, '2025-12-13 06:39:58'),
(169, 170, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 9', 1, 0, '2025-12-13 06:39:58'),
(170, 171, 'img/product-placeholder.jpg', 'image', 0, 'Audio Product 10', 1, 0, '2025-12-13 06:39:58'),
(171, 172, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 1', 1, 0, '2025-12-13 06:39:58'),
(172, 173, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 2', 1, 0, '2025-12-13 06:39:58'),
(173, 174, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 3', 1, 0, '2025-12-13 06:39:58'),
(174, 175, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 4', 1, 0, '2025-12-13 06:39:58'),
(175, 176, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 5', 1, 0, '2025-12-13 06:39:58'),
(176, 177, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 6', 1, 0, '2025-12-13 06:39:58'),
(177, 178, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 7', 1, 0, '2025-12-13 06:39:58'),
(178, 179, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 8', 1, 0, '2025-12-13 06:39:58'),
(179, 180, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 9', 1, 0, '2025-12-13 06:39:58'),
(180, 181, 'img/product-placeholder.jpg', 'image', 0, 'Gadgets Product 10', 1, 0, '2025-12-13 06:39:58'),
(231, 424, 'uploads/medias/products/424/img_1766637124_694cbe44b32cc.jpg', 'image', 0, NULL, 0, 5, '2025-12-25 04:32:04'),
(205, 421, 'uploads/medias/products/421/img_1766328478_6948089e69be6.jpg', 'image', 0, NULL, 0, 2, '2025-12-21 14:47:58'),
(229, 424, 'uploads/medias/products/424/img_1766637124_694cbe44b2a90.jpg', 'image', 0, NULL, 0, 3, '2025-12-25 04:32:04'),
(230, 424, 'uploads/medias/products/424/img_1766637124_694cbe44b2f58.jpg', 'image', 0, NULL, 0, 4, '2025-12-25 04:32:04'),
(208, 423, 'uploads/medias/products/423/img_1766373903_6948ba0f29163.jpg', 'image', 0, NULL, 1, 1, '2025-12-22 03:25:03'),
(224, 421, 'uploads/medias/products/421/img_1766559549_694b8f3deb384.jpg', 'image', 0, NULL, 0, 1, '2025-12-24 06:59:09'),
(225, 421, 'uploads/medias/products/421/img_1766559561_694b8f49deb70.jpg', 'image', 0, NULL, 1, 0, '2025-12-24 06:59:21'),
(226, 421, 'uploads/medias/products/421/img_1766559567_694b8f4fc2b59.jpg', 'image', 0, NULL, 0, 3, '2025-12-24 06:59:27'),
(227, 424, 'uploads/medias/products/424/img_1766637124_694cbe44af8d2.jpg', 'image', 0, NULL, 1, 1, '2025-12-25 04:32:04'),
(228, 424, 'uploads/medias/products/424/img_1766637124_694cbe44b268c.jpg', 'image', 0, NULL, 0, 2, '2025-12-25 04:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `sku` varchar(100) NOT NULL,
  `variant_name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int DEFAULT '0',
  `barcode` varchar(100) DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_product` (`product_id`),
  KEY `idx_sku` (`sku`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `variant_name`, `price`, `stock_quantity`, `barcode`, `weight`, `is_active`, `created_at`, `updated_at`) VALUES
(109, 421, 'PROD-421-23BD1E', 'V4', 6000.00, 60, NULL, NULL, 1, '2025-12-24 14:32:55', '2025-12-24 14:32:55'),
(107, 421, 'PROD-421-73A8BC', 'V2', 6500.00, 65, NULL, NULL, 1, '2025-12-24 14:32:55', '2025-12-24 14:32:55'),
(108, 421, 'PROD-421-4D952A', 'V3', 3200.00, 32, NULL, NULL, 1, '2025-12-24 14:32:55', '2025-12-24 14:32:55'),
(106, 421, 'PROD-421-659825', 'V1', 5500.00, 55, NULL, NULL, 1, '2025-12-24 14:32:55', '2025-12-24 14:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_options`
--

DROP TABLE IF EXISTS `product_variant_options`;
CREATE TABLE IF NOT EXISTS `product_variant_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `attribute_name` varchar(100) NOT NULL,
  `attribute_value` varchar(255) NOT NULL,
  `display_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_option` (`product_id`,`attribute_name`,`attribute_value`(100))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_views`
--

DROP TABLE IF EXISTS `product_views`;
CREATE TABLE IF NOT EXISTS `product_views` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_views_product` (`product_id`),
  KEY `idx_product_views_user` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `request_count` int DEFAULT '1',
  `last_request_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rate_limits_ip` (`ip_address`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `ip_address`, `endpoint`, `request_count`, `last_request_at`, `expires_at`) VALUES
(1, '::1', 'coupons', 1, '2025-12-24 10:13:14', '2025-12-24 11:13:14'),
(2, '::1', 'coupons', 1, '2025-12-24 10:30:06', '2025-12-24 11:30:06'),
(3, '::1', 'coupons', 1, '2025-12-24 11:26:21', '2025-12-24 12:26:21'),
(4, '::1', 'coupons', 1, '2025-12-24 11:26:29', '2025-12-24 12:26:29'),
(5, '::1', 'coupons', 1, '2025-12-24 12:58:09', '2025-12-24 13:58:09'),
(6, '::1', 'coupons', 1, '2025-12-24 14:26:33', '2025-12-24 15:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

DROP TABLE IF EXISTS `returns`;
CREATE TABLE IF NOT EXISTS `returns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('requested','approved','rejected','picked_up','processed','refunded') DEFAULT 'requested',
  `reason` text,
  `images` json DEFAULT NULL,
  `admin_note` text,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_method` enum('original_payment','store_credit') DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `admin_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `admin_id` (`admin_id`),
  KEY `idx_return_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_items`
--

DROP TABLE IF EXISTS `return_items`;
CREATE TABLE IF NOT EXISTS `return_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `return_id` int NOT NULL,
  `order_item_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  KEY `order_item_id` (`order_item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `rating` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `review_text` text,
  `is_verified_purchase` tinyint(1) DEFAULT '0',
  `is_approved` tinyint(1) DEFAULT '0',
  `helpful_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`customer_id`,`product_id`),
  KEY `order_id` (`order_id`),
  KEY `idx_reviews_product` (`product_id`),
  KEY `idx_reviews_rating` (`rating`),
  KEY `idx_reviews_approved` (`is_approved`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `review_images`
--

DROP TABLE IF EXISTS `review_images`;
CREATE TABLE IF NOT EXISTS `review_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `review_id` int NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `review_id` (`review_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_votes`
--

DROP TABLE IF EXISTS `review_votes`;
CREATE TABLE IF NOT EXISTS `review_votes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `review_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `vote` enum('helpful','not_helpful') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_review_vote` (`user_id`,`review_id`),
  KEY `review_id` (`review_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

DROP TABLE IF EXISTS `search_history`;
CREATE TABLE IF NOT EXISTS `search_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `search_term` varchar(255) NOT NULL,
  `search_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `result_count` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_search_history_user` (`customer_id`),
  KEY `idx_search_history_date` (`search_date`),
  KEY `idx_search_history_term` (`search_term`(250))
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `search_history`
--

INSERT INTO `search_history` (`id`, `customer_id`, `search_term`, `search_date`, `result_count`) VALUES
(1, 1, 'vr', '2025-12-21 11:49:08', 0),
(2, 1, 'vr', '2025-12-21 11:49:12', 0),
(3, 1, 'vr', '2025-12-21 11:49:12', 0),
(4, 1, 'Sony', '2025-12-21 11:49:21', 3),
(5, 1, 'Sony', '2025-12-21 11:51:55', 3),
(6, 1, 'Sony', '2025-12-21 11:52:04', 3),
(7, 1, 'Sony', '2025-12-21 11:52:08', 3);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_rates`
--

DROP TABLE IF EXISTS `shipping_rates`;
CREATE TABLE IF NOT EXISTS `shipping_rates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wilaya` varchar(100) NOT NULL,
  `flat_rate` decimal(10,2) DEFAULT '0.00',
  `weight_tier_1` decimal(10,2) DEFAULT '0.00',
  `weight_tier_2` decimal(10,2) DEFAULT '0.00',
  `weight_tier_3` decimal(10,2) DEFAULT '0.00',
  `weight_tier_4` decimal(10,2) DEFAULT '0.00',
  `free_shipping_threshold` decimal(10,2) DEFAULT '0.00',
  `delivery_days` int DEFAULT '7',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shipping_rates`
--

INSERT INTO `shipping_rates` (`id`, `wilaya`, `flat_rate`, `weight_tier_1`, `weight_tier_2`, `weight_tier_3`, `weight_tier_4`, `free_shipping_threshold`, `delivery_days`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Alger', 500.00, 500.00, 600.00, 700.00, 800.00, 10000.00, 3, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(2, 'Oran', 600.00, 600.00, 700.00, 800.00, 900.00, 10000.00, 4, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(3, 'Constantine', 700.00, 700.00, 800.00, 900.00, 1000.00, 10000.00, 5, 1, '2025-12-12 12:56:21', '2025-12-12 12:56:21'),
(4, 'Alger', 500.00, 500.00, 600.00, 700.00, 800.00, 10000.00, 3, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(5, 'Oran', 600.00, 600.00, 700.00, 800.00, 900.00, 10000.00, 4, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(6, 'Constantine', 700.00, 700.00, 800.00, 900.00, 1000.00, 10000.00, 5, 1, '2025-12-12 12:56:36', '2025-12-12 12:56:36'),
(7, 'Alger', 500.00, 500.00, 600.00, 700.00, 800.00, 10000.00, 3, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(8, 'Oran', 600.00, 600.00, 700.00, 800.00, 900.00, 10000.00, 4, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(9, 'Constantine', 700.00, 700.00, 800.00, 900.00, 1000.00, 10000.00, 5, 1, '2025-12-12 12:56:49', '2025-12-12 12:56:49'),
(10, 'Alger', 500.00, 500.00, 600.00, 700.00, 800.00, 10000.00, 3, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(11, 'Oran', 600.00, 600.00, 700.00, 800.00, 900.00, 10000.00, 4, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(12, 'Constantine', 700.00, 700.00, 800.00, 900.00, 1000.00, 10000.00, 5, 1, '2025-12-12 12:57:30', '2025-12-12 12:57:30'),
(13, 'Adrar', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:25'),
(14, 'Aïn Defla', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(15, 'Aïn Témouchent', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(16, 'Annaba', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(17, 'Batna', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(18, 'Béchar', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(19, 'Béjaïa', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(20, 'Béni Abbès', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(21, 'Biskra', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(22, 'Blida', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(23, 'Bordj Badji Mokhtar', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(24, 'Bordj Bou Arreridj', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(25, 'Bouira', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(26, 'Boumerdès', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(27, 'Djanet', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(28, 'Djelfa', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(29, 'El Bayadh', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(30, 'El M\'ghair', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(31, 'El Menia', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(32, 'El Oued', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(33, 'El Tarf', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(34, 'Ghardaïa', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(35, 'Guelma', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(36, 'Illizi', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(37, 'In Guezzam', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(38, 'In Salah', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(39, 'Jijel', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(40, 'Khenchela', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(41, 'Laghouat', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(42, 'M\'sila', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(43, 'Mascara', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(44, 'Médéa', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(45, 'Mila', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(46, 'Mostaganem', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(47, 'Naâma', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(48, 'Ouargla', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(49, 'Ouled Djellal', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(50, 'Oum El Bouaghi', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(51, 'Relizane', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(52, 'Saïda', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(53, 'Sétif', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(54, 'Sidi Bel Abbès', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(55, 'Skikda', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(56, 'Souk Ahras', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(57, 'Tamanrasset', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(58, 'Tébessa', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(59, 'Tiaret', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(60, 'Timimoun', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(61, 'Tindouf', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(62, 'Tipaza', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(63, 'Tissemsilt', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(64, 'Tizi Ouzou', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(65, 'Tlemcen', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22'),
(66, 'Touggourt', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7, 0, '2025-12-15 08:53:22', '2025-12-15 08:53:22');

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

DROP TABLE IF EXISTS `shopping_cart`;
CREATE TABLE IF NOT EXISTS `shopping_cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int NOT NULL,
  `variant_id` int DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price_at_time` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `variant_id` (`variant_id`),
  KEY `idx_cart_user` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shopping_cart`
--

INSERT INTO `shopping_cart` (`id`, `customer_id`, `session_id`, `product_id`, `variant_id`, `quantity`, `price_at_time`, `created_at`, `updated_at`) VALUES
(1, NULL, '3ri57lnblqbvbb7664str0c9qm', 206, NULL, 1, 53394.88, '2025-12-15 15:41:34', '2025-12-15 15:41:34'),
(2, NULL, '3ri57lnblqbvbb7664str0c9qm', 206, NULL, 1, 53394.88, '2025-12-15 15:41:35', '2025-12-15 15:41:35'),
(3, NULL, '3ri57lnblqbvbb7664str0c9qm', 205, NULL, 1, 32361.68, '2025-12-15 15:41:36', '2025-12-15 15:41:36'),
(4, NULL, '3ri57lnblqbvbb7664str0c9qm', 203, NULL, 1, 27125.12, '2025-12-15 15:41:39', '2025-12-15 15:41:39'),
(5, NULL, '3ri57lnblqbvbb7664str0c9qm', 205, NULL, 1, 32361.68, '2025-12-15 15:41:50', '2025-12-15 15:41:50'),
(6, NULL, '3ri57lnblqbvbb7664str0c9qm', 205, NULL, 1, 32361.68, '2025-12-15 15:41:52', '2025-12-15 15:41:52'),
(7, NULL, '3ri57lnblqbvbb7664str0c9qm', 205, NULL, 1, 32361.68, '2025-12-15 15:41:52', '2025-12-15 15:41:52'),
(8, NULL, '3ri57lnblqbvbb7664str0c9qm', 205, NULL, 1, 32361.68, '2025-12-15 15:41:53', '2025-12-15 15:41:53'),
(9, NULL, '3ri57lnblqbvbb7664str0c9qm', 205, NULL, 1, 32361.68, '2025-12-15 15:41:53', '2025-12-15 15:41:53'),
(10, NULL, '3ri57lnblqbvbb7664str0c9qm', 205, NULL, 1, 32361.68, '2025-12-15 15:41:53', '2025-12-15 15:41:53'),
(11, NULL, '3ri57lnblqbvbb7664str0c9qm', 421, NULL, 1, 910.00, '2025-12-15 15:42:16', '2025-12-15 15:42:16'),
(13, NULL, '3ri57lnblqbvbb7664str0c9qm', 421, NULL, 1, 910.00, '2025-12-15 15:42:17', '2025-12-15 15:42:17'),
(14, NULL, '3ri57lnblqbvbb7664str0c9qm', 421, NULL, 1, 910.00, '2025-12-15 15:42:17', '2025-12-15 15:42:17'),
(56, NULL, 'oo22cj6k6cso39efd954fdkcqv', 421, 335, 2, 900.00, '2025-12-20 13:02:59', '2025-12-20 13:03:00'),
(114, 1, NULL, 421, 109, 2, 6000.00, '2025-12-25 04:45:37', '2025-12-25 07:39:52');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(191) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','text','json','boolean','number') DEFAULT 'string',
  `is_public` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  UNIQUE KEY `idx_setting_key` (`setting_key`)
) ENGINE=MyISAM AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `is_public`, `created_at`, `updated_at`, `setting_group`) VALUES
(1, 'site_title', 'GameCult', 'string', 1, '2025-12-12 10:54:01', '2025-12-24 13:25:03', 'general'),
(3, 'currency_code', 'DZD', 'string', 1, '2025-12-12 10:54:01', '2025-12-25 09:40:15', 'ecommerce'),
(4, 'currency_symbol', 'DA', 'string', 1, '2025-12-12 10:54:01', '2025-12-25 09:40:15', 'ecommerce'),
(21, 'tax_rate', '19.0', 'string', 0, '2025-12-13 07:58:09', '2025-12-13 08:57:22', 'ecommerce'),
(22, 'low_stock_threshold', '10', 'string', 0, '2025-12-13 07:58:09', '2025-12-13 08:57:22', 'ecommerce'),
(24, 'site_tagline', 'Simply Better Tech', 'string', 0, '2025-12-13 07:58:27', '2025-12-24 13:10:31', 'general'),
(25, 'site_logo', '', 'string', 0, '2025-12-13 07:58:27', '2025-12-24 13:21:51', 'general'),
(26, 'contact_email', 'contact@qwenshop.dz', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 07:58:27', 'general'),
(27, 'contact_phone', '+213 555 123 456', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 07:58:27', 'general'),
(28, 'contact_address', '123 Rue Didouche Mourad, Algiers', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 07:58:27', 'general'),
(31, 'maintenance_mode', '0', 'string', 0, '2025-12-13 07:58:27', '2025-12-24 13:15:03', 'general'),
(32, 'theme', 'dark', 'string', 0, '2025-12-13 07:58:27', '2025-12-15 14:35:08', 'appearance'),
(33, 'primary_color', '#376d36', 'string', 0, '2025-12-13 07:58:27', '2025-12-24 13:52:44', 'appearance'),
(34, 'homepage_layout', 'deals_promotions', 'string', 0, '2025-12-13 07:58:27', '2025-12-22 09:30:05', 'appearance'),
(92, 'site_logo_font_size', '30', 'string', 0, '2025-12-24 13:33:29', '2025-12-24 13:53:16', 'general'),
(91, 'site_logo_height', '40', 'string', 0, '2025-12-24 13:21:51', '2025-12-24 13:21:51', 'general'),
(93, 'logo_primary_color', '#4d4f51', 'string', 0, '2025-12-24 13:53:09', '2025-12-24 13:53:09', 'general'),
(44, 'smtp_host', 'smtp.gmail.com', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 07:58:27', 'email'),
(45, 'smtp_port', '587', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 07:58:27', 'email'),
(49, 'admin_notification_email', 'admin@qwenshop.dz', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 07:58:27', 'email'),
(94, 'logo_secondary_color', '#dc3545', 'string', 0, '2025-12-24 13:53:09', '2025-12-24 13:53:09', 'general'),
(51, 'min_password_length', '8', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 07:58:27', 'security'),
(53, 'privacy_policy_url', '/privacy', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 07:58:27', 'security'),
(54, 'cookie_consent_enabled', '1', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 07:58:27', 'security'),
(55, 'meta_title', 'QwenShop - Premium Electronics', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 08:57:22', 'performance'),
(56, 'meta_description', 'The best online shop for electronics, computers', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 08:58:14', 'performance'),
(57, 'meta_keywords', 'electronics, algiers, computer, phone', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 08:57:22', 'performance'),
(58, 'google_analytics_id', '', 'string', 0, '2025-12-13 07:58:27', '2025-12-13 08:57:22', 'performance'),
(59, 'facebook_url', '', 'string', 0, '2025-12-13 08:06:29', '2025-12-13 08:06:29', 'general'),
(60, 'twitter_url', '', 'string', 0, '2025-12-13 08:06:29', '2025-12-13 08:06:29', 'general'),
(61, 'instagram_url', '', 'string', 0, '2025-12-13 08:06:29', '2025-12-13 08:06:29', 'general'),
(62, 'linkedin_url', '', 'string', 0, '2025-12-13 08:06:29', '2025-12-13 08:06:29', 'general'),
(63, 'youtube_url', '', 'string', 0, '2025-12-13 08:06:29', '2025-12-13 08:06:29', 'general'),
(64, 'secondary_color', '#376d36', 'string', 0, '2025-12-13 08:49:20', '2025-12-24 13:52:44', 'appearance'),
(65, 'accent_color', '#585555', 'string', 0, '2025-12-13 08:49:20', '2025-12-15 15:04:25', 'appearance'),
(66, 'footer_content', '© 2025  All rights reserved.', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:57:29', 'appearance'),
(67, 'tax_calculation_method', 'exclusive', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(68, 'shipping_cost_standard', '500', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(69, 'shipping_cost_express', '1000', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(70, 'shipping_free_threshold', '10000', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(71, 'paypal_enabled', '0', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(72, 'paypal_client_id', '', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(73, 'paypal_secret', 'admin123', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:57:22', 'ecommerce'),
(74, 'stripe_enabled', '0', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(75, 'stripe_publishable_key', '', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(76, 'stripe_secret_key', '', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(77, 'inventory_tracking', '1', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'ecommerce'),
(78, 'smtp_username', '', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'email'),
(79, 'smtp_password', '', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'email'),
(80, 'smtp_encryption', 'tls', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'email'),
(81, 'order_confirmation_template', 'Dear {customer_name}, your order #{order_id} has been confirmed. Thank you for shopping with us!', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'email'),
(82, 'order_shipped_template', 'Dear {customer_name}, your order #{order_id} has been shipped. Tracking number: {tracking_number}', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'email'),
(83, 'user_registration_enabled', '1', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'security'),
(84, 'max_login_attempts', '5', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'security'),
(85, 'lockout_duration', '900', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'security'),
(86, 'terms_of_service_url', 'terms.php', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'security'),
(87, 'cache_enabled', '1', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'performance'),
(88, 'cache_duration', '3600', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'performance'),
(89, 'sitemap_auto_generate', '1', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 08:49:20', 'performance'),
(90, 'tinymce_api_key', 'idz8jb861xjzbu95kv33utuv0txp56vrgx7gp2baffdw0tug', 'string', 0, '2025-12-13 08:49:20', '2025-12-13 09:04:50', 'performance'),
(95, 'bootstrap_theme', 'Sandstone', 'string', 0, '2025-12-25 07:10:34', '2025-12-25 07:25:37', 'appearance');

-- --------------------------------------------------------

--
-- Table structure for table `sku_counters`
--

DROP TABLE IF EXISTS `sku_counters`;
CREATE TABLE IF NOT EXISTS `sku_counters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `subcategory_id` int DEFAULT NULL,
  `next_number` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_combo` (`category_id`,`subcategory_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sku_counters`
--

INSERT INTO `sku_counters` (`id`, `category_id`, `subcategory_id`, `next_number`, `created_at`, `updated_at`) VALUES
(1, 1, 11, 3, '2025-12-19 08:20:45', '2025-12-22 03:25:03'),
(2, 1, 14, 9, '2025-12-21 13:36:34', '2025-12-21 13:39:21'),
(3, 3, 23, 2, '2025-12-25 04:32:04', '2025-12-25 04:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `stock_notifications`
--

DROP TABLE IF EXISTS `stock_notifications`;
CREATE TABLE IF NOT EXISTS `stock_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `is_sent` tinyint(1) DEFAULT '0',
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('admin','manager','moderator','agent') DEFAULT 'moderator',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_admin_users_email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `role`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'admin@qwenshop.dz', '$2y$10$88WhihDXQfT802xw9Rg43uN0A.CJkbW6ldgHqAb0z64GlzXdNuelO', 'Admin', 'User', 'admin', 1, '2025-12-24 13:24:51', '2025-12-13 05:12:38', '2025-12-24 13:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `wilaya` varchar(100) NOT NULL,
  `daira` varchar(100) NOT NULL,
  `commune` varchar(100) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `street_address` text NOT NULL,
  `apartment_suite` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user_addresses_customer` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `customer_id`, `wilaya`, `daira`, `commune`, `postal_code`, `street_address`, `apartment_suite`, `phone_number`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, 'Alger', 'Alger', 'Yangpu District', '210000', 'Siping Road 2065 Building 6 apartment 405', '', '13091435037', 1, '2025-12-19 12:12:40', '2025-12-24 11:32:50');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;
CREATE TABLE IF NOT EXISTS `user_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user_notifications_customer` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `variant_attributes`
--

DROP TABLE IF EXISTS `variant_attributes`;
CREATE TABLE IF NOT EXISTS `variant_attributes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_variant_id` int NOT NULL,
  `attribute_name` varchar(100) NOT NULL,
  `attribute_value` varchar(191) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_variant` (`product_variant_id`),
  KEY `idx_search` (`attribute_name`,`attribute_value`(100))
) ENGINE=MyISAM AUTO_INCREMENT=215 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `variant_attributes`
--

INSERT INTO `variant_attributes` (`id`, `product_variant_id`, `attribute_name`, `attribute_value`) VALUES
(1, 1, 'Color', 'Retro Power'),
(2, 2, 'Color', 'Aurora Black'),
(3, 3, 'Color', 'Aurora Black'),
(4, 4, 'Color', 'Aurora Black'),
(5, 5, 'Color', 'Aurora Black'),
(6, 5, 'Memory', '2+32'),
(7, 6, 'Color', 'Aurora Black'),
(8, 6, 'Memory', '2+32'),
(9, 7, 'Color', 'Retro White'),
(10, 7, 'Memory', '2+32'),
(11, 8, 'Color', 'Aurora Black'),
(12, 8, 'Memory', '2+32'),
(13, 9, 'Color', 'Retro White'),
(14, 9, 'Memory', '2+32'),
(15, 10, 'Color', 'Aurora Black'),
(16, 10, 'Memory', '2+32'),
(17, 11, 'Color', 'Retro White'),
(18, 11, 'Memory', '2+32'),
(19, 12, 'Color', 'Retro Power'),
(20, 12, 'Memory', '2+64'),
(21, 13, 'Color', 'Aurora Black'),
(22, 13, 'Memory', '2+64'),
(23, 14, 'Color', 'Aurora Black'),
(24, 14, 'Memory', '2+32'),
(25, 15, 'Color', 'Retro White'),
(26, 15, 'Memory', '2+32'),
(27, 16, 'Color', 'Retro Power'),
(28, 16, 'Memory', '2+64'),
(29, 17, 'Color', 'Aurora Black'),
(30, 17, 'Memory', '2+64'),
(31, 18, 'Color', 'Aurora Black'),
(32, 18, 'Memory', '2+32'),
(33, 19, 'Color', 'Retro White'),
(34, 19, 'Memory', '2+32'),
(35, 20, 'Color', 'Retro Power'),
(36, 20, 'Memory', '2+64'),
(37, 21, 'Color', 'Aurora Black'),
(38, 21, 'Memory', '2+64'),
(39, 22, 'Color', 'Aurora Black'),
(40, 22, 'Memory', '2+32'),
(41, 23, 'Color', 'Retro White'),
(42, 23, 'Memory', '2+32'),
(43, 24, 'Color', 'Retro Power'),
(44, 24, 'Memory', '2+64'),
(45, 25, 'Color', 'Aurora Black'),
(46, 25, 'Memory', '2+64'),
(47, 26, 'Color', 'Aurora Black'),
(48, 26, 'Memory', '2+32'),
(49, 27, 'Color', 'Retro White'),
(50, 27, 'Memory', '2+32'),
(51, 28, 'Color', 'Retro Power'),
(52, 28, 'Memory', '2+64'),
(53, 29, 'Color', 'Aurora Black'),
(54, 29, 'Memory', '2+64'),
(55, 30, 'Color', 'Aurora Black'),
(56, 30, 'Memory', '2+32'),
(57, 31, 'Color', 'Retro White'),
(58, 31, 'Memory', '2+32'),
(59, 32, 'Color', 'Retro Power'),
(60, 32, 'Memory', '2+64'),
(61, 33, 'Color', 'Aurora Black'),
(62, 33, 'Memory', '2+64'),
(63, 34, 'Color', 'Aurora Black'),
(64, 34, 'Memory', '2+32'),
(65, 35, 'Color', 'Retro White'),
(66, 35, 'Memory', '2+32'),
(67, 36, 'Color', 'Retro Power'),
(68, 36, 'Memory', '2+64'),
(69, 37, 'Color', 'Aurora Black'),
(70, 37, 'Memory', '2+64'),
(71, 38, 'Color', 'Aurora Black'),
(72, 38, 'Memory', '2+32'),
(73, 39, 'Color', 'Retro White'),
(74, 39, 'Memory', '2+32'),
(75, 40, 'Color', 'Retro Power'),
(76, 40, 'Memory', '2+64'),
(77, 41, 'Color', 'Aurora Black'),
(78, 41, 'Memory', '2+64'),
(79, 42, 'Color', 'Aurora Black'),
(80, 42, 'Memory', '2+32'),
(81, 43, 'Color', 'Retro White'),
(82, 43, 'Memory', '2+32'),
(83, 44, 'Color', 'Retro Power'),
(84, 44, 'Memory', '2+64'),
(85, 45, 'Color', 'Aurora Black'),
(86, 45, 'Memory', '2+64'),
(87, 46, 'Color', 'Aurora Black'),
(88, 46, 'Memory', '2+32'),
(89, 47, 'Color', 'Retro White'),
(90, 47, 'Memory', '2+32'),
(91, 48, 'Color', 'Retro Power'),
(92, 48, 'Memory', '2+64'),
(93, 49, 'Color', 'Aurora Black'),
(94, 49, 'Memory', '2+64'),
(95, 50, 'Color', 'Aurora Black'),
(96, 50, 'Memory', '2+32'),
(97, 51, 'Color', 'Retro White'),
(98, 51, 'Memory', '2+32'),
(99, 52, 'Color', 'Retro Power'),
(100, 52, 'Memory', '2+64'),
(101, 53, 'Color', 'Aurora Black'),
(102, 53, 'Memory', '2+64'),
(103, 54, 'Color', 'Aurora Black'),
(104, 54, 'Memory', '2+32'),
(105, 55, 'Color', 'Retro White'),
(106, 55, 'Memory', '2+32'),
(107, 56, 'Color', 'Retro Power'),
(108, 56, 'Memory', '2+64'),
(109, 57, 'Color', 'Aurora Black'),
(110, 57, 'Memory', '2+64'),
(111, 58, 'Color', 'Aurora Black'),
(112, 58, 'Memory', '2+32'),
(113, 59, 'Color', 'Retro White'),
(114, 59, 'Memory', '2+32'),
(115, 60, 'Color', 'Retro Power'),
(116, 60, 'Memory', '2+64'),
(117, 61, 'Color', 'Aurora Black'),
(118, 61, 'Memory', '2+64'),
(119, 62, 'Color', 'Aurora Black'),
(120, 62, 'Memory', '2+32'),
(121, 63, 'Color', 'Retro White'),
(122, 63, 'Memory', '2+32'),
(123, 64, 'Color', 'Retro Power'),
(124, 64, 'Memory', '2+64'),
(125, 65, 'Color', 'Aurora Black'),
(126, 65, 'Memory', '2+64'),
(127, 66, 'Color', 'Aurora Black'),
(128, 66, 'Memory', '2+32'),
(129, 67, 'Color', 'Retro White'),
(130, 67, 'Memory', '2+32'),
(131, 68, 'Color', 'Retro Power'),
(132, 68, 'Memory', '2+64'),
(133, 69, 'Color', 'Aurora Black'),
(134, 69, 'Memory', '2+64'),
(135, 70, 'Color', 'Aurora Black'),
(136, 70, 'Memory', '2+32'),
(137, 71, 'Color', 'Retro White'),
(138, 71, 'Memory', '2+32'),
(139, 72, 'Color', 'Retro Power'),
(140, 72, 'Memory', '2+64'),
(141, 73, 'Color', 'Aurora Black'),
(142, 73, 'Memory', '2+64'),
(143, 74, 'Color', 'Aurora Black'),
(144, 74, 'Memory', '2+32'),
(145, 75, 'Color', 'Retro White'),
(146, 75, 'Memory', '2+32'),
(147, 76, 'Color', 'Retro Power'),
(148, 76, 'Memory', '2+64'),
(149, 77, 'Color', 'Aurora Black'),
(150, 77, 'Memory', '2+64'),
(151, 78, 'Color', 'Aurora Black'),
(152, 78, 'Memory', '2+32'),
(153, 79, 'Color', 'Retro White'),
(154, 79, 'Memory', '2+32'),
(155, 80, 'Color', 'Retro Power'),
(156, 80, 'Memory', '2+64'),
(157, 81, 'Color', 'Aurora Black'),
(158, 81, 'Memory', '2+64'),
(159, 82, 'Color', 'Aurora Black'),
(160, 82, 'Memory', '2+32'),
(161, 83, 'Color', 'Retro White'),
(162, 83, 'Memory', '2+32'),
(163, 84, 'Color', 'Retro Power'),
(164, 84, 'Memory', '2+64'),
(165, 85, 'Color', 'Aurora Black'),
(166, 85, 'Memory', '2+64'),
(167, 86, 'Color', 'Aurora Black'),
(168, 86, 'Memory', '2+32'),
(169, 87, 'Color', 'Retro White'),
(170, 87, 'Memory', '2+32'),
(171, 88, 'Color', 'Retro Power'),
(172, 88, 'Memory', '2+64'),
(173, 89, 'Color', 'Aurora Black'),
(174, 89, 'Memory', '2+64'),
(175, 90, 'Color', 'Aurora Black'),
(176, 90, 'Memory', '2+32'),
(177, 91, 'Color', 'Retro White'),
(178, 91, 'Memory', '2+32'),
(179, 92, 'Color', 'Retro Power'),
(180, 92, 'Memory', '2+64'),
(181, 93, 'Color', 'Aurora Black'),
(182, 93, 'Memory', '2+64'),
(183, 94, 'Color', 'Aurora Black'),
(184, 94, 'Memory', '2+32'),
(185, 95, 'Color', 'Retro White'),
(186, 95, 'Memory', '2+32'),
(187, 96, 'Color', 'Retro Power'),
(188, 96, 'Memory', '2+64'),
(189, 97, 'Color', 'Aurora Black'),
(190, 97, 'Memory', '2+64'),
(191, 98, 'Color', 'Aurora Black'),
(192, 98, 'Memory', '2+32'),
(193, 99, 'Color', 'Retro White'),
(194, 99, 'Memory', '2+32'),
(195, 100, 'Color', 'Retro Power'),
(196, 100, 'Memory', '2+64'),
(197, 101, 'Color', 'Aurora Black'),
(198, 101, 'Memory', '2+64'),
(199, 102, 'Color', 'Aurora Black'),
(200, 102, 'Memory', '2+32'),
(201, 103, 'Color', 'Retro White'),
(202, 103, 'Memory', '2+32'),
(203, 104, 'Color', 'Retro Power'),
(204, 104, 'Memory', '2+64'),
(205, 105, 'Color', 'Aurora Black'),
(206, 105, 'Memory', '2+64'),
(207, 106, 'Color', 'Aurora Black'),
(208, 106, 'Memory', '2+32'),
(209, 107, 'Color', 'Retro White'),
(210, 107, 'Memory', '2+32'),
(211, 108, 'Color', 'Retro Power'),
(212, 108, 'Memory', '2+64'),
(213, 109, 'Color', 'Aurora Black'),
(214, 109, 'Memory', '2+64');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_wishlist_user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(4, 1, 205, '2025-12-16 10:27:06'),
(3, 1, 421, '2025-12-16 09:43:06'),
(5, 1, 206, '2025-12-16 10:27:10');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE IF NOT EXISTS `wishlists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`customer_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_wishlist_user` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `customer_id`, `product_id`, `created_at`) VALUES
(18, 1, 205, '2025-12-19 12:01:10'),
(27, 1, 244, '2025-12-19 12:02:34'),
(26, 1, 246, '2025-12-19 12:02:33'),
(28, 1, 245, '2025-12-19 12:02:36'),
(31, 1, 203, '2025-12-19 14:25:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products` ADD FULLTEXT KEY `name_en` (`name_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `name_fr` (`name_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_en` (`description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_fr` (`description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_en` (`short_description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_fr` (`short_description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `sku_2` (`sku`);
ALTER TABLE `products` ADD FULLTEXT KEY `name_en_2` (`name_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `name_fr_2` (`name_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_en_2` (`description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_fr_2` (`description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_en_2` (`short_description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_fr_2` (`short_description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `sku_3` (`sku`);
ALTER TABLE `products` ADD FULLTEXT KEY `name_en_3` (`name_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `name_fr_3` (`name_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_en_3` (`description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_fr_3` (`description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_en_3` (`short_description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_fr_3` (`short_description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `sku_4` (`sku`);
ALTER TABLE `products` ADD FULLTEXT KEY `name_en_4` (`name_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `name_fr_4` (`name_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_en_4` (`description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_fr_4` (`description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_en_4` (`short_description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_fr_4` (`short_description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `sku_5` (`sku`);
ALTER TABLE `products` ADD FULLTEXT KEY `name_en_5` (`name_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `name_fr_5` (`name_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_en_5` (`description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `description_fr_5` (`description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_en_5` (`short_description_en`);
ALTER TABLE `products` ADD FULLTEXT KEY `short_description_fr_5` (`short_description_fr`);
ALTER TABLE `products` ADD FULLTEXT KEY `sku_6` (`sku`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
