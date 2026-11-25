<?php
session_start();
require_once "conn.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm = trim($_POST["confirm"]);
    $role = 'user'; // default for all users
    $user_role = $_POST["role"];
    $course = $_POST["course"] ?? null;
    $year_level = $_POST["year_level"] ?? null;
    $section = $_POST["section"] ?? null;

    // Password match check
    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $error = "Email already registered!";
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password_hash, role, course, year_level, section, user_role)
                VALUES (:full_name, :email, :password_hash, :role, :course, :year_level, :section, :user_role)
            ");
            $stmt->execute([
                'full_name' => $fullname,
                'email' => $email,
                'password_hash' => $hashed,
                'role' => $role,
                'course' => $course,
                'year_level' => $year_level,
                'section' => $section,
                'user_role' => $user_role
            ]);

            $_SESSION['success'] = "Registration successful! You can now log in.";
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Campus Lost & Found | Register</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body {
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: Arial, sans-serif;
}
.login-container {
    background: #fff;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    width: 400px;
    text-align: center;
}
.login-container h2 { margin-bottom: 10px; color: #333; }
.subtitle { color: #777; margin-bottom: 20px; }
input, select { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; font-size: 14px; }
button { width: 100%; padding: 10px; background-color: #2b6cb0; border: none; color: #fff; border-radius: 5px; cursor: pointer; font-size: 16px; }
button:hover { background-color: #1e4e8c; }
.error { color: red; font-size: 14px; margin-top: 10px; }
.student-fields { display: none; }
</style>
</head>
<body>
<div class="login-container">
    <h2>🎒 Create Account</h2>
    <p class="subtitle">Campus Lost & Found Tracking System</p>

    <form method="POST" action="">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm" placeholder="Confirm Password" required>

        <select name="role" id="role" required>
            <option value="">Select Role</option>
            <option value="student">Student</option>
            <option value="faculty_staff">Faculty / Staff</option>
            <option value="visitor">Visitor</option>
        </select>

        <div id="studentFields" class="student-fields">
            <select name="course">
                <option value="">Select Course</option>
                <option value="BSIT">BSIT</option>
                <option value="BSICT">BSICT</option>
                <option value="CCJE">BS Criminology</option>
                <option value="BSHM">BSHM</option>
                <option value="BTVTED">BTVTED</option>
                <option value="BEED">BEED</option>
                <option value="BSED">English</option>
                <option value="BSED">Mathematics</option>
                <option value="BSED">Filipino</option>
                <option value="SOCSTUD">Social Study</option>
            </select>

            <select name="year_level">
                <option value="">Select Year Level</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
            </select>

            <input type="text" name="section" placeholder="Section (e.g., A, B, or C)">
        </div>

        <button type="submit">Register</button>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </form>

    <p style="margin-top: 10px; font-size: 14px;">
        Already have an account? <a href="login.php" style="color: #2b6cb0; text-decoration: none;">Login here</a>
    </p>
</div>

<script>
document.getElementById("role").addEventListener("change", function() {
    const studentFields = document.getElementById("studentFields");
    studentFields.style.display = this.value === "student" ? "block" : "none";
});
</script>
</body>
</html>
