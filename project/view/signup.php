<?php
session_start();

$errorMsg = '';
if (isset($_GET['error'])) {
    $err = $_GET['error'];
    if ($err === 'email_exists') {
        $errorMsg = 'This email is already registered.';
    } elseif ($err === 'regerror') {
        $errorMsg = 'Registration failed. Try again.';
    } elseif ($err === 'badrequest') {
        $errorMsg = 'Please fill the form correctly.';
    } elseif ($err === '404') {
        header("Location: error404.php");
        exit;
    } elseif ($err === '500') {
        header("Location: error500.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Hotel Resarvation - Signup</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .error-msg { color: red; font-weight: 600; margin: 4px 0; }
        .ok { color: green; font-weight: 700; margin-top: 10px; text-align: center; }
        .center-under { text-align: center; font-weight: 700; margin: 6px 0 14px; color: green; }
        .notice { text-align:center; font-weight:700; color:green; margin-bottom:12px; }
    </style>
</head>
<body>
    <h1>Signup Page</h1>

    <?php if (!empty($errorMsg)): ?>
        <p class="notice"><?= htmlspecialchars($errorMsg) ?></p>
    <?php endif; ?>

    <form id="signupForm">
        <fieldset>
            Username:
            <input type="text" id="signupUsername" name="username" onblur="checkSignupUsername()" />
            <p id="signupUError" class="error-msg"></p>

            Email:
            <input type="text" id="signupEmail" name="email" onblur="checkSignupEmail()" />
            <p id="signupEError" class="error-msg"></p>

            Password:
            <input type="password" id="signupPassword" name="password" onblur="checkSignupPassword()" />
            <p id="signupPError" class="error-msg"></p>

            <input type="button" value="Sign Up" onclick="signupUser()" />
            <p id="signupSuccess" class="ok"></p>
        </fieldset>

        <p style="text-align:center;">
            <input type="button" 
                   value="Login" 
                   onclick="window.location.href='login.php'">
        </p>
    </form>

    <script>
        function checkSignupUsername() {
            const username = document.getElementById('signupUsername').value.trim();
            let msg = "";
            if (username === "") msg = "Please type username!";
            else if (username.length < 3) msg = "Username must be at least 3 characters!";
            document.getElementById('signupUError').innerHTML = msg;
        }
        
        function checkSignupEmail() {
            const email = document.getElementById('signupEmail').value.trim();
            const valid = email !== "" && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            document.getElementById('signupEError').innerHTML = valid ? "" : "Please enter a valid email!";
        }

        function checkSignupPassword() {
            const password = document.getElementById('signupPassword').value;
            document.getElementById('signupPError').innerHTML =
                password.length < 4 ? "Password must be at least 4 characters!" : "";
        }

        function signupUser() {
            checkSignupUsername();
            checkSignupEmail();
            checkSignupPassword();
            
            const username = document.getElementById('signupUsername').value.trim();
            const email = document.getElementById('signupEmail').value.trim();
            const password = document.getElementById('signupPassword').value;
            
            const ok = document.getElementById('signupUError').innerHTML === "" &&
                       document.getElementById('signupEError').innerHTML === "" &&
                       document.getElementById('signupPError').innerHTML === "";
            document.getElementById('signupSuccess').innerHTML = ok ? "Submitting…" : "";

            if (!ok) return false;

            const user = {
                'username': username,
                'email': email,
                'password': password
            };

            const data = JSON.stringify(user);

            const xhttp = new XMLHttpRequest();
            xhttp.open('POST', '../controller/signupCheck.php', true);
            xhttp.setRequestHeader("Content-type", "application/json");
            xhttp.send(data);

            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    const response = JSON.parse(this.responseText);
                    if (response.status === 'success') {
                        document.getElementById('signupSuccess').innerHTML = "Signup successful!";
                        window.location.href = 'login.php?success=registered';
                    } else {
                        document.getElementById('signupSuccess').innerHTML = response.message;
                    }
                }
            }
        }
    </script>
</body>
</html>
