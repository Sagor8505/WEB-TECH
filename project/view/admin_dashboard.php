<?php
session_start();
require_once('../model/userModel.php');

// --- Admin Authentication ---
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header('location: ../view/login.php?error=badrequest');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Hotel Management</title>
<link rel="stylesheet" href="../asset/ad.css">
<style>
.card { max-width: 900px; margin: 20px auto; padding: 18px; background: #fff; border-radius: 8px; box-shadow: 0 0 8px #ddd; }
h1 { text-align: center; }
.summary label { font-weight: bold; margin-right: 8px; }
.summary span { font-weight: bold; color: #2c3e50; }
.button-container { text-align: center; margin-top: 12px; }
.button-container input { margin: 5px; padding: 8px 12px; border-radius: 6px; border: none; background-color: #2c3e50; color: #fff; cursor: pointer; }
.button-container input:hover { background-color: #34495e; }
</style>
</head>
<body>

<div class="card">
<h1>Hotel Admin Dashboard</h1>

<div class="summary">
    <p><label>Total Users:</label> <span id="totalUsers">0</span></p>
    <p><label>Active Room Bookings:</label> <span id="activeBookings">0</span></p>
    <p><label>Total Rooms:</label> <span id="totalRooms">0</span></p>
    <p><label>Pending Maintenance Requests:</label> <span id="pendingMaintenance">0</span></p>
</div>

<div class="button-container">
    <!-- These pages exist in your project -->
    <input type="button" value="Manage Users" onclick="window.location.href='admin_panel.php'">
    <input type="button" value="Manage Rooms" onclick="window.location.href='manage_rooms.php'">
    <input type="button" value="View Bookings" onclick="window.location.href='view_bookings.php'">
    <input type="button" value="Reports" onclick="window.location.href='reports.php'">
    <input type="button" value="Logout" onclick="window.location.href='../controller/logout.php'">
</div>
</div>

<script>
function updateDashboardData() {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', '../controller/get_dashboard_data.php', true);
    xhr.onload = function() {
        if(xhr.status === 200){
            const res = JSON.parse(xhr.responseText);
            if(res.status==='success'){
                document.getElementById('totalUsers').innerText = res.totalUsers;
                document.getElementById('activeBookings').innerText = res.activeBookings;
                document.getElementById('totalRooms').innerText = res.totalRooms;
                document.getElementById('pendingMaintenance').innerText = res.pendingMaintenance;
            }
        }
    };
    xhr.send();
}

// Auto-refresh every 15 seconds
updateDashboardData();
setInterval(updateDashboardData, 15000);
</script>

</body>
</html>
