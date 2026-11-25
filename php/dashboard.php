<?php
session_start();
require_once "conn.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_id = $_SESSION["user_id"];

// === Fetch user info (full name + profile pic) ===
$stmt = $pdo->prepare("SELECT full_name, profile_pic FROM users WHERE id = :uid");
$stmt->execute(['uid' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found.");

$full_name = $user['full_name'];
$initials = strtoupper(substr($full_name, 0, 1));

// Profile image logic
$profileImage = null;
if (!empty($user['profile_pic'])) {
    $filePath = __DIR__ . '/../uploads/profile_pics/' . $user['profile_pic'];
    if (file_exists($filePath)) {
        $profileImage = '../uploads/profile_pics/' . $user['profile_pic'];
    }
}

// === Count unseen reports for badge ===
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unseen_reports FROM reports WHERE user_id = :uid AND is_seen = 0");
    $stmt->execute(['uid' => $user_id]);
    $unseen_reports = $stmt->fetch(PDO::FETCH_ASSOC)['unseen_reports'] ?? 0;
} catch (PDOException $e) { $unseen_reports = 0; }

// === Count unseen claims for badge ===
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unseen_claims FROM claims WHERE claimant_user_id = :uid AND is_seen = 0");
    $stmt->execute(['uid' => $user_id]);
    $unseen_claims = $stmt->fetch(PDO::FETCH_ASSOC)['unseen_claims'] ?? 0;
} catch (PDOException $e) { $unseen_claims = 0; }

// === Count unseen notifications for badge ===
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unseen_notifications FROM notifications WHERE user_id = :uid AND is_read = 0");
    $stmt->execute(['uid' => $user_id]);
    $unseen_notifications = $stmt->fetch(PDO::FETCH_ASSOC)['unseen_notifications'] ?? 0;
} catch (PDOException $e) { $unseen_notifications = 0; }

