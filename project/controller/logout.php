<?php
session_start();
session_unset();
session_destroy();
setcookie('status', '', time() - 3600, '/');
setcookie('remember_user', '', time() - 3600, '/');
setcookie('remember_role', '', time() - 3600, '/');
header('Location: ../view/login.php');
exit;
?>
