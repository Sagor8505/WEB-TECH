<?php
require_once(__DIR__ . '/db.php');

/**
 * Bookings CSV source (same signature)
 */
function getBookings($from, $to, $username) {
    $con = getConnection();
    $sql = "SELECT 
                b.id,
                u.username, 
                u.email,
                COALESCE(rt.name, CONCAT('Type#', r.type)) AS room_type,
                r.room_number AS room_name,
                b.nights, 
                b.total_cost, 
                b.status, 
                b.booking_date
            FROM bookings b
            JOIN users u  ON b.user_id = u.id
            JOIN rooms r  ON b.room_id = r.id
            LEFT JOIN room_types rt ON r.type = rt.id
            WHERE b.booking_date BETWEEN ? AND ? 
              AND u.username = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $from, $to, $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Rooms CSV source (same signature)
 */
function getRoomRecords($from, $to, $username) {
    $con = getConnection();
    $sql = "SELECT DISTINCT
                r.id,
                r.room_number AS name,
                COALESCE(rt.name, CONCAT('Type#', r.type)) AS type,
                r.price,
                r.status,
                r.created_at
            FROM rooms r
            LEFT JOIN room_types rt ON r.type = rt.id
            JOIN bookings b ON b.room_id = r.id
            JOIN users u  ON b.user_id = u.id
            WHERE b.booking_date BETWEEN ? AND ? 
              AND u.username = ?
            ORDER BY r.room_number ASC";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $from, $to, $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
