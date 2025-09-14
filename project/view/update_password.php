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
        echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
        exit;
    }
}

if (strtolower($_SESSION['role']) !== 'user') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User ID not found.']);
    exit;
}

$currentPassword = '';
$user = getUserById($id);
if ($user) {
    $currentPassword = $user['password'] ?? '';
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}

$errors = ['old' => '', 'new' => '', 'general' => ''];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass = trim($_POST['old_password'] ?? '');
    $newPass = trim($_POST['new_password'] ?? '');

    if ($oldPass === '') {
        $errors['old'] = 'Enter old password!';
    } else {
        if ($oldPass !== $currentPassword) {
            $errors['old'] = 'Old password is incorrect!';
        }
    }

    if ($newPass === '') {
        $errors['new'] = 'Enter new password!';
    } elseif (strlen($newPass) < 4) {
        $errors['new'] = 'New password must be at least 4 characters!';
    } elseif ($oldPass !== '' && $oldPass === $newPass) {
        $errors['new'] = 'New password must be different from old password!';
    }

    if (empty($errors['old']) && empty($errors['new'])) {
        if ($id > 0) {
            $con = getConnection();
            $safePass = mysqli_real_escape_string($con, $newPass);
            $sql = "UPDATE users SET password='{$safePass}' WHERE id=" . (int)$id;
            if (mysqli_query($con, $sql)) {
                $success = 'Password updated successfully!';
                $_SESSION['auth_password'] = $newPass;
                echo json_encode(['status' => 'success', 'message' => $success]);
            } else {
                $errors['general'] = 'Unable to update password. Try again.';
                echo json_encode(['status' => 'error', 'message' => $errors['general']]);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Update Password</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .error-msg { color: red; font-weight: 600; }
        .center-success { text-align: center; font-weight: bold; color: green; margin: 8px 0 16px; }
    </style>
</head>
<body>
    <h1>Update Password</h1>

    <?php if (!empty($success)): ?>
        <p class="center-success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <p class="error-msg" style="text-align:center;"><?= htmlspecialchars($errors['general']) ?></p>
    <?php endif; ?>

    <form id="updatePasswordForm">
        <fieldset>
            Old Password:
            <input type="password" id="oldPass" name="old_password">
            <p id="oldError" class="error-msg"><?= htmlspecialchars($errors['old']) ?></p>

            New Password:
            <input type="password" id="newPass" name="new_password">
            <p id="newError" class="error-msg"><?= htmlspecialchars($errors['new']) ?></p>

            <input type="submit" value="Update Password">
            <p id="updateSuccess"></p>

            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
        </fieldset>
    </form>

    <script>
        document.getElementById('updatePasswordForm').onsubmit = function(e) {
            e.preventDefault();
            var oldPass = document.getElementById('oldPass').value.trim();
            var newPass = document.getElementById('newPass').value.trim();

            var formData = new FormData();
            formData.append('old_password', oldPass);
            formData.append('new_password', newPass);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../controller/update_password_handler.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        document.getElementById('updateSuccess').innerHTML = response.message;
                        document.getElementById('updateSuccess').style.color = 'green';  // Green color for success
                        document.getElementById('oldError').innerHTML = '';
                        document.getElementById('newError').innerHTML = '';
                    } else {
                        document.getElementById('oldError').innerHTML = response.message;
                        document.getElementById('updateSuccess').innerHTML = '';
                    }
                }
            };
            xhr.send(formData);
        };
    </script>
</body>
</html>
