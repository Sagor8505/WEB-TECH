<?php
// Allow session id from GET
if (isset($_GET[session_name()])) {
    session_id($_GET[session_name()]);
}
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../view/login.php?error=badrequest');
    exit;
}

// ... rest of your export code ...


require_once __DIR__ . '/../model/BookingAdminModel.php';

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to   = $_GET['to']   ?? date('Y-m-d');
$username = trim($_GET['username'] ?? '');

$rows = ba_listBookings($username, 'All', $from, $to, 1, 1000);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="bookings.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID','User','Room','Type','Nights','Total','Check-in','Check-out','Pickup','Status','Booking Date']);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['id'],
        $r['username'],
        $r['room_number'],
        $r['room_type'],
        $r['nights'],
        $r['total_cost'],
        $r['checkin_date'],
        $r['checkout_date'],
        $r['pickup_date'],
        $r['status'],
        $r['booking_date']
    ]);
}

fclose($out);
exit;
