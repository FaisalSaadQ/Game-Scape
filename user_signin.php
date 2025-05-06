<?php
session_start();
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: index.php');
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
$success = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $passwordInput === $user['password_hash']) {
        session_regenerate_id(true);
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

if (isset($_POST['signup'])) {
    $newEmail = trim($_POST['new_email'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$newEmail]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        $error = 'Email already exists.';
    } elseif (empty($newEmail) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?)');
        $stmt->execute([$newEmail, $newPassword, 'First', 'Last']);
        $success = 'Account created successfully. You can now login.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Sign In / Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            margin: 10px 0;
        }
        .success {
            color: #3DBDA7;
            margin: 10px 0;
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
            padding: 15px;
            background: linear-gradient(90deg, #3DBDA7, #30A18C);
            border: none;
            border-radius: 8px;
            color: black;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(61, 189, 167, 0.8);
        }
        .switch-link {
            color: #3DBDA7;
            margin-top: 15px;
            display: block;
            cursor: pointer;
            text-decoration: underline;
        }
        .image-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .image-container img {
            width: 200px;
            border-radius: 20px;
        }
    </style>
    <script>
        function showSignup() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('signup-form').style.display = 'block';
        }
        function showLogin() {
            document.getElementById('signup-form').style.display = 'none';
            document.getElementById('login-form').style.display = 'block';
        }
        function validateSignupForm() {
            const email = document.querySelector('[name="new_email"]').value.trim();
            const password = document.querySelector('[name="new_password"]').value;
            const confirm = document.querySelector('[name="confirm_password"]').value;

            if (!email.includes("@") || !email.includes(".")) {
                alert("Enter a valid email.");
                return false;
            }
            if (password.length < 6) {
                alert("Password must be at least 6 characters.");
                return false;
            }
            if (password !== confirm) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="image-container">
    <img src="assets/logo.png" alt="User Logo">
</div>

<div class="auth-container">
    <div id="login-form">
        <h2>User Login</h2>
        <form method="post" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <?php if (isset($_POST['login']) && $error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <button type="submit" name="login">Login</button>
        </form>
        <span class="switch-link" onclick="showSignup()">Don't have an account? Sign Up</span>
    </div>

    <div id="signup-form" style="display: none;">
        <h2>Create Account</h2>
        <form method="post" action="" onsubmit="return validateSignupForm();">
            <input type="email" name="new_email" placeholder="Email" required>
            <input type="password" name="new_password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <?php if (isset($_POST['signup']) && $error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_POST['signup']) && $success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <button type="submit" name="signup">Sign Up</button>
        </form>
        <span class="switch-link" onclick="showLogin()">Already have an account? Login</span>
    </div>
</div>
</body>
</html>