<?php
session_start();
require_once('../model/userModel.php');

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    echo json_encode(['status'=>'error', 'message'=>'Unauthorized']);
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (deleteUser($id)) {
        echo json_encode(['status'=>'success']);
        exit;
    }
}

echo json_encode(['status'=>'error', 'message'=>'Delete failed']);
?>
