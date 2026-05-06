<?php
/**
 * api/booking.php
 * Receives booking form data via POST, inserts into database.
 * Returns JSON response.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../admin/includes/db.php';

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON body']);
    exit;
}

// Validate required fields
$required = ['fullName', 'email', 'phone', 'eventType', 'venue', 'eventDate', 'eventTime', 'duration', 'package'];
$errors = [];

foreach ($required as $field) {
    if (empty(trim($input[$field] ?? ''))) {
        $errors[] = $field . ' is required';
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors]);
    exit;
}

// Sanitize and prepare data
$data = [
    'name'       => htmlspecialchars(trim($input['fullName']), ENT_QUOTES, 'UTF-8'),
    'email'      => filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL),
    'phone'      => htmlspecialchars(trim($input['phone']), ENT_QUOTES, 'UTF-8'),
    'company'    => htmlspecialchars(trim($input['company'] ?? ''), ENT_QUOTES, 'UTF-8'),
    'event_type' => htmlspecialchars(trim($input['eventType']), ENT_QUOTES, 'UTF-8'),
    'date'       => $input['eventDate'],
    'time'       => htmlspecialchars(trim($input['eventTime']), ENT_QUOTES, 'UTF-8'),
    'venue'      => htmlspecialchars(trim($input['venue']), ENT_QUOTES, 'UTF-8'),
    'duration'   => htmlspecialchars(trim($input['duration']), ENT_QUOTES, 'UTF-8'),
    'package'    => htmlspecialchars(trim($input['package']), ENT_QUOTES, 'UTF-8'),
    'addons'     => htmlspecialchars(trim($input['addons'] ?? ''), ENT_QUOTES, 'UTF-8'),
    'notes'      => htmlspecialchars(trim($input['notes'] ?? ''), ENT_QUOTES, 'UTF-8'),
];

// Validate email format
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
    exit;
}

try {
    $db = get_db();
    $stmt = $db->prepare(
        'INSERT INTO bookings (name, email, phone, company, event_type, date, time, venue, duration, package, addons, notes, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['company'],
        $data['event_type'],
        $data['date'],
        $data['time'],
        $data['venue'],
        $data['duration'],
        $data['package'],
        $data['addons'],
        $data['notes'],
        'Pending',
    ]);

    $bookingId = $db->lastInsertId();

    echo json_encode([
        'status'     => 'ok',
        'message'    => 'Booking created successfully',
        'booking_id' => $bookingId,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
