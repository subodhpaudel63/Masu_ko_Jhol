<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/process_no_shows.php';

header('Content-Type: application/json');

// Process any expired no-shows first
processNoShows($conn);

// Read input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing booking ID or status']);
    exit;
}

$id = (int)$data['id'];
$newStatus = trim($data['status']);

// ── Validate status is in the allowed list ───────────────────────────────────
if (!in_array($newStatus, BOOKING_STATUSES)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status: ' . $newStatus]);
    exit;
}

// ── Fetch current booking to enforce state machine ───────────────────────────
$stmt = $conn->prepare("SELECT status, booking_date, booking_time FROM bookings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    $stmt->close();
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();
$currentStatus = $booking['status'];

// ── State machine: define valid transitions ──────────────────────────────────
$validTransitions = [
    'Pending'    => ['Confirmed', 'Cancelled'],
    'Confirmed'  => ['Checked-in', 'Cancelled'],
    'Checked-in' => ['Completed'],
    'Completed'  => [],
    'Cancelled'  => [],
    'No-show'    => [],
];

$allowed = $validTransitions[$currentStatus] ?? [];
if (!in_array($newStatus, $allowed)) {
    echo json_encode([
        'success' => false,
        'message' => "Cannot transition from '$currentStatus' to '$newStatus'."
    ]);
    exit;
}

// ── Compute grace_end_at when confirming ─────────────────────────────────────
$graceEndAt = null;
if ($newStatus === 'Confirmed') {
    $tz = new DateTimeZone(RESTAURANT_TIMEZONE);
    $bookingDT = DateTime::createFromFormat(
        'Y-m-d H:i:s',
        $booking['booking_date'] . ' ' . $booking['booking_time'],
        $tz
    );
    if ($bookingDT) {
        $bookingDT->modify('+' . NO_SHOW_GRACE_MINUTES . ' minutes');
        $graceEndAt = $bookingDT->format('Y-m-d H:i:s');
    }
}

// ── Execute the update ───────────────────────────────────────────────────────
if ($newStatus === 'Confirmed' && $graceEndAt !== null) {
    // Set grace_end_at when confirming
    $stmt = $conn->prepare("UPDATE bookings SET status = ?, grace_end_at = ? WHERE id = ?");
    $stmt->bind_param("ssi", $newStatus, $graceEndAt, $id);
} elseif ($newStatus === 'Checked-in') {
    // Clear grace_end_at when checked in (stop countdown, never mark no-show)
    $stmt = $conn->prepare("UPDATE bookings SET status = ?, grace_end_at = NULL WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $id);
} else {
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => "Booking status updated to $newStatus"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
