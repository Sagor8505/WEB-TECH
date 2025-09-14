<?php
// controller/RoomBooking_handler.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../model/bookingModel.php';
require_once __DIR__ . '/../model/pathHelpers.php';

function send($ok, $msg='', $extra=[]) {
  echo json_encode(array_merge(['success'=>$ok,'message'=>$msg], $extra)); exit;
}

// -------- GET: price & preview --------
if (isset($_GET['getPrice'])) {
  $roomId = (int)($_GET['roomId'] ?? 0);
  if ($roomId <= 0) send(false, 'Invalid room');
  $room = bm_getRoomDetails($roomId);
  if (!$room) send(false, 'Room not found');

  send(true, '', [
    'price' => (float)$room['price'],
    'path'  => room_image_url($room['path'] ?? '')
  ]);
}

// -------- POST: create booking --------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = (int)($_SESSION['user_id'] ?? 0);
  if ($user_id <= 0) $user_id = 1; // demo fallback

  $room_id = (int)($_POST['roomId'] ?? 0);
  $nights  = (int)($_POST['nights'] ?? 0);
  if ($room_id <= 0 || $nights <= 0) send(false, 'Select room and nights');

  $res = bm_createBooking($user_id, $room_id, $nights);
  if (!$res['ok']) send(false, $res['error'] ?? 'Booking failed');

  send(true, 'Booked', [
    'totalCost' => number_format((float)$res['total_cost'], 2),
    'bookingId' => (int)$res['booking_id']
  ]);
}

send(false, 'Bad request');
