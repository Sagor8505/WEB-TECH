<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Customer Services</title>
<link rel="stylesheet" href="../asset/customer_services.css">
<script>
document.addEventListener('DOMContentLoaded', function () {
const buttons = document.querySelectorAll('.service-btn');
buttons.forEach(btn => {
btn.addEventListener('click', function (e) {
btn.disabled = true;
setTimeout(() => btn.disabled = false, 800);
});
});
});
</script>
</head>
<body>
<main class="container">
<h1>Customer Services</h1>
<p class="subtitle">Choose a service:</p>
<div class="grid">

    <a class="service-btn" href="room_facilities_report.php">Room Facilities Report</a>
    <a class="service-btn" href="room_rental_calculator.php">Room Rental Calculator</a>
    <a class="service-btn" href="reporting_options.php">Reporting Options</a>
    <a class="service-btn" href="hotel_locations.php">Hotel Locations</a>
    <a class="service-btn" href="inventory_tracking.php">Inventory Tracking</a>
    <a class="service-btn" href="maintenance_records.php">Maintenance Records</a>
    <a class="service-btn" href="loyalty_program.php">Loyalty Program</a> 
    <a class="service-btn" href="user_dashboard.php">Back To Dashboard</a>
    <a class="service-btn" href="profile.php">Back To Profile</a>
</div>


</div>
<footer class="foot"></footer>
</main>
</body>
</html>