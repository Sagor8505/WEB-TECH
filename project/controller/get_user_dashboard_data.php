<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/bookingModel.php');  // model to handle hotel room bookings

// --- Session and Cookie Validation ---
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

// --- Role Validation ---
if (strtolower($_SESSION['role']) !== 'user') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

// --- Fetch Hotel User Data ---
$userId = $_SESSION['user_id'];
$totalBookings = getUserHotelBookings($userId);      // total room bookings
$upcomingCheckins = getUpcomingCheckins($userId);    // upcoming reservations
$rewardPoints = getRewardPoints($userId);            // hotel loyalty rewards

if ($totalBookings === false || $upcomingCheckins === false || $rewardPoints === false) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch user data.']);
    exit;
}

// --- Success Response ---
echo json_encode([
    'status' => 'success',
    'totalBookings' => $totalBookings,
    'upcomingCheckins' => $upcomingCheckins,
    'rewardPoints' => $rewardPoints
]);
?>
