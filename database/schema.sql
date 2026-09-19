-- Harvestly Database Schema
CREATE DATABASE IF NOT EXISTS `harvestly_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `harvestly_db`;

-- Drop tables if exist in reverse order of foreign keys
DROP TABLE IF EXISTS `district_hub_mapping`;
DROP TABLE IF EXISTS `courier_coverage`;
DROP TABLE IF EXISTS `notifications_log`;
DROP TABLE IF EXISTS `complaints`;
DROP TABLE IF EXISTS `ratings`;
DROP TABLE IF EXISTS `settlements`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `deliveries`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `courier_partners`;
DROP TABLE IF EXISTS `farmers`;
DROP TABLE IF EXISTS `buyers`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `hubs`;
DROP TABLE IF EXISTS `districts`;
DROP TABLE IF EXISTS `zone_fee_tiers`;
DROP TABLE IF EXISTS `platform_settings`;

-- 1. Zone Fee Tiers Matrix
CREATE TABLE `zone_fee_tiers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `zone_name` VARCHAR(100) NOT NULL,
  `base_fee` DECIMAL(10,2) NOT NULL DEFAULT 150.00,
  `per_kg_fee` DECIMAL(10,2) NOT NULL DEFAULT 20.00,
  `min_days` INT NOT NULL DEFAULT 1,
  `max_days` INT NOT NULL DEFAULT 3,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Hubs
CREATE TABLE `hubs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hub_name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `district` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Districts
CREATE TABLE `districts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `district_name` VARCHAR(100) NOT NULL UNIQUE,
  `province` VARCHAR(100) NOT NULL,
  `zone_fee_tier_id` INT DEFAULT NULL,
  `primary_hub_id` INT DEFAULT NULL,
  FOREIGN KEY (`zone_fee_tier_id`) REFERENCES `zone_fee_tiers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`primary_hub_id`) REFERENCES `hubs`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. Admins
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) DEFAULT 'Super Admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Buyers
CREATE TABLE `buyers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `province` VARCHAR(100) NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `status` ENUM('active', 'suspended') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. Farmers
CREATE TABLE `farmers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `nic_number` VARCHAR(30) NOT NULL UNIQUE,
  `farm_address` TEXT NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `id_document_path` VARCHAR(255) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'resubmit_requested', 'suspended') DEFAULT 'pending',
  `rejection_reason` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 7. Courier Partners (Companies ONLY)
CREATE TABLE `courier_partners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_name` VARCHAR(200) NOT NULL,
  `contact_person` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `brn_number` VARCHAR(50) NOT NULL UNIQUE,
  `business_address` TEXT NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `registration_cert_path` VARCHAR(255) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'resubmit_requested', 'suspended') DEFAULT 'pending',
  `rejection_reason` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8. Products (Farmer-declared grade A/B/C)
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `farmer_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `price_per_unit` DECIMAL(10,2) NOT NULL,
  `unit_type` VARCHAR(30) NOT NULL DEFAULT 'kg',
  `grade` ENUM('Grade A', 'Grade B', 'Grade C') DEFAULT 'Grade A',
  `district` VARCHAR(100) NOT NULL,
  `image_url` TEXT DEFAULT NULL,
  `status` ENUM('active', 'flagged', 'removed') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`farmer_id`) REFERENCES `farmers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Orders
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `buyer_id` INT NOT NULL,
  `courier_id` INT DEFAULT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `delivery_fee` DECIMAL(10,2) NOT NULL DEFAULT 150.00,
  `status` ENUM('pending_assignment', 'assigned', 'picked_up', 'in_transit', 'delivered', 'confirmed_received', 'cancelled') DEFAULT 'pending_assignment',
  `delivery_address` TEXT NOT NULL,
  `district_id` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`buyer_id`) REFERENCES `buyers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`courier_id`) REFERENCES `courier_partners`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 10. Order Items
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. Deliveries
CREATE TABLE `deliveries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL UNIQUE,
  `courier_id` INT DEFAULT NULL,
  `pickup_status` ENUM('pending', 'picked_up') DEFAULT 'pending',
  `delivery_status` ENUM('pending', 'in_transit', 'delivered', 'confirmed') DEFAULT 'pending',
  `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `delivered_at` DATETIME DEFAULT NULL,
  `auto_release_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`courier_id`) REFERENCES `courier_partners`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 12. Payments
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL UNIQUE,
  `payment_method` VARCHAR(50) DEFAULT 'Escrow Pay',
  `amount` DECIMAL(10,2) NOT NULL,
  `escrow_status` ENUM('held', 'released', 'refunded') DEFAULT 'held',
  `paid_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 13. Settlements
