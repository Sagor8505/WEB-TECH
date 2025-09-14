<?php
// model/bookingModel.php
require_once __DIR__ . '/db.php';

function bm_conn() {
  if (function_exists('getConnection')) return getConnection();
  global $conn; if ($conn) return $conn;
  throw new Exception('No DB connection found');
}

/**
 * List rooms available to book.
 * Your rooms.status uses Title Case (e.g., 'Available').
 */
function bm_getAvailableRooms(): array {
  $c = bm_conn();
  $stmt = $c->prepare("SELECT id, room_number, type, price FROM rooms WHERE status = 'Available' ORDER BY room_number ASC");
  $stmt->execute();
  $res = $stmt->get_result();
  return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

/** Fetch price + image path for a room */
function bm_getRoomDetails(int $roomId): ?array {
  $c = bm_conn();
  $stmt = $c->prepare("SELECT price, path FROM rooms WHERE id=? LIMIT 1");
  $stmt->bind_param("i", $roomId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  return $row ?: null;
}

/**
 * Create a booking:
 * - check room is 'Available'
 * - compute total = price * nights
 * - insert into bookings (status 'Pending', dates derived)
 * - set room to 'Booked' to avoid double booking
 */
function bm_createBooking(int $user_id, int $room_id, int $nights): array {
  $c = bm_conn();
  $c->begin_transaction();



  

  try {
    // lock row to avoid race condition
    $stmt = $c->prepare("SELECT price, status FROM rooms WHERE id=? FOR UPDATE");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    if (!$room) {
      $c->rollback();
      return ['ok'=>false, 'error'=>'Room not found'];
    }
    if (strcasecmp($room['status'], 'Available') !== 0) {
      $c->rollback();
      return ['ok'=>false, 'error'=>'Room is not available'];
    }

    $price = (float)$room['price'];
    $total = $price * max(1, $nights);

    // derive dates: today -> checkin, checkout = checkin + nights
    $checkin  = date('Y-m-d');
    $checkout = date('Y-m-d', strtotime("+$nights day"));

    // insert booking
    $sql = "INSERT INTO bookings
            (user_id, room_id, booking_date, checkin_date, checkout_date, nights, total_cost, pickup_date, status, created_at)
            VALUES (?, ?, NOW(), ?, ?, ?, ?, NULL, 'Pending', NOW())";
    $stmt2 = $c->prepare($sql);
    $stmt2->bind_param("iissid", $user_id, $room_id, $checkin, $checkout, $nights, $total);
    if (!$stmt2->execute()) {
      $c->rollback();
      return ['ok'=>false, 'error'=>'Could not create booking'];
    }
    $booking_id = $stmt2->insert_id;

    // mark room as Booked
    $stmt3 = $c->prepare("UPDATE rooms SET status='Booked' WHERE id=?");
    $stmt3->bind_param("i", $room_id);
    if (!$stmt3->execute()) {
      $c->rollback();
      return ['ok'=>false, 'error'=>'Could not update room status'];
    }

    $c->commit();
    return ['ok'=>true, 'booking_id'=>$booking_id, 'total_cost'=>$total];

  } catch (Throwable $e) {
    if ($c->errno) { /* mysqli */ }
    $c->rollback();
    return ['ok'=>false, 'error'=>'Unexpected error'];
  }
}
