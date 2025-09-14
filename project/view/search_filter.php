<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/searchModel.php');

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

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$q        = trim($_POST['searchBox'] ?? '');
$category = $_POST['categoryFilter'] ?? 'All';
$status   = $_POST['statusFilter'] ?? 'All';

$allowedCategories = ['All','Rooms','Users','Bookings'];
if (!in_array($category, $allowedCategories, true)) $category = 'All';

$roomStatuses     = ['Available','Booked','Maintenance','Inactive'];
$bookingStatuses  = ['Pending','Confirmed','CheckedIn','CheckedOut','Cancelled'];

$allowedStatusAny = ['All'];
if ($category === 'Rooms') {
    $allowedStatusAny = array_merge(['All'], $roomStatuses);
} elseif ($category === 'Bookings') {
    $allowedStatusAny = array_merge(['All'], $bookingStatuses);
} elseif ($category === 'Users') {
    $allowedStatusAny = ['All'];
} else {
    $allowedStatusAny = array_merge(['All'], $roomStatuses, $bookingStatuses);
}
if (!in_array($status, $allowedStatusAny, true)) $status = 'All';

if (mb_strlen($q) > 80) $q = mb_substr($q, 0, 80);

$results = search_all($q, $category, $status, 50);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search & Filter</title>
  <link rel="stylesheet" href="../asset/ad.css">
  <style>
    .card{max-width:1000px;margin:18px auto;padding:18px;background:#fff;border-radius:8px;box-shadow:0 0 8px #ddd}
    fieldset{border:none;padding:0;margin:0 0 12px}
    label{display:inline-block;min-width:110px}
    input[type="text"],select{padding:6px 8px}
    ul.results{list-style:none;padding-left:0}
    ul.results li{border:1px solid #e1e1e1;border-radius:8px;padding:10px;margin:8px 0}
    .muted{color:#666}
  </style>
</head>
<body>
<div class="card">
  <h1>Search & Filter</h1>

  <form method="POST" action="">
    <fieldset>
      <label for="searchBox">Search:</label>
      <input type="text" id="searchBox" name="searchBox" placeholder="Type keyword..." value="<?= h($q) ?>">

      <label for="categoryFilter">Category:</label>
      <select id="categoryFilter" name="categoryFilter">
        <?php foreach ($allowedCategories as $c): ?>
          <option value="<?= h($c) ?>" <?= ($category===$c)?'selected':'' ?>><?= h($c) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="statusFilter">Status:</label>
      <select id="statusFilter" name="statusFilter">
        <?php foreach ($allowedStatusAny as $s): ?>
          <option value="<?= h($s) ?>" <?= ($status===$s)?'selected':'' ?>><?= h($s) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Apply</button>
      <button type="button" onclick="window.location.href='search_filter.php'">Clear</button>
    </fieldset>
  </form>

  <h2>Results</h2>
  <?php if (count($results) === 0): ?>
    <div class="muted">No results found.</div>
  <?php else: ?>
    <ul class="results">
      <?php foreach ($results as $r): ?>
        <li>
          <strong><?= h($r['name']) ?></strong>
          <div class="muted">
            <?= h($r['category']) ?> • <?= h($r['status']) ?><?= $r['extra'] ? ' • '.h($r['extra']) : '' ?>
            <?php if (!empty($r['link']) && $r['link'] !== '#'): ?>
              • <a href="<?= h($r['link']) ?>">Open</a>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form><fieldset>
    <input type="button" value="Back to Dashboard" onclick="window.location.href='admin_dashboard.php'">
  </fieldset></form>
</div>
</body>
</html>
