<?php
session_start();
include "includes/db.php";

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$session_id = session_id();

if ($is_logged_in) {
    $stmt = $con->prepare("SELECT cart_id FROM carts WHERE user_id = ? AND is_checked_out = 0 LIMIT 1");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $con->prepare("SELECT cart_id FROM carts WHERE session_id = ? AND is_checked_out = 0 LIMIT 1");
    $stmt->bind_param("s", $session_id);
}

$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result) {
    $cart_id = $result['cart_id'];
    $stmt = $con->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
}

header('Location: cart.php');
exit();
