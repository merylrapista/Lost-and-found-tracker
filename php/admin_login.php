<?php
session_start();
require_once "conn.php"; // your PDO connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {
        try {
            // Correct table name: users
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND role = 'admin' LIMIT 1");
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION["user_id"] = $admin["id"];
                $_SESSION["full_name"] = $admin["full_name"];
                $_SESSION["role"] = $admin["role"];
                $_SESSION["user_role"] = $admin["user_role"] ?? '';

                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid admin email or password.";
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
    <title>🔒 Admin Login | Lost & Found Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-color: #f4f6f8;
            font-family: Arial, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        input, button {
            width: 90%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #d47357;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #b95e43;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        a {
            color: #d47357;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>🔒 Admin Login</h2>
    <p class="subtitle">Manage Lost & Found Tracker</p>

    <form method="POST" action="">
        <input type="email" name="email" placeholder="Admin Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </form>

    <p style="margin-top: 10px; font-size: 14px;">
        <!-- <a href="login.php">Back to User Login</a> -->
    </p>
</div>
</body>
</html>
