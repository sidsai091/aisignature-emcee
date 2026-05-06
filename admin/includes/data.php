<?php
// =============================================
// DATA LAYER — Real MySQL queries
// =============================================

require_once __DIR__ . '/db.php';

/**
 * Get all bookings, ordered by created_at DESC.
 */
function get_bookings() {
    $db = get_db();
    $stmt = $db->query('SELECT * FROM bookings ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();

    // Map to legacy format used by views
    return array_map(function($row) {
        return [
            'id'         => $row['id'],
            'name'       => $row['name'],
            'email'      => $row['email'],
            'phone'      => $row['phone'],
            'company'    => $row['company'] ?? '',
            'event_type' => $row['event_type'],
            'date'       => $row['date'],
            'time'       => $row['time'],
            'venue'      => $row['venue'],
            'duration'   => $row['duration'],
            'package'    => ucfirst($row['package']),
            'addons'     => $row['addons'] ?? '',
            'notes'      => $row['admin_notes'] ?? '',
            'client_notes' => $row['notes'] ?? '',
            'status'     => $row['status'],
            'created'    => $row['created_at'],
        ];
    }, $rows);
}

/**
 * Get a single booking by ID.
 */
function get_booking_by_id($id) {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM bookings WHERE id = ?');
    $stmt->execute([(int)$id]);
    $row = $stmt->fetch();

    if (!$row) return null;

    return [
        'id'         => $row['id'],
        'name'       => $row['name'],
        'email'      => $row['email'],
        'phone'      => $row['phone'],
        'company'    => $row['company'] ?? '',
        'event_type' => $row['event_type'],
        'date'       => $row['date'],
        'time'       => $row['time'],
        'venue'      => $row['venue'],
        'duration'   => $row['duration'],
        'package'    => ucfirst($row['package']),
        'addons'     => $row['addons'] ?? '',
        'notes'      => $row['admin_notes'] ?? '',
        'client_notes' => $row['notes'] ?? '',
        'status'     => $row['status'],
        'created'    => $row['created_at'],
    ];
}

/**
 * Update a booking's status and admin notes.
 */
function update_booking($id, $status, $admin_notes) {
    $db = get_db();
    $stmt = $db->prepare('UPDATE bookings SET status = ?, admin_notes = ? WHERE id = ?');
    return $stmt->execute([$status, $admin_notes, (int)$id]);
}

/**
 * Delete a booking by ID.
 */
function delete_booking($id) {
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM bookings WHERE id = ?');
    return $stmt->execute([(int)$id]);
}

/**
 * Insert a new booking. Returns the new booking ID.
 */
function insert_booking($data) {
    $db = get_db();
    $stmt = $db->prepare(
        'INSERT INTO bookings (name, email, phone, company, event_type, date, time, venue, duration, package, addons, notes, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['company'] ?? '',
        $data['event_type'],
        $data['date'],
        $data['time'],
        $data['venue'],
        $data['duration'],
        $data['package'],
        $data['addons'] ?? '',
        $data['notes'] ?? '',
        'Pending',
    ]);
    return $db->lastInsertId();
}

/**
 * Get dashboard statistics.
 */
function get_stats() {
    $db = get_db();
    $thisMonth = date('Y-m');

    $stmt = $db->query('SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status');
    $counts = $stmt->fetchAll();

    $total = 0; $pending = 0; $confirmed = 0; $completed = 0; $cancelled = 0;
    foreach ($counts as $row) {
        $total += $row['cnt'];
        match($row['status']) {
            'Pending'   => $pending   = $row['cnt'],
            'Confirmed' => $confirmed = $row['cnt'],
            'Completed' => $completed = $row['cnt'],
            'Cancelled' => $cancelled = $row['cnt'],
            default     => null,
        };
    }

    // Monthly revenue: sum package prices for confirmed+completed bookings this month
    $packagePrices = ['basic'=>550, 'standard'=>750, 'premium'=>150];

    $stmt = $db->prepare(
        "SELECT package, duration FROM bookings
         WHERE DATE_FORMAT(date, '%Y-%m') = ?
         AND status IN ('Confirmed','Completed')"
    );
    $stmt->execute([$thisMonth]);
    $monthBookings = $stmt->fetchAll();

    $monthRevenue = 0;
    foreach ($monthBookings as $b) {
        $pkg = strtolower($b['package']);
        if ($pkg === 'premium') {
            // RM150/hr — estimate from duration
            $hours = match($b['duration']) {
                '2hrs'    => 2,
                '4hrs'    => 4,
                'fullday' => 8,
                default   => 2,
            };
            $monthRevenue += 150 * $hours;
        } else {
            $monthRevenue += $packagePrices[$pkg] ?? 0;
        }
    }

    return compact('total','pending','confirmed','completed','cancelled','monthRevenue');
}

/**
 * Get monthly revenue data for chart.
 */
function get_monthly_revenue() {
    $db = get_db();
    $packagePrices = ['basic'=>550, 'standard'=>750, 'premium'=>150];

    $stmt = $db->query(
        "SELECT DATE_FORMAT(date, '%Y-%m') as month, package, duration
         FROM bookings
         WHERE status IN ('Confirmed','Completed')
         ORDER BY date"
    );
    $rows = $stmt->fetchAll();

    $months = [];
    foreach ($rows as $b) {
        $m = $b['month'];
        $pkg = strtolower($b['package']);
        if ($pkg === 'premium') {
            $hours = match($b['duration']) {
                '2hrs'    => 2,
                '4hrs'    => 4,
                'fullday' => 8,
                default   => 2,
            };
            $months[$m] = ($months[$m] ?? 0) + (150 * $hours);
        } else {
            $months[$m] = ($months[$m] ?? 0) + ($packagePrices[$pkg] ?? 0);
        }
    }
    ksort($months);
    return $months;
}

/**
 * Get booked dates (for calendar).
 */
function get_booked_dates() {
    $db = get_db();
    $stmt = $db->query("SELECT DISTINCT date FROM bookings WHERE status IN ('Pending','Confirmed')");
    return array_column($stmt->fetchAll(), 'date');
}

/**
 * Status badge CSS class.
 */
function status_class($status) {
    return match($status) {
        'Confirmed'  => 'badge-confirmed',
        'Pending'    => 'badge-pending',
        'Completed'  => 'badge-completed',
        'Cancelled'  => 'badge-cancelled',
        default      => ''
    };
}
