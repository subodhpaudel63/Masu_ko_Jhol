<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "alu";

 // This should match your actual database name

// Create connection using MySQLi
$conn = new mysqli($host, $user, $password, $db  );

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check and add order_number column if it does not exist in orders table
$checkCol = $conn->query("SHOW COLUMNS FROM `orders` LIKE 'order_number'");
if ($checkCol && $checkCol->num_rows === 0) {
    $conn->query("ALTER TABLE `orders` ADD COLUMN `order_number` VARCHAR(50) DEFAULT NULL AFTER `order_id`");
    $conn->query("ALTER TABLE `orders` ADD INDEX `idx_order_number` (`order_number`)");
    $conn->query("UPDATE `orders` SET `order_number` = CONCAT('ORD-', LPAD(`order_id`, 4, '0')) WHERE `order_number` IS NULL OR `order_number` = ''");
}

// Add google_id column to users table if it does not exist
$googleCol = $conn->query("SHOW COLUMNS FROM `users` LIKE 'google_id'");
if ($googleCol && $googleCol->num_rows === 0) {
    $conn->query("ALTER TABLE `users` ADD COLUMN `google_id` VARCHAR(255) DEFAULT NULL AFTER `user_img`");
    $conn->query("ALTER TABLE `users` ADD UNIQUE KEY `google_id` (`google_id`)");
}
?>