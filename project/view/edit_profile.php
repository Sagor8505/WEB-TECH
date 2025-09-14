<?php
session_start();
require_once('../model/userModel.php');

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

$id = $_SESSION['user_id'] ?? 0;
if ($id === 0 && isset($_SESSION['username'])) {
    $tmp = getUserByUsername($_SESSION['username']);
    $id = $tmp['id'] ?? 0;
    if ($id) $_SESSION['user_id'] = $id;
}

$user = $id ? getUserById($id) : [];
$name = $user['username'] ?? $_SESSION['username'];
$email = $user['email'] ?? '';
$profile = $user['profile'] ?? '';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - Hotel Management System</title>
    <link rel="stylesheet" href="../asset/auth.css">
    <style>
        .err { color:red; font-weight:600; margin:6px 0; }
        .ok { color:green; font-weight:700; margin:8px 0; text-align:center; }
        #profile-preview { border-radius:8px; border:1px solid #ccc; width:120px; height:120px; object-fit:cover; display:block; margin-bottom:8px; }
        fieldset { width:60%; min-width:320px; margin:20px auto; padding:18px; }
        label { font-weight:bold; margin-top:8px; display:block; }
        input[type="text"], input[type="email"], input[type="file"] { width:100%; padding:10px; margin-top:6px; border:1.5px solid #aaa; border-radius:6px; box-sizing:border-box; }
        input[type="submit"], input[type="button"] { margin-top:12px; padding:10px 18px; background:#1976d2; color:#fff; border:none; border-radius:4px; cursor:pointer; }
        input[type="submit"]:hover, input[type="button"]:hover { background:#145a86; }
    </style>
</head>
<body>
    <h1>Edit Profile</h1>

    <form id="editProfileForm" method="post" enctype="multipart/form-data">
        <fieldset>
            <label>Name:</label>
            <input type="text" name="name" id="editName" value="<?= h($name) ?>" onblur="checkName()">
            <p class="err" id="nameError"></p>

            <label>Email:</label>
            <input type="email" name="email" id="editEmail" value="<?= h($email) ?>" onblur="checkEmail()">
            <p class="err" id="emailError"></p>

            <label>Profile Picture:</label>
            <?php if (!empty($profile)): ?>
                <img src="../<?= h($profile) ?>" id="profile-preview" alt="Profile">
            <?php else: ?>
                <img src="" id="profile-preview" alt="Profile" style="display:none;">
            <?php endif; ?>
            <input type="file" name="avatar" id="editAvatar" accept="image/*">
            <p class="err" id="avatarError"></p>

            <input type="submit" value="Update Profile">
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
            <p class="ok" id="saveSuccess"></p>
            <p class="err" id="generalError"></p>
        </fieldset>
    </form>

    <script>
        function checkName() {
            const name = document.getElementById('editName').value.trim();
            document.getElementById('nameError').innerText = name === '' ? 'Please enter name!' : '';
        }

        function checkEmail() {
            const email = document.getElementById('editEmail').value.trim();
            const valid = email !== '' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            document.getElementById('emailError').innerText = valid ? '' : 'Please enter valid email!';
        }

        document.getElementById('editProfileForm').onsubmit = function(e) {
            e.preventDefault();
            document.getElementById('saveSuccess').innerText = '';
            document.getElementById('generalError').innerText = '';
            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../controller/edit_profile_handler.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const res = JSON.parse(xhr.responseText);
                    if (res.status === 'success') {
                        document.getElementById('saveSuccess').innerText = res.message || 'Profile updated!';
                        if (res.avatar) {
                            const img = document.getElementById('profile-preview');
                            img.src = '../' + res.avatar + '?t=' + Date.now();
                            img.style.display = 'inline-block';
                        }
                    } else {
                        if (res.errors) {
                            if (res.errors.name) document.getElementById('nameError').innerText = res.errors.name;
                            if (res.errors.email) document.getElementById('emailError').innerText = res.errors.email;
                            if (res.errors.avatar) document.getElementById('avatarError').innerText = res.errors.avatar;
                        }
                        if (res.message) document.getElementById('generalError').innerText = res.message;
                    }
                } else {
                    document.getElementById('generalError').innerText = 'Server error. Try again.';
                }
            };
            xhr.send(formData);
        };
    </script>
</body>
</html>
