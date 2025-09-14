<?php
// controller/bookings_admin_handler.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../model/BookingAdminModel.php';

function j($ok,$msg='',$extra=[]){ echo json_encode(array_merge(['success'=>$ok,'message'=>$msg],$extra)); exit; }

$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);
if ($id <= 0) j(false, 'Invalid booking id');

if ($action === 'confirm') {
    $ok = ba_confirmBooking($id);    // sets status='Confirmed'
    j($ok, $ok ? 'Booking confirmed.' : 'Confirm failed');
}

if ($action === 'reject') {
    $ok = ba_rejectBooking($id);     // deletes row + frees room
    j($ok, $ok ? 'Booking rejected & deleted.' : 'Reject failed');
}

j(false, 'Bad request');
