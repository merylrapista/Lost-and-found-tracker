<?php
session_start();
require_once "conn.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_id = $_SESSION["user_id"];

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


// Handle search/filter
$search = $_GET['search'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

try {
    // Only show items that are approved (exclude Pending and Rejected)
    // For Found items: hide if already claimed and approved
    // For Lost items: hide if status = 'Resolved'
    $query = "SELECT r.*, u.full_name 
    FROM reports r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN claims c 
    ON r.report_id = c.item_id AND c.status = 'Approved'
    WHERE r.user_id != :current_user 
    AND r.status NOT IN ('Rejected', 'Pending', 'Resolved', 'Claimed')
    AND c.item_id IS NULL
";


    $params = ['current_user' => $user_id];

    if ($search) {
        $query .= " AND r.item_name LIKE :search";
        $params['search'] = "%$search%";
    }
    if ($typeFilter) {
        $query .= " AND r.type = :type";
        $params['type'] = $typeFilter;
    }
    if ($categoryFilter) {
        $query .= " AND r.category = :category";
        $params['category'] = $categoryFilter;
    }

    $query .= " ORDER BY r.date_reported DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

/* ✅ SPLIT ITEMS */
$lostItems  = [];
$foundItems = [];

foreach ($items as $item) {
    if ($item['type'] === 'Lost') {
        $lostItems[] = $item;
    }
    else if ($item['type'] === 'Found') {
        $foundItems[] = $item;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🧳 Browse Items | Lost & Found Tracker</title>
<style>
/* Keep all your previous CSS exactly the same */
body { font-family: 'Poppins', sans-serif; margin:0; background:#f7f9fc; display:flex; }
.sidebar { width:230px; background:#0b2447; color:white; display:flex; flex-direction:column; align-items:center; padding:30px 0; position:fixed; height:100vh; }
.sidebar h2 { margin-bottom:40px; font-size:22px; color:#00aaff; }
.sidebar a { text-decoration:none; color:white; width:80%; background:rgba(255,255,255,0.1); margin:6px 0; padding:12px; border-radius:10px; text-align:center; transition:background 0.3s; font-weight:500; }
.sidebar a:hover { background:rgba(255,255,255,0.3); }
.sidebar a.active { background:rgba(255,255,255,0.35); font-weight:bold; }
.sidebar .logout-btn { margin-top:auto; margin-bottom:20px; background:white; color:#0b2447; font-weight:bold; }
.sidebar .logout-btn:hover { background:#cde8ff; }

.main-content { flex:1; margin-left:230px; padding:20px; overflow-y:auto; }
header { background:#145aa3; color:#fff; padding:15px 20px;border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; justify-content:space-between; align-items:center; }
header h1 { margin:0; font-size:24px; }
header p { font-size:14px; }
.filter-section { margin:20px 0; display:flex; flex-wrap:wrap; gap:10px; }
.filter-section input, .filter-section select, .filter-section button { padding:8px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
.items-grid { display:flex; flex-wrap:wrap; gap:15px; }
.item-card { width:180px; background:white; border-radius:10px; box-shadow:0 3px 6px rgba(0,0,0,0.1); overflow:hidden; text-align:center; transition:transform 0.2s ease, box-shadow 0.2s ease; cursor:pointer; }
.item-card:hover { transform:translateY(-3px); box-shadow:0 5px 10px rgba(0,0,0,0.15); }
.item-card img {
    width: auto;
    height: auto;
    max-width: 100%;
    max-height: 120px;
    display: block;
    margin: 0 auto;
}

.item-card h4 { color:#145aa3; margin:6px 0 2px; font-size:14px; }
.item-card p { color:#555; font-size:12px; margin-bottom:5px; }
.item-card span { font-size:11px; color:#999; }

/* Modals & Claim Form CSS (same as before) */
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(10, 20, 40, 0.7); backdrop-filter: blur(6px); animation: fadeIn 0.3s ease-in-out; }
.modal-content { background: #ffffff; margin: 5% auto; padding: 25px 30px; border-radius: 16px; width: 90%; max-width: 550px; position: relative; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); transform: translateY(-20px); animation: slideUp 0.4s ease forwards; }
.close { position: absolute; right: 20px; top: 15px; font-size: 26px; font-weight: bold; color: #333; cursor: pointer; transition: color 0.2s ease; }
.close:hover { color: #ff5252; }
.modal img {
    width: auto;
    height: auto;
    max-width: 100%;
    max-height: 230px;
    display: block;
    margin: 0 auto 15px auto;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.modal h3 { color: #0b2447; font-size: 20px; margin: 10px 0; border-bottom: 2px solid #e5f1ff; padding-bottom: 5px; }
.modal p { color: #333; font-size: 14px; margin: 5px 0; line-height: 1.5; }
.modal button { display: inline-block; padding: 10px 16px; background: linear-gradient(135deg, #00aaff, #0077cc); color: white; border: none; border-radius: 8px; cursor: pointer; margin-top: 12px; font-weight: 500; transition: all 0.3s ease; }
.modal button:hover { background: linear-gradient(135deg, #0088cc, #005fa3); transform: scale(1.03); }
.modal button:disabled { background: #ccc; cursor: not-allowed; transform: none; }
.badge { position: absolute; top: 6px; right: 16px; background-color: red; color: white; font-size: 12px; font-weight: bold; padding: 2px 6px; border-radius: 50%; pointer-events: none; }
.sidebar a { position: relative; }

/* HORIZONTAL SECTIONS FOR LOST & FOUND */
.section-card {
    background: #ffffff;
    padding: 18px;
    margin-top: 20px;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.section-header h2 {
    margin: 0;
    font-size: 18px;
}

.lost-header {
    color: #b30000;
}

.found-header {
    color: #2525a8ff;
}

.horizontal-row {
    display: flex;
    gap: 15px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.horizontal-row::-webkit-scrollbar {
    height: 7px;
}

.horizontal-row::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 8px;
}

.item-card {
    min-width: 180px;   /* important for horizontal */
}

/* better hover feel */
.section-card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}


#claimForm label { display: block; font-size: 14px; color: #0b2447; margin-top: 12px; font-weight: 500; }
#claimForm input[type="text"], #claimForm textarea, #claimForm input[type="file"] { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; outline: none; transition: border 0.3s ease, box-shadow 0.3s ease; }
#claimForm input:focus, #claimForm textarea:focus { border-color: #00aaff; box-shadow: 0 0 0 3px rgba(0, 170, 255, 0.2); }
#claimForm textarea { resize: none; }
#claimFormModal .modal-content { background: #fdfdfd; border: 2px solid #e8f3ff; }
@keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
@keyframes slideUp { from {transform: translateY(40px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
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
        <h1> Browse Items</h1>
        <p>Find Lost and Found items reported here!</p>
    </header>

    <div class="filter-section">
        <form method="GET">
            <input type="text" name="search" placeholder="Search by item name..." value="<?= htmlspecialchars($search); ?>">
            <select name="type">
                <option value="">All Types</option>
                <option value="Lost" <?= $typeFilter=='Lost'?'selected':''; ?>>Lost</option>
                <option value="Found" <?= $typeFilter=='Found'?'selected':''; ?>>Found</option>
            </select>
            <select name="category">
                <option value="">All Categories</option>
                <option value="Electronics" <?= $categoryFilter=='Electronics'?'selected':''; ?>>Electronics</option>
                <option value="Clothing" <?= $categoryFilter=='Clothing'?'selected':''; ?>>Clothing</option>
                <option value="Accessories" <?= $categoryFilter=='Accessories'?'selected':''; ?>>Accessories</option>
                <option value="Documents" <?= $categoryFilter=='Documents'?'selected':''; ?>>Documents</option>
                <option value="Others" <?= $categoryFilter=='Others'?'selected':''; ?>>Others</option>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <!-- LOST ITEMS -->
    <div class="section-card">
        <div class="section-header">
            <h2 class="lost-header"> Lost Items</h2>
        </div>

        <div class="horizontal-row">
            <?php if(!empty($lostItems)): ?>
                <?php foreach($lostItems as $item): ?>
                    <div class="item-card" onclick="openModal(<?= $item['report_id']; ?>)">
                        <img src="../uploads/<?= htmlspecialchars($item['image'] ?: 'default.png'); ?>">
                        <h4><?= htmlspecialchars($item['item_name']); ?></h4>
                        <p><?= htmlspecialchars($item['category']); ?></p>
                        <span><?= htmlspecialchars($item['location']); ?></span>
                    </div>

                    <!-- Modal -->
                    <div id="modal-<?= $item['report_id']; ?>" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="closeModal(<?= $item['report_id']; ?>)">&times;</span>
                            <img src="../uploads/<?= htmlspecialchars($item['image'] ?: 'default.png'); ?>">
                            <h3><?= htmlspecialchars($item['item_name']); ?></h3>
                            <p><strong>Type:</strong> Lost</p>
                            <p><strong>Description:</strong> <?= htmlspecialchars($item['description']); ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($item['location']); ?></p>
                            <p><strong>Date:</strong> <?= htmlspecialchars($item['date_reported']); ?></p>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p>No lost items found.</p>
            <?php endif; ?>
        </div>
    </div>




    <!-- FOUND ITEMS -->
    <div class="section-card">
        <div class="section-header">
            <h2 class="found-header"> Found Items (You can claim)</h2>
        </div>

        <div class="horizontal-row">
            <?php if(!empty($foundItems)): ?>
                <?php foreach($foundItems as $item): ?>
                    <div class="item-card" onclick="openModal(<?= $item['report_id']; ?>)">
                        <img src="../uploads/<?= htmlspecialchars($item['image'] ?: 'default.png'); ?>">
                        <h4><?= htmlspecialchars($item['item_name']); ?></h4>
                        <p><?= htmlspecialchars($item['category']); ?></p>
                        <span><?= htmlspecialchars($item['location']); ?></span>
                    </div>

                    <!-- Modal -->
                    <div id="modal-<?= $item['report_id']; ?>" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="closeModal(<?= $item['report_id']; ?>)">&times;</span>

                            <img src="../uploads/<?= htmlspecialchars($item['image'] ?: 'default.png'); ?>">
                            <h3><?= htmlspecialchars($item['item_name']); ?></h3>
                            <p><strong>Type:</strong> Found</p>
                            <p><strong>Description:</strong> <?= htmlspecialchars($item['description']); ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($item['location']); ?></p>
                            <p><strong>Date:</strong> <?= htmlspecialchars($item['date_reported']); ?></p>
                            <p><strong>Reported by:</strong> <?= htmlspecialchars($item['full_name']); ?></p>

                            <!-- ✅ CLAIM BUTTON -->
                            <?php if($item['type'] === 'Found'): ?>
                                <button onclick="openClaimForm(<?= $item['report_id']; ?>)">
                                     Claim this item
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>


                <?php endforeach; ?>
            <?php else: ?>
                <p>No found items available.</p>
            <?php endif; ?>
        </div>
    </div>


</div>

<!-- Claim Form Modal -->
<div id="claimFormModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('claimFormModal').style.display='none'">&times;</span>
    <h3> Claim This Item</h3>
    <form id="claimForm" enctype="multipart/form-data">
      <input type="hidden" name="item_id" id="claim_item_id">
      <label>Where and when did you lose this item?</label>
      <input type="text" name="verification_q1" required>

      <label>Can you describe any unique features or markings on the item?</label>
      <input type="text" name="verification_q2" required>

      <label>What proof do you have that this item belongs to you?</label>
      <input type="text" name="verification_q3" required>

      <label>Additional Description:</label>
      <textarea name="additional_description" rows="3" required></textarea>
      <label>Optional Proof Image:</label>
      <input type="file" name="proof_image" accept="image/*">
      <button type="submit">Submit Claim</button>
    </form>
  </div>
</div>
<script>
function openModal(id){ document.getElementById('modal-'+id).style.display='block'; }
function closeModal(id){ document.getElementById('modal-'+id).style.display='none'; }
window.onclick=function(e){ document.querySelectorAll('.modal').forEach(m=>{if(e.target===m)m.style.display='none';}); }

function openClaimForm(itemId){
  document.getElementById('claim_item_id').value = itemId;
  document.getElementById('claimFormModal').style.display = 'block';
}

// AJAX form submission
// AJAX form submission
document.getElementById('claimForm').addEventListener('submit', function(e){
  e.preventDefault();
  const formData = new FormData(this);

  fetch('ajax_claim.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    alert(data.message);
    if (data.status === 'success' || data.status === 'exists') {
      // Hide modal (optional)
      document.getElementById('claimFormModal').style.display='none';
      // Redirect to My Claims page
      window.location.href = 'browse_items.php';
    }
  })
  .catch(err => alert('Error: ' + err));
});

</script>

</body>
</html>
