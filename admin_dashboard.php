<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$host = 'localhost:3307';
$dbname = 'e_commerce';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Add new product and its variant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, description, is_active, spec) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['category_id'],
            $_POST['description'],
            $_POST['is_active'],
            json_encode([])
        ]);

        $product_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, sku, colour, size, price, price_after, stock_qty)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");

        $sku = strtoupper(substr($_POST['name'], 0, 3)) . '-' . rand(1000, 9999);

        $stmt->execute([
            $product_id,
            $sku,
            $_POST['colour'],
            $_POST['size'],
            $_POST['price'],
            $_POST['price_after'],
            $_POST['stock_qty']
        ]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error adding product: " . $e->getMessage());
    }
}

// Edit product and its variant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, description = ?, is_active = ? WHERE product_id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['category_id'],
        $_POST['description'],
        $_POST['is_active'],
        $_POST['product_id']
    ]);

    if (!empty($_POST['variant_id'])) {
        $stmt = $pdo->prepare("UPDATE product_variants SET colour = ?, size = ?, price = ?, price_after = ?, stock_qty = ? WHERE variant_id = ?");
        $stmt->execute([
            $_POST['colour'],
            $_POST['size'],
            $_POST['price'],
            $_POST['price_after'],
            $_POST['stock_qty'],
            $_POST['variant_id']
        ]);
    }
}

// Delete product
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE product_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch all products with their first variant
$products = $pdo->query("
    SELECT p.*, v.variant_id, v.colour, v.size, v.price, v.price_after, v.stock_qty
    FROM products p
    LEFT JOIN (
        SELECT *
        FROM product_variants
        GROUP BY product_id
    ) v ON p.product_id = v.product_id
    WHERE p.is_active = 1
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: white;
            padding: 40px;
            background-image: url('https://source.unsplash.com/1600x900/?gaming,cyberpunk');
            background-size: cover;
            background-position: center;
        }

        h2, h3 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
        }

        .dashboard-container {
            background: #00453F;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(61, 189, 167, 0.7);
            max-width: 1100px;
            margin: auto;
        }

        form {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #3DBDA7;
            padding: 12px;
            text-align: center;
            background-color: #1e1e1e;
        }

        th {
            background-color: #005E56;
            color: white;
        }

        input, select {
            width: 100%;
            padding: 6px 10px;
            background-color: #2a2a2a;
            color: white;
            border: 1px solid #3DBDA7;
            border-radius: 4px;
            font-size: 13px;
            box-sizing: border-box;
            max-width: 160px;
        }

        button {
            padding: 8px 12px;
            background: linear-gradient(90deg, #3DBDA7, #30A18C);
            border: none;
            color: black;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(61, 189, 167, 0.8);
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .delete-link button {
            background-color: #FF6B6B;
        }

        .delete-link button:hover {
            box-shadow: 0 0 12px #ff6b6b;
        }
    </style>
</head>
<body>
    <h2>Admin Dashboard - Product Management</h2>

    <!-- Logout Button -->
    <div style="text-align: center; margin-bottom: 20px;">
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit" style="
                padding: 8px 12px;
                background: linear-gradient(90deg, #3DBDA7, #30A18C);
                border: none;
                color: black;
                font-size: 14px;
                font-weight: bold;
                border-radius: 6px;
                cursor: pointer;
                box-shadow: 0 0 12px rgba(61, 189, 167, 0.5);
            ">Logout</button>
        </form>
    </div>

    <div class="dashboard-container">
        <h3>Add New Product + Variant</h3>
        <form method="POST">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Category ID</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Colour</th>
                    <th>Size</th>
                    <th>Price</th>
                    <th>Price After</th>
                    <th>Stock Qty</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <td><input type="text" name="name" required></td>
                    <td><input type="number" name="category_id" required></td>
                    <td><input type="text" name="description" required></td>
                    <td>
                        <select name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </td>
                    <td><input type="text" name="colour" required></td>
                    <td><input type="text" name="size" required></td>
                    <td><input type="number" step="0.01" name="price" required></td>
                    <td><input type="number" step="0.01" name="price_after" required></td>
                    <td><input type="number" name="stock_qty" required></td>
                    <td><button type="submit" name="add_product">Add</button></td>
                </tr>
            </table>
        </form>

        <h3>All Products</h3>
        <table>
            <tr>
                <th>Name</th>
                <th>Category ID</th>
                <th>Description</th>
                <th>Status</th>
                <th>Colour</th>
                <th>Size</th>
                <th>Price</th>
                <th>Price After</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($products as $product): ?>
                <tr>
                    <form method="POST">
                        <td><input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>"></td>
                        <td><input type="number" name="category_id" value="<?= $product['category_id'] ?>"></td>
                        <td><input type="text" name="description" value="<?= htmlspecialchars($product['description']) ?>"></td>
                        <td>
                            <select name="is_active">
                                <option value="1" <?= $product['is_active'] ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= !$product['is_active'] ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </td>
                        <td><input type="text" name="colour" value="<?= htmlspecialchars($product['colour']) ?>"></td>
                        <td><input type="text" name="size" value="<?= htmlspecialchars($product['size']) ?>"></td>
                        <td><input type="number" step="0.01" name="price" value="<?= $product['price'] ?>"></td>
                        <td><input type="number" step="0.01" name="price_after" value="<?= $product['price_after'] ?>"></td>
                        <td><input type="number" name="stock_qty" value="<?= $product['stock_qty'] ?>"></td>
                        <td class="action-buttons">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <input type="hidden" name="variant_id" value="<?= $product['variant_id'] ?>">
                            <button type="submit" name="edit_product">Edit</button>
                            <a href="?delete=<?= $product['product_id'] ?>" class="delete-link" onclick="return confirm('Are you sure?');">
                                <button type="button">Delete</button>
                            </a>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
