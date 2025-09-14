<?php
session_start();
require_once('../model/userModel.php');

// Admin access check
if (!isset($_SESSION['status'], $_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('location: ../view/login.php?error=badrequest');
    exit;
}

// Utility
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Get user ID
$id = $_GET['id'] ?? ($_POST['id'] ?? 0);
$id = (int)$id;
if ($id <= 0) {
    header('Location: admin_panel_user_management.php'); exit;
}

// Fetch user data
$user = getUserById($id);
if (!$user) {
    header('Location: admin_panel_user_management.php'); exit;
}
$formUsername = $user['username'] ?? '';
$formEmail = $user['email'] ?? '';
$formProfile = $user['profile'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit User (Admin)</title>
    <link rel="stylesheet" href="../asset/auth.css">
    <style>
        .err { color:red; font-weight:600; margin:6px 0; }
        .ok { color:green; font-weight:700; margin:8px 0; text-align:center; }
        #profile-preview { border-radius:8px; border:1px solid #ccc; object-fit:cover; width:120px; height:120px; display:block; margin-bottom:8px; }
        fieldset.form-fieldset { width:60%; min-width:320px; margin:20px auto; padding:18px; box-sizing:border-box; }
        label { font-weight:bold; display:block; margin-top:8px; }
        input, select, textarea { width:100%; padding:10px; margin-top:6px; font-size:1em; border:1.5px solid #aaa; border-radius:6px; box-sizing:border-box; }
        input[type="submit"], input[type="button"] { margin-top:12px; padding:10px 18px; background:#1976d2; color:#fff; border:none; border-radius:4px; cursor:pointer; }
        input[type="submit"]:hover, input[type="button"]:hover { background:#145a86; }
    </style>
</head>
<body>
    <h1>Edit User</h1>

    <form id="editUserForm" method="post" enctype="multipart/form-data">
        <fieldset class="form-fieldset">
            <input type="hidden" name="id" value="<?= (int)$id ?>">

            <label>Username:</label>
            <input type="text" id="username" name="username" value="<?= h($formUsername) ?>">
            <p id="usernameErr" class="err"></p>

            <label>Email:</label>
            <input type="email" id="email" name="email" value="<?= h($formEmail) ?>">
            <p id="emailErr" class="err"></p>

            <label>Profile Picture:</label>
            <?php if ($formProfile): ?>
                <img src="../<?= h($formProfile) ?>" id="profile-preview" alt="Profile">
            <?php endif; ?>
            <input type="file" id="profile" name="profile" accept="image/*">
            <p id="profileErr" class="err"></p>

            <input type="submit" value="Update User">
            <input type="button" value="Back to Users" onclick="window.location.href='admin_panel.php'">
        </fieldset>
    </form>

    <script>
        document.getElementById('editUserForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../controller/edit_user_handler.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const res = JSON.parse(xhr.responseText);
                    alert(res.message);
                    if (res.status === 'success') window.location.reload();
                } else {
                    alert('Server error. Try again.');
                }
            };
            xhr.send(formData);
        };
    </script>
</body>
</html>
