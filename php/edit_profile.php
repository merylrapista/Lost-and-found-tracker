<?php
session_start();
require_once "conn.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$error = $success = "";

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found!");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $role = $_POST["role"];
    $course = $_POST["course"] ?? null;
    $year_level = $_POST["year_level"] ?? null;
    $section = $_POST["section"] ?? null;

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = "../uploads/profile_pics/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = $user_id . "_" . basename($_FILES['profile_pic']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
            $profilePicToSave = $fileName;
        } else {
            $error = "Failed to upload profile picture.";
        }
    } else {
        $profilePicToSave = $user['profile_pic'];
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE users 
            SET full_name = :full_name, email = :email, role = :role,
                course = :course, year_level = :year_level, section = :section,
                profile_pic = :profile_pic
            WHERE id = :id
        ");

        $stmt->execute([
            'full_name' => $fullname,
            'email' => $email,
            'role' => $role,
            'course' => $course,
            'year_level' => $year_level,
            'section' => $section,
            'profile_pic' => $profilePicToSave,
            'id' => $user_id
        ]);

        $success = "Profile updated successfully!";
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile | Lost & Found</title>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #eef3f9;
    margin: 0;
    display: flex;
    justify-content: center;
    padding: 40px 15px;
}

.back-btn {
    position: fixed;
    top: 20px;
    left: 20px;
    background: #0b2447;
    color: white;
    padding: 10px 18px;
    font-size: 14px;
    border-radius: 8px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    transition: 0.2s;
    z-index: 999;
}
.back-btn:hover {
    background: #0d2f5f;
    transform: translateX(-3px);
}

.card {
    background: #ffffff;
    border-radius: 16px;
    padding: 35px;
    width: 100%;
    max-width: 550px;
    box-shadow: 0px 10px 25px rgba(0,0,0,0.12);
}

h2 {
    text-align: center;
    color: #0b2447;
    font-weight: 700;
    margin-bottom: 20px;
}

.profile-img {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    border: 4px solid #0b2447;
    object-fit: cover;
    display: block;
    margin: 0 auto 20px;
}

form label {
    font-weight: 600;
    margin: 10px 0 6px;
    display: block;
}

form input,
form select {
    width: 100%;
    padding: 12px;
    border: 1px solid #c9d3dd;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 15px;
    background: #f9fcff;
}

form input:focus,
form select:focus {
    border-color: #0b2447;
    outline: none;
}

form button {
    width: 100%;
    padding: 14px;
    background: #0b2447;
    color: white;
    font-size: 16px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    margin-top: 5px;
    transition: 0.2s;
}

form button:hover {
    background: #0f3165;
}

.success {
    padding: 10px;
    background: #d3f8d3;
    color: #2e7d32;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 15px;
}

.error {
    padding: 10px;
    background: #ffd4d4;
    color: #c62828;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 15px;
}

.student-fields {
    display: none;
}
</style>
</head>
<body>

<a href="profile.php" class="back-btn">⬅ Back</a>

<div class="card">
    <h2>Edit Profile</h2>

    <img src="<?= $user['profile_pic'] ? '../uploads/profile_pics/' . $user['profile_pic'] : '../uploads/profile_pics/default_avatar.png' ?>" 
         class="profile-img">

    <?php if($success) echo "<p class='success'>$success</p>"; ?>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Full Name</label>
        <input type="text" name="fullname" value="<?= htmlspecialchars($user['full_name']); ?>" required>

        <label>Email Address</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

        <label>Profile Picture</label>
        <input type="file" name="profile_pic" accept="image/*">

        <label>Role</label>
        <select name="role" id="role" required>
            <option value="student" <?= $user['role']=='student'?'selected':'' ?>>Student</option>
            <option value="faculty_staff" <?= $user['role']=='faculty_staff'?'selected':'' ?>>Faculty / Staff</option>
            <option value="visitor" <?= $user['role']=='visitor'?'selected':'' ?>>Visitor</option>
        </select>

        <div id="studentFields" class="student-fields">

            <label>Course</label>
            <select name="course">
                <option value="">Select Course</option>
                <option value="BSIT" <?= $user['course']=='BSIT'?'selected':'' ?>>BSIT</option>
                <option value="BSICT" <?= $user['course']=='BSICT'?'selected':'' ?>>BSICT</option>
                <option value="CCJE" <?= $user['course']=='CCJE'?'selected':'' ?>>BS Criminology</option>
                <option value="BSHM" <?= $user['course']=='BSHM'?'selected':'' ?>>BSHM</option>
                <option value="BTVTED" <?= $user['course']=='BTVTED'?'selected':'' ?>>BTVTED</option>
                <option value="BEED" <?= $user['course']=='BEED'?'selected':'' ?>>BEED</option>
                <option value="BSED" <?= $user['course']=='BSED'?'selected':'' ?>>BSED</option>
                <option value="SOCSTUD" <?= $user['course']=='SOCSTUD'?'selected':'' ?>>SOCSTUD</option>
            </select>

            <label>Year Level</label>
            <select name="year_level">
                <option value="">Select Year Level</option>
                <option value="1st Year" <?= $user['year_level']=='1st Year'?'selected':'' ?>>1st Year</option>
                <option value="2nd Year" <?= $user['year_level']=='2nd Year'?'selected':'' ?>>2nd Year</option>
                <option value="3rd Year" <?= $user['year_level']=='3rd Year'?'selected':'' ?>>3rd Year</option>
                <option value="4th Year" <?= $user['year_level']=='4th Year'?'selected':'' ?>>4th Year</option>
            </select>

            <label>Section</label>
            <select name="section">
                <option value="">Select Section</option>
                <?php foreach(['A','B','C','D'] as $sec): ?>
                    <option value="<?= $sec ?>" <?= $user['section']==$sec?'selected':'' ?>><?= $sec ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">Update Profile</button>
    </form>
</div>

<script>
const roleSelect = document.getElementById('role');
const studentFields = document.getElementById('studentFields');
const profileImg = document.querySelector('.profile-img');
const profileInput = document.querySelector('input[name="profile_pic"]');

function toggleFields() {
    studentFields.style.display = (roleSelect.value === 'student') ? 'block' : 'none';
}
toggleFields();
roleSelect.addEventListener('change', toggleFields);

// live preview
profileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        profileImg.src = URL.createObjectURL(file);
    }
});
</script>

</body>
</html>

