<?php
session_start();
$_SESSION = [];
session_destroy();
setcookie('remember_user', '', time() - 3600, '/');
header('Location: index.php');
exit;