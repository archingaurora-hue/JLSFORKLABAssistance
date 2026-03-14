-- phpMyAdmin SQL Dump
-- Database: `laundry_db`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+08:00"; -- Asia/Manila

-- --------------------------------------------------------

--
-- Table structure for table `Shop_Status`
--

CREATE TABLE IF NOT EXISTS `Shop_Status` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_shop_open` tinyint(1) NOT NULL DEFAULT 1,
  `default_open_time` time NOT NULL DEFAULT '08:00:00',
  `default_close_time` time NOT NULL DEFAULT '20:00:00',
  `current_closing_time` time DEFAULT NULL,
  `next_manual_open_time` datetime DEFAULT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `Shop_Status`
--

INSERT INTO `Shop_Status` (`status_id`, `is_shop_open`, `default_open_time`, `default_close_time`, `current_closing_time`, `next_manual_open_time`) VALUES
(1, 1, '08:00:00', '20:00:00', NULL, NULL)
ON DUPLICATE KEY UPDATE status_id=1;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Customer','Employee','Manager') NOT NULL DEFAULT 'Customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `Order`
--

CREATE TABLE IF NOT EXISTS `Order` (
  `order_id` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `tracking_code` varchar(20) NOT NULL,
  `services_requested` varchar(255) NOT NULL,
  `supplies_requested` varchar(255) DEFAULT NULL,
  `bag_counts` varchar(255) NOT NULL,
  `customer_note` text DEFAULT NULL,
  `estimated_price` decimal(10,2) NOT NULL,
  `final_price` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `current_phase` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `Process_Load` (The Bags)
--

CREATE TABLE IF NOT EXISTS `Process_Load` (
  `load_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `load_category` varchar(50) NOT NULL,
  `bag_label` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending Dropoff',
  `timer_end` datetime DEFAULT NULL,
  PRIMARY KEY (`load_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `fk_process_order` FOREIGN KEY (`order_id`) REFERENCES `Order` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `Order_Logs`
--

CREATE TABLE IF NOT EXISTS `Order_Logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `log_message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `fk_log_order` FOREIGN KEY (`order_id`) REFERENCES `Order` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;