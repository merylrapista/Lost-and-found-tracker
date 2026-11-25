<?php
session_start();
require_once "conn.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_id = $_SESSION["user_id"];
$message = "";

// === Count unseen reports for badge ===
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unseen_reports FROM reports WHERE user_id = :uid AND is_seen = 0");
    $stmt->execute(['uid' => $user_id]);
    $unseen_reports = $stmt->fetch(PDO::FETCH_ASSOC)['unseen_reports'] ?? 0;
} catch (PDOException $e) {
    $unseen_reports = 0;
}

// === Count unseen claims for badge ===
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unseen_claims FROM claims WHERE claimant_user_id = :uid AND is_seen = 0");
    $stmt->execute(['uid' => $user_id]);
    $unseen_claims = $stmt->fetch(PDO::FETCH_ASSOC)['unseen_claims'] ?? 0;
} catch (PDOException $e) {
    $unseen_claims = 0;
}

// === Count unseen notifications for badge ===
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unseen_notifications FROM notifications WHERE user_id = :uid AND is_read = 0");
    $stmt->execute(['uid' => $user_id]);
    $unseen_notifications = $stmt->fetch(PDO::FETCH_ASSOC)['unseen_notifications'] ?? 0;
} catch (PDOException $e) {
    $unseen_notifications = 0;
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM reports WHERE report_id = :rid AND user_id = :uid");
        $stmt->execute(['rid' => $delete_id, 'uid' => $user_id]);
        $message = "<p class='success'>✅ Report deleted successfully!</p>";
    } catch (PDOException $e) {
        $message = "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
}

// Fetch user reports
try {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE user_id = :uid ORDER BY date_reported DESC");
    $stmt->execute(['uid' => $user_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// === Mark all reports as seen when user opens page ===
try {
    $pdo->prepare("UPDATE reports SET is_seen = 1 WHERE user_id = :uid")->execute(['uid' => $user_id]);
} catch (PDOException $e) {
    // ignore silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📂 My Reports | Lost & Found Tracker</title>
<style>
body { font-family: 'Poppins', sans-serif; margin:0; background:#f7f9fc; display:flex; }

/* Sidebar */
.sidebar { width:230px; background:#0b2447; color:white; display:flex; flex-direction:column; align-items:center; padding:30px 0; position:fixed; height:100vh; }
.sidebar h2 { margin-bottom:40px; font-size:22px; color:#00aaff; }
.sidebar a { text-decoration:none; color:white; width:80%; background:rgba(255,255,255,0.1); margin:6px 0; padding:12px; border-radius:10px; text-align:center; transition:background 0.3s; font-weight:500; position:relative; }
.sidebar a:hover { background:rgba(255,255,255,0.3); }
.sidebar a.active { background:rgba(255,255,255,0.35); font-weight:bold; }

.badge { position:absolute; top:6px; right:18px; background:red; color:white; font-size:12px; font-weight:bold; padding:2px 6px; border-radius:50%; }

/* Main Content */
.main-content { flex:1; margin-left:230px; padding:20px; overflow-x:auto; }
header { background:#145aa3; color:white; padding:15px 20px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; justify-content:space-between; align-items:center; }
header h1 { margin:0; font-size:24px; }
header p { font-size:14px; }

/* Table */
table { width:100%; border-collapse:collapse; margin-top:20px; background:white; border-radius:10px; overflow:hidden; }
th, td { padding:12px; text-align:left; border-bottom:1px solid #eee; font-size:14px; }
th { background:#0b2447; color:white; }
tr:hover { background:#f0f6ff; }

/* Action buttons */
.btn { padding:6px 10px; border:none; border-radius:6px; cursor:pointer; font-size:13px; color:white; text-decoration:none; }
.edit-btn { background:#00aaff;gap:5px; }
.edit-btn:hover { background:#0088cc; }
.delete-btn { background:#ff4c4c; }
.delete-btn:hover { background:#cc0000; }

/* Messages */
.success, .error { margin-top:10px; padding:10px; border-radius:8px; text-align:center; }
.success { background:#eafbea; color:#2b7a0b; }
.error { background:#fdeaea; color:#b30000; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>🔍 Lost & Found</h2>
    <a href="dashboard.php" class="<?= $current_page=='dashboard.php'?'active':'' ?>">🏠 Dashboard</a>
    <a href="browse_items.php" class="<?= $current_page=='browse_items.php'?'active':'' ?>">🧳 Browse Items</a>
    <a href="report_item.php" class="<?= $current_page=='report_item.php'?'active':'' ?>">📝 Report Item</a>
    <a href="my_reports.php" class="<?= $current_page=='my_reports.php'?'active':'' ?>">
        📂 My Reports
        <?php if($unseen_reports > 0): ?><span class="badge"><?= $unseen_reports ?></span><?php endif; ?>
    </a>
    <a href="my_claims.php" class="<?= $current_page=='my_claims.php'?'active':'' ?>">
        🧾 My Claims
        <?php if($unseen_claims > 0): ?><span class="badge"><?= $unseen_claims ?></span><?php endif; ?>
    </a>
    <a href="notifications.php" class="<?= $current_page=='notifications.php'?'active':'' ?>">
        🔔 Notifications
        <?php if($unseen_notifications > 0): ?><span class="badge"><?= $unseen_notifications ?></span><?php endif; ?>
    </a>
    <a href="../html/index.html">🚪 Logout</a>
</div>

<div class="main-content">
    <header>
        <h1> My Reports</h1>
        <p>Manage your reported items</p>
    </header>

    <?= $message; ?>

    <table>
        <tr>
            <th>Type</th>
            <th>Item Name</th>
            <th>Description</th>
            <th>Category</th>
            <th>Location</th>
            <th>Date Reported</th>
            <th>Status</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        <?php if(!empty($reports)): ?>
            <?php foreach($reports as $report): ?>
            <tr>
                <td><?= htmlspecialchars($report['type']); ?></td>
                <td><?= htmlspecialchars($report['item_name']); ?></td>
                <td><?= htmlspecialchars($report['description']); ?></td>
                <td><?= htmlspecialchars($report['category']); ?></td>
                <td><?= htmlspecialchars($report['location']); ?></td>
                <td><?= htmlspecialchars($report['date_reported']); ?></td>
                <td><?= htmlspecialchars($report['status']); ?></td>
                <td>
                    <?php if($report['image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($report['image']); ?>" alt="Item" style="width:60px; height:60px; object-fit:cover; border-radius:6px;">
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_report.php?id=<?= $report['report_id']; ?>" class="btn edit-btn">Edit</a>
                    <a href="my_reports.php?delete_id=<?= $report['report_id']; ?>" onclick="return confirm('Are you sure?');" class="btn delete-btn">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9" style="text-align:center;">No reports found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
