<?php
session_start();

$name = '';
$email = '';
$msg = '';
$captcha = '';
$errors = ['name'=>'', 'email'=>'', 'msg'=>'', 'captcha'=>''];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $msg     = trim($_POST['message'] ?? '');
    $captcha = trim($_POST['captcha'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Please enter your name!';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email!';
    }

    if ($msg === '') {
        $errors['msg'] = 'Please type your message!';
    }

    if ($captcha !== '9') {
        $errors['captcha'] = 'Wrong answer!';
    }

    if ($errors['name'] === '' && $errors['email'] === '' && $errors['msg'] === '' && $errors['captcha'] === '') {
        // TODO: send email or save to DB as needed.
        $success = 'Thank you! Your inquiry has been submitted.';
        // Clear fields after success (so the form resets)
        $name = $email = $msg = $captcha = '';
    }
}

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .ok { color: green; font-weight: bold; text-align: center; margin: 10px 0 14px; }
    </style>
</head>
<body>
    <h1>Contact Us</h1>

    <?php if ($success): ?>
        <p class="ok"><?= h($success) ?></p>
    <?php endif; ?>

    <form method="post" action="" onsubmit="return contactCheck()">
        <fieldset>
            Name:
            <input type="text" id="contactName" name="name" value="<?= h($name) ?>" onblur="checkContactName()">
            <span id="nameError" class="error-msg"><?= h($errors['name']) ?></span>
            <br><br>

            Email:
            <input type="text" id="contactEmail" name="email" value="<?= h($email) ?>" onblur="checkContactEmail()">
            <span id="emailError" class="error-msg"><?= h($errors['email']) ?></span>
            <br><br>

            Your Message:
            <br>
            <textarea id="contactMsg" name="message" rows="5" cols="40" onblur="checkContactMsg()"><?= h($msg) ?></textarea>
            <span id="msgError" class="error-msg"><?= h($errors['msg']) ?></span>
            <br><br>

            CAPTCHA: <b>7 + 2 = ?</b>
            <input type="text" id="captchaInput" name="captcha" style="width:50px;" value="<?= h($captcha) ?>" onblur="checkCaptcha()">
            <span id="captchaError" class="error-msg"><?= h($errors['captcha']) ?></span>
            <br><br>

            <input type="submit" value="Submit">
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
            <br>
        </fieldset>
    </form>

    <script>
        function checkContactName() {
            const name = document.getElementById('contactName').value.trim();
            document.getElementById('nameError').innerHTML = name === "" ? "Please enter your name!" : "";
        }
        function checkContactEmail() {
            const email = document.getElementById('contactEmail').value.trim();
            const valid = email !== "" && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            document.getElementById('emailError').innerHTML = valid ? "" : "Please enter a valid email!";
        }
        function checkContactMsg() {
            const msg = document.getElementById('contactMsg').value.trim();
            document.getElementById('msgError').innerHTML = msg === "" ? "Please type your message!" : "";
        }
        function checkCaptcha() {
            const c = document.getElementById('captchaInput').value.trim();
            document.getElementById('captchaError').innerHTML = (c === "9") ? "" : "Wrong answer!";
        }
        function contactCheck() {
            checkContactName();
            checkContactEmail();
            checkContactMsg();
            checkCaptcha();
            const ok =
                document.getElementById('nameError').innerHTML === "" &&
                document.getElementById('emailError').innerHTML === "" &&
                document.getElementById('msgError').innerHTML === "" &&
                document.getElementById('captchaError').innerHTML === "";
            return ok;
        }
    </script>
</body>
</html>
