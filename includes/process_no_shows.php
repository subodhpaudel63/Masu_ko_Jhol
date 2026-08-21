<?php
/**
 * Centralized, idempotent no-show processing.
 * 
 * Transitions any 'Confirmed' bookings whose grace period has expired
 * (grace_end_at <= NOW) to 'No-show'. Safe to call multiple times.
 *
 * Requires: config/bootstrap.php loaded (for RESTAURANT_TIMEZONE),
 *           $conn (mysqli) available.
 */

if (!defined('RESTAURANT_TIMEZONE')) {
    require_once __DIR__ . '/../config/bootstrap.php';
}

/**
 * Mark expired-grace Confirmed bookings as No-show.
 *
 * @param mysqli $conn  Active database connection
 * @return int          Number of bookings marked as No-show
 */
function processNoShows(mysqli $conn): int
{
    $tz  = new DateTimeZone(RESTAURANT_TIMEZONE);
    $now = (new DateTime('now', $tz))->format('Y-m-d H:i:s');

    $stmt = $conn->prepare(
        "UPDATE bookings 
         SET status = 'No-show' 
         WHERE status = 'Confirmed' 
           AND grace_end_at IS NOT NULL 
           AND grace_end_at <= ?"
    );
    $stmt->bind_param("s", $now);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    return $affected;
}
