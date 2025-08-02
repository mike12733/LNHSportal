<?php
require_once 'config/database.php';
require_once 'classes/User.php';

$user = new User();
$user->logout();

header('Location: index.php?message=logged_out');
exit();
?>