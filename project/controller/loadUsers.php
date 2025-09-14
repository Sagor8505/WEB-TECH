<?php
session_start();
require_once('../model/userModel.php');
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}
$roleFilter = $_POST['roleFilter'] ?? 'All';
$allUsers = getAlluser();
$filteredUsers = [];
if ($roleFilter === 'All') {
    $filteredUsers = $allUsers;
} else {
    foreach ($allUsers as $u) if ($u['role'] === $roleFilter) $filteredUsers[] = $u;
}
echo json_encode(count($filteredUsers)>0?['status'=>'success','users'=>$filteredUsers]:['status'=>'error','message'=>'No users found.']);
?>
