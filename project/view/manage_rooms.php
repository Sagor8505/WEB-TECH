<?php
// view/manage_rooms.php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header('location: login.php?error=badrequest'); exit;
}
require_once __DIR__ . '/../model/RoomAdminModel.php';
require_once __DIR__ . '/../model/pathHelpers.php';

$types    = ra_getRoomTypes();     // optional; keep if you use it to populate the type dropdown
$features = ra_getRoomFeatures();  // optional; keep if you want pretty feature names

// Filters
$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'All';
$type   = trim($_GET['type'] ?? ''); // since type is TEXT now
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 10;

$total  = ra_countRooms($search, $status, $type);
$rooms  = ra_getRooms($search, $status, $type, $page, $per);
$pages  = (int)ceil(($total ?: 0) / $per);

// Build feature name map (optional)
$featMap = [];
foreach ($features as $f) $featMap[(int)$f['id']] = $f['name'];
function renderFeatureNames(?string $csv, array $map): string {
    if (!$csv) return '—';
    $ids = array_filter(array_map('trim', explode(',', $csv)), 'strlen');
    $names = [];
    foreach ($ids as $id) { $id = (int)$id; if (isset($map[$id])) $names[] = $map[$id]; }
    return $names ? implode(', ', $names) : htmlspecialchars($csv);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Rooms</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:24px;color:#111;background:#fafafa}
    h1{margin:0 0 16px}
    .wrap{max-width:1100px;margin:0 auto}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
    .card{background:#fff;border:1px solid #eee;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.04);padding:18px;margin:0 0 18px}
    .row{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}
    label{display:block;margin:6px 0 6px;color:#555}
    input[type="text"], input[type="number"], select{padding:10px;border:1px solid #ddd;border-radius:10px;min-width:180px}
    input[type="file"]{border:1px dashed #ddd;padding:8px;border-radius:10px;background:#fcfcfc}
    button,.btn{padding:10px 14px;border-radius:10px;border:1px solid #111;background:#111;color:#fff;cursor:pointer;text-decoration:none}
    .btn.secondary{background:#fff;color:#111;border:1px solid #ddd}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{border-bottom:1px solid #eee;padding:10px;text-align:left;vertical-align:top}
    .muted{color:#666}
    .actions button{margin-right:6px}
    .pill{display:inline-block;padding:4px 8px;border-radius:999px;border:1px solid #eee;font-size:12px}
    .status-available{background:#e7f7ec;border-color:#c9ebd1}
    .status-booked{background:#f7f1e7;border-color:#eadbc3}
    .status-maintenance{background:#f7e7e7;border-color:#ebc9c9}
    .pagination a{margin-right:6px}
    .grid{display:grid;grid-template-columns:repeat(2,minmax(260px,1fr));gap:12px}
    @media (max-width:900px){.grid{grid-template-columns:1fr}}
    .flash{display:none;margin-bottom:12px;padding:10px 12px;border-radius:10px;font-weight:600}
    .flash.success{display:block;background:#e9f9ee;border:1px solid #c9e9d3;color:#156d3f}
  </style>
</head>
<body>
<div class="wrap">
  <div class="topbar">
    <h1>Manage Rooms</h1>
    <a class="btn secondary" href="admin_dashboard.php">← Admin Dashboard</a>
  </div>

  <div id="flash" class="flash"></div>

  <!-- Filters -->
  <div class="card">
    <form class="row" method="get">
      <div>
        <label>Search</label>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Room # or Type">
      </div>
      <div>
        <label>Status</label>
        <select name="status">
          <?php foreach (['All','available','booked','maintenance'] as $s): ?>
            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Type</label>
        <input type="text" name="type" value="<?= htmlspecialchars($type) ?>" placeholder="e.g. Suite">
      </div>
      <div>
        <button type="submit" class="btn secondary">Apply</button>
      </div>
      <div>
        <button type="button" onclick="openCreate()" class="btn">+ New Room</button>
      </div>
    </form>
  </div>

  <!-- Rooms table -->
  <div class="card">
    <div class="muted"><?= $total ?> result<?= $total==1?'':'s' ?><?= $pages>1 ? " • Page $page of $pages" : '' ?></div>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Room #</th><th>Type</th><th>Price</th><th>Status</th><th>Features</th><th>Image</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rooms as $r): $img = room_image_url($r['path'] ?? ''); ?>
          <tr data-id="<?= (int)$r['id'] ?>">
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['room_number']) ?></td>
            <td><?= htmlspecialchars($r['type_name']) ?></td>
            <td><?= number_format((float)$r['price'], 2) ?></td>
            <td><span class="pill status-<?= htmlspecialchars(strtolower($r['status'])) ?>"><?= htmlspecialchars(ucfirst($r['status'])) ?></span></td>
            <td class="muted"><?= renderFeatureNames($r['features'] ?? '', $featMap) ?></td>
            <td>
              <?php if ($img): ?>
                <a href="<?= htmlspecialchars($img) ?>" target="_blank" class="muted">view</a>
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
            </td>
            <td class="actions">
              <button type="button" onclick='openEdit(<?= json_encode($r, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'>Edit</button>
              <button type="button" onclick="delRoom(<?= (int)$r['id'] ?>)" style="background:#b00020;border-color:#b00020">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rooms): ?>
          <tr><td colspan="8" class="muted">No rooms found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($pages>1): ?>
      <div class="pagination" style="margin-top:12px">
        <?php for ($p=1; $p<=$pages; $p++): $q = $_GET; $q['page']=$p; $qs = http_build_query($q); ?>
          <a href="?<?= $qs ?>" class="btn <?= $p===$page?'':'secondary' ?>"><?= $p ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Create/Edit form -->
  <div class="card" id="formCard" style="display:none">
    <h3 id="formTitle">New Room</h3>
    <form id="roomForm" class="grid" onsubmit="return submitForm(event)" enctype="multipart/form-data">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="id" value="">
      <div><label>Room Number</label><input type="text" name="room_number" required placeholder="e.g. 301"></div>
      <div><label>Type (text)</label><input type="text" name="type_name" required placeholder="e.g. Suite"></div>
      <div><label>Price</label><input type="number" name="price" step="0.01" min="0" required></div>
      <div>
        <label>Status</label>
        <select name="status" required>
          <?php foreach (['available','booked','maintenance'] as $s): ?>
            <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Features</label>
        <select name="features[]" multiple size="5" style="min-width:100%">
          <?php foreach ($features as $f): ?>
            <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="muted">Hold Ctrl/Cmd to select multiple.</div>
      </div>
      <div>
        <label>Image</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        <div class="muted">Optional; JPG/PNG/WebP only.</div>
      </div>
      <div>
        <button type="submit" class="btn" id="submitBtn">Save</button>
        <button type="button" class="btn secondary" onclick="closeForm()">Cancel</button>
        <span id="msg" class="muted"></span>
      </div>
    </form>
  </div>
</div>

<script>
const formCard = document.getElementById('formCard');
const form     = document.getElementById('roomForm');
const titleEl  = document.getElementById('formTitle');
const msg      = document.getElementById('msg');
const flash    = document.getElementById('flash');
const submitBtn= document.getElementById('submitBtn');

function showFlash(text){ flash.textContent=text; flash.className='flash success'; flash.style.display='block'; }
function openCreate(){ titleEl.textContent='New Room'; form.reset(); form.action.value='create'; form.id.value=''; formCard.style.display='block'; msg.textContent=''; flash.style.display='none'; }
function openEdit(room){
  titleEl.textContent = 'Edit Room #'+room.room_number;
  form.action.value = 'update';
  form.id.value = room.id;
  form.room_number.value = room.room_number;
  form.type_name.value = room.type_name; // text type
  form.price.value = room.price;
  form.status.value = room.status;
  // features CSV -> multi-select
  const feats = (room.features||'').split(',').map(s=>s.trim()).filter(Boolean);
  [...form.querySelectorAll('select[name="features[]"] option')].forEach(o=>{o.selected = feats.includes(String(o.value));});
  formCard.style.display = 'block'; msg.textContent=''; flash.style.display='none';
}
function closeForm(){ formCard.style.display='none'; }

async function submitForm(e){
  e.preventDefault(); msg.textContent=''; submitBtn.disabled = true;
  const fd = new FormData(form);
  // send text type under the key the handler expects
  fd.append('type_name', form.type_name.value);

  try{
    const res = await fetch('../controller/managerooms_handler.php', { method:'POST', body: fd });
    const data = await res.json();
    if(data.success){
      showFlash(form.action.value==='create' ? 'New room added successfully.' : 'Room updated successfully.');
      formCard.style.display='none'; setTimeout(()=>location.reload(), 900); return false;
    }
    msg.textContent = data.message || 'Failed';
  }catch(err){ msg.textContent='Error submitting form'; }
  finally{ submitBtn.disabled=false; }
  return false;
}
async function delRoom(id){
  if(!confirm('Delete this room?')) return;
  try{
    const fd = new FormData(); fd.append('action','delete'); fd.append('id', String(id));
    const res = await fetch('../controller/managerooms_handler.php', { method:'POST', body: fd });
    const data = await res.json();
    if(data.success){ showFlash('Room deleted successfully.'); setTimeout(()=>location.reload(), 600); return; }
    alert(data.message || 'Delete failed');
  }catch(err){ alert('Error'); }
}
</script>
</body>
</html>
