<?php
session_start();
require_once "conn.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("User not found.");

$initials = strtoupper(substr($user['full_name'], 0, 1));

// Determine profile image
$profileImage = !empty($user['profile_pic']) && file_exists('../uploads/profile_pics/' . $user['profile_pic'])
    ? '../uploads/profile_pics/' . $user['profile_pic']
    : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile | Lost & Found Tracker</title>

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background-color: #f7f9fc;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Back Button */
.back-btn {
    position: fixed;
    top: 20px;
    left: 20px;
    background: #145aa3;
    padding: 10px 18px;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: 0.3s;
}
.back-btn:hover {
    background: #0f4a86;
}

/* Profile Card */
.profile-container {
    padding: 20px;
    width: 100%;
    display: flex;
    justify-content: center;
}
.profile-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    padding: 30px;
    max-width: 600px;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
}
.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #00aaff;
    color: #fff;
    font-size: 36px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
}
.profile-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}
.profile-info h2 { margin: 0; color: #145aa3; }
.profile-info p { margin: 4px 0; color: #555; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}
.info-card {
    background: #f0f6ff;
    padding: 15px 20px;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}
.label { font-weight: 600; color: #333; }
.value { color: #555; font-size: 14px; }

.edit-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 30px;
    background: linear-gradient(135deg, #455476ff, #54636bff);
    color: #fffaf4;
    border-radius: 30px;
    font-weight: 600;
    text-decoration: none;
    font-size: 16px;
    text-align: center;
    transition: 0.3s;
}
.edit-btn:hover {
    transform: translateY(-3px);
}
</style>
</head>
<body>

<!-- 🔙 Back button -->
<a href="dashboard.php" class="back-btn">← Back</a>

<!-- Profile Card -->
<div class="profile-container">
    <div class="profile-card">

        <div class="profile-header">
            <div class="profile-avatar">
                <?php if ($profileImage): ?>
                    <img src="<?= $profileImage ?>" alt="Profile Picture">
                <?php else: ?>
                    <?= $initials ?>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['full_name']) ?></h2>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <div class="info-grid">
            <?php if(!empty($user['user_role'])): ?>
            <div class="info-card">
                <span class="label">User Type</span>
                <span class="value"><?= htmlspecialchars($user['user_role']) ?></span>
            </div>
            <?php endif; ?>
            <?php if(!empty($user['course'])): ?>
            <div class="info-card">
                <span class="label">Course</span>
                <span class="value"><?= htmlspecialchars($user['course']) ?></span>
            </div>
            <?php endif; ?>
            <?php if(!empty($user['year_level'])): ?>
            <div class="info-card">
                <span class="label">Year Level</span>
                <span class="value"><?= htmlspecialchars($user['year_level']) ?></span>
            </div>
            <?php endif; ?>
            <?php if(!empty($user['section'])): ?>
            <div class="info-card">
                <span class="label">Section</span>
                <span class="value"><?= htmlspecialchars($user['section']) ?></span>
            </div>
            <?php endif; ?>

            <div class="info-card">
                <span class="label">Joined</span>
                <span class="value"><?= date("F d, Y", strtotime($user['created_at'])) ?></span>
            </div>
        </div>

        <a href="edit_profile.php" class="edit-btn">✏️ Edit Profile</a>

    </div>
</div>

</body>
</html>
