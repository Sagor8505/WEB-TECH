<?php
session_start();
require_once('../model/userModel.php');

$data = file_get_contents("php://input");
$user = json_decode($data);

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data received.']);
    exit;
}

$username = trim($user->username ?? '');
$password = trim($user->password ?? '');
$email    = trim($user->email ?? '');

if ($username === "" || $password === "" || $email === "") {
    echo json_encode(['status' => 'error', 'message' => 'Please fill the form correctly.']);
    exit;
}

$users = getAlluser();
$exists = false;

foreach ($users as $u) {
    if (isset($u['email']) && $u['email'] === $email) {
        $exists = true;
        break;
    }
}

if ($exists) {
    echo json_encode(['status' => 'error', 'message' => 'This email is already registered.']);
    exit;
}

$user = [
    'username' => $username,
    'password' => $password,
    'email'    => $email,
    'role'     => 'User'
];

$status = addUser($user);

if ($status) {
    echo json_encode(['status' => 'success', 'message' => 'Registration successful!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Registration failed. Try again.']);
}
?>
