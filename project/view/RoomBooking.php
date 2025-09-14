<?php
session_start();
require_once('../model/roomModel.php');
require_once('../model/pathHelpers.php');
require_once('../model/db.php'); // DB connection

// Validate ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("Invalid room ID");
}

$roomId = (int)$_GET['id'];
$room   = getRoomById($roomId);

if (!$room) {
    die("Room not found");
}

// Build feature map to show names instead of IDs
$allFeatures = getRoomFeatures();
$featureMap = [];
foreach ($allFeatures as $f) {
    $featureMap[$f['id']] = $f['name'];
}
function renderFeatures($csv, $map) {
    if (!$csv) return "—";
    $ids = array_filter(array_map('trim', explode(',', $csv)));
    $names = [];
    foreach ($ids as $id) {
        if (isset($map[$id])) $names[] = $map[$id];
    }
    return $names ? implode(", ", $names) : htmlspecialchars($csv);
}

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Handle booking form submission
$success = "";
$error   = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId   = $_SESSION['user_id'] ?? null;
    $checkin  = $_POST['checkin_date'] ?? '';
    $nights   = (int)($_POST['nights'] ?? 0);
    $checkout = $_POST['checkout_date'] ?? '';
    $conn     = getConnection();

    if (!$userId) {
        $error = "You must be logged in to book a room.";
    } elseif (!$checkin || !$checkout || $nights <= 0) {
        $error = "Please select check-in date, nights, and checkout date.";
    } elseif (strtolower($room['status']) !== 'available') {
        $error = "This room is not available for booking. Please choose another room.";
    } else {
        $totalCost = $nights * (float)$room['price'];

        // Insert booking
        $stmt = $conn->prepare("
            INSERT INTO bookings (user_id, room_id, booking_date, checkin_date, checkout_date, nights, total_cost, status, created_at)
            VALUES (?, ?, NOW(), ?, ?, ?, ?, 'Pending', NOW())
        ");
        $stmt->bind_param("isssid", $userId, $roomId, $checkin, $checkout, $nights, $totalCost);

        if ($stmt->execute()) {
            // Mark room as booked
            $update = $conn->prepare("UPDATE rooms SET status='booked' WHERE id=?");
            $update->bind_param("i", $roomId);
            $update->execute();
            $success = "Room booked successfully!";
        } else {
            $error = "Failed to book room. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Room Details</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f7f9; margin:0; padding:20px; color:#333; }
.container { max-width:800px; margin:0 auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
h1 { margin-top:0; color:#2c3e50; }
img { max-width:100%; border-radius:8px; margin:15px 0; }
p { margin:8px 0; }
.badge { padding:4px 8px; border-radius:5px; font-size:13px; font-weight:600; color:#fff; }
.available { background:#28a745; }
.booked { background:#dc3545; }
.maintenance { background:#ffc107; color:#333; }
.back-btn { display:inline-block; margin-top:20px; padding:10px 20px; background:#2c3e50; color:#fff; text-decoration:none; border-radius:6px; }
.back-btn:hover { background:#1f2a38; }
.success { color:green; font-weight:bold; }
.error { color:red; font-weight:bold; }
form { margin-top:20px; }
label { display:block; margin:10px 0 5px; }
input[type="date"], input[type="number"], button { padding:10px; border-radius:6px; border:1px solid #ccc; }
button { background:#2c3e50; color:#fff; border:none; cursor:pointer; }
button:hover { background:#1f2a38; }
.total { font-weight:bold; margin-top:10px; }
</style>
<script>
function updateBooking() {
    const nights = parseInt(document.getElementById('nights').value) || 0;
    const checkin = document.getElementById('checkin_date').value;
    const price = <?= (float)$room['price'] ?>;

    // Update total
    const total = nights > 0 ? nights * price : 0;
    document.getElementById('totalCost').textContent = "Total: TK " + total.toFixed(2);

    // Auto update checkout date
    if (checkin && nights > 0) {
        const ci = new Date(checkin);
        ci.setDate(ci.getDate() + nights);
        document.getElementById('checkout_date').value = ci.toISOString().split('T')[0];
    }
}
</script>
</head>
<body>
<div class="container">
    <h1>Room <?= h($room['room_number']) ?> — <?= h($room['type']) ?></h1>

    <img src="<?= h(room_image_url($room['path'] ?? '')) ?>" alt="Room Image">

    <p><strong>Price per Night:</strong> TK <?= number_format((float)($room['price'] ?? 0), 2) ?></p>
    <p><strong>Status:</strong> 
        <span class="badge <?= strtolower($room['status']) ?>">
            <?= h(ucfirst($room['status'])) ?>
        </span>
    </p>
    <p><strong>Features:</strong> <?= renderFeatures($room['features'], $featureMap) ?></p>

    <?php if ($success): ?>
        <p class="success"><?= h($success) ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= h($error) ?></p>
    <?php elseif (strtolower($room['status']) === 'available'): ?>
        <!-- Booking Form -->
        <form method="POST">
            <label for="checkin_date">Check-in Date:</label>
            <input type="date" name="checkin_date" id="checkin_date" onchange="updateBooking()" required>

            <label for="nights">Nights:</label>
            <input type="number" name="nights" id="nights" min="1" value="1" onchange="updateBooking()" required>

            <label for="checkout_date">Checkout Date:</label>
            <input type="date" name="checkout_date" id="checkout_date" onchange="updateBooking()" required>

            <p id="totalCost" class="total">Total: TK <?= (float)$room['price'] ?></p>

            <br>
            <button type="submit">Confirm Booking</button>
        </form>
        <script>updateBooking();</script>
    <?php else: ?>
        <p class="error">This room is not available for booking. Please select another available room.</p>
    <?php endif; ?>

    <a class="back-btn" href="room_inventory.php">← Back to Inventory</a>
</div>
</body>
</html>
