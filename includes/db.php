<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "Masukojhol";

 // This should match your actual database name

// Create connection using MySQLi
$conn = new mysqli($host, $user, $password, $db  );

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check and add admin_note column if it does not exist in orders table
// $checkCol = $conn->query("SHOW COLUMNS FROM `orders` LIKE 'admin_note'");
// if ($checkCol && $checkCol->num_rows === 0) {
//     $conn->query("ALTER TABLE `orders` ADD COLUMN `admin_note` TEXT DEFAULT NULL");
// }
?>