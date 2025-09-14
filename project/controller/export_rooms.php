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


require_once __DIR__ . '/../model/RoomAdminModel.php';

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to   = $_GET['to']   ?? date('Y-m-d');
$username = trim($_GET['username'] ?? '');

// You may filter rooms by $username if needed; here we export all
$rows = ra_listRooms($from, $to);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="rooms.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID','Room Number','Type','Status','Price','Features','Created At']);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['id'],
        $r['room_number'],
        $r['type'],
        $r['status'],
        $r['price'],
        $r['features'],
        $r['created_at']
    ]);
}

fclose($out);
exit;
