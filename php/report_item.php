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

// Handle submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_name = trim($_POST["item_name"]);
    $description = trim($_POST["description"]);
    $category = trim($_POST["category"]);
    $type = trim($_POST["type"]);
    $location = trim($_POST["location"]);
    $date_reported = $_POST["date_reported"];
    $status = "Pending";
    $image = null;

    if (!empty($_FILES["image"]["name"])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $image = $fileName;
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO reports 
            (user_id, item_name, description, category, type, location, date_reported, status, image)
            VALUES 
            (:user_id, :item_name, :description, :category, :type, :location, :date_reported, :status, :image)
        ");
        $stmt->execute([
            'user_id' => $user_id,
            'item_name' => $item_name,
            'description' => $description,
            'category' => $category,
            'type' => $type,
            'location' => $location,
            'date_reported' => $date_reported,
            'status' => $status,
            'image' => $image
        ]);

        $message = "✅ Report submitted successfully!";
    } catch (PDOException $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📝 Report Item | Lost & Found Tracker</title>
<style>
body {font-family:'Poppins',sans-serif;margin:0;background:#f7f9fc;display:flex;min-height:100vh;}
.sidebar {width:230px;background:#0b2447;color:white;display:flex;flex-direction:column;align-items:center;padding:30px 0;position:fixed;left:0;top:0;height:100%;}
.sidebar h2 {margin-bottom:40px;font-size:22px;color:#00aaff;}
.sidebar a {text-decoration:none;color:white;width:80%;background:rgba(255,255,255,0.1);margin:6px 0;padding:12px;border-radius:10px;text-align:center;transition:0.3s;font-weight:500;position:relative;}
.sidebar a:hover {background:rgba(255,255,255,0.3);}
.sidebar a.active {background:rgba(255,255,255,0.35);font-weight:bold;}
.badge {position:absolute;top:6px;right:18px;background:red;color:white;font-size:12px;font-weight:bold;padding:2px 6px;border-radius:50%;}
.main-content {flex:1;margin-left:230px;padding:20px;}
header {background:#145aa3;color:#fff;padding:15px 20px;display:flex;justify-content:space-between;align-items:center;border-radius:8px;}
header h1 {margin:0;font-size:24px;}
header p {font-size:14px;}
.report-section {display:flex;justify-content:center;padding:20px 0;}
.report-form {background:white;border-radius:12px;box-shadow:0 4px 10px rgba(0,0,0,0.1);padding:25px 30px;width:100%;max-width:550px;}
.report-form h2 {color:#0b2447;text-align:center;margin-bottom:15px;font-size:20px;}
label {display:block;font-weight:600;margin-top:10px;color:#333;font-size:13px;}
input, select, textarea {width:100%;padding:8px;margin-top:5px;border:1px solid #ccc;border-radius:6px;font-size:13px;}
textarea {resize:none;}
button {background:#00aaff;color:white;border:none;border-radius:6px;padding:10px;width:100%;font-size:14px;cursor:pointer;margin-top:15px;transition:0.3s;}
button:hover {background:#0088cc;}
.popup {display:none;position:fixed;top:50%;left:60%;transform:translate(-50%, -50%);background-color:#00aaff;color:white;padding:20px 30px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.2);font-size:16px;text-align:center;z-index:1000;animation:fadeInOut 3s forwards;}
@keyframes fadeInOut {0% {opacity:0; transform:translate(-50%, -60%);}10% {opacity:1; transform:translate(-50%, -50%);}90% {opacity:1; transform:translate(-50%, -50%);}100% {opacity:0; transform:translate(-50%, -60%);}}
</style>
</head>
<body>

<div class="sidebar">
    <h2>🔍 Lost & Found</h2>
    <a href="dashboard.php" class="<?= $current_page=='dashboard.php'?'active':'' ?>">🏠 Dashboard</a>
    <a href="browse_items.php" class="<?= $current_page=='browse_items.php'?'active':'' ?>">🧳 Browse Items</a>
    <a href="report_item.php" class="active">📝 Report Item</a>
    <a href="my_reports.php" class="<?= $current_page=='my_reports.php'?'active':'' ?>">
        📂 My Reports
        <?php if($unseen_reports>0): ?><span class="badge"><?= $unseen_reports ?></span><?php endif; ?>
    </a>
    <a href="my_claims.php" class="<?= $current_page=='my_claims.php'?'active':'' ?>">
        🧾 My Claims
        <?php if($unseen_claims>0): ?><span class="badge"><?= $unseen_claims ?></span><?php endif; ?>
    </a>
    <a href="notifications.php" class="<?= $current_page=='notifications.php'?'active':'' ?>">
        🔔 Notifications
        <?php if($unseen_notifications>0): ?><span class="badge"><?= $unseen_notifications ?></span><?php endif; ?>
    </a>
    <a href="../html/index.html">🚪 Logout</a>
</div>

<div class="main-content">
    <header>
        <h1> Report Lost or Found Item</h1>
        <p>“Help items find their way home.”</p>
    </header>

    <div class="report-section">
        <div class="report-form">
            <h2>📝 Submit a New Report</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>Type</label>
                <select name="type" required>
                    <option value="">Select Type</option>
                    <option value="Lost">Lost</option>
                    <option value="Found">Found</option>
                </select>

                <label>Item Name</label>
                <input type="text" name="item_name" required placeholder="Enter the name of the item">

                <label>Description</label>
                <textarea name="description" rows="4" required placeholder="Describe the item..."></textarea>

                <label>Category</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Clothing">Clothing</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Documents">Documents</option>
                    <option value="Others">Others</option>
                </select>

                <label>Location</label>
                <input type="text" name="location" required placeholder="Where did you lose or find it?">

                <label>Date</label>
                <input type="date" name="date_reported" required>

                <label>Upload Image</label>
                <input type="file" name="image" accept="image/*">

                <button type="submit">Submit Report</button>
            </form>
        </div>
    </div>
</div>

<div id="popup" class="popup"></div>

<script>
<?php if(!empty($message)): ?>
    const popup = document.getElementById('popup');
    popup.innerText = "<?= addslashes($message); ?>";
    popup.style.display = "block";
    setTimeout(()=>{ popup.style.display='none'; }, 3000);
<?php endif; ?>
</script>

</body>
</html>
