<?php
session_start();
require_once(__DIR__ . '/../model/userModel.php');
require_once(__DIR__ . '/../model/customerModel.php');

header('Content-Type: application/json; charset=utf-8');

function json_out($arr, $code=200){ http_response_code($code); echo json_encode($arr); exit; }

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) json_out(['status'=>'error','message'=>'Unauthorized'], 401);

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) json_out(['status'=>'error','message'=>'User session not found'], 400);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $p = getCustomerProfile($userId);
    json_out(['status'=>'ok','profile'=>$p]);
}

if ($method === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $license  = trim($_POST['license_no'] ?? '');
    $seat     = trim($_POST['seat_pref'] ?? '');
    $mirror   = trim($_POST['mirror_pref'] ?? '');
    $fileName = null;

    if (!empty($_FILES['license_file']['name'])) {
        $up = $_FILES['license_file'];
        if ($up['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
            $safe = 'license_' . $userId . '_' . time() . '.' . preg_replace('/[^a-z0-9]+/i','', $ext);
            $dest = __DIR__ . '/../uploads/' . $safe;
            if (!is_dir(dirname($dest))) @mkdir(dirname($dest), 0775, true);
            if (move_uploaded_file($up['tmp_name'], $dest)) $fileName = $safe;
        }
    }

    $ok = updateCustomerProfile($userId, $fullName, $license, $seat, $mirror, $fileName);
    json_out($ok ? ['status'=>'ok'] : ['status'=>'error','message'=>'Save failed'], $ok?200:500);
}

json_out(['status'=>'error','message'=>'Bad request'], 400);