// === Dashboard counts ===
try {
    $stmt = $pdo->prepare("
    SELECT COUNT(*) AS total 
    FROM reports 
    WHERE LOWER(type) = 'lost'
    AND status NOT IN ('claimed', 'resolved')
    ");

    $stmt->execute();
    $lost_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmt = $pdo->prepare("
    SELECT COUNT(*) AS total 
    FROM reports 
    WHERE LOWER(type) = 'found'
    AND status NOT IN ('claimed', 'resolved')
    ");

    $stmt->execute();
    $found_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM reports WHERE user_id = :uid");
    $stmt->execute(['uid' => $user_id]);
    $my_reports = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread FROM notifications WHERE user_id = :uid");
    $stmt->execute(['uid' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $notifications = $result['total'] ?? 0;
    $unread_notifications = $result['unread'] ?? 0;

    $activityStmt = $pdo->prepare("SELECT message, created_at FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 3");
    $activityStmt->execute(['uid' => $user_id]);
    $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

    $itemStmt = $pdo->query("
        SELECT item_name, type, location, image 
        FROM reports 
        WHERE status NOT IN ('claimed', 'resolved')
        ORDER BY date_reported DESC 
        LIMIT 3
    ");

    $featuredItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🔍 Dashboard | Lost & Found Tracker</title>
<!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"> -->
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background-color: #f7f9fc;
    display: flex;
    height: 100vh;
}

/* === Sidebar === */
.sidebar {
    width: 230px;
    background-color: #0b2447;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 30px 0;
    position: fixed;
    left: 0;
    top: 0;
    height: 100%;
}

.sidebar h2 {
    margin-bottom: 40px;
    font-size: 22px;
    color: #00aaff;
}

.sidebar a {
    text-decoration: none;
    color: white;
    width: 80%;
    background: rgba(255,255,255,0.1);
    margin: 6px 0;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    transition: background 0.3s;
    font-weight: 500;
    position: relative;
}

.sidebar a:hover {
    background: rgba(255,255,255,0.3);
}

.sidebar a.active {
    background: rgba(255,255,255,0.35);
    font-weight: bold;
}

/* Badge */
.badge {
    position: absolute;
    top: 6px;
    right: 16px;
    background-color: red;
    color: white;
    font-size: 12px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 50%;
    pointer-events: none;
}

/* Main Content */
.main-content {
    flex: 1;
    margin-left: 230px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

/* Header */
header {
    background: #145aa3;
    color: #fff;
    padding: 20px 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

header h1 { font-size: 24px; margin: 0; }
.header-center {
    position: absolute;
    left: 55%;
    transform: translateX(-50%);
    text-align: center;
}
.header-center p {
    font-size: 15px;
    margin: 0;
    color: #f0f0f0;
    font-style: italic;
}

/* Profile section */
.profile-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.2s ease, background 0.2s ease;
    padding: 5px;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
}
.profile-section:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}
.profile-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #00aaff;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 5px;
}
.profile-name { font-size: 13px; color: #fff; }

/* Dashboard Cards */
.dashboard { padding: 30px; }
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    padding: 20px;
    text-align: center;
    transition: transform 0.2s ease;
}
.stat-card:hover { transform: translateY(-5px); }
.stat-icon { font-size: 35px; color: #00aaff; margin-bottom: 10px; }
.stat-card h3 { color: #333; margin: 10px 0 5px; }
.stat-card p { color: #777; }

/* Featured Items */
.featured-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    padding: 20px 25px;
    margin-bottom: 25px;
}
.featured-section h2 { color: #0b2447; margin-bottom: 10px; font-size: 18px; }
.items-grid { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-start; }
.item-card {
    width: 150px;
    background: #f0f6ff;
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.item-card:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
.item-card img { width: 100%; height: 110px; object-fit: cover; }
.item-card h4 { color: #145aa3; margin: 6px 0 2px; font-size: 14px; }
.item-card p { color: #555; font-size: 12px; margin-bottom: 8px; }

/* Activity Section */
.activity-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    padding: 20px 25px;
}
.activity-section h2 { color: #0b2447; margin-bottom: 15px; }
.activity-list { list-style: none; padding: 0; }
.activity-list li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    font-size: 15px;
    color: #555;
}
.activity-list li:last-child { border-bottom: none; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>🔍 Lost & Found</h2>
    <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a>
    <a href="browse_items.php" class="<?= $current_page == 'browse_items.php' ? 'active' : '' ?>">🧳 Browse Items</a>
    <a href="report_item.php" class="<?= $current_page == 'report_item.php' ? 'active' : '' ?>">📝 Report Item</a>
    <a href="my_reports.php" class="<?= $current_page=='my_reports.php'?'active':'' ?>">📂 My Reports
        <?php if($unseen_reports>0): ?><span class="badge"><?= $unseen_reports ?></span><?php endif; ?>
    </a>
    <a href="my_claims.php" class="<?= $current_page=='my_claims.php'?'active':'' ?>">🧾 My Claims
        <?php if($unseen_claims>0): ?><span class="badge"><?= $unseen_claims ?></span><?php endif; ?>
    </a>
    <a href="notifications.php" class="<?= $current_page=='notifications.php'?'active':'' ?>">🔔 Notifications
        <?php if($unseen_notifications>0): ?><span class="badge"><?= $unseen_notifications ?></span><?php endif; ?>
    </a>
    <a href="../html/index.html">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <header>
        <h1>Welcome back, <?= htmlspecialchars($full_name); ?>!</h1>
        <div class="header-center">
            <p>“Every item has a story — help it find its way home.”</p>
        </div>
        <a href="profile.php" class="profile-section">
            <div class="profile-circle">
                <?php if ($profileImage): ?>
                    <img src="<?= htmlspecialchars($profileImage); ?>" alt="<?= htmlspecialchars($full_name); ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                <?php else: ?>
                    <?= $initials ?>
                <?php endif; ?>
            </div>
            <div class="profile-name"><?= htmlspecialchars($full_name) ?></div>
        </a>
    </header>

    <div class="dashboard">
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon">🔎</div>
                <h3><?= $lost_count; ?> Lost Items</h3>
                <p>Currently reported missing</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <h3><?= $found_count; ?> Found Items</h3>
                <p>Waiting for their owners</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <h3><?= $my_reports; ?> My Reports</h3>
                <p>Your submitted reports</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔔</div>
                <h3><?= $notifications; ?> Notifications</h3>
                <p>Recent updates</p>
            </div>
        </div>

        <!-- Featured Items -->
        <div class="featured-section">
            <h2>🧳 Recently Reported Items</h2>
            <div class="items-grid">
                <?php if (!empty($featuredItems)): ?>
                    <?php foreach ($featuredItems as $item): ?>
                        <div class="item-card">
                            <img src="../uploads/<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['item_name']); ?>">
                            <h4><?= htmlspecialchars($item['item_name']); ?></h4>
                            <p><?= htmlspecialchars($item['type']); ?> • <?= htmlspecialchars($item['location']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No recent items reported.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-section">
            <h2>Recent Activity</h2>
            <ul class="activity-list">
                <?php if (!empty($activities)): ?>
                    <?php foreach ($activities as $activity): ?>
                        <li><?= htmlspecialchars($activity['message']); ?> 
                            <small style="color:#999;">(<?= $activity['created_at']; ?>)</small>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No recent activities yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

</body>
</html>
