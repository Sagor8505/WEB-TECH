<?php
require_once __DIR__ . '/../model/db.php';
require_once __DIR__ . '/../model/MaintenanceModel.php';
$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : null;
$rows = getServicesByRoom($roomId);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Maintenance Requests</title></head>
<body>
<h2>Maintenance Requests<?= $roomId ? " for Room #$roomId" : '' ?></h2>
<form method="post" action="../controller/Maintenance_handler.php">
    <input type="number" name="room_id" placeholder="Room ID" required>
    <input type="text"   name="new_issue" placeholder="Issue" required>
    <button type="submit">Create Request</button>
</form>
<table border="1" cellpadding="6">
<tr><th>ID</th><th>Room</th><th>Issue</th><th>Status</th><th>Reported</th><th>Actions</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= (int)$r['id'] ?></td>
  <td><?= htmlspecialchars($r['room_number']) ?></td>
  <td><?= htmlspecialchars($r['issue']) ?></td>
  <td><?= htmlspecialchars($r['status']) ?></td>
  <td><?= htmlspecialchars($r['reported_at']) ?></td>
  <td>
    <form method="post" action="../controller/Maintenance_handler.php" style="display:inline">
      <input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>">
      <button type="submit">Delete</button>
    </form>
    <form method="post" action="../controller/Maintenance_handler.php" style="display:inline">
      <input type="hidden" name="set_status_id" value="<?= (int)$r['id'] ?>">
      <select name="status">
        <option>Open</option><option>In Progress</option><option>Closed</option>
      </select>
      <button type="submit">Set</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</table>
</body></html>
