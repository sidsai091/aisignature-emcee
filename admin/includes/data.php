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
            'price'      => (float)$row['price'],
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
        'price'      => (float)$row['price'],
        'addons'     => $row['addons'] ?? '',
        'notes'      => $row['admin_notes'] ?? '',
        'client_notes' => $row['notes'] ?? '',
        'status'     => $row['status'],
        'created'    => $row['created_at'],
    ];
}

/**
 * Update a booking's status, admin notes, and price.
 */
function update_booking($id, $status, $admin_notes, $price = null) {
    $db = get_db();
    if ($price !== null) {
        $stmt = $db->prepare('UPDATE bookings SET status = ?, admin_notes = ?, price = ? WHERE id = ?');
        return $stmt->execute([$status, $admin_notes, (float)$price, (int)$id]);
    } else {
        $stmt = $db->prepare('UPDATE bookings SET status = ?, admin_notes = ? WHERE id = ?');
        return $stmt->execute([$status, $admin_notes, (int)$id]);
    }
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
    
    // Calculate initial price
    $packagePrices = ['basic'=>550, 'standard'=>750, 'premium'=>150];
    $pkg = strtolower($data['package']);
    $price = 0;
    
    // 1. Base Package Price
    if ($pkg === 'premium') {
        $hours = match($data['duration']) {
            '2hrs'    => 2,
            '4hrs'    => 4,
            'fullday' => 8,
            default   => 2,
        };
        $price = 150 * $hours;
    } else {
        $price = $packagePrices[$pkg] ?? 0;
    }

    // 2. Add-ons Price
    $addons = $data['addons'] ?? '';
    if (stripos($addons, 'coordinator') !== false) {
        $price += 300;
    }
    if (stripos($addons, 'djcrew') !== false) {
        $price += 500;
    }

    $stmt = $db->prepare(
        'INSERT INTO bookings (name, email, phone, company, event_type, date, time, venue, duration, package, price, addons, notes, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
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
        $price,
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

    // Monthly revenue: sum price column for confirmed+completed bookings this month
    $stmt = $db->prepare(
        "SELECT SUM(price) as revenue FROM bookings
         WHERE DATE_FORMAT(date, '%Y-%m') = ?
         AND status IN ('Confirmed','Completed')"
    );
    $stmt->execute([$thisMonth]);
    $res = $stmt->fetch();
    $monthRevenue = (float)($res['revenue'] ?? 0);

    return compact('total','pending','confirmed','completed','cancelled','monthRevenue');
}

/**
 * Get monthly revenue data for chart.
 */
function get_monthly_revenue() {
    $db = get_db();
    $stmt = $db->query(
        "SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(price) as revenue
         FROM bookings
         WHERE status IN ('Confirmed','Completed')
         GROUP BY month
         ORDER BY month"
    );
    $rows = $stmt->fetchAll();
    
    $months = [];
    foreach ($rows as $row) {
        $months[$row['month']] = (float)$row['revenue'];
    }
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
