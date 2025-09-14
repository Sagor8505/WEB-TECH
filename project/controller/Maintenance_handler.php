<?php
session_start();
require_once __DIR__ . '/../model/MaintenanceModel.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_issue'], $_POST['room_id'])) {
        $issue = trim($_POST['new_issue']);
        $room  = (int)$_POST['room_id'];
        $ok = addService($room, $issue, 'Open');
        echo json_encode($ok ? ['success'=>true] : ['success'=>false,'message'=>'Create failed']); exit;
    }
    if (isset($_POST['delete_id'])) {
        $ok = deleteService((int)$_POST['delete_id']);
        echo json_encode($ok ? ['success'=>true] : ['success'=>false,'message'=>'Delete failed']); exit;
    }
    if (isset($_POST['set_status_id'], $_POST['status'])) {
        $ok = setServiceStatus((int)$_POST['set_status_id'], (string)$_POST['status']);
        echo json_encode($ok ? ['success'=>true] : ['success'=>false,'message'=>'Update failed']); exit;
    }
}
echo json_encode(['success'=>false,'message'=>'Bad request']);
