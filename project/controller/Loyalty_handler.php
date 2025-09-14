<?php
session_start();
require_once __DIR__ . '/../model/bookingModel.php';
require_once __DIR__ . '/../model/LoyaltyModel.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 1;

// --- Room booking (kept as-is if you post bookingRoomId) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookingRoomId'])) {
    $roomId = (int)($_POST['bookingRoomId'] ?? 0);
    $nights = max(1, (int)($_POST['nights'] ?? 1));

    if ($roomId <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid room']); exit; }

    $price = bm_getRoomPrice($roomId);
    $totalCost = $price * $nights;

    $ok = bm_createBooking($user_id, $roomId, $nights, $totalCost);
    if ($ok) {
        echo json_encode(['success'=>true,'message'=>'Room booked successfully!','total'=>$totalCost]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed to book room.']);
    }
    exit;
}

// --- Loyalty program endpoints ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeemPoints'])) {
    $redeem = (int)$_POST['redeemPoints'];
    $state  = redeemLoyaltyPoints($user_id, $redeem);
    $curr   = getLoyaltyPoints($user_id);
    $prog   = getLoyaltyProgress((int)$curr['points'], $curr['tier'], 5000);
    echo json_encode(['success'=>true, 'points'=>$curr['points'], 'tier'=>$curr['tier'], 'progress'=>$prog]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['status']) && $_GET['status']==='loyalty') {
    $curr = getLoyaltyPoints($user_id);
    $prog = getLoyaltyProgress((int)$curr['points'], $curr['tier'], 5000);
    echo json_encode(['success'=>true, 'points'=>$curr['points'], 'tier'=>$curr['tier'], 'progress'=>$prog]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Bad request']);
