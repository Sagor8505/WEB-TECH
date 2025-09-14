<?php
session_start();
require_once('../model/userModel.php');

// Admin check
if (!isset($_SESSION['status'], $_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized access.']); exit;
}

$id = (int)($_POST['id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$profilePath = '';

if ($id <= 0) { echo json_encode(['status'=>'error','message'=>'Invalid user ID.']); exit; }
if ($username === '') { echo json_encode(['status'=>'error','message'=>'Username is required.']); exit; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['status'=>'error','message'=>'Valid email is required.']); exit; }

// Profile upload
if (isset($_FILES['profile']) && $_FILES['profile']['error'] !== UPLOAD_ERR_NO_FILE) {
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext,$allowed)) { echo json_encode(['status'=>'error','message'=>'Invalid file type.']); exit; }
    $fileName = 'profile_'.$id.'_'.time().'.'.$ext;
    $destPath = '../asset/uploads/'.$fileName;
    if (!move_uploaded_file($_FILES['profile']['tmp_name'],$destPath)) {
        echo json_encode(['status'=>'error','message'=>'Profile upload failed.']); exit;
    }
    $profilePath = 'asset/uploads/'.$fileName;
}

// Update DB
$con = getConnection();
$safeUser = mysqli_real_escape_string($con, $username);
$safeEmail = mysqli_real_escape_string($con, $email);
$sql = "UPDATE users SET username='{$safeUser}', email='{$safeEmail}'";
if ($profilePath) $sql .= ", profile='{$profilePath}'";
$sql .= " WHERE id={$id}";

if (mysqli_query($con,$sql)) {
    echo json_encode(['status'=>'success','message'=>'User updated successfully.']);
} else {
    echo json_encode(['status'=>'error','message'=>'Update failed. Try again.']);
}
?>
