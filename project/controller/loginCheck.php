<?php
// Force PHP session cookie to be valid for the entire project
session_set_cookie_params([
    'lifetime' => 0,   // session ends when browser closes
    'path'     => '/', // 👈 makes cookie valid across /view/, /controller/, /model/
    'domain'   => '',  // current domain
    'secure'   => false, // set true if using HTTPS
    'httponly' => true
]);
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
$remember = isset($user->remember) && $user->remember === '1';

if ($username === "" || $password === "") {
    echo json_encode(['status' => 'error', 'message' => 'Please enter both username and password.']);
    exit;
}

$userData = ['username' => $username, 'password' => $password];
$status = login($userData);

if (!$status) {
    echo json_encode(['status' => 'error', 'message' => 'Username/Password is not valid']);
    exit;
}

$con = getConnection();
if (!$con) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection error.']);
    exit;
}

$u_safe = mysqli_real_escape_string($con, $username);
$p_safe = mysqli_real_escape_string($con, $password);
$sql = "SELECT * FROM users WHERE username='{$u_safe}' AND password='{$p_safe}' LIMIT 1";
$result = mysqli_query($con, $sql);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Database query error.']);
    exit;
}

$row = mysqli_fetch_assoc($result);
if (!$row) {
    echo json_encode(['status' => 'error', 'message' => 'Username/Password is not valid']);
    exit;
}

$role = strtolower(trim((string)($row['role'] ?? '')));

$_SESSION['role']     = $role === 'admin' ? 'admin' : 'user';
session_regenerate_id(true);
$_SESSION['status']   = true;
$_SESSION['username'] = $row['username'];
$_SESSION['user_id']  = (int)($row['id'] ?? 0);

if ($remember) {
    $exp = time() + 86400 * 30;
    setcookie('status', '1', $exp, '/');
    setcookie('remember_user', $_SESSION['username'], $exp, '/');
    setcookie('remember_role', $_SESSION['role'], $exp, '/');
}

echo json_encode(['status' => 'success', 'role' => $role]);
