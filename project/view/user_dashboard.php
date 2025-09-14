<?php
session_start();
require_once('../model/userModel.php');

// --- Session & Cookie Guard ---
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
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}

// --- Get logged-in user ID ---
$userId = $_SESSION['user_id'] ?? 0;

// --- Dashboard Function Calls (from userModel.php) ---
$totalBookings   = getUserRoomBookings($userId);   // Total room bookings
$upcomingCheckin = getUpcomingCheckins($userId);  // Next reservation/check-in
$loyaltyPoints   = getLoyaltyPoints($userId);     // User loyalty points
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - HotelEase</title>
    <link rel="stylesheet" type="text/css" href="../asset/dashboard.css">
    <script>
        // --- Auto-refresh Dashboard Data ---
        function updateDashboardData() {
            const xhttp = new XMLHttpRequest();
            xhttp.open('GET', '../controller/get_user_dashboard_data.php', true);
            xhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    const data = JSON.parse(this.responseText);
                    if (data.status === 'success') {
                        document.getElementById('totalBookings').innerText   = data.totalBookings;
                        document.getElementById('upcomingCheckin').innerText = data.upcomingCheckin;
                        document.getElementById('loyaltyPoints').innerText   = data.loyaltyPoints;
                    } else {
                        console.error("Error fetching data:", data.message);
                    }
                }
            };
            xhttp.send();
        }

        // Refresh every 15 seconds
        setInterval(updateDashboardData, 15000);
    </script>
</head>
<body>
<h1>Welcome to Your Hotel Dashboard</h1>

<form class="dashboard-form">
    <fieldset>
        <legend>Summary</legend>
        <label>My Room Bookings:</label>
        <span class="dashboard-number" id="totalBookings"><?php echo $totalBookings; ?></span><br><br>

        <label>Upcoming Check-in:</label>
        <span class="dashboard-number" id="upcomingCheckin"><?php echo $upcomingCheckin; ?></span><br><br>

        <label>Loyalty Points:</label>
        <span class="dashboard-number" id="loyaltyPoints"><?php echo $loyaltyPoints; ?></span><br><br>
    </fieldset>
</form>

<form class="dashboard-form">
    <fieldset>
        <legend>Quick Actions</legend>
        <input type="button" value="My Profile" onclick="window.location.href='profile.php'" />
        <input type="button" value="Contact Us" onclick="window.location.href='contact.php'" />
        <input type="button" value="Book a Room" onclick="window.location.href='room_inventory.php'" />
        <input type="button" value="Export Bookings" onclick="window.location.href='export.php'" />
        <input type="button" value="Activity Log" onclick="window.location.href='activitylog.php'" />
        <input type="button" value="Logout" onclick="window.location.href='../controller/logout.php'" />
    </fieldset>
</form>
</body>
</html>
