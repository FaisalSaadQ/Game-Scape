-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: May 06, 2025 at 01:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e_commerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `street`, `city`, `zip_code`, `country`) VALUES
(1, 0, 'aa', 'aa', '22222', 'sa'),
(2, 0, 'aa', 'aa', 'aa', 'aa'),
(3, 0, 'aa', 'aa', 'aa', 'aaa');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `is_checked_out` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cart_id`, `user_id`, `session_id`, `is_checked_out`, `created_at`) VALUES
(1, NULL, 'sggh0ji4gr9ngri385cvflubgs', 0, '2025-05-05 16:05:31'),
(2, 0, NULL, 1, '2025-05-05 21:54:53'),
(3, NULL, 'dgm9jn56gipnkqvspjuat8ken7', 0, '2025-05-05 22:06:05'),
(4, 0, NULL, 0, '2025-05-05 22:42:09');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `variant_id`, `quantity`) VALUES
(11, 3, 8, 7),
(12, 2, 9, 3),
(19, 4, 8, 1),
(20, 4, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `parent_id`, `name`) VALUES
(1, NULL, 'Computers'),
(2, NULL, 'Furnitures'),
(3, NULL, 'Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `movement_id` bigint(20) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `qty_change` int(11) NOT NULL,
  `reason` enum('initial','sale','return','manual','cancel') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`movement_id`, `variant_id`, `qty_change`, `reason`, `reference_id`, `created_at`) VALUES
(1, 9, -3, 'sale', 2, '2025-05-05 22:24:55'),
(2, 8, -1, 'sale', 3, '2025-05-05 23:24:18'),
(3, 1, -1, 'sale', 3, '2025-05-05 23:24:18');

--
-- Triggers `inventory_movements`
--
DELIMITER $$
CREATE TRIGGER `trg_inv_mov_after_insert` AFTER INSERT ON `inventory_movements` FOR EACH ROW BEGIN
    UPDATE product_variants
       SET stock_qty = stock_qty + NEW.qty_change
     WHERE variant_id = NEW.variant_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_inv_mov_before_insert` BEFORE INSERT ON `inventory_movements` FOR EACH ROW BEGIN
    DECLARE current_qty INT;
    SELECT stock_qty INTO current_qty
      FROM product_variants
     WHERE variant_id = NEW.variant_id
     FOR UPDATE;
    IF current_qty + NEW.qty_change < 0 THEN
        SIGNAL SQLSTATE '45000'
           SET MESSAGE_TEXT = 'Insufficient stock for this variant';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
  `grand_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `status`, `grand_total`) VALUES
(1, 0, '2025-05-05 21:37:38', 'paid', 0.00),
(2, 0, '2025-05-05 22:24:55', 'paid', 4797.00),
(3, 0, '2025-05-05 23:24:18', 'paid', 7798.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `variant_id`, `quantity`, `unit_price`) VALUES
(1, 2, 9, 3, 1599.00),
(2, 3, 8, 1, 799.00),
(3, 3, 1, 1, 6999.00);

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `trg_orderitem_after_delete` AFTER DELETE ON `order_items` FOR EACH ROW BEGIN
    INSERT INTO inventory_movements (variant_id, qty_change, reason, reference_id)
    VALUES (OLD.variant_id,  OLD.quantity, 'cancel', OLD.order_id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_orderitem_after_insert` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
    INSERT INTO inventory_movements (variant_id, qty_change, reason, reference_id)
    VALUES (NEW.variant_id, -NEW.quantity, 'sale', NEW.order_id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `discontinued_at` timestamp NULL DEFAULT NULL,
  `spec` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`spec`))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `description`, `is_active`, `created_at`, `updated_at`, `discontinued_at`, `spec`) VALUES
