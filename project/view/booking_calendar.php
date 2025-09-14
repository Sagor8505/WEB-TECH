<?php
require_once __DIR__ . '/../model/db.php';

// ==================== ROOM BOOKING ====================

function getAvailableRooms() {
    $conn = getConnection();
    $rooms = [];
    $sql = "SELECT r.id, r.room_number, COALESCE(rt.name, CONCAT('Type#', r.type)) AS type, r.price
            FROM rooms r
            LEFT JOIN room_types rt ON r.type = rt.id
            WHERE r.status = 'available'";
    $result = $conn->query($sql);
    if ($result) while ($row = $result->fetch_assoc()) $rooms[] = $row;
    return $rooms;
}

function createBooking($userId, $roomId, $nights) {
    $conn = getConnection();

    // compute dates + price
    $nights = max(1, (int)$nights);
    $stmt = $conn->prepare("SELECT price FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $price = (float)($stmt->get_result()->fetch_assoc()['price'] ?? 0);
    $total = $price * $nights;

    $checkin  = date('Y-m-d');
    $checkout = date('Y-m-d', strtotime("+$nights days"));

    $stmt = $conn->prepare("
        INSERT INTO bookings (user_id, room_id, booking_date, checkin_date, checkout_date, nights, total_cost, status, created_at)
        VALUES (?, ?, NOW(), ?, ?, ?, ?, 'Pending', NOW())
    ");
    $stmt->bind_param("iissid", $userId, $roomId, $checkin, $checkout, $nights, $total);
    $ok = $stmt->execute();

    if ($ok) {
        $stmt2 = $conn->prepare("UPDATE rooms SET status = 'booked' WHERE id = ?");
        $stmt2->bind_param("i", $roomId);
        $stmt2->execute();
    }
    return $ok;
}

function getRoomById($roomId) {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT r.room_number, COALESCE(rt.name, CONCAT('Type#', r.type)) AS type, r.price
        FROM rooms r
        LEFT JOIN room_types rt ON r.type = rt.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
