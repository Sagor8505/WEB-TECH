<?php
session_start();
require_once('../model/userModel.php'); // DB functions

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'User not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['new_password'] ?? '');

    if (strlen($newPassword) < 4) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 4 characters!']);
        exit;
    }

    $id = (int)$_SESSION['user_id'];
    if (updateUserPassword($id, $newPassword)) {
        echo json_encode(['status'=>'success','message'=>'Password has been reset successfully!']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to update password. Try again.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
