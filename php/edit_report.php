<?php
session_start();
require_once "conn.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$report_id = $_GET['id'] ?? null;

if (!$report_id) {
    header("Location: my_reports.php");
    exit;
}

$message = "";

// Fetch report data
$stmt = $pdo->prepare("SELECT * FROM reports WHERE report_id = :rid AND user_id = :uid");
$stmt->execute(['rid' => $report_id, 'uid' => $user_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    header("Location: my_reports.php");
    exit;
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $type = $_POST['type'];

    try {
        $stmt = $pdo->prepare("UPDATE reports SET item_name=:item_name, description=:description, category=:category, location=:location, type=:type WHERE report_id=:rid AND user_id=:uid");
        $stmt->execute([
            'item_name' => $item_name,
            'description' => $description,
            'category' => $category,
            'location' => $location,
            'type' => $type,
            'rid' => $report_id,
            'uid' => $user_id
        ]);
        $message = "<p class='success'>✅ Report updated successfully!</p>";
        // Refresh report data
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE report_id = :rid AND user_id = :uid");
        $stmt->execute(['rid' => $report_id, 'uid' => $user_id]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Report | Lost & Found Tracker</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.container { max-width: 500px; margin:50px auto; background:white; padding:30px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:20px; color:#0b2447;}
input, textarea, select { width:100%; padding:10px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc;}
button { width:100%; padding:10px; border:none; border-radius:6px; background:#00aaff; color:white; font-weight:bold; cursor:pointer;}
button:hover { background:#0088cc;}
.success { background:#eafbea; color:#2b7a0b; padding:10px; border-radius:6px; margin-bottom:10px; text-align:center;}
.error { background:#fdeaea; color:#b30000; padding:10px; border-radius:6px; margin-bottom:10px; text-align:center;}
</style>
</head>
<body>
<div class="container">
    <h2>Edit Report</h2>
    <?= $message; ?>
    <form method="POST">
        <input type="text" name="item_name" value="<?= htmlspecialchars($report['item_name']); ?>" placeholder="Item Name" required>
        <textarea name="description" placeholder="Description" required><?= htmlspecialchars($report['description']); ?></textarea>
        <select name="category" required>
            <option value="Electronics" <?= $report['category']=='Electronics'?'selected':''; ?>>Electronics</option>
            <option value="Clothing" <?= $report['category']=='Clothing'?'selected':''; ?>>Clothing</option>
            <option value="Accessories" <?= $report['category']=='Accessories'?'selected':''; ?>>Accessories</option>
            <option value="Documents" <?= $report['category']=='Documents'?'selected':''; ?>>Documents</option>
            <option value="Others" <?= $report['category']=='Others'?'selected':''; ?>>Others</option>
        </select>
        <input type="text" name="location" value="<?= htmlspecialchars($report['location']); ?>" placeholder="Location" required>
        <select name="type" required>
            <option value="Lost" <?= $report['type']=='Lost'?'selected':''; ?>>Lost</option>
            <option value="Found" <?= $report['type']=='Found'?'selected':''; ?>>Found</option>
        </select>
        <button type="submit">Update Report</button>
    </form>
    <p style="text-align:center; margin-top:15px;"><a href="my_reports.php" style="color:#00aaff;">← Back to My Reports</a></p>
</div>
</body>
</html>
