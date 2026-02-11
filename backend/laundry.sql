-- 1. Users Table (Managers, Employees, Customers)
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

-- 2. Main Order Table (General Order Info)
CREATE TABLE IF NOT EXISTS `Order` (
  `order_id` VARCHAR(20) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `tracking_code` INT(4) NOT NULL,
  
  -- Overall Order Status
  `status` ENUM('Pending Dropoff', 'Processing', 'Ready for Pickup', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending Dropoff',
  
  -- Service Details
  `services_requested` TEXT NOT NULL, 
  `supplies_requested` TEXT,          
  `bag_counts` TEXT NOT NULL,         
  `customer_note` TEXT,
  `estimated_price` DECIMAL(10,2) NOT NULL,
  
  -- Payment & Totals
  `additional_fees` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `final_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`order_id`),
  FOREIGN KEY (`customer_id`) REFERENCES `User`(`user_id`) ON DELETE CASCADE
);

-- 3. Process_Load Table (Individual Bag Tracking)
CREATE TABLE IF NOT EXISTS `Process_Load` (
  `load_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` VARCHAR(20) NOT NULL,
  
  -- Bag Type & Label
  `load_category` ENUM('Colored', 'White', 'Fold Only', 'Other') NOT NULL,
  `bag_label` VARCHAR(50) NOT NULL, -- e.g. "Colored #1"
  
  -- Detailed Status List
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
  
  PRIMARY KEY (`load_id`),
  FOREIGN KEY (`order_id`) REFERENCES `Order`(`order_id`) ON DELETE CASCADE
);

-- 4. System_Log Table (Audit History)
CREATE TABLE IF NOT EXISTS `System_Log` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `load_id` INT(11) NOT NULL, 
  `status_event` VARCHAR(50) NOT NULL, -- Records the status change
  `employee_name` VARCHAR(255) NOT NULL, -- Who did it
  `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`load_id`) REFERENCES `Process_Load`(`load_id`) ON DELETE CASCADE
);