(1, 1, 'ASUS TUF Gaming F15', 'ASUS TUF Gaming F15 is a powerful gaming laptop designed for performance.', 1, '2025-05-04 06:42:28', '2025-05-05 20:17:47', '0000-00-00 00:00:00', '[ \r\n  {\"spec_key\": \"Processor (CPU)\", \"spec_value\": \"Intel Core i7-12700H (14 cores, 20 threads)\"},\r\n  {\"spec_key\": \"Graphics (GPU)\", \"spec_value\": \"NVIDIA GeForce RTX 3060 6GB GDDR6\"},\r\n  {\"spec_key\": \"RAM\", \"spec_value\": \"16GB DDR4 3200MHz\"},\r\n  {\"spec_key\": \"Storage\", \"spec_value\": \"512GB NVMe SSD + 1TB HDD\"},\r\n  {\"spec_key\": \"Display\", \"spec_value\": \"15.6\\\" FHD 144Hz IPS\"}\r\n]'),
(2, 1, 'MSI Katana GF66', 'MSI Katana GF66 offers excellent value for gamers with solid performance.', 1, '2025-05-04 06:42:28', '2025-05-04 06:42:28', NULL, '[{\"spec_key\": \"Processor (CPU)\", \"spec_value\": \"Intel Core i5-12450H (12 cores, 16 threads)\"},\r\n    {\"spec_key\": \"Graphics (GPU)\", \"spec_value\": \"NVIDIA GeForce RTX 3050 Ti 4GB GDDR6\"},\r\n    {\"spec_key\": \"RAM\", \"spec_value\": \"8GB DDR4 3200MHz\"},\r\n    {\"spec_key\": \"Storage\", \"spec_value\": \"512GB NVMe SSD\"},\r\n    {\"spec_key\": \"Display\", \"spec_value\": \"15.6\\\" FHD 144Hz IPS\"}]'),
(3, 1, 'HP OMEN 16', 'HP OMEN 16 is a sleek, powerful laptop for gamers who demand performance.', 1, '2025-05-04 06:42:28', '2025-05-04 06:42:28', NULL, '[{\"spec_key\": \"Processor (CPU)\", \"spec_value\": \"AMD Ryzen 7 6800H (8 cores, 16 threads)\"},\r\n    {\"spec_key\": \"Graphics (GPU)\", \"spec_value\": \"NVIDIA GeForce RTX 3070 Ti 8GB GDDR6\"},\r\n    {\"spec_key\": \"RAM\", \"spec_value\": \"16GB DDR5 4800MHz\"},\r\n    {\"spec_key\": \"Storage\", \"spec_value\": \"1TB NVMe SSD\"},\r\n    {\"spec_key\": \"Display\", \"spec_value\": \"16.1\\\" QHD 165Hz IPS\"}]'),
(4, 1, 'Alienware Aurora R15', 'Alienware Aurora R15 delivers ultimate gaming performance in a sleek design.', 1, '2025-05-04 06:42:28', '2025-05-05 22:05:39', '0000-00-00 00:00:00', '[{\"spec_key\": \"Processor (CPU)\", \"spec_value\": \"AMD Ryzen 9 7900X (12 Cores, 24 Threads)\"},\r\n   {\"spec_key\": \"Graphics (GPU)\", \"spec_value\": \"NVIDIA GeForce RTX 4080 16GB GDDR6X\"},\r\n   {\"spec_key\": \"RAM\", \"spec_value\": \"32GB DDR5 6000MHz\"},\r\n   {\"spec_key\": \"Storage\", \"spec_value\": \"2TB NVMe Gen 4 SSD\"},\r\n   {\"spec_key\": \"Cooling System\", \"spec_value\": \"360mm Liquid Cooler + 6 RGB Fans\"}]'),
(5, 1, 'CyberPowerPC Gamer Xtreme VR', 'CyberPowerPC Gamer Xtreme VR series is optimized for high-performance gaming.', 1, '2025-05-04 06:42:28', '2025-05-04 06:42:28', NULL, '[{\"spec_key\": \"Processor (CPU)\", \"spec_value\": \"Intel Core i7-13700KF (16 cores, 24 threads)\"},\r\n   {\"spec_key\": \"Graphics (GPU)\", \"spec_value\": \"NVIDIA GeForce RTX 4070 Ti 12GB GDDR6X\"},\r\n   {\"spec_key\": \"RAM\", \"spec_value\": \"16GB DDR5 5600MHz\"},\r\n   {\"spec_key\": \"Storage\", \"spec_value\": \"1TB NVMe SSD + 2TB HDD\"},\r\n   {\"spec_key\": \"Power Supply\", \"spec_value\": \"800W 80+ Gold\"}]'),
(6, 1, 'iBUYPOWER SlateMR 291i', 'iBUYPOWER SlateMR 291i is an excellent entry-level gaming desktop.', 1, '2025-05-04 06:42:28', '2025-05-04 06:42:28', NULL, '[{\"spec_key\": \"Processor (CPU)\", \"spec_value\": \"AMD Ryzen 5 5600X (6 cores, 12 threads)\"},\r\n   {\"spec_key\": \"Graphics (GPU)\", \"spec_value\": \"NVIDIA GeForce RTX 3060 12GB GDDR6\"},\r\n   {\"spec_key\": \"RAM\", \"spec_value\": \"16GB DDR4 3200MHz\"},\r\n   {\"spec_key\": \"Storage\", \"spec_value\": \"500GB NVMe SSD\"},\r\n   {\"spec_key\": \"Case\", \"spec_value\": \"Slate MR Tempered Glass RGB\"}]'),
(7, 1, 'Skytech Chronos', 'Skytech Chronos offers sleek design with the latest gaming components.', 1, '2025-05-04 06:42:28', '2025-05-04 06:42:28', NULL, '[{\"spec_key\": \"Processor (CPU)\", \"spec_value\": \"Intel Core i9-13900K (24 cores, 32 threads)\"},\r\n   {\"spec_key\": \"Graphics (GPU)\", \"spec_value\": \"NVIDIA GeForce RTX 4090 24GB GDDR6X\"},\r\n   {\"spec_key\": \"RAM\", \"spec_value\": \"32GB DDR5 6000MHz\"},\r\n   {\"spec_key\": \"Storage\", \"spec_value\": \"2TB NVMe Gen 4 SSD\"},\r\n   {\"spec_key\": \"Cooling\", \"spec_value\": \"360mm RGB AIO Liquid Cooler\"}]'),
(8, 2, 'Ergonomic Gaming Chair', 'High-back ergonomic gaming chair with adjustable height and lumbar support.', 1, '2025-05-04 06:45:41', '2025-05-05 16:11:26', '0000-00-00 00:00:00', '[{\"spec_key\": \"Material\", \"spec_value\": \"PU Leather\"},\r\n   {\"spec_key\": \"Weight Capacity\", \"spec_value\": \"300 lbs\"},\r\n   {\"spec_key\": \"Adjustable Features\", \"spec_value\": \"Height, Armrests, Lumbar Support\"},\r\n   {\"spec_key\": \"Recline\", \"spec_value\": \"90-180 degrees\"},\r\n   {\"spec_key\": \"Color Options\", \"spec_value\": \"Black, Red, Blue, White\"}]'),
(9, 2, 'Standing Desk', 'Electric adjustable standing desk with memory presets and USB ports.', 1, '2025-05-04 06:45:41', '2025-05-05 20:04:05', '0000-00-00 00:00:00', '[{\"spec_key\": \"Desktop Size\", \"spec_value\": \"48\\\" x 24\\\"\"},\r\n   {\"spec_key\": \"Height Range\", \"spec_value\": \"28\\\" to 48\\\"\"},\r\n   {\"spec_key\": \"Weight Capacity\", \"spec_value\": \"175 lbs\"},\r\n   {\"spec_key\": \"Motor\", \"spec_value\": \"Dual Electric Motors\"},\r\n   {\"spec_key\": \"Special Features\", \"spec_value\": \"Memory Presets, USB Charging Ports\"}]'),
(10, 2, 'Gaming Desk', 'Spacious gaming desk designed for gamers with integrated cable management.', 1, '2025-05-04 06:45:41', '2025-05-04 07:01:05', NULL, '[{\"spec_key\": \"Dimensions\", \"spec_value\": \"63\\\" x 31.5\\\"\"},\r\n   {\"spec_key\": \"Material\", \"spec_value\": \"Carbon Fiber Surface + Steel Frame\"},\r\n   {\"spec_key\": \"Features\", \"spec_value\": \"Cable Management, Cup Holder, Headphone Hook\"},\r\n   {\"spec_key\": \"Color\", \"spec_value\": \"Black with RGB Lighting\"},\r\n   {\"spec_key\": \"Weight Capacity\", \"spec_value\": \"200 lbs\"}]'),
(12, 2, 'Gaming Recliner Sofa', 'Luxury gaming recliner sofa with leather finish and cup holders.', 1, '2025-05-04 06:45:41', '2025-05-05 20:18:08', '0000-00-00 00:00:00', '[{\"spec_key\": \"Material\", \"spec_value\": \"Premium Bonded Leather\"},\r\n   {\"spec_key\": \"Dimensions\", \"spec_value\": \"36\\\" W x 40\\\" D x 42\\\" H\"},\r\n   {\"spec_key\": \"Recline\", \"spec_value\": \"180 degrees\"},\r\n   {\"spec_key\": \"Features\", \"spec_value\": \"Cup Holders, Storage Pocket, Massage Function\"},\r\n   {\"spec_key\": \"Color Options\", \"spec_value\": \"Black, Brown, Red\"}]'),
(13, 3, 'Wireless Gaming Mouse', 'High-precision wireless gaming mouse with customizable DPI and RGB lighting.', 1, '2025-05-04 06:45:41', '2025-05-05 21:39:33', '0000-00-00 00:00:00', '[{\"spec_key\": \"Sensor\", \"spec_value\": \"PixArt PAW3370\"},\r\n   {\"spec_key\": \"DPI\", \"spec_value\": \"100-19000 (adjustable)\"},\r\n   {\"spec_key\": \"Polling Rate\", \"spec_value\": \"1000Hz (1ms)\"},\r\n   {\"spec_key\": \"Battery Life\", \"spec_value\": \"70 hours\"},\r\n   {\"spec_key\": \"Connectivity\", \"spec_value\": \"2.4GHz Wireless + Bluetooth\"}]'),
(14, 3, 'Mechanical Gaming Keyboard', 'Tactile mechanical gaming keyboard with RGB backlight and programmable keys.', 1, '2025-05-04 06:45:41', '2025-05-05 21:34:11', '0000-00-00 00:00:00', '[{\"spec_key\": \"Switch Type\", \"spec_value\": \"Gateron Red (Linear)\"},\r\n   {\"spec_key\": \"Backlight\", \"spec_value\": \"RGB Per-Key Lighting\"},\r\n   {\"spec_key\": \"Key Rollover\", \"spec_value\": \"N-Key Rollover\"},\r\n   {\"spec_key\": \"Connectivity\", \"spec_value\": \"Wired USB-C\"},\r\n   {\"spec_key\": \"Additional Features\", \"spec_value\": \"Detachable Wrist Rest, Media Controls\"}]'),
(15, 3, 'Gaming Headset', 'Comfortable surround sound gaming headset with noise-canceling microphone.', 1, '2025-05-04 06:45:41', '2025-05-04 06:45:41', NULL, '[{\"spec_key\": \"Driver Size\", \"spec_value\": \"50mm Neodymium\"},\r\n   {\"spec_key\": \"Frequency Response\", \"spec_value\": \"20Hz-20kHz\"},\r\n   {\"spec_key\": \"Microphone\", \"spec_value\": \"Noise-Canceling Boom Mic\"},\r\n   {\"spec_key\": \"Connectivity\", \"spec_value\": \"3.5mm + USB\"},\r\n   {\"spec_key\": \"Features\", \"spec_value\": \"7.1 Virtual Surround Sound, Memory Foam Earpads\"}]'),
(16, 3, 'Webcam Full HD', 'Full HD webcam with built-in microphone, ideal for streaming and video calls.', 1, '2025-05-04 06:45:41', '2025-05-04 06:45:41', NULL, '[{\"spec_key\": \"Resolution\", \"spec_value\": \"1080p at 30fps\"},\r\n   {\"spec_key\": \"Lens\", \"spec_value\": \"Glass, 78° FOV\"},\r\n   {\"spec_key\": \"Autofocus\", \"spec_value\": \"Yes\"},\r\n   {\"spec_key\": \"Microphone\", \"spec_value\": \"Built-in Dual Omnidirectional\"},\r\n   {\"spec_key\": \"Mounting\", \"spec_value\": \"Universal Clip, Tripod Compatible\"}]'),
(17, 3, 'Gaming Mouse Pad', 'Extra-large gaming mouse pad with RGB lighting and anti-slip base.', 1, '2025-05-04 06:45:41', '2025-05-04 06:45:41', NULL, '[{\"spec_key\": \"Dimensions\", \"spec_value\": \"31.5\\\" x 11.8\\\"\"},\r\n   {\"spec_key\": \"Surface\", \"spec_value\": \"Micro-textured Cloth\"},\r\n   {\"spec_key\": \"Base\", \"spec_value\": \"Rubber Anti-Slip\"},\r\n   {\"spec_key\": \"Lighting\", \"spec_value\": \"RGB Edge Lighting (16.8M colors)\"},\r\n   {\"spec_key\": \"Compatibility\", \"spec_value\": \"All Mice (Optical/Laser)\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `product_id_mapping`
--

CREATE TABLE `product_id_mapping` (
  `old_id` int(11) NOT NULL,
  `new_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `position` tinyint(3) UNSIGNED DEFAULT 0,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `filename`, `position`, `alt_text`, `created_at`) VALUES
(1, 1, 'asus-tuf-gaming-f15.png', 0, 'ASUS TUF Gaming F15', '2025-05-04 06:43:40'),
(2, 2, 'msi-katana-gf66.png', 0, 'MSI Katana GF66', '2025-05-04 06:43:40'),
(3, 3, 'hp-omen-16.png', 0, 'HP OMEN 16', '2025-05-04 06:43:40'),
(4, 4, 'alienware-aurora-r15.png', 0, 'Alienware Aurora R15', '2025-05-04 06:43:40'),
(5, 5, 'cyberpowerpc-gamer-xtreme.png', 0, 'CyberPowerPC Gamer Xtreme VR', '2025-05-04 06:43:40'),
(6, 6, 'ibuypower-slatemr-291i.png', 0, 'iBUYPOWER SlateMR 291i', '2025-05-04 06:43:40'),
(7, 7, 'skytech-chronos.png', 0, 'Skytech Chronos', '2025-05-04 06:43:40'),
(8, 8, 'gaming-chair.png', 0, 'Ergonomic Gaming Chair', '2025-05-04 06:47:51'),
(9, 9, 'standing-desk.png', 0, 'Standing Desk', '2025-05-04 06:47:51'),
(10, 10, 'gaming-desk.png', 0, 'gaming Desk', '2025-05-04 06:47:51'),
(12, 12, 'gaming-sofa.png', 0, 'Gaming Recliner Sofa', '2025-05-04 06:47:51'),
(13, 13, 'wireless-mouse.png', 0, 'Wireless Gaming Mouse', '2025-05-04 06:47:51'),
(14, 14, 'mechanical-keyboard.png', 0, 'Mechanical Gaming Keyboard', '2025-05-04 06:47:51'),
(15, 15, 'gaming-headset.png', 0, 'Gaming Headset', '2025-05-04 06:47:51'),
(16, 16, 'webcam.png', 0, 'Webcam Full HD', '2025-05-04 06:47:51'),
(17, 17, 'gaming-mousepad.png', 0, 'Gaming Mouse Pad', '2025-05-04 06:47:51');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `colour` varchar(30) NOT NULL,
  `size` varchar(10) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_after` decimal(10,2) DEFAULT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0 CHECK (`stock_qty` >= 0),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `sku`, `colour`, `size`, `price`, `price_after`, `stock_qty`, `created_at`) VALUES
(1, 1, 'SKU-1', 'Default', 'Standard', 6999.00, 6799.00, 9, '2025-05-04 06:43:15'),
(2, 2, 'SKU-2', 'Default', 'Standard', 5499.00, 5299.00, 10, '2025-05-04 06:43:15'),
(3, 3, 'SKU-3', 'Default', 'Standard', 7499.00, 7299.00, 10, '2025-05-04 06:43:15'),
(4, 4, 'SKU-4', 'Default', 'Standard', 15999.00, 15499.00, 10, '2025-05-04 06:43:15'),
(5, 5, 'SKU-5', 'Default', 'Standard', 8999.00, 8799.00, 10, '2025-05-04 06:43:15'),
(6, 6, 'SKU-6', 'Default', 'Standard', 5999.00, 5799.00, 10, '2025-05-04 06:43:15'),
(7, 7, 'SKU-7', 'Default', 'Standard', 10999.00, 10699.00, 10, '2025-05-04 06:43:15'),
(8, 8, 'SKU-8', 'Default', 'Standard', 799.00, 749.00, 9, '2025-05-04 06:47:38'),
(9, 9, 'SKU-9', 'Default', 'Standard', 1599.00, 1499.00, 7, '2025-05-04 06:47:38'),
(10, 10, 'SKU-10', 'Default', 'Standard', 1299.00, 1199.00, 10, '2025-05-04 06:47:38'),
(12, 12, 'SKU-12', 'Default', 'Standard', 1899.00, 1799.00, 10, '2025-05-04 06:47:38'),
(13, 13, 'SKU-13', 'Default', 'Standard', 349.00, 299.00, 10, '2025-05-04 06:47:38'),
(14, 14, 'SKU-14', 'Default', 'Standard', 499.00, 459.00, 10, '2025-05-04 06:47:38'),
(15, 15, 'SKU-15', 'Default', 'Standard', 599.00, 559.00, 10, '2025-05-04 06:47:38'),
(16, 16, 'SKU-16', 'Default', 'Standard', 299.00, 279.00, 10, '2025-05-04 06:47:38'),
(17, 17, 'SKU-17', 'Default', 'Standard', 199.00, 189.00, 10, '2025-05-04 06:47:38');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_locations`
--

CREATE TABLE `shop_locations` (
  `shop_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'Main Store',
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` char(97) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `is_manager` tinyint(1) DEFAULT 0,
  `failed_login_attempts` tinyint(4) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `registration_date`, `last_login`, `is_manager`, `failed_login_attempts`, `locked_until`, `created_at`) VALUES
(1, 'ali@gmail.com', '123456', 'First', 'Last', NULL, '2025-05-05 20:55:56', NULL, 0, 0, NULL, '2025-05-05 20:55:56'),
(2220000000, 'hassan@gamil.com', 'Q12345678q', 'Hassan', 'Alzourei', '0500000000', '2025-05-05 00:32:53', NULL, 0, 0, NULL, '2025-05-05 00:32:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `uq_open_cart_user` (`user_id`,`is_checked_out`),
  ADD UNIQUE KEY `uq_open_cart_session` (`session_id`,`is_checked_out`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `uq_cat_name` (`name`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_id_mapping`
--
ALTER TABLE `product_id_mapping`
  ADD PRIMARY KEY (`old_id`),
  ADD KEY `idx_new_id` (`new_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `uq_prod_colour_size` (`product_id`,`colour`,`size`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `uq_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `shop_locations`
--
ALTER TABLE `shop_locations`
  ADD PRIMARY KEY (`shop_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_locked` (`locked_until`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `movement_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shop_locations`
--
ALTER TABLE `shop_locations`
  MODIFY `shop_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2220000001;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
