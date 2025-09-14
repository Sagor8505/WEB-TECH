<?php
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    header('Location: ../view/login.php?error=badrequest');
    exit;
}
if (strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Forbidden: Only admin can export rooms.";
    exit;
}

require_once "../model/roomModel.php";

$from     = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to       = $_GET['to']   ?? date('Y-m-d');
$username = trim($_GET['username'] ?? '');

// TODO: implement this in roomModel
$rows = getAllRoomsWithBookings($from, $to);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=rooms.csv');

$output = fopen('php://output', 'w');
fputcsv($output, [
    'Room ID','Room Number','Type','Price','Status','Booked By','Booking Dates'
]);

foreach ($rows as $r) {
    if ($username !== '' && (!isset($r['username']) || stripos($r['username'], $username) === false)) continue;
    fputcsv($output, [
        $r['id'],
        $r['room_number'],
        $r['type'],
        $r['price'],
        $r['status'],
        $r['username'] ?? '',
        ($r['checkin_date'] ?? '') . " - " . ($r['checkout_date'] ?? '')
    ]);
}
fclose($output);
exit;
