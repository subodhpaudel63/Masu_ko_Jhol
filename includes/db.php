<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "Masukojhol"; // This should match your actual database name

// Create connection using MySQLi
$conn = new mysqli($host, $user, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optionally, you can also check if the database exists and create it if not
// $conn->query("CREATE DATABASE IF NOT EXISTS `$db`");
// $conn->select_db($db);
?>