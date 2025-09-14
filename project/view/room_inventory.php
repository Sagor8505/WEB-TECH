<?php
session_start();
require_once('../model/roomModel.php');
require_once('../model/userModel.php');
require_once('../model/pathHelpers.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    header('Location: ../view/login.php?error=badrequest');
    exit;
}

// --- Fetch room data ---
$roomTypes    = getRoomTypes();
$roomFeatures = getRoomFeatures();
$errors       = ['type'=>'','feature'=>'','price'=>''];
$typeId       = $_POST['type'] ?? '';
$featureId    = $_POST['feature'] ?? '';
$priceStr     = $_POST['price'] ?? '';
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 12;

// --- Validation ---
if ($typeId !== '' && !ctype_digit((string)$typeId)) $errors['type']='Invalid room type.';
if ($featureId !== '' && !ctype_digit((string)$featureId)) $errors['feature']='Invalid feature.';
if ($priceStr !== '' && !preg_match('/^\d+\-\d+$/', $priceStr)) $errors['price']='Invalid price range.';

$filters = [];
if ($typeId !== '')    $filters['type_id']    = (int)$typeId;
if ($featureId !== '') $filters['feature_id'] = (int)$featureId;
if ($priceStr !== '')  $filters['price']      = $priceStr;

$total      = countRoomsFiltered($filters);
$totalPages = max(1, (int)ceil($total/$perPage));
if ($page > $totalPages) $page = $totalPages;
$rooms      = getRoomsFiltered($filters, $page, $perPage);

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// --- Build feature ID → name map ---
$featureMap = [];
foreach ($roomFeatures as $f) {
    $featureMap[$f['id']] = $f['name'];
}

// --- Render features as names ---
function renderFeatureNames(?string $csv, array $map): string {
    if (!$csv) return '—';
    $ids = array_filter(array_map('trim', explode(',', $csv)));
    $names = [];
    foreach ($ids as $id) {
        if (isset($map[$id])) {
            $names[] = $map[$id];
        }
    }
    return $names ? implode(', ', $names) : $csv;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Room Inventory - HotelEase</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f7f9; margin:0; padding:20px; color:#333; }
h1 { text-align:center; color:#2c3e50; margin-bottom:20px; }

.filters { display:flex; gap:15px; justify-content:center; margin-bottom:25px; flex-wrap:wrap; }
.filters select, .filters button { padding:8px 14px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
.filters button { background:#2c3e50; color:#fff; border:none; cursor:pointer; }
.filters button:hover { background:#1f2a38; }

.grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:20px; }

.room-card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:0.2s; }
.room-card:hover { transform:translateY(-4px); box-shadow:0 8px 20px rgba(0,0,0,0.15); }
.room-card img { width:100%; height:160px; object-fit:cover; }
.room-card-content { padding:12px 16px; display:flex; flex-direction:column; gap:6px; }
.room-card-content h3 { margin:0; font-size:18px; color:#1f618d; }
.room-card-content p { margin:0; font-size:14px; color:#555; }
.price { font-weight:600; color:#2c3e50; }
.badge { padding:4px 8px; border-radius:5px; font-size:12px; font-weight:600; color:#fff; display:inline-block; }
.available { background:#28a745; }
.booked { background:#dc3545; }
.maintenance { background:#ffc107; color:#333; }
.btn { padding:8px 12px; border:none; border-radius:6px; background:#2c3e50; color:#fff; cursor:pointer; text-align:center; }
.btn.secondary { background:#fff; color:#2c3e50; border:1px solid #2c3e50; }
.btn.secondary:hover { background:#2c3e50; color:#fff; }

.pagination { display:flex; gap:8px; justify-content:center; margin-top:20px; flex-wrap:wrap; }
.pagination a, .pagination span { padding:6px 10px; border-radius:5px; text-decoration:none; border:1px solid #ddd; }
.pagination .active { background:#2c3e50; color:#fff; border-color:#2c3e50; }

.back-btn { display:block; width:max-content; margin:30px auto; padding:10px 20px; font-size:15px; }
.error-message { color:red; font-size:13px; }
</style>
<script>
function submitFilters() { document.getElementById('filterForm').submit(); }
function resetFilters() { document.getElementById('filterForm').reset(); submitFilters(); }
</script>
</head>
<body>

<h1>Room Inventory</h1>

<form id="filterForm" method="POST" action="">
<div class="filters">
    <select name="type" onchange="submitFilters()">
        <option value="">--Room Type--</option>
        <?php foreach($roomTypes as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= ($typeId!=='' && (int)$t['id']===(int)$typeId)?'selected':'' ?>><?= h($t['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="feature" onchange="submitFilters()">
        <option value="">--Feature--</option>
        <?php foreach($roomFeatures as $f): ?>
            <option value="<?= (int)$f['id'] ?>" <?= ($featureId!=='' && (int)$f['id']===(int)$featureId)?'selected':'' ?>><?= h($f['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="price" onchange="submitFilters()">
        <option value="">--Price--</option>
        <option value="0-2000" <?= ($priceStr==='0-2000')?'selected':'' ?>>0-2000</option>
        <option value="2001-4000" <?= ($priceStr==='2001-4000')?'selected':'' ?>>2001-4000</option>
        <option value="4001-6000" <?= ($priceStr==='4001-6000')?'selected':'' ?>>4001-6000</option>
        <option value="6001-10000" <?= ($priceStr==='6001-10000')?'selected':'' ?>>6001-10000</option>
    </select>

    <button type="button" class="btn secondary" onclick="resetFilters()">Reset</button>
</div>
</form>

<div class="grid">
<?php if(!empty($rooms)): ?>
    <?php foreach($rooms as $room): 
        $statusClass = strtolower($room['status'] ?? 'available');
        $imgUrl = room_image_url($room['path'] ?? '');
        $title = trim('Room ' . ($room['room_number'] ?? '') .
                     (isset($room['type']) ? ' — ' . $room['type'] 
                     : (isset($room['type_name']) ? ' — ' . $room['type_name'] : '')));
    ?>
    <div class="room-card">
        <img src="<?= h($imgUrl) ?>" alt="<?= h($title ?: 'Room Image') ?>">
        <div class="room-card-content">
            <h3><?= h($title ?: 'Room') ?></h3>
            <p class="price">TK <?= h(number_format((float)($room['price'] ?? 0),2)) ?></p>
            <p><span class="badge <?= $statusClass ?>"><?= h(ucfirst($room['status'] ?? 'Available')) ?></span></p>
            <p><strong>Features:</strong> <?= h(renderFeatureNames($room['features'] ?? '', $featureMap)) ?></p>
            <button class="btn" onclick="window.location.href='RoomBooking.php?id=<?= (int)$room['id'] ?>'">View Details</button>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="grid-column:1/-1;text-align:center;">No rooms found.</p>
<?php endif; ?>
</div>

<div class="pagination">
<?php for($p=1;$p<=$totalPages;$p++): ?>
    <?php if($p==$page): ?>
        <span class="active"><?= $p ?></span>
    <?php else: ?>
        <a href="?page=<?= $p ?>"><?= $p ?></a>
    <?php endif; ?>
<?php endfor; ?>
</div>

<button class="btn secondary back-btn" onclick="window.location.href='user_dashboard.php'">Back to Dashboard</button>

</body>
</html>
