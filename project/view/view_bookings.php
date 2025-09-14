<?php
// view/view_bookings.php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header('location: login.php?error=badrequest'); exit;
}

require_once __DIR__ . '/../model/BookingAdminModel.php';

// Filters
$search  = trim($_GET['q'] ?? '');
$status  = $_GET['status'] ?? 'All'; // All | Pending | Confirmed
$from    = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to      = $_GET['to']   ?? date('Y-m-d');
$page    = max(1, (int)($_GET['page'] ?? 1));
$per     = 10;

$total   = ba_countBookings($search, $status, $from, $to);
$rows    = ba_listBookings($search, $status, $from, $to, $page, $per);
$pages   = (int)ceil(($total ?: 0)/$per);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin • View Bookings</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:24px;color:#111;background:#fafafa}
    .wrap{max-width:1150px;margin:0 auto}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
    .btn{padding:10px 14px;border-radius:10px;border:1px solid #111;background:#111;color:#fff;text-decoration:none;cursor:pointer}
    .btn.secondary{background:#fff;color:#111;border:1px solid #ddd}
    .btn.danger{background:#b00020;border-color:#b00020}
    .card{background:#fff;border:1px solid #eee;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.04);padding:18px;margin:0 0 18px}
    .row{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}
    label{display:block;margin:6px 0 6px;color:#555}
    input,select{padding:10px;border:1px solid #ddd;border-radius:10px}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{border-bottom:1px solid #eee;padding:10px;text-align:left;vertical-align:top}
    .muted{color:#666}
    .pill{display:inline-block;padding:4px 8px;border-radius:999px;border:1px solid #eee;font-size:12px}
    .status-pending{background:#fff6d9;border-color:#f0e2a6}
    .status-confirmed{background:#e7f7ec;border-color:#c9ebd1}
    .flash{display:none;margin-bottom:12px;padding:10px 12px;border-radius:10px;font-weight:600}
    .flash.success{display:block;background:#e9f9ee;border:1px solid #c9e9d3;color:#156d3f}
    .pagination a{margin-right:6px}
    .nowrap{white-space:nowrap}
  </style>
</head>
<body>
<div class="wrap">
  <div class="topbar">
    <h1>View Bookings</h1>
    <a class="btn secondary" href="admin_dashboard.php">← Admin Dashboard</a>
  </div>

  <div id="flash" class="flash"></div>

  <div class="card">
    <form class="row" method="get">
      <div>
        <label>Search (User / Room # / Type)</label>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. john or 302 or Deluxe">
      </div>
      <div>
        <label>Status</label>
        <select name="status">
          <?php foreach (['All','Pending','Confirmed'] as $s): ?>
            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>From</label>
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
      </div>
      <div>
        <label>To</label>
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
      </div>
      <div>
        <button class="btn secondary" type="submit">Apply</button>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="muted"><?= $total ?> result<?= $total==1?'':'s' ?><?= $pages>1 ? " • Page $page of $pages" : '' ?></div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Room</th>
          <th>Type</th>
          <th>Nights</th>
          <th>Total</th>
          <th>Check-in / out</th>
          <th>Pickup</th>
          <th>Status</th>
          <th class="nowrap">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="10" class="muted">No bookings found.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $b): ?>
        <tr data-id="<?= (int)$b['id'] ?>">
          <td><?= (int)$b['id'] ?></td>
          <td><?= htmlspecialchars($b['username']) ?><br><span class="muted"><?= htmlspecialchars($b['email']) ?></span></td>
          <td>#<?= htmlspecialchars($b['room_number']) ?></td>
          <td><?= htmlspecialchars($b['room_type']) ?></td>
          <td><?= (int)$b['nights'] ?></td>
          <td><?= number_format((float)$b['total_cost'], 2) ?></td>
          <td class="nowrap">
            <?= htmlspecialchars($b['checkin_date'] ?? '') ?> → <?= htmlspecialchars($b['checkout_date'] ?? '') ?><br>
            <span class="muted"><?= htmlspecialchars($b['booking_date'] ?? '') ?></span>
          </td>
          <td><?= htmlspecialchars($b['pickup_date'] ?? '—') ?></td>
          <td>
            <span class="pill status-<?= strtolower($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span>
          </td>
          <td class="nowrap">
            <?php if (strtolower($b['status']) !== 'confirmed'): ?>
              <button class="btn" onclick="confirmBooking(<?= (int)$b['id'] ?>)">Confirm</button>
              <button class="btn danger" onclick="rejectBooking(<?= (int)$b['id'] ?>)">Reject</button>
            <?php else: ?>
              <span class="muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if ($pages>1): ?>
    <div class="pagination" style="margin-top:12px">
      <?php for ($p=1; $p<=$pages; $p++): 
        $q = $_GET; $q['page']=$p; $qs = http_build_query($q); ?>
        <a href="?<?= $qs ?>" class="btn <?= $p===$page?'':'secondary' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
const flash = document.getElementById('flash');
function showFlash(text){ flash.textContent=text; flash.className='flash success'; flash.style.display='block'; }

async function confirmBooking(id){
  if(!confirm('Confirm this booking?')) return;
  const fd = new FormData(); fd.append('action','confirm'); fd.append('id', String(id));
  const res = await fetch('../controller/bookings_admin_handler.php', {method:'POST', body: fd});
  const data = await res.json();
  if(data.success){ showFlash('Booking confirmed.'); setTimeout(()=>location.reload(), 800); }
  else alert(data.message || 'Failed');
}

async function rejectBooking(id){
  if(!confirm('Reject & delete this booking?')) return;
  const fd = new FormData(); fd.append('action','reject'); fd.append('id', String(id));
  const res = await fetch('../controller/bookings_admin_handler.php', {method:'POST', body: fd});
  const data = await res.json();
  if(data.success){ showFlash('Booking rejected and removed.'); setTimeout(()=>location.reload(), 800); }
  else alert(data.message || 'Failed');
}
</script>
</body>
</html>
