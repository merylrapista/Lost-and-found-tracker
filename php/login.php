<?php
session_start();
require_once "conn.php"; // conn.php defines $pdo

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {
        try {
            // Fetch user by email regardless of role
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Verify password
                if (password_verify($password, $user["password_hash"])) {
                    // Set session variables
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["full_name"] = $user["full_name"];
                    $_SESSION["role"] = $user["role"];
                    $_SESSION["user_role"] = $user["user_role"] ?? null;
                    $_SESSION['profile_pic'] = $user['profile_pic'] ?? null;

                    // Redirect based on role
                    if ($user["role"] === "admin") {
                        header("Location: admin_dashboard.php");
                        exit();
                    } else {
                        header("Location: dashboard.php");
                        exit();
                    }
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "Invalid email.";
            }

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in both fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🔍 Lost & Found Tracker | Login</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body { background-color: #f4f6f8; font-family: Arial, sans-serif; }
.login-container {
    max-width: 400px; margin: 80px auto; background: #fff;
    padding: 30px; border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center;
}
input, button {
    width: 90%; padding: 10px; margin-top: 10px;
    border-radius: 5px; border: 1px solid #ccc;
}
button {
    background-color: #d47357; color: #fff; border: none; cursor: pointer;
}
button:hover { background-color: #b95e43; }
.error { color: red; font-size: 14px; margin-top: 10px; }
a { color: #d47357; text-decoration: none; }
</style>
</head>
<body>
<div class="login-container">
    <h2>🔍 Lost & Found Tracker</h2>
    <p class="subtitle">Report or Recover Lost Items Easily</p>

    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </form>

    <p style="margin-top: 10px; font-size: 14px;">
        Don’t have an account? <a href="register.php">Register here</a>
    </p>
</div>
</body>
</html>
