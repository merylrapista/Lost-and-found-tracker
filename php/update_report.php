<?php
session_start();
require_once "conn.php";

header('Content-Type: application/json');

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check POST data
if (!isset($_POST['action'], $_POST['id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$report_id = intval($_POST['id']);
$action = $_POST['action'];

// Fetch report type
$stmt = $pdo->prepare("SELECT type, status FROM reports WHERE report_id = :rid");
$stmt->execute(['rid' => $report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    echo json_encode(['error' => 'Report not found']);
    exit;
}

// Determine new status
$new_status = $report['status']; // default is current status
switch ($action) {
    case 'approve':
        if ($report['type'] === 'Lost') {
            $new_status = 'Missing';
        } else {
            $new_status = 'Open';
        }
        break;
    case 'reject':
        $new_status = 'Rejected';
        break;
    case 'claimed':
        $new_status = 'Claimed';
        break;
    default:
        echo json_encode(['error' => 'Unknown action']);
        exit;
}

// Update the report
$update = $pdo->prepare("UPDATE reports SET status = :status WHERE report_id = :rid");
$update->execute(['status' => $new_status, 'rid' => $report_id]);

// --- ADD NOTIFICATION LOGIC ---
$stmtUser = $pdo->prepare("SELECT user_id, item_name FROM reports WHERE report_id=:rid");
$stmtUser->execute(['rid' => $report_id]);
$reportInfo = $stmtUser->fetch(PDO::FETCH_ASSOC);

if ($reportInfo && $reportInfo['user_id']) {
    $itemName = $reportInfo['item_name'] ?? 'your item';
    $notifMsg = "Your report for '{$itemName}' has been set to '{$new_status}' by the admin.";

    $insertNotif = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (:uid, :msg, NOW())");
    $insertNotif->execute([
        'uid' => $reportInfo['user_id'],
        'msg' => $notifMsg
    ]);
}
// --- END NOTIFICATION LOGIC ---




// Return success and new status for frontend
echo json_encode([
    'success' => true,
    'report_id' => $report_id,
    'new_status' => $new_status
]);
