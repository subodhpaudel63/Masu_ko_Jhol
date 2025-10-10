<?php
require_once 'db.php';

header('Content-Type: application/json');

// Fetch all bookings including the message column
$stmt = $conn->prepare("SELECT id, name, email, phone, booking_date, booking_time, people, message, status FROM bookings ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

echo json_encode(['ok' => true, 'bookings' => $bookings]);