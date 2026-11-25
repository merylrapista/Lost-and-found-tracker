<?php
session_start();
require_once "conn.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_id = $_SESSION["user_id"];

// === Count unseen reports for sidebar badge ===
try {
    $stmtReports = $pdo->prepare("SELECT COUNT(*) AS unseen_reports FROM reports WHERE user_id = :uid AND is_seen = 0");
    $stmtReports->execute(['uid' => $user_id]);
    $unseen_reports = $stmtReports->fetch(PDO::FETCH_ASSOC)['unseen_reports'] ?? 0;
} catch (PDOException $e) {
    $unseen_reports = 0;
}

// === Count unseen claims for badge ===
try {
    $stmtClaims = $pdo->prepare("SELECT COUNT(*) AS unseen_claims FROM claims WHERE claimant_user_id = :uid AND is_seen = 0");
    $stmtClaims->execute(['uid' => $user_id]);
    $unseen_claims = $stmtClaims->fetch(PDO::FETCH_ASSOC)['unseen_claims'] ?? 0;
} catch (PDOException $e) {
    $unseen_claims = 0;
}

// === Count unseen notifications for sidebar badge ===
try {
    $stmtNotif = $pdo->prepare("SELECT COUNT(*) AS unseen_notifications FROM notifications WHERE user_id = :uid AND is_read = 0");
    $stmtNotif->execute(['uid' => $user_id]);
    $unseen_notifications = $stmtNotif->fetch(PDO::FETCH_ASSOC)['unseen_notifications'] ?? 0;
} catch (PDOException $e) {
    $unseen_notifications = 0;
}


// === Fetch claims for logged-in user ===
try {
    $stmt = $pdo->prepare("
        SELECT c.*, r.item_name, r.type, r.category, r.description, r.location, r.image, r.date_reported, u.full_name AS reporter_name
        FROM claims c
        JOIN reports r ON c.item_id = r.report_id
        JOIN users u ON r.user_id = u.id
        WHERE c.claimant_user_id = :uid
        ORDER BY c.created_at DESC
    ");
    $stmt->execute(['uid' => $user_id]);
    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching claims: " . $e->getMessage());
}

// === Mark all claims as seen when page opens ===
try {
    $pdo->prepare("UPDATE claims SET is_seen = 1 WHERE claimant_user_id = :uid")->execute(['uid' => $user_id]);
} catch (PDOException $e) {
    // ignore silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🧾 My Claims | Lost & Found Tracker</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background-color: #f7f9fc;
    display: flex;
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
    height: 100vh;
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
.sidebar a:hover { background: rgba(255,255,255,0.3); }
.sidebar a.active { background: rgba(255,255,255,0.35); font-weight: bold; }

/* Badge */
.badge {
    position: absolute;
    top: 6px;
    right: 18px;
    background-color: red;
    color: white;
    font-size: 12px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 50%;
}

/* Main Content */
.main-content { flex: 1; margin-left: 230px; padding: 20px; overflow-x: auto; }

/* Header */
header {
    background: #145aa3;
    color: #fff;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header h1 { margin: 0; font-size: 24px; }
header p { font-size: 14px; }

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 10px;
    overflow: hidden;
}
th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}
th { background: #0b2447; color: white; }
tr:hover { background: #f0f6ff; }

/* Status badge */
.status {
    padding: 5px 10px;
    border-radius: 6px;
    color: white;
    font-weight: bold;
    font-size: 13px;
}
.status.Pending { background: #ffb84d; }
.status.Approved { background: #28a745; }
.status.Rejected { background: #dc3545; }

/* Messages */
.success, .error { margin-top: 10px; padding: 10px; border-radius: 8px; text-align: center; }
.success { background: #eafbea; color: #2b7a0b; }
.error { background: #fdeaea; color: #b30000; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>🔍 Lost & Found</h2>
    <a href="dashboard.php" class="<?= $current_page=='dashboard.php' ? 'active':'' ?>">🏠 Dashboard</a>
    <a href="browse_items.php" class="<?= $current_page=='browse_items.php' ? 'active':'' ?>">🧳 Browse Items</a>
    <a href="report_item.php" class="<?= $current_page=='report_item.php' ? 'active':'' ?>">📝 Report Item</a>
    <a href="my_reports.php" class="<?= $current_page=='my_reports.php' ? 'active':'' ?>">
        📂 My Reports
        <?php if($unseen_reports > 0): ?>
            <span class="badge"><?= $unseen_reports ?></span>
        <?php endif; ?>
    </a>
    <a href="my_claims.php" class="<?= $current_page=='my_claims.php' ? 'active':'' ?>">
        🧾 My Claims
        <?php if($unseen_claims > 0): ?>
            <span class="badge"><?= $unseen_claims ?></span>
        <?php endif; ?>
    </a>
    <a href="notifications.php" class="<?= $current_page=='notifications.php' ? 'active':'' ?>">
        🔔 Notifications
        <?php if($unseen_notifications > 0): ?>
            <span class="badge"><?= $unseen_notifications ?></span>
        <?php endif; ?>
    </a>

    <a href="../html/index.html">🚪 Logout</a>
</div>

<div class="main-content">
    <header>
        <h1> My Claims</h1>
        <p>View the items you have claimed</p>
    </header>

    <table>
        <tr>
            <th>Item Name</th>
            <th>Type</th>
            <th>Category</th>
            <th>Description</th>
            <th>Location</th>
            <th>Reporter</th>
            <th>Status</th>
            <th>Date Claimed</th>
            <th>Image</th>
        </tr>
        <?php if(!empty($claims)): ?>
            <?php foreach($claims as $claim): ?>
            <tr>
                <td><?= htmlspecialchars($claim['item_name']); ?></td>
                <td><?= htmlspecialchars($claim['type']); ?></td>
                <td><?= htmlspecialchars($claim['category']); ?></td>
                <td><?= htmlspecialchars($claim['description']); ?></td>
                <td><?= htmlspecialchars($claim['location']); ?></td>
                <td><?= htmlspecialchars($claim['reporter_name']); ?></td>
                <td><span class="status <?= htmlspecialchars($claim['status']); ?>"><?= htmlspecialchars($claim['status']); ?></span></td>
                <td><?= htmlspecialchars($claim['created_at']); ?></td>
                <td>
                    <?php if($claim['image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($claim['image']); ?>" alt="Item" style="width:60px; height:60px; object-fit:cover; border-radius:6px;">
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9" style="text-align:center;">No claims found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
