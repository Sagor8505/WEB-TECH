<?php
session_start();

$error = '';
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email!';
    } else {
        $success = 'A reset link has been sent to your email!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .error-msg { color:red; font-weight:600; }
        .ok { color:green; font-weight:bold; text-align:center; margin:10px 0; }
    </style>
</head>
<body>
    <h1>Forgot Password</h1>

    <?php if ($success): ?>
        <p class="ok"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" action="" onsubmit="return forgotCheck()">
        <fieldset>
            Enter your email:
            <input type="text" id="forgotEmail" name="email" value="<?= htmlspecialchars($email) ?>" onblur="checkForgotEmail()" />
            <p id="forgotEError" class="error-msg"><?= htmlspecialchars($error) ?></p>

            <input type="submit" value="Send Reset Link" />
            <p id="forgotSuccess"></p>
        </fieldset>
        <p style="text-align:center;">
            <input type="button" value="Back to Login" onclick="window.location.href='login.php'">
        </p>
    </form>

    <script>
        function checkForgotEmail() {
            let email = document.getElementById('forgotEmail').value.trim();
            if (email === "" || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('forgotEError').innerHTML = "Please enter a valid email!";
            } else {
                document.getElementById('forgotEError').innerHTML = "";
            }
        }

        function forgotCheck() {
            checkForgotEmail();
            return document.getElementById('forgotEError').innerHTML === "";
        }
    </script>
</body>
</html>
