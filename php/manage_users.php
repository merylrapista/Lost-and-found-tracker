<?php
session_start();
require_once "conn.php";

// // Only allow admin
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login.php");
//     exit;
// }

$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION['full_name'];

// Handle Activate/Deactivate for normal users
if (isset($_GET['toggle_user'])) {
    $user_id = intval($_GET['toggle_user']);
    // Get current status
    $stmt = $pdo->prepare("SELECT is_active, role FROM users WHERE id = :uid");
    $stmt->execute(['uid' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && $user['role'] !== 'admin') { // Only normal users can be toggled
        $new_status = $user['is_active'] ? 0 : 1;
        $update = $pdo->prepare("UPDATE users SET is_active = :status WHERE id = :uid");
        $update->execute(['status' => $new_status, 'uid' => $user_id]);
    }
    header("Location: manage_users.php");
    exit;
}

// Search filter
$search = "";
$search_sql = "";
$params = [];
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_sql = " AND (full_name LIKE :search OR email LIKE :search)";
    $params['search'] = "%$search%";
}

// Fetch admins
$admin_stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC");
$admin_stmt->execute();
$admins = $admin_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch normal users
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE role != 'admin' $search_sql ORDER BY created_at DESC");
$user_stmt->execute($params);
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>👥 Manage Users | Admin Panel</title>
<style>
body {font-family:'Poppins',sans-serif;margin:0;background:#f7f9fc;display:flex;min-height:100vh;}
.sidebar {width:230px;background:#0b2447;color:white;display:flex;flex-direction:column;align-items:center;padding:30px 0;position:fixed;height:100%;}
.sidebar h2 {margin-bottom:40px;font-size:22px;color:#00aaff;}
.sidebar a {text-decoration:none;color:white;width:80%;background:rgba(255,255,255,0.1);margin:6px 0;padding:12px;border-radius:10px;text-align:center;transition:background 0.3s;font-weight:500;}
.sidebar a:hover {background:rgba(255,255,255,0.3);}
.sidebar a.active {background:rgba(255,255,255,0.35);font-weight:bold;}
.main-content {flex:1;margin-left:230px;padding:20px;overflow:auto;}
header {background:#145aa3;color:white;padding:22px 20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);margin-bottom:10px;}
header h1 {margin:0;font-size:24px;}
header p {margin:5px 0 0 0;font-size:14px;text-align:right;}
.search-container {margin-bottom:15px;}
.search-container input {padding:6px 12px;border-radius:6px;border:1px solid #ccc;width:200px;font-size:14px;}
.search-container button {padding:6px 12px;border:none;background:#00aaff;color:white;border-radius:6px;cursor:pointer;transition:0.3s;font-size:14px;}
.search-container button:hover {background:#0088cc;}
table {width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.1);margin-bottom:30px;}
th, td {padding:12px 15px;text-align:left;border-bottom:1px solid #ddd;font-size:14px;}
th {background:#145aa3;color:white;}
tr:hover {background:#f1f1f1;}
.action-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    text-decoration: none; /* remove underline */
    display: inline-block;
}

.activate {
    background: #1dd1a1;
}

.activate:hover {
    background: #16a085;
}

.deactivate {
    background: #ff6b6b;
}

.deactivate:hover {
    background: #c0392b;
}

.admin-badge {font-weight:bold;color:#ffdd00;}
</style>
</head>
<body>

<div class="sidebar">
    <h2>⚙️ Admin Panel</h2>
    <a href="admin_dashboard.php" class="<?= $current_page=='admin_dashboard.php'?'active':'' ?>">🏠 Dashboard</a>
    <a href="manage_users.php" class="active">👥 Manage Users</a>
    <a href="manage_reports.php">📄 Manage Reports</a>
    <a href="manage_claims.php">📦 Manage Claims</a>
    <a href="admin_notifications.php">🔔 Notifications</a>
    <a href="../html/index.html">🚪 Logout</a>
</div>

<div class="main-content">
    <header>
        <h1> Manage Users</h1>
        <p>View, search, and activate/deactivate user accounts.</p>
    </header>

    <h2> Admin Accounts</h2>
    <table>
        <tr>
            <th>#</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
        </tr>
        <?php if(empty($admins)): ?>
            <tr><td colspan="5" style="text-align:center;color:#888;">No admin accounts found.</td></tr>
        <?php else: foreach($admins as $index => $admin): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($admin['full_name']) ?></td>
                <td><?= htmlspecialchars($admin['email']) ?></td>
                <td class="admin-badge"><?= ucfirst($admin['role']) ?></td>
                <td><?= $admin['is_active'] ? "Active" : "Inactive" ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </table>

    <h2> Normal Users</h2>
    <div class="search-container">
        <form method="GET">
            <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>
    <table>
        <tr>
            <th>#</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php if(empty($users)): ?>
            <tr><td colspan="6" style="text-align:center;color:#888;">No users found.</td></tr>
        <?php else: foreach($users as $index => $user): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($user['full_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= ucfirst($user['role']) ?></td>
                <td><?= $user['is_active'] ? "Active" : "Inactive" ?></td>
                <td>
                    <a href="manage_users.php?toggle_user=<?= $user['id'] ?>" 
                       class="action-btn <?= $user['is_active'] ? 'deactivate':'activate' ?>">
                       <?= $user['is_active'] ? 'Deactivate':'Activate' ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </table>
</div>
</body>
</html>