CREATE TABLE `settlements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_type` ENUM('farmer', 'courier') NOT NULL,
  `user_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `settlement_date` DATE NOT NULL,
  `status` ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
  `reference_no` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 14. Ratings
CREATE TABLE `ratings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `buyer_id` INT NOT NULL,
  `farmer_id` INT NOT NULL,
  `rating` INT CHECK (`rating` >= 1 AND `rating` <= 5),
  `review_text` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`buyer_id`) REFERENCES `buyers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`farmer_id`) REFERENCES `farmers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 15. Complaints & Disputes
CREATE TABLE `complaints` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `buyer_id` INT NOT NULL,
  `complaint_type` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `evidence_file` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('open', 'investigating', 'resolved', 'escalated', 'dismissed') DEFAULT 'open',
  `resolution_notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`buyer_id`) REFERENCES `buyers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 16. Notifications Log
CREATE TABLE `notifications_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `recipient_scope` ENUM('all', 'farmers', 'buyers', 'couriers', 'individual') NOT NULL,
  `recipient_id` INT DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `created_by` VARCHAR(100) DEFAULT 'Admin',
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 17. Courier Coverage
CREATE TABLE `courier_coverage` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `courier_id` INT NOT NULL,
  `district_id` INT NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  FOREIGN KEY (`courier_id`) REFERENCES `courier_partners`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 18. District Hub Mapping
CREATE TABLE `district_hub_mapping` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `district_id` INT NOT NULL,
  `hub_id` INT NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hub_id`) REFERENCES `hubs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 19. Platform Settings
CREATE TABLE `platform_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed Data Insertion
INSERT INTO `platform_settings` (`setting_key`, `setting_value`, `description`) VALUES
('commission_rate', '12.0', 'Platform commission percentage retained per order'),
('tier_base_fee_western', '150.00', 'Base delivery fee for Zone 1 (Western Province)'),
('tier_base_fee_central', '220.00', 'Base delivery fee for Zone 2 (Central/Upcountry)'),
('tier_base_fee_southern', '190.00', 'Base delivery fee for Zone 3 (Southern/Coastal)'),
('tier_base_fee_northern', '250.00', 'Base delivery fee for Zone 4 (Northern/Eastern)'),
('auto_release_timeout_hours', '48', 'Hours after delivery before escrow funds auto-release to farmer if buyer does not click Confirm Received');

-- Initial Zone Tiers
INSERT INTO `zone_fee_tiers` (`id`, `zone_name`, `base_fee`, `per_kg_fee`, `min_days`, `max_days`) VALUES
(1, 'Zone 1 - Western (Colombo/Gampaha/Kalutara)', 150.00, 15.00, 1, 1),
(2, 'Zone 2 - Central Highlands (Kandy/Nuwara Eliya/Matale)', 220.00, 25.00, 1, 2),
(3, 'Zone 3 - Southern Coast (Galle/Matara/Hambantota)', 190.00, 20.00, 1, 2),
(4, 'Zone 4 - Northern & Eastern (Jaffna/Kilinochchi/Batticaloa)', 250.00, 30.00, 2, 3);

