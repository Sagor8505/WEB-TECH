<?php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && $_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
            $_SESSION['username'] = $_COOKIE['remember_user'];
        }
    } else {
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <link rel="stylesheet" href="../asset/css/auth.css">
</head>
<body>
    <form method="post" action="../controller/file.php" enctype="multipart/form-data">
        image: <input type="file" name="myfile" value="" />
        <input type="submit" name="submit" value="Submit" />
    </form>
    <p><a href="user_dashboard.php">Back to Home</a></p>
</body>
</html>
