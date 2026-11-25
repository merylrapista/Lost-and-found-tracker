<?php
session_start();
require_once "conn.php";

header('Content-Type: application/json');

// ✅ Admin restriction
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

// ✅ Validate POST data
if (!isset($_POST['action'], $_POST['id'])) {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$id = intval($_POST['id']);
$action = $_POST['action'];

// ✅ Determine new status
$status = ($action === 'approve') ? 'Approved' : 'Rejected';

try {
    // ✅ Update claim status and timestamp
    $update = $pdo->prepare("
        UPDATE claims 
        SET status = :status, resolved_at = NOW() 
        WHERE id = :id
    ");
    $update->execute(['status' => $status, 'id' => $id]);

    // ✅ Get claimant and item info for notification
    $stmtClaim = $pdo->prepare("SELECT claimant_user_id, item_id FROM claims WHERE id = :id");
    $stmtClaim->execute(['id' => $id]);
    $claim = $stmtClaim->fetch(PDO::FETCH_ASSOC);

    if ($claim && $claim['claimant_user_id']) {
        // Fetch item name safely
        $stmtItem = $pdo->prepare("SELECT item_name FROM reports WHERE report_id = :rid");
        $stmtItem->execute(['rid' => $claim['item_id']]);
        $item = $stmtItem->fetch(PDO::FETCH_ASSOC);
        $itemName = $item['item_name'] ?? 'your item';

        // ✅ Add user notification
        $notifMsg = "📢 Your claim for '{$itemName}' has been set to {$status} by the admin.";
        $insertNotif = $pdo->prepare("
            INSERT INTO notifications (user_id, message, created_at)
            VALUES (:uid, :msg, NOW())
        ");
        $insertNotif->execute([
            'uid' => $claim['claimant_user_id'],
            'msg' => $notifMsg
        ]);
    }

    // ✅ Add admin log (optional, to mirror ajax_claim.php style)
    $adminMessage = "Claim ID #{$id} has been {$status} by {$_SESSION['full_name']}.";
    $insertAdminNotif = $pdo->prepare("
        INSERT INTO admin_notifications (message, is_read, created_at)
        VALUES (:msg, 0, NOW())
    ");
    $insertAdminNotif->execute(['msg' => $adminMessage]);

    // ✅ Return success (aligned to JS format)
    echo json_encode(['success' => true, 'message' => "Claim {$status} successfully."]);
    exit;

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>
