<?php
require_once('db.php');

// --- Authentication ---
function login($user) {
    $con = getConnection();
    $username = mysqli_real_escape_string($con, $user['username']);
    $password = mysqli_real_escape_string($con, $user['password']);
    $sql = "SELECT * FROM users WHERE username='{$username}' AND password='{$password}'";
    $result = mysqli_query($con, $sql);
    return ($result && mysqli_num_rows($result) === 1);
}

// --- User CRUD ---
function addUser($user) {
    $con = getConnection();
    $role = isset($user['role']) ? $user['role'] : 'User';
    $username = mysqli_real_escape_string($con, $user['username']);
    $password = mysqli_real_escape_string($con, $user['password']);
    $email    = mysqli_real_escape_string($con, $user['email']);
    $roleEsc  = mysqli_real_escape_string($con, $role);

    $sql = "INSERT INTO users (username,password,email,role) 
            VALUES ('{$username}','{$password}','{$email}','{$roleEsc}')";
    return mysqli_query($con, $sql);
}

function getAlluser() {
    $con = getConnection();
    $result = mysqli_query($con, "SELECT * FROM users");
    $users = [];
    if($result) while($row = mysqli_fetch_assoc($result)) $users[] = $row;
    return $users;
}

function getUserById($id) {
    $con = getConnection();
    $id = (int)$id;
    $result = mysqli_query($con,"SELECT * FROM users WHERE id={$id}");
    return ($result && $row=mysqli_fetch_assoc($result)) ? $row : null;
}

function getUserByUsername($username) {
    $con = getConnection();
    $username = mysqli_real_escape_string($con, $username);
    $result = mysqli_query($con,"SELECT * FROM users WHERE username='{$username}'");
    return ($result && $row=mysqli_fetch_assoc($result)) ? $row : null;
}

/**
 * Update user profile (name, email, and optional profile picture)
 */
function updateUserProfile($id, $username, $email, $profilePath = null) {
    $con = getConnection();
    $id = (int)$id;

    if ($profilePath !== null) {
        $stmt = $con->prepare("UPDATE users SET username=?, email=?, profile=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $profilePath, $id);
    } else {
        $stmt = $con->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $email, $id);
    }

    return $stmt->execute();
}

function updateUser($user) {
    $con = getConnection();
    if(empty($user['id'])) return false;
    $id = (int)$user['id'];
    $username = mysqli_real_escape_string($con, $user['username']);
    $email = mysqli_real_escape_string($con, $user['email']);
    $sql = "UPDATE users SET username='{$username}', email='{$email}' WHERE id={$id}";
    return mysqli_query($con,$sql);
}

function deleteUser($id) {
    $con = getConnection();
    $id = (int)$id;
    return ($id>0) ? mysqli_query($con,"DELETE FROM users WHERE id={$id}") : false;
}

// --- Dashboard Stats ---
function getTotalUsers() {
    $con = getConnection();
    $res = mysqli_query($con,"SELECT COUNT(*) AS total_users FROM users");
    if($res && $row=mysqli_fetch_assoc($res)) return (int)$row['total_users'];
    return 0;
}

function getActiveRoomBookings() {
    $con = getConnection();
    $res = mysqli_query($con,"SELECT COUNT(*) AS active_bookings 
                              FROM bookings WHERE status IN ('Pending','Confirmed')");
    if($res && $row=mysqli_fetch_assoc($res)) return (int)$row['active_bookings'];
    return 0;
}

function getTotalRooms() {
    $con = getConnection();
    $res = mysqli_query($con,"SELECT COUNT(*) AS total_rooms FROM rooms");
    if($res && $row=mysqli_fetch_assoc($res)) return (int)$row['total_rooms'];
    return 0;
}

function getPendingMaintenanceRequests() {
    $con = getConnection();
    $res = mysqli_query($con,"SELECT COUNT(*) AS pending_requests 
                              FROM maintenance_requests WHERE status='Pending'");
    if($res && $row=mysqli_fetch_assoc($res)) return (int)$row['pending_requests'];
    return 0;
}

// --- User Dashboard ---
function getUserRoomBookings($userId) {
    $con = getConnection();
    $userId = (int)$userId;
    $res = mysqli_query($con,"SELECT COUNT(*) AS total_bookings FROM bookings WHERE user_id={$userId}");
    if($res && $row=mysqli_fetch_assoc($res)) return (int)$row['total_bookings'];
    return 0;
}

function getUpcomingCheckins($userId) {
    $con = getConnection();
    $userId = (int)$userId;
    $res = mysqli_query($con, "SELECT COUNT(*) AS upcoming_checkins 
                               FROM bookings 
                               WHERE user_id={$userId} 
                               AND pickup_date >= CURDATE() 
                               AND status IN ('Pending','Confirmed')");
    if($res && $row=mysqli_fetch_assoc($res)) return (int)$row['upcoming_checkins'];
    return 0;
}

function getLoyaltyPoints($userId) {
    $con = getConnection();
    $userId = (int)$userId;
    $res = mysqli_query($con,"SELECT points FROM loyalty_points WHERE user_id={$userId}");
    if($res && $row=mysqli_fetch_assoc($res)) return (int)$row['points'];
    return 0;
}
?>
