<?php
session_start();

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect back to login page
header('Location: login.php');
exit;
?>
