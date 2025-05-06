<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_manager = 1 LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && $passwordInput === $admin['password_hash']) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['user_id'];
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
            background-image: url('https://source.unsplash.com/1600x900/?gaming,cyberpunk');
            background-size: cover;
            background-position: center;
        }
        .auth-container {
            background: #00453F;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(61, 189, 167, 0.7);
            text-align: center;
            width: 400px;
        }
        h2 {
            color: white;
            font-size: 28px;
        }
        .error {
            color: #FF6B6B;
            margin-bottom: 10px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #3DBDA7;
            border-radius: 8px;
            background-color: #1e1e1e;
            color: white;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 30px;
            background: linear-gradient(90deg, #3DBDA7, #30A18C);
            border: none;
            border-radius: 8px;
            color: black;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(61, 189, 167, 0.8);
        }
        .image-container {
            text-align: center;
            margin-bottom: 50px;
        }
        .image-container img {
            width: 200px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="image-container">
        <img src= "../assets/logo.png" alt="Admin Logo">
    </div>
    <div class="auth-container">
        <h2>Admin Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
