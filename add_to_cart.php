<?php
session_start();
include "includes/db.php";

// 1) Validate action & variant ID
$action    = $_GET['action'] ?? '';
$variantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (! in_array($action, ['add','remove','delete'], true) || $variantId < 1) {
    header('Location: cart.php');
    exit;
}

$userId    = $_SESSION['user_id'] ?? null;
$sessionId = session_id();

// 2) Find or create the open cart
if ($userId !== null) {
    $stmt = $con->prepare("
      SELECT cart_id
        FROM carts
       WHERE user_id = ?
         AND is_checked_out = 0
       LIMIT 1
    ");
    $stmt->bind_param("i", $userId);
} else {
    $stmt = $con->prepare("
      SELECT cart_id
        FROM carts
       WHERE session_id = ?
         AND is_checked_out = 0
       LIMIT 1
    ");
    $stmt->bind_param("s", $sessionId);
}
$stmt->execute();
$row    = $stmt->get_result()->fetch_assoc();
$cartId = $row['cart_id'] ?? null;

if (! $cartId) {
    if ($userId !== null) {
        $stmt = $con->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->bind_param("i", $userId);
    } else {
        $stmt = $con->prepare("INSERT INTO carts (session_id) VALUES (?)");
        $stmt->bind_param("s", $sessionId);
    }
    $stmt->execute();
    $cartId = $stmt->insert_id;
}

// 3) Perform the requested action
if ($action === 'add') {
    // 3a) how many already in cart?
    $stmt = $con->prepare("
      SELECT quantity
        FROM cart_items
       WHERE cart_id = ? AND variant_id = ?
    ");
    $stmt->bind_param("ii", $cartId, $variantId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $currentQty = $existing['quantity'] ?? 0;

    // 3b) how many in stock?
    $stmt = $con->prepare("
      SELECT stock_qty
        FROM product_variants
       WHERE variant_id = ?
    ");
    $stmt->bind_param("i", $variantId);
    $stmt->execute();
    $stock = $stmt->get_result()->fetch_assoc()['stock_qty'] ?? 0;

    // 3c) if at or above stock, flash error
    if ($currentQty >= $stock) {
        $_SESSION['error'] = $stock
          ? "Only {$stock} available—cannot add more."
          : "This item is out of stock.";
        header('Location: cart.php');
        exit;
    }

    // 3d) otherwise add or bump
    if ($existing) {
        $stmt = $con->prepare("
          UPDATE cart_items
             SET quantity = quantity + 1
           WHERE cart_id = ? AND variant_id = ?
        ");
        $stmt->bind_param("ii", $cartId, $variantId);
    } else {
        $stmt = $con->prepare("
          INSERT INTO cart_items (cart_id, variant_id, quantity)
          VALUES (?, ?, 1)
        ");
        $stmt->bind_param("ii", $cartId, $variantId);
    }
    $stmt->execute();

} elseif ($action === 'remove') {
    // subtract one or delete
    $stmt = $con->prepare("
      SELECT quantity
        FROM cart_items
       WHERE cart_id = ? AND variant_id = ?
    ");
    $stmt->bind_param("ii", $cartId, $variantId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && $row['quantity'] > 1) {
        $stmt = $con->prepare("
          UPDATE cart_items
             SET quantity = quantity - 1
           WHERE cart_id = ? AND variant_id = ?
        ");
        $stmt->bind_param("ii", $cartId, $variantId);
    } else {
        $stmt = $con->prepare("
          DELETE FROM cart_items
           WHERE cart_id = ? AND variant_id = ?
        ");
        $stmt->bind_param("ii", $cartId, $variantId);
    }
    $stmt->execute();

} else { // delete entire line
    $stmt = $con->prepare("
      DELETE FROM cart_items
       WHERE cart_id = ? AND variant_id = ?
    ");
    $stmt->bind_param("ii", $cartId, $variantId);
    $stmt->execute();
}


// 4) Redirect back to the cart page
header('Location: cart.php');
exit;
