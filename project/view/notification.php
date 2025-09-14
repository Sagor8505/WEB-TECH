<?php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && $_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
            $_SESSION['username'] = $_COOKIE['remember_user'];
        }
    } else {
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}
?>
<html>
<head>
    <title>Notifications</title>
    <link rel="stylesheet" type="text/css" href="../asset/dashboard.css">
</head>
<body>
    <h1>
        &#128276; Notifications (3)
    </h1>
    <form class="dashboard-form">
        <fieldset>
            <legend>Notification Settings</legend>
            <label><input type="checkbox" checked> Email Alerts</label><br><br>
            <label><input type="checkbox"> In-App Alerts</label>
        </fieldset>
    </form>
    <form class="dashboard-form">
        <fieldset>
            <legend>Notification Center</legend>
            <label><b>New Booking Confirmed</b> (Email)</label>
            <br>
            <span>Your car rental booking is confirmed!</span>
            <hr>
            <label><b>Pickup Reminder</b> (In-App)</label>
            <br>
            <span>You have a pickup scheduled for tomorrow.</span>
            <hr>
            <label><b>Loyalty Points Update</b> (In-App)</label>
            <br>
            <span>You've earned 20 new loyalty points!</span>
        </fieldset>
    </form>
    <form class="dashboard-form">
        <fieldset>
            <legend>Quick Actions</legend>
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'" />
        </fieldset>
    </form>
</body>
</html>
