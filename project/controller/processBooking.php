<?php
session_start();
require_once(__DIR__ . '/../model/bookingModel.php');

header('Content-Type: application/json');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']); exit;
}

$username = $_SESSION['username'] ?? '';
$userId = bm_getUserIdByUsername($username);
if ($userId <= 0) { echo json_encode(['status'=>'error','message'=>'Unable to resolve user']); exit; }

// Accept both old (vehicle_*) and new (room_*) param names
$roomId = (int)($_POST['room_id'] ?? ($_POST['vehicle_id'] ?? 0));
$nights = (int)($_POST['nights'] ?? 1);
$nights = max(1, $nights);

if ($roomId <= 0) { echo json_encode(['status'=>'error','message'=>'Missing room id']); exit; }

if (!bm_isRoomAvailable($roomId)) {
    echo json_encode(['status'=>'error','message'=>'Room not available']); exit;
}

$price = bm_getRoomPrice($roomId);
$total = $price * $nights;

$ok = bm_createBooking($userId, $roomId, $nights, $total);
if (!$ok) { echo json_encode(['status'=>'error','message'=>'Booking failed']); exit; }

echo json_encode(['status'=>'success','message'=>'Booking created','total_cost'=>$total]);