-- Initial Hubs
INSERT INTO `hubs` (`id`, `hub_name`, `code`, `district`, `address`, `status`) VALUES
(1, 'Colombo Central Logistics Hub', 'HUB-CMB-01', 'Colombo', '45 Baseline Road, Colombo 09', 'active'),
(2, 'Nuwara Eliya Cold Chain Hub', 'HUB-NWE-01', 'Nuwara Eliya', '12 Agrarian Way, Nuwara Eliya', 'active'),
(3, 'Kandy Regional Sorting Center', 'HUB-KND-01', 'Kandy', '88 Peradeniya Road, Kandy', 'active'),
(4, 'Jaffna Northern Distribution Hub', 'HUB-JAF-01', 'Jaffna', '102 Hospital Road, Jaffna', 'active');

-- Initial Districts
INSERT INTO `districts` (`id`, `district_name`, `province`, `zone_fee_tier_id`, `primary_hub_id`) VALUES
(1, 'Colombo', 'Western', 1, 1),
(2, 'Gampaha', 'Western', 1, 1),
(3, 'Nuwara Eliya', 'Central', 2, 2),
(4, 'Kandy', 'Central', 2, 3),
(5, 'Matale', 'Central', 2, 3),
(6, 'Jaffna', 'Northern', 4, 4),
(7, 'Galle', 'Southern', 3, 1);

-- Default Admin User (Password: admin123)
INSERT INTO `admins` (`id`, `full_name`, `email`, `password_hash`, `role`) VALUES
(1, 'System Administrator', 'admin@harvestly.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', 'Super Admin');

-- Seed Farmers (Approved and Pending)
INSERT INTO `farmers` (`id`, `full_name`, `email`, `password_hash`, `phone`, `nic_number`, `farm_address`, `district`, `id_document_path`, `status`) VALUES
(1, 'Sunil Perera', 'sunil@farm.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 77 123 4567', '198214500123', 'Sunlight Highlands Farm, Moon Plains, Nuwara Eliya', 'Nuwara Eliya', 'nic_sunil_perera.jpg', 'approved'),
(2, 'Kamal Bandara', 'kamal@farm.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 71 987 6543', '198536700890', 'Mahaweli Green Farm, Dambulla Road, Matale', 'Matale', 'nic_kamal_bandara.jpg', 'approved'),
(3, 'Rohan Fernando', 'rohan@farm.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 76 345 6789', '199045600321', 'Vadamarachchi Orchards, Point Pedro Road, Jaffna', 'Jaffna', 'nic_rohan_fernando.jpg', 'pending');

-- Seed Courier Partners (Companies ONLY)
INSERT INTO `courier_partners` (`id`, `company_name`, `contact_person`, `email`, `password_hash`, `phone`, `brn_number`, `business_address`, `district`, `registration_cert_path`, `status`) VALUES
(1, 'Lanka Agro Logistics Pvt Ltd', 'Nimal Silva', 'logistics@lankaagro.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 11 234 5678', 'PV-0089123', '100 Union Place, Colombo 02', 'Colombo', 'brn_lanka_agro.pdf', 'approved'),
(2, 'Island Express Cargo Courier', 'Pradeep Kumara', 'info@islandexpress.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 81 445 6789', 'PV-0094567', '45 Kandy Road, Peradeniya', 'Kandy', 'brn_island_express.pdf', 'approved'),
(3, 'Ceylon Fresh Transit Co.', 'Dinesh Wijesinghe', 'contact@freshtransit.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 52 223 1199', 'PV-0103344', '18 Grand Hotel Road, Nuwara Eliya', 'Nuwara Eliya', 'brn_ceylon_fresh.pdf', 'pending');

