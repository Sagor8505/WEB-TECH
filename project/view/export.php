<?php
// view/export.php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    header('Location: login.php'); exit;
}
$username = $_SESSION['username'] ?? '';
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to   = $_GET['to']   ?? date('Y-m-d');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Export</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:24px;color:#111}
    .card{max-width:720px;border:1px solid #eee;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
    label{display:block;margin:10px 0 6px;color:#555}
    input,button{padding:10px;border:1px solid #ddd;border-radius:10px}
    .row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    a.btn,button{background:#111;color:#fff;text-decoration:none;padding:10px 14px;border:0}
  </style>
</head>
<body>
  <h1>Export Data</h1>
  <div class="card">
    <form class="row" method="get">
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
      <div style="margin-top:28px">
        <button type="submit">Apply</button>
      </div>
    </form>

    <?php
      $q = http_build_query([
        'from' => $from,
        'to'   => $to,
        'username' => $username,
      ]);
    ?>

    <div style="margin-top:18px" class="row">
      <a class="btn" href="../controller/export_bookings.php?<?= $q ?>">Download Bookings CSV</a>
      <a class="btn" href="../controller/export_rooms.php?<?= $q ?>">Download Rooms CSV</a>
    </div>
  </div>
</body>
</html>
