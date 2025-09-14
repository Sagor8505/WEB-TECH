<?php
require_once(__DIR__ . '/db.php');

function getCustomerProfile($userId) {
    $con = getConnection();
    $stmt = mysqli_prepare($con, "SELECT profile FROM users WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $json = $row && !empty($row['profile']) ? json_decode($row['profile'], true) : [];
    return is_array($json) ? $json : [];
}

function insertCustomerProfile($userId, $fullName, $licenseNo, $seat, $mirror, $licenseFile) {
    // behaves like upsert into users.profile
    return updateCustomerProfile($userId, $fullName, $licenseNo, $seat, $mirror, $licenseFile);
}

function updateCustomerProfile($userId, $fullName, $licenseNo, $seat, $mirror, $licenseFile = null) {
    $con = getConnection();
    $curr = getCustomerProfile($userId);
    $curr['full_name']   = $fullName;
    $curr['license_no']  = $licenseNo;
    $curr['seat_pref']   = $seat;
    $curr['mirror_pref'] = $mirror;
    if ($licenseFile !== null) $curr['license_file'] = $licenseFile;

    $payload = json_encode($curr, JSON_UNESCAPED_UNICODE);
    $stmt = mysqli_prepare($con, "UPDATE users SET profile=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $payload, $userId);
    return mysqli_stmt_execute($stmt);
}
