<?php 
session_start();
require_once('../model/userModel.php'); // User DB operations

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['new_password'] ?? '');

    if (strlen($newPassword) < 4) {
        $error = "Password must be at least 4 characters!";
    } else {
        // Save password in DB via userModel
        if (isset($_SESSION['user_id'])) {
            $id = (int)$_SESSION['user_id'];
            if (updateUserPassword($id, $newPassword)) {
                $success = "Password has been reset successfully!";
            } else {
                $error = "Failed to update password. Try again.";
            }
        } else {
            $error = "User not found. Please login again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .err { color:red; font-weight:600; }
        .ok { color:green; font-weight:600; }
    </style>
</head>
<body>
    <h1>Reset Password</h1>
    <form id="resetPasswordForm">
        <fieldset>
            New Password:
            <input type="password" id="newPassword" name="new_password" />
            <p id="resetPError" class="err"><?= htmlspecialchars($error) ?></p>
            <input type="submit" value="Reset Password" />
            <p id="resetSuccess" class="ok"><?= htmlspecialchars($success) ?></p>
        </fieldset>
        <p><a href="login.php">Back to Login</a></p>
    </form>

    <script>
        document.getElementById('resetPasswordForm').onsubmit = function(e) {
            e.preventDefault();
            var newPassword = document.getElementById('newPassword').value;

            if (newPassword.length < 4) {
                document.getElementById('resetPError').innerText = "Password must be at least 4 characters!";
                return;
            }

            var formData = new FormData();
            formData.append('new_password', newPassword);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../controller/reset_password_handler.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        document.getElementById('resetSuccess').innerText = response.message;
                        document.getElementById('resetPError').innerText = '';
                    } else {
                        document.getElementById('resetPError').innerText = response.message;
                        document.getElementById('resetSuccess').innerText = '';
                    }
                }
            };
            xhr.send(formData);
        };
    </script>
</body>
</html>
