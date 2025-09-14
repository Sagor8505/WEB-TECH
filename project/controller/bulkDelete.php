<?php
session_start();
require_once('../model/userModel.php');

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    echo json_encode(['status'=>'error', 'message'=>'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_ids'])) {
    $ids = json_decode($_POST['user_ids'], true);
    $deleted = 0;
    foreach ($ids as $id) {
        if (deleteUser((int)$id)) $deleted++;
    }
    echo json_encode(['status'=>'success', 'deleted'=>$deleted]);
    exit;
}

echo json_encode(['status'=>'error', 'message'=>'Invalid request']);
?>
