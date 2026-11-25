<?php
session_start();
require_once "conn.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION["full_name"];
$initials = strtoupper(substr($admin_name, 0, 1));

try {
    // === Summary Counts ===
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_lost = $pdo->query("SELECT COUNT(*) FROM reports WHERE LOWER(type) = 'lost'")->fetchColumn();
    $total_found = $pdo->query("SELECT COUNT(*) FROM reports WHERE LOWER(type) = 'found'")->fetchColumn();
    $total_claims = $pdo->query("SELECT COUNT(*) FROM claims")->fetchColumn();

    // === Recent Reports ===
    $stmt = $pdo->query("SELECT item_name, type, location, image, date_reported FROM reports ORDER BY date_reported DESC LIMIT 4");
    $recent_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === Recent Activities (Only user actions) ===
    $activityStmt = $pdo->query("
        SELECT CONCAT('User ', u.full_name, ' reported a ', r.type, ' item: ''', r.item_name, '''') AS message,
               r.date_reported AS created_at
        FROM reports r
        JOIN users u ON r.user_id = u.id
        UNION
        SELECT CONCAT('User ', u.full_name, ' submitted a claim for ''', rep.item_name, '''') AS message,
               c.created_at AS created_at
        FROM claims c
        JOIN users u ON c.claimant_user_id = u.id
        JOIN reports rep ON c.item_id = rep.report_id
        ORDER BY created_at DESC
        LIMIT 4
    ");
    $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>⚙️ Admin Dashboard | Lost & Found Tracker</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background-color: #f7f9fc;
    display: flex;
    height: 100vh;
}
/* Sidebar */
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
.sidebar h2 { margin-bottom: 40px; font-size: 22px; color: #00aaff; }
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
}
.sidebar a:hover { background: rgba(255,255,255,0.3); }
.sidebar a.active { background: rgba(255,255,255,0.35); font-weight: bold; }
.sidebar .logout-btn { margin-top: auto; margin-bottom: 20px; background: white; color: #0b2447; font-weight: bold; }
.sidebar .logout-btn:hover { background: #cde8ff; }

/* Main Content */
.main-content { flex: 1; margin-left: 230px; display: flex; flex-direction: column; overflow-y: auto; }

/* Header */
header { background: #145aa3; color: #fff; padding: 20px 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; position: relative; }
header h1 { font-size: 24px; margin: 0; }
/* Centered Quote */
.header-center { position: absolute; left: 50%; transform: translateX(-50%); text-align: center; }
.header-center p { font-size: 15px; margin: 0; color: #f0f0f0; font-style: italic; }
/* Profile section */
.profile-section { display: flex; flex-direction: column; align-items: center; }
.profile-circle { width: 45px; height: 45px; border-radius: 50%; background: #00aaff; color: white; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: bold; margin-bottom: 5px; }
.profile-name { font-size: 13px; color: #fff; }

/* Dashboard Cards */
.dashboard { padding: 30px; }
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: white; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 20px; text-align: center; transition: transform 0.2s ease; }
.stat-card:hover { transform: translateY(-5px); }
.stat-icon { font-size: 35px; color: #00aaff; margin-bottom: 10px; }
.stat-card h3 { color: #333; margin: 10px 0 5px; }
.stat-card p { color: #777; }

/* Recent Reports */
.featured-section { background: white; border-radius: 15px; box-shadow: 0 3px 6px rgba(0,0,0,0.1); padding: 20px 25px; margin-bottom: 25px; }
.featured-section h2 { color: #0b2447; margin-bottom: 10px; font-size: 18px; }
.items-grid { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-start; }
.item-card { width: 150px; background: #f0f6ff; border: 1px solid #eee; border-radius: 10px; overflow: hidden; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s ease, box-shadow 0.2s ease; }
.item-card:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
.item-card img { width: 100%; height: 110px; object-fit: cover; }
.item-card h4 { color: #145aa3; margin: 6px 0 2px; font-size: 14px; }
.item-card p { color: #555; font-size: 12px; margin-bottom: 8px; }

/* Activity Section */
.activity-section { background: white; border-radius: 15px; box-shadow: 0 3px 6px rgba(0,0,0,0.1); padding: 20px 25px; }
.activity-section h2 { color: #0b2447; margin-bottom: 15px; }
.activity-list { list-style: none; padding: 0; }
.activity-list li { padding: 10px 0; border-bottom: 1px solid #eee; font-size: 15px; color: #555; }
.activity-list li:last-child { border-bottom: none; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>⚙️ Admin Panel</h2>
    <a href="admin_dashboard.php" class="<?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="manage_reports.php">📄 Manage Reports</a>
    <a href="manage_claims.php">📦 Manage Claims</a>
    <a href="admin_notifications.php">🔔 Notifications</a>
    <a href="../html/index.html">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <header>
        <h1>Welcome, <?= htmlspecialchars($admin_name); ?>!</h1>
        <div class="header-center">
            <p>“Admin control center — keep everything in order.”</p>
        </div>
        <div class="profile-section">
            <div class="profile-circle"><?= $initials; ?></div>
            <div class="profile-name">Admin</div>
        </div>
    </header>

    <div class="dashboard">
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <h3><?= $total_users; ?> Users</h3>
                <p>Registered on the platform</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔎</div>
                <h3><?= $total_lost; ?> Lost Items</h3>
                <p>Reported as missing</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <h3><?= $total_found; ?> Found Items</h3>
                <p>Waiting for owners</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📑</div>
                <h3><?= $total_claims; ?> Claims</h3>
                <p>Under review</p>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="featured-section">
            <h2>🧾 Recently Reported Items</h2>
            <div class="items-grid">
                <?php if (!empty($recent_reports)): ?>
                    <?php foreach ($recent_reports as $report): ?>
                        <div class="item-card">
                            <img src="../uploads/<?= htmlspecialchars($report['image']); ?>" alt="<?= htmlspecialchars($report['item_name']); ?>">
                            <h4><?= htmlspecialchars($report['item_name']); ?></h4>
                            <p><?= htmlspecialchars($report['type']); ?> • <?= htmlspecialchars($report['location']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No recent reports found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activities -->
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
                    <li>No recent activities.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

</body>
</html>
