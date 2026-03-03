-- Users table
CREATE TABLE IF NOT EXISTS `User` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('Manager', 'Employee', 'Customer') NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token_hash` VARCHAR(64) NULL DEFAULT NULL,
  `reset_token_expires_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
);

-- Orders table
CREATE TABLE IF NOT EXISTS `Order` (
  `order_id` VARCHAR(20) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `tracking_code` INT(4) NOT NULL,
  
  -- Service info
  `services_requested` TEXT NOT NULL, 
  `supplies_requested` TEXT,          
  `bag_counts` TEXT NOT NULL,         
  `customer_note` TEXT,
  `estimated_price` DECIMAL(10,2) NOT NULL,
  
  -- Payment details
  `additional_fees` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `final_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`order_id`),
  FOREIGN KEY (`customer_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE
);

-- Bag tracking
CREATE TABLE IF NOT EXISTS `Process_Load` (
  `load_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` VARCHAR(20) NOT NULL,
  
  -- Bag details
  `load_category` ENUM('Colored', 'White', 'Fold Only', 'Other') NOT NULL,
  `bag_label` VARCHAR(50) NOT NULL, -- e.g. "Colored #1"
  
  -- Status options
  `status` ENUM(
      'Pending Dropoff', 
      'In Queue', 
      'Washing', 
      'Wash Complete', 
      'Drying', 
      'Drying Complete', 
      'Folding', 
      'Folding Complete', 
      'Awaiting Pickup', 
      'Completed',
      'Order Completed' 
  ) NOT NULL DEFAULT 'Pending Dropoff',
  
  `start_time` DATETIME DEFAULT NULL,
  `end_time` DATETIME DEFAULT NULL,
  `timer_paused` int(11) DEFAULT NULL,
  
  PRIMARY KEY (`load_id`),
  FOREIGN KEY (`order_id`) REFERENCES `Order`(`order_id`) ON DELETE CASCADE
);

-- Audit logs
CREATE TABLE IF NOT EXISTS `System_Log` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `load_id` INT(11) NOT NULL, 
  `status_event` VARCHAR(50) NOT NULL, -- Change event
  `employee_name` VARCHAR(255) NOT NULL, -- Employee
  `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`load_id`) REFERENCES `Process_Load`(`load_id`) ON DELETE CASCADE
);  

-- Shop Settings & Status Configuration
CREATE TABLE IF NOT EXISTS `Shop_Status` (
    `status_id` INT PRIMARY KEY,
    `is_shop_open` TINYINT(1) NOT NULL DEFAULT 1,
    `current_closing_time` TIME NULL,
    `next_manual_open_time` DATETIME NULL,
    `default_open_time` TIME NULL,
    `default_close_time` TIME NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default singleton configuration row
INSERT IGNORE INTO `Shop_Status` 
(`status_id`, `is_shop_open`, `current_closing_time`, `next_manual_open_time`, `default_open_time`, `default_close_time`) 
VALUES 
(1, 1, '20:00:00', '2026-02-23 08:00:00', '08:00:00', '21:00:00');