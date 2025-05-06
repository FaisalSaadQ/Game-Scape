<?php
session_start();
session_unset();
session_destroy();

// Store logout message in a temporary session
session_start();
$_SESSION['logout_message'] = "You have been logged out.";
header("Location: index.php");
exit;
?>