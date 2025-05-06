<?php
session_start();
include "includes/db.php";

// grab & clear any flash-error
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// 1) Identify cart
if (isset($_SESSION['user_id'])) {
    $identifier = $_SESSION['user_id'];
    $sql        = "SELECT cart_id FROM carts WHERE user_id=? AND is_checked_out=0 LIMIT 1";
} else {
    $identifier = session_id();
    $sql        = "SELECT cart_id FROM carts WHERE session_id=? AND is_checked_out=0 LIMIT 1";
}
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $identifier);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

// 2) Fetch items + stock
$cart_items = [];
$totalPrice = 0;
if ($cart) {
    $cart_id = $cart['cart_id'];
    $sql = "
      SELECT
        ci.variant_id, ci.quantity,
        pv.price, pv.stock_qty,
        pv.colour, pv.size,
        p.name, pi.filename
      FROM cart_items ci
      JOIN product_variants pv ON ci.variant_id = pv.variant_id
      JOIN products p         ON pv.product_id = p.product_id
      LEFT JOIN product_images pi
        ON p.product_id = pi.product_id AND pi.position = 0
      WHERE ci.cart_id = ?
    ";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $totalPrice    += $row['subtotal'];
        $cart_items[]   = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>My Cart</title>
  <link rel="stylesheet" href="css/cart.css" />
  <link rel="stylesheet" href="css/header-footer.css" />
</head>
<body>
  <?php include "includes/header.php"; ?>

  <div class="cart-container">
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table>
      <summary><h2 style="color:white">My Cart</h2></summary>
      <thead>
        <tr>
          <th>Image</th><th>Name</th><th>Qty</th>
          <th>Price</th><th>Subtotal</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($cart_items)): ?>
          <tr><td colspan="6">Your cart is empty.</td></tr>
        <?php else: ?>
          <?php foreach ($cart_items as $item): ?>
            <tr>
              <td>
                <img src="assets/prodects/<?= htmlspecialchars($item['filename']) ?>" width="60">
              </td>
              <td>
                <?= htmlspecialchars($item['name']) ?><br>
                <small><?= "{$item['colour']} / {$item['size']}" ?></small>
              </td>
              <td>
                <div class="quantity-container">
                  <!-- always allow remove -->
                  <a href="add_to_cart.php?action=remove&id=<?= $item['variant_id'] ?>">
                    <button type="button">-</button>
                  </a>

                  <!-- current quantity -->
                  <span><?= $item['quantity'] ?></span>

                  <!-- only allow add if under stock -->
                  <?php if ($item['quantity'] < $item['stock_qty']): ?>
                    <a href="add_to_cart.php?action=add&id=<?= $item['variant_id'] ?>">
                      <button type="button">+</button>
                    </a>
                  <?php else: ?>
                    <button type="button" disabled title="Max in stock">+</button>
                  <?php endif; ?>
                </div>
              </td>
              <td><?= number_format($item['price'],2) ?> SAR</td>
              <td><?= number_format($item['subtotal'],2) ?> SAR</td>
              <td>
                <a href="add_to_cart.php?action=delete&id=<?= $item['variant_id'] ?>">
                  <button class="remove-button">Remove</button>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4">Total</td>
          <td colspan="2"><?= number_format($totalPrice,2) ?> SAR</td>
        </tr>
      </tfoot>
    </table>

    <div class="form-actions">
      <form action="checkout.php" method="post">
        <button type="submit">Proceed to Checkout</button>
      </form>
      <form action="clear_cart.php" method="post" onsubmit="return confirm('Clear cart?');">
        <button type="submit">Clear Cart</button>
      </form>
    </div>
  </div>

  <?php include "includes/footer.html"; ?>
</body>
</html>
