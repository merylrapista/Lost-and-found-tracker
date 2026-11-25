<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['report_id'])) {
    $item_id = intval($_GET['report_id']);
    $user_id = $_SESSION['user_id'];

    try {
        // Check if already claimed
        $check = $pdo->prepare("SELECT * FROM claims WHERE item_id = :item_id AND claimant_user_id = :uid");
        $check->execute(['item_id' => $item_id, 'uid' => $user_id]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('You already submitted a claim for this item.'); window.location.href='browse_items.php';</script>";
            exit;
        }

        // Insert new claim
        $stmt = $pdo->prepare("
            INSERT INTO claims (item_id, claimant_user_id, message, status, created_at)
            VALUES (:item_id, :uid, 'Claim request submitted', 'Pending', NOW())
        ");
        $stmt->execute(['item_id' => $item_id, 'uid' => $user_id]);

        // ✅ Add notification for claimant
        $notif_stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message, created_at)
            VALUES (:user_id, :message, NOW())
        ");
        $notif_stmt->execute([
            'user_id' => $user_id,
            'message' => '📩 You have successfully submitted a claim request for an item.'
        ]);

        // ✅ Safely add admin notification (will not break existing claim)
        try {
            // Get item name for better admin message
            $item_stmt = $pdo->prepare("SELECT item_name FROM reports WHERE report_id = :id");
            $item_stmt->execute(['id' => $item_id]);
            $item = $item_stmt->fetch(PDO::FETCH_ASSOC);
            $item_name = $item['item_name'] ?? 'Unknown Item';

            $admin_notif_stmt = $pdo->prepare("
                INSERT INTO admin_notifications (message, is_read, created_at)
                VALUES (:message, 0, NOW())
            ");
            $admin_message = "User " . ($_SESSION['full_name'] ?? 'Unknown User') . " submitted a claim for '{$item_name}'";
            $admin_notif_stmt->execute(['message' => $admin_message]);
        } catch (PDOException $e) {
            // Optional: log admin notification error without breaking user flow
        }

        echo "<script>alert('Claim request submitted successfully!'); window.location.href='browse_items.php';</script>";
        exit;

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request: No report ID provided.";
}
?>
