<?php
session_start();
require_once('../model/activityLogModel.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$filter = json_decode($_POST['filter'], true);

$fromDate = $filter['fromDate'] ?? '2025-01-01';
$toDate   = $filter['toDate'] ?? date('Y-m-d');
$user     = $filter['user'] ?? '';

$logs = getActivityLog($fromDate, $toDate, $user);

echo json_encode($logs);
?>
