<?php
session_start();
require_once "conn.php"; // PDO connection

// Check admin login
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login.php");
//     exit;
// }

$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION['full_name'];
$initials = strtoupper(substr($admin_name, 0, 1));

// --- Handle Mark All as Read ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update = $pdo->prepare("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
    $update->execute();
    header("Location: admin_notifications.php");
    exit;
}

// --- Fetch notifications ---
$stmt = $pdo->query("SELECT * FROM admin_notifications ORDER BY created_at DESC");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🔔 Admin Notifications | Lost & Found</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin:0;
    background:#f7f9fc;
    display:flex;
    min-height:100vh;
}

/* Sidebar */
.sidebar {
    width:230px;
    background:#0b2447;
    color:white;
    display:flex;
    flex-direction:column;
    align-items:center;
    padding:30px 0;
    position:fixed;
    height:100%;
}
.sidebar h2 { margin-bottom:40px; font-size:22px; color:#00aaff; }
.sidebar a { text-decoration:none; color:white; width:80%; background:rgba(255,255,255,0.1); margin:6px 0; padding:12px; border-radius:10px; text-align:center; transition:background 0.3s; font-weight:500; }
.sidebar a:hover { background:rgba(255,255,255,0.3); }
.sidebar a.active { background:rgba(255,255,255,0.35); font-weight:bold; }

/* Main Content */
.main-content {
    flex:1;
    margin-left:230px;
    padding:20px;
    overflow:auto;
}

/* Header */
header {
    background:#145aa3;
    color:white;
    padding:22px 20px;
    border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
    margin-bottom:10px;
}

header h1 { margin:0; font-size:24px; }
header p { margin:5px 0 0 0; font-size:14px; text-align:right; }

/* Mark All as Read button */
.mark-read-container {
    margin-bottom:20px;
    text-align:right;
}
.mark-read-container form button {
    padding:8px 14px;
    font-size:14px;
    border:none;
    border-radius:6px;
    background:#00aaff;
    color:white;
    cursor:pointer;
    transition:0.3s;
}
.mark-read-container form button:hover { background:#0088cc; }

/* Notification cards */
.notification-container {
    display:flex;
    flex-direction:column;
    gap:15px;
}

.notification {
    background:white;
    border-radius:10px;
    box-shadow:0 4px 8px rgba(0,0,0,0.1);
    padding:15px 20px;
    display:flex;
    align-items:center;
    transition:transform 0.2s;
}
.notification:hover { transform:translateY(-2px); }

.icon {
    font-size:22px;
    width:40px;
    height:40px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    color:white;
    margin-right:15px;
}
.icon.lost { background:#ff6b6b; }
.icon.found { background:#1dd1a1; }
.icon.claim { background:#48dbfb; }
.icon.general { background:#5f27cd; }

.message { flex:1; font-size:14px; color:#333; font-weight:500; }
.date { font-size:12px; color:#777; margin-top:4px; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>⚙️ Admin Panel</h2>
    <a href="admin_dashboard.php" class="<?= $current_page=='admin_dashboard.php'?'active':'' ?>">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="manage_reports.php">📄 Manage Reports</a>
    <a href="manage_claims.php">📦 Manage Claims</a>
    <a href="admin_notifications.php" class="active">🔔 Notifications</a>
    <a href="../html/index.html">🚪 Logout</a>
</div>

<div class="main-content">
    <header>
        <h1> Admin Notifications</h1>
        <p>Keep track of user reports and claims.</p>
    </header>

    <div class="mark-read-container">
        <form method="POST">
            <button type="submit">Mark All as Read</button>
        </form>
    </div>

    <div class="notification-container">
        <?php if(empty($notifications)): ?>
            <p style="text-align:center; color:#888;">No new notifications.</p>
        <?php else: ?>
            <?php foreach($notifications as $notif):
                $msg = $notif['message'];
                $iconClass = 'general';
                $iconEmoji = '📢';

                if(stripos($msg,'LOST')!==false){ $iconClass='lost'; $iconEmoji='❌'; }
                elseif(stripos($msg,'FOUND')!==false){ $iconClass='found'; $iconEmoji='📦'; }
                elseif(stripos($msg,'claim')!==false){ $iconClass='claim'; $iconEmoji='🧾'; }
            ?>
            <div class="notification">
                <div class="icon <?= $iconClass ?>"><?= $iconEmoji ?></div>
                <div>
                    <div class="message"><?= htmlspecialchars($msg) ?></div>
                    <div class="date"><?= date("F j, Y g:i A", strtotime($notif['created_at'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
