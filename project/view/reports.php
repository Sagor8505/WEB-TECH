<?php
session_start();

// Guard: only admin can see reports
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header('location: login.php?error=badrequest'); 
    exit;
}

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to   = $_GET['to']   ?? date('Y-m-d');
$username = $_GET['username'] ?? '';

// Build query string for links
$q = http_build_query([
    'from'     => $from,
    'to'       => $to,
    'username' => $username
]);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reports</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:24px;color:#111}
    .card{max-width:720px;border:1px solid #eee;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
    .row{display:flex;gap:12px;flex-wrap:wrap;margin-top:12px}
    label{display:block;margin:10px 0 6px;color:#555}
    input,button,a.btn{padding:10px;border:1px solid #ddd;border-radius:10px}
    a.btn{background:#111;color:#fff;text-decoration:none}
  </style>
</head>
<body>
  <h1>Reports</h1>
  <div class="card">
    <form method="get" class="row">
      <div>
        <label>From</label>
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
      </div>
      <div>
        <label>To</label>
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
      </div>
      <div>
        <label>User</label>
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>">
      </div>
      <div style="align-self:flex-end">
        <button type="submit">Apply</button>
      </div>
    </form>

    <div class="row">
      <a class="btn" href="../controller/export_bookings.php?<?= $q ?>">Download Bookings CSV</a>
      <a class="btn" href="../controller/export_rooms.php?<?= $q ?>">Download Rooms CSV</a>
      <a class="btn" href="activitylog.php">View Activity Log</a>
    </div>
  </div>
</body>
</html>