-- Seed Buyers
INSERT INTO `buyers` (`id`, `full_name`, `email`, `password_hash`, `phone`, `province`, `district`, `address`, `status`) VALUES
(1, 'Kasun Jayasinghe', 'kasun@gmail.com', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 77 888 9999', 'Western', 'Colombo', '12 Rajagiriya Road, Colombo 08', 'active'),
(2, 'Anusha Wickramasinghe', 'anusha@gmail.com', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 75 111 2222', 'Central', 'Kandy', '56 Peradeniya Road, Kandy', 'active');

-- Seed Products
INSERT INTO `products` (`id`, `farmer_id`, `title`, `category`, `price_per_unit`, `unit_type`, `grade`, `district`, `image_url`, `status`) VALUES
(1, 1, 'Nuwara Eliya Crisp Carrots', 'Vegetables', 420.00, 'kg', 'Grade A', 'Nuwara Eliya', 'assets/images/carrots.jpg', 'active'),
(2, 2, 'Dambulla Red Onions', 'Vegetables', 380.00, 'kg', 'Grade A', 'Matale', 'assets/images/red_onions.jpg', 'active'),
(3, 3, 'Jaffna Karutha Colomban Mangoes', 'Fresh Fruits', 650.00, 'kg', 'Grade A', 'Jaffna', 'assets/images/mangoes.jpg', 'active'),
(4, 1, 'Highland Gotu Kola Bundle', 'Leafy Greens', 160.00, 'bundle', 'Grade A', 'Nuwara Eliya', 'assets/images/gotu_kola.jpg', 'active'),
(5, 2, 'Organic Ceylon Cinnamon', 'Spices & Staples', 890.00, '250g', 'Grade A', 'Matale', 'assets/images/cinnamon.jpg', 'active');

-- Seed Orders
INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `courier_id`, `total_amount`, `delivery_fee`, `status`, `delivery_address`, `district_id`) VALUES
(1, 'ORD-2026-1001', 1, 1, 1410.00, 150.00, 'delivered', '12 Rajagiriya Road, Colombo 08', 1),
(2, 'ORD-2026-1002', 2, 2, 890.00, 220.00, 'in_transit', '56 Peradeniya Road, Kandy', 4),
(3, 'ORD-2026-1003', 1, NULL, 650.00, 150.00, 'pending_assignment', '12 Rajagiriya Road, Colombo 08', 1);

-- Seed Order Items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 3.00, 420.00, 1260.00),
(2, 2, 2, 1.76, 380.00, 670.00),
(3, 3, 3, 1.00, 650.00, 650.00);

-- Seed Deliveries
INSERT INTO `deliveries` (`id`, `order_id`, `courier_id`, `pickup_status`, `delivery_status`, `assigned_at`, `delivered_at`, `auto_release_at`) VALUES
(1, 1, 1, 'picked_up', 'delivered', '2026-09-01 10:00:00', '2026-09-02 14:30:00', '2026-09-04 14:30:00'),
(2, 2, 2, 'picked_up', 'in_transit', '2026-09-03 09:15:00', NULL, NULL);

-- Seed Payments
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `escrow_status`) VALUES
(1, 1, 'Escrow Pay', 1410.00, 'released'),
(2, 2, 'Escrow Pay', 890.00, 'held'),
(3, 3, 'Escrow Pay', 650.00, 'held');

-- Seed Settlements
INSERT INTO `settlements` (`id`, `user_type`, `user_id`, `amount`, `settlement_date`, `status`, `reference_no`) VALUES
(1, 'farmer', 1, 1108.80, '2026-09-03', 'completed', 'SET-F-2026-001'),
(2, 'courier', 1, 150.00, '2026-09-03', 'completed', 'SET-C-2026-001');

-- Seed Complaints
INSERT INTO `complaints` (`id`, `order_id`, `buyer_id`, `complaint_type`, `description`, `evidence_file`, `status`) VALUES
(1, 1, 1, 'Damaged Goods', 'Carrots arrived bruised due to improper carton handling during transit.', 'complaint_evidence_1.jpg', 'open');

-- Seed Courier Coverage
INSERT INTO `courier_coverage` (`id`, `courier_id`, `district_id`, `status`) VALUES
(1, 1, 1, 'active'),
(2, 1, 2, 'active'),
(3, 2, 4, 'active'),
(4, 2, 5, 'active');

-- Seed District Hub Mapping
INSERT INTO `district_hub_mapping` (`id`, `district_id`, `hub_id`, `is_primary`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 1),
(3, 3, 2, 1),
(4, 4, 3, 1),
(5, 5, 3, 1),
(6, 6, 4, 1);
