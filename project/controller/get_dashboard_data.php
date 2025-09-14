<?php
session_start();
require_once('../model/userModel.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true || strtolower($_SESSION['role']) !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$totalUsers = getTotalUsers();
$activeBookings = getActiveRoomBookings();
$totalRooms = getTotalRooms();
$pendingMaintenance = getPendingMaintenanceRequests();

echo json_encode([
    'status' => 'success',
    'totalUsers' => $totalUsers,
    'activeBookings' => $activeBookings,
    'totalRooms' => $totalRooms,
    'pendingMaintenance' => $pendingMaintenance
]);
?>
