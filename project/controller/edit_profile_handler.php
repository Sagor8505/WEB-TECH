<?php
session_start();
require_once('../model/userModel.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$name   = trim($_POST['name'] ?? '');
$email  = trim($_POST['email'] ?? '');

$errors = [];

// --- Validation ---
if ($name === '') $errors['name'] = "Name cannot be empty";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format";

$avatarPath = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($_FILES['avatar']['type'], $allowed)) {
        $errors['avatar'] = "Only JPG, PNG, or WebP allowed";
    } else {
        // Ensure upload folder exists
        $uploadDir = __DIR__ . '/../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $newFile = 'profile_' . $userId . '_' . time() . '.' . $ext;
        $target  = $uploadDir . $newFile;

        // Delete old avatar if exists
        $oldUser = getUserById($userId);
        if (!empty($oldUser['profile'])) {
            $oldPath = __DIR__ . '/../' . $oldUser['profile'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Move new file
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            $avatarPath = 'uploads/profiles/' . $newFile; // relative path saved in DB
        } else {
            $errors['avatar'] = "Failed to upload file";
        }
    }
}

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit;
}

// --- Update in DB ---
$ok = updateUserProfile($userId, $name, $email, $avatarPath);

if ($ok) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Profile updated successfully',
        'avatar'  => $avatarPath
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}
