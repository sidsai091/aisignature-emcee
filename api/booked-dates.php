<?php
/**
 * api/booked-dates.php
 * Returns booked dates as JSON for the booking form calendar.
 * Dates with status Confirmed or Pending are considered unavailable.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../admin/includes/db.php';

try {
    $db = get_db();
    $stmt = $db->query("SELECT DISTINCT date FROM bookings WHERE status IN ('Pending','Confirmed') ORDER BY date");
    $bookedDates = array_column($stmt->fetchAll(), 'date');

    echo json_encode([
        'booked' => $bookedDates,
        'status' => 'ok'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'booked' => [],
        'status' => 'error',
        'message' => 'Database error'
    ]);
}
