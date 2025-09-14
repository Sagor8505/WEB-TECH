<?php
// controller/managerooms_handler.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../model/roomModel.php';

function j($ok, $msg='', $extra=[]) {
    echo json_encode(array_merge(['success'=>$ok,'message'=>$msg], $extra));
    exit;
}

function norm_status($s) {
    $s = strtolower(trim($s ?? 'available'));
    return in_array($s, ['available','booked','maintenance'], true) ? $s : 'available';
}

$action = $_POST['action'] ?? '';

if ($action === 'create' || $action === 'update') {
    $id          = (int)($_POST['id'] ?? 0);
    $room_number = trim($_POST['room_number'] ?? '');
    $type_name   = trim($_POST['type_name'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $status      = norm_status($_POST['status'] ?? 'available');
    $featuresArr = $_POST['features'] ?? [];
    $featuresCsv = implode(',', array_map(fn($v)=> (string)(int)$v, (array)$featuresArr));

    if ($room_number === '' || $type_name === '' || $price < 0) {
        j(false, 'Invalid input');
    }

    // Optional image upload
    $fileName = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allow = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allow, true)) j(false, 'Only JPG/PNG/WebP allowed');
        if ($_FILES['image']['size'] > 5*1024*1024) j(false, 'Max file size is 5MB');

        $safe = 'room_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dir = __DIR__ . '/../asset/uploads';   // ✅ fixed to uploads/
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $dest = $dir . '/' . $safe;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) j(false, 'Upload failed');

        $fileName = $safe; // ✅ store only filename
    }

    if ($action === 'create') {
        $ok = ra_createRoom($room_number, $price, $type_name, $status, $featuresCsv, $fileName);
        j($ok, $ok ? 'New room added successfully.' : 'Create failed');
    } else {
        $ok = ra_updateRoom($id, $room_number, $price, $type_name, $status, $featuresCsv, $fileName);
        j($ok, $ok ? 'Room updated successfully.' : 'Update failed');
    }
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) j(false, 'Invalid id');
    $ok = ra_deleteRoom($id);
    j($ok, $ok ? 'Room deleted successfully.' : 'Delete failed');
}

j(false, 'Bad request');
