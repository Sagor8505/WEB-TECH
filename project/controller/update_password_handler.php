<?php
session_start();
require_once('../model/userModel.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && (string)$_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
            $_SESSION['username'] = $_COOKIE['remember_user'];
        }
        if (!isset($_SESSION['role']) && isset($_COOKIE['remember_role'])) {
            $c = strtolower(trim((string)$_COOKIE['remember_role']));
            $_SESSION['role'] = ($c === 'admin') ? 'Admin' : 'User';
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
        exit;
    }
}

if (strtolower($_SESSION['role']) !== 'user') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User ID not found.']);
    exit;
}

$currentPassword = '';
$user = getUserById($id);
if ($user) {
    $currentPassword = $user['password'] ?? '';
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}

$errors = ['old' => '', 'new' => '', 'general' => ''];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass = trim($_POST['old_password'] ?? '');
    $newPass = trim($_POST['new_password'] ?? '');

    if ($oldPass === '') {
        $errors['old'] = 'Enter old password!';
    } else {
        if ($oldPass !== $currentPassword) {
            $errors['old'] = 'Old password is incorrect!';
        }
    }

    if ($newPass === '') {
        $errors['new'] = 'Enter new password!';
    } elseif (strlen($newPass) < 4) {
        $errors['new'] = 'New password must be at least 4 characters!';
    } elseif ($oldPass !== '' && $oldPass === $newPass) {
        $errors['new'] = 'New password must be different from old password!';
    }

    if (empty($errors['old']) && empty($errors['new'])) {
        if ($id > 0) {
            $con = getConnection();
            $safePass = mysqli_real_escape_string($con, $newPass);
            $sql = "UPDATE users SET password='{$safePass}' WHERE id=" . (int)$id;
            if (mysqli_query($con, $sql)) {
                $success = 'Password updated successfully!';
                $_SESSION['auth_password'] = $newPass;
                echo json_encode(['status' => 'success', 'message' => $success]);
            } else {
                $errors['general'] = 'Unable to update password. Try again.';
                echo json_encode(['status' => 'error', 'message' => $errors['general']]);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    }
}
?>
