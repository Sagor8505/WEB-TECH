<?php
// model/BookingAdminModel.php
require_once __DIR__ . '/db.php';

function ba_conn() {
    if (function_exists('getConnection')) return getConnection();
    global $conn; if ($conn) return $conn;
    throw new Exception('No DB connection found');
}

/** List bookings with robust date filter and left joins */
function ba_listBookings(string $search, string $status, string $from, string $to, int $page, int $per): array {
    $c = ba_conn();
    $page = max(1,$page); $per = max(1,min(100,$per)); $off = ($page-1)*$per;

    $sql = "SELECT 
                b.id, b.user_id, b.room_id, b.nights, b.total_cost, b.status,
                DATE(b.booking_date)  AS booking_date,
                DATE(b.checkin_date)  AS checkin_date,
                DATE(b.checkout_date) AS checkout_date,
                DATE(b.pickup_date)   AS pickup_date,
                COALESCE(u.username, '—') AS username,
                COALESCE(u.email,    '—') AS email,
                COALESCE(r.room_number, '—') AS room_number,
                r.type AS room_type
            FROM bookings b
            LEFT JOIN users u  ON u.id  = b.user_id
            LEFT JOIN rooms r  ON r.id  = b.room_id
            WHERE b.booking_date >= ? 
              AND b.booking_date < DATE_ADD(?, INTERVAL 1 DAY)";

    $params = [$from, $to]; $types = 'ss';

    if ($search !== '') {
        $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR r.room_number LIKE ? OR r.type LIKE ?)";
        $like = "%$search%";
        array_push($params, $like, $like, $like, $like); $types .= 'ssss';
    }
    if (strtolower($status) !== 'all') {
        $sql .= " AND LOWER(b.status) = LOWER(?)";
        $params[] = $status; $types .= 's';
    }

    $sql .= " ORDER BY b.booking_date DESC, b.id DESC LIMIT ? OFFSET ?";
    $params[] = $per; $params[] = $off; $types .= 'ii';

    $stmt = $c->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $row['id']         = (int)$row['id'];
        $row['nights']     = (int)$row['nights'];
        $row['total_cost'] = (float)$row['total_cost'];
        $rows[] = $row;
    }
    return $rows;
}

/** Count for pagination (mirrors filters) */
function ba_countBookings(string $search, string $status, string $from, string $to): int {
    $c = ba_conn();
    $sql = "SELECT COUNT(*) AS c
            FROM bookings b
            LEFT JOIN users u  ON u.id  = b.user_id
            LEFT JOIN rooms r  ON r.id  = b.room_id
            WHERE b.booking_date >= ? 
              AND b.booking_date < DATE_ADD(?, INTERVAL 1 DAY)";
    $params = [$from, $to]; $types = 'ss';

    if ($search !== '') {
        $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR r.room_number LIKE ? OR r.type LIKE ?)";
        $like = "%$search%";
        array_push($params, $like, $like, $like, $like); $types .= 'ssss';
    }
    if (strtolower($status) !== 'all') {
        $sql .= " AND LOWER(b.status) = LOWER(?)";
        $params[] = $status; $types .= 's';
    }

    $stmt = $c->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['c'] ?? 0);
}

/** Confirm booking */
function ba_confirmBooking(int $bookingId): bool {
    $c = ba_conn();
    $stmt = $c->prepare("UPDATE bookings SET status='Confirmed' WHERE id=?");
    $stmt->bind_param("i", $bookingId);
    return $stmt->execute();
}

/** Reject: delete booking + free room */
function ba_rejectBooking(int $bookingId): bool {
    $c = ba_conn();
    $stmt = $c->prepare("SELECT room_id FROM bookings WHERE id=?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $room_id = (int)($stmt->get_result()->fetch_assoc()['room_id'] ?? 0);

    $stmt2 = $c->prepare("DELETE FROM bookings WHERE id=?");
    $stmt2->bind_param("i", $bookingId);
    $ok = $stmt2->execute();

    if ($ok && $room_id > 0) {
        $stmt3 = $c->prepare("UPDATE rooms SET status='Available' WHERE id=?");
        $stmt3->bind_param("i", $room_id);
        $stmt3->execute();
    }
    return $ok;
}
