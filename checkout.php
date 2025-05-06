<?php
session_start();

// ─── 0) Require login ────────────────────────────────────────
if (! isset($_SESSION['user_id'])) {
    // send them to login, then back here
    header("Location: login.php?redirect=checkout.php");
    exit;
}

// ─── 1) Setup ────────────────────────────────────────────────
include "includes/db.php";
$userId    = (int) $_SESSION['user_id'];
$sessionId = session_id();

/** Safely fetch & optionally filter a POST value */
function getPost(string $key, $filter = null) {
    if (!isset($_POST[$key])) return null;
    $v = trim($_POST[$key]);
    if ($filter) {
        $v = filter_var($v, $filter);
        return $v === false ? null : $v;
    }
    return $v;
}

/**
 * Loop through $cartItems to create order, items, address snapshot,
 * mark cart checked‐out. Triggers will adjust stock.
 */
function processPurchase(int $userId, array $cartItems) {
    global $con;

    // 1) Calculate total
    $grandTotal = "0.00";
    foreach ($cartItems as $it) {
        $line       = bcmul((string)$it['price'], (string)$it['quantity'], 2);
        $grandTotal = bcadd($grandTotal, $line, 2);
    }

    // 2) Snapshot address
    $street  = getPost('address');
    $city    = getPost('city');
    $zip     = getPost('zip');
    $country = getPost('country');
    $stmt = $con->prepare("
        INSERT INTO addresses (user_id, street, city, zip_code, country)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $userId, $street, $city, $zip, $country);
    if (!$stmt->execute()) {
        return [false, "Address save failed: ".$stmt->error];
    }

    // 3) Create order
    $status = 'paid';
    $stmt = $con->prepare("
        INSERT INTO orders (user_id, grand_total, status)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("ids", $userId, $grandTotal, $status);
    if (!$stmt->execute()) {
        return [false, "Order creation failed: ".$stmt->error];
    }
    $orderId = $stmt->insert_id;

    // 4) Insert items
    $stmt = $con->prepare("
        INSERT INTO order_items
          (order_id, variant_id, quantity, unit_price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($cartItems as $it) {
        $stmt->bind_param(
          "iiid",
          $orderId,
          $it['variant_id'],
          $it['quantity'],
          $it['price']
        );
        if (!$stmt->execute()) {
            return [false, "Failed adding item {$it['variant_id']}: ".$stmt->error];
        }
    }

    // 5) Mark cart checked-out
    $cartId = $cartItems[0]['cart_id'];
    $stmt = $con->prepare("
        UPDATE carts
           SET is_checked_out = 1
         WHERE cart_id = ?
    ");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();

    return [true, $orderId];
}

// ─── 2) Fetch the active cart (either/or lookup) ───────────────
$stmt = $con->prepare("
    SELECT cart_id
      FROM carts
     WHERE is_checked_out = 0
       AND (user_id = ? OR session_id = ?)
     LIMIT 1
");
$stmt->bind_param("is", $userId, $sessionId);
$stmt->execute();
$row     = $stmt->get_result()->fetch_assoc();
$cart_id = $row['cart_id'] ?? null;

// ─── 3) Pull the items ───────────────────────────────────────
$cart_items = [];
$totalPrice = 0.00;
if ($cart_id) {
    $sql = "
     SELECT ci.cart_id, ci.variant_id, ci.quantity, pv.price,
       p.name, p.product_id, pv.colour, pv.size
        FROM cart_items ci
        JOIN product_variants pv ON ci.variant_id = pv.variant_id
        JOIN products p         ON pv.product_id = p.product_id
       WHERE ci.cart_id = ?
    ";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($cart_items as &$it) {
        $it['subtotal'] = $it['price'] * $it['quantity'];
        $totalPrice   += $it['subtotal'];
    }
}

// ─── 4) Handle POSTed payment data ────────────────────────────
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_number'])) {
    $required = [
      'name'        => getPost('name'),
      'address'     => getPost('address'),
      'city'        => getPost('city'),
      'zip'         => getPost('zip'),
      'country'     => getPost('country'),
      'card_name'   => getPost('card_name'),
      'card_number' => getPost('card_number'),
      'exp'         => getPost('exp'),
      'cvv'         => getPost('cvv'),
    ];
    $missing = [];
    foreach ($required as $k => $v) {
        if (!$v) $missing[] = $k;
    }
    if ($missing) {
        $error = "Missing fields: ".implode(", ", $missing);
    } else {
        // safe: $userId is guaranteed to be an int
        
        list($ok, $res) = processPurchase($userId, $cart_items);
        if ($ok) {
            header("Location: thank.html");
              $productIds = array_column($cart_items, 'product_id');
              $existing = [];

          if (isset($_COOKIE['pastPurchases'])) {
              $existing = explode(',', $_COOKIE['pastPurchases']);
        }

        $all = array_merge($existing, $productIds);
        $all = array_unique($all);
        $all = array_slice($all, -10); 

        setcookie('pastPurchases', implode(',', $all), time() + (30 * 24 * 60 * 60), '/');
            exit;
        } else {
            $error = $res;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Checkout</title>
  <link rel="stylesheet" href="css/checkout.css" />
  <link rel="stylesheet" href="css/header-footer.css" />
</head>
<body>
  <?php include "includes/header.php"; ?>

  <div class="checkout-container">
    <h2>Review Your Cart</h2>
    <?php if (empty($cart_items)): ?>
      <p>Your cart is empty. <a href="index.php">Continue shopping</a>.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Name</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
          <?php foreach ($cart_items as $it): ?>
            <tr>
              <td>
                <?= htmlspecialchars($it['name']) ?><br>
                <small><?= "{$it['colour']} / {$it['size']}" ?></small>
              </td>
              <td><?= $it['quantity'] ?></td>
              <td><?= number_format($it['price'],2) ?> SAR</td>
              <td><?= number_format($it['subtotal'],2) ?> SAR</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3">Total</td>
            <td><?= number_format($totalPrice,2) ?> SAR</td>
          </tr>
        </tfoot>
      </table>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <h2>Shipping & Payment</h2>
      <form method="post" action="checkout.php">
        <label>
          Full Name
          <input type="text" name="name" required
                 value="<?= htmlspecialchars($required['name'] ?? '') ?>">
        </label>
        <label>
          Address
          <input type="text" name="address" required
                 value="<?= htmlspecialchars($required['address'] ?? '') ?>">
        </label>
        <label>
          City
          <input type="text" name="city" required
                 value="<?= htmlspecialchars($required['city'] ?? '') ?>">
        </label>
        <label>
          ZIP / Postal Code
          <input type="text" name="zip" required
                 value="<?= htmlspecialchars($required['zip'] ?? '') ?>">
        </label>
        <label>
          Country
          <input type="text" name="country" required
                 value="<?= htmlspecialchars($required['country'] ?? '') ?>">
        </label>
        <hr>
        <label>
          Name on Card
          <input type="text" name="card_name" required>
        </label>
        <label>
          Card Number
          <input type="text" name="card_number" required>
        </label>
        <label>
          Expiration (MM/YY)
          <input type="text" name="exp" required>
        </label>
        <label>
          CVV
          <input type="text" name="cvv" required>
        </label>
        <button type="submit">
          Pay <?= number_format($totalPrice,2) ?> SAR
        </button>
      </form>
    <?php endif; ?>
  </div>

  <?php include "includes/footer.html"; ?>

  
</body>
</html>
