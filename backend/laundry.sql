CREATE TABLE IF NOT EXISTS `User` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('Manager', 'Employee', 'Customer') NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
);

-- 1. Main Order Table
CREATE TABLE IF NOT EXISTS `Order` (
  `order_id` VARCHAR(20) NOT NULL, -- Format: MMDDYYYYXXX
  `customer_id` INT(11) NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `tracking_code` INT(4) NOT NULL,
  `status` ENUM('Pending Dropoff', 'In Queue', 'Processing', 'Ready', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending Dropoff',
  
  -- Inputs
  `services_requested` TEXT NOT NULL, 
  `supplies_requested` TEXT,          
  `bag_counts` TEXT NOT NULL,         
  `customer_note` TEXT,
  `estimated_price` DECIMAL(10,2) NOT NULL,
  
  -- Financials
  `additional_fees` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `final_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`order_id`),
  FOREIGN KEY (`customer_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE
);

-- 2. Process_Load Table (The Physical Bags)
CREATE TABLE IF NOT EXISTS `Process_Load` (
  `load_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` VARCHAR(20) NOT NULL,
  
  -- Describes the bag (e.g., "Colored", "White")
  `load_category` ENUM('Colored', 'White', 'Fold Only', 'Other') NOT NULL,
  
  -- Display Label (e.g., "Colored #1", "White #1")
  `bag_label` VARCHAR(50) NOT NULL,
  
  -- Current Status of this specific bag
  `status` ENUM('Pending', 'In Queue', 'Washing', 'Wash Complete', 'Drying', 'Drying Complete', 'Folding', 'Folding Complete', 'Completed') NOT NULL DEFAULT 'Pending',
  
  `start_time` DATETIME DEFAULT NULL,
  `end_time` DATETIME DEFAULT NULL,
  
  PRIMARY KEY (`load_id`),
  FOREIGN KEY (`order_id`) REFERENCES `Order`(`order_id`) ON DELETE CASCADE
);