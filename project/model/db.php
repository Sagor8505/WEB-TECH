<?php
$host = "127.0.0.1";
$dbuser = "root";
$dbpass = "";
$dbname = "webtech";

function getConnection() {
    global $host, $dbuser, $dbpass, $dbname;
    $con = mysqli_connect($host, $dbuser, $dbpass, $dbname);
    if (!$con) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    return $con;
}
?>
