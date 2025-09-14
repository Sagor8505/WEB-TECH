<?php
session_start();
require_once('../model/userModel.php');

// Check login status
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

// Determine user ID
$id = $_SESSION['user_id'] ?? 0;
if ($id === 0 && isset($_SESSION['username'])) {
    $tmp = getUserByUsername($_SESSION['username']);
    $id = $tmp['id'] ?? 0;
}

// Fetch user data
$user = ($id > 0) ? getUserById($id) : [];

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$name   = $user['username'] ?? ($_SESSION['username'] ?? 'User');
$email  = $user['email'] ?? '';
$avatar = $user['profile'] ?? '';

// ✅ Build correct public URL for avatar
if (!empty($avatar)) {
    // Ensure it’s a relative web path
    $avatar = ltrim($avatar, '/'); // remove accidental leading slash
    $avatarPath = "../" . $avatar; // web path
} else {
    $avatarPath = "../asset/default-avatar.png"; // fallback image
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>My Profile</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .profile-card{max-width:520px;margin:20px auto;padding:20px;border:1px solid #ddd;border-radius:10px;background:#fff;box-shadow:0 0 8px #ccc;}
        .profile-actions input{margin-right:8px;margin-top:8px;cursor:pointer;}
        .avatar{border-radius:8px;object-fit:cover;border:1px solid #ccc;}
        .meta{color:#444;margin-bottom:8px;}
        .no-photo { width:120px;height:120px;border:1px dashed #ccc;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#888;margin-bottom:10px; }
    </style>
</head>
<body>
    <h1 style="text-align:center;">My Profile</h1>
    <form class="profile-card">
        <fieldset>
            <?php if (!empty($avatarPath)): ?>
                <img src="<?= h($avatarPath) ?>" alt="Profile Picture" id="profilePic" width="120" height="120" class="avatar"><br><br>
            <?php else: ?>
                <div class="no-photo">No photo</div>
            <?php endif; ?>

            <div class="meta"><strong>Name:</strong> <?= h($name) ?></div>
            <div class="meta"><strong>Email:</strong> <?= h($email ?: '—') ?></div>
        </fieldset>
        <fieldset class="profile-actions">
            <input type="button" value="Edit Profile" onclick="window.location.href='edit_profile.php'">
            <input type="button" value="Update Password" onclick="window.location.href='update_password.php'">
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
            <br><br>
            <div>Access services:</div>
            <input type="button" value="Customer Services" onclick="window.location.href='customer_services.php'">
        </fieldset>
    </form>
</body>
</html>
