<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "conn.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);
    $user_id = $_SESSION['user_id'];

    // ✅ Capture form inputs
    $verification_q1 = trim($_POST['verification_q1'] ?? '');
    $verification_q2 = trim($_POST['verification_q2'] ?? '');
    $verification_q3 = trim($_POST['verification_q3'] ?? '');
    $additional_description = trim($_POST['description'] ?? ''); // corrected name

    $proof_image = null;

    // ✅ Optional proof image upload
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/claims/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $tmp_name = $_FILES['proof_image']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['proof_image']['name']);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($tmp_name, $target_path)) {
            $proof_image = $target_path;
        }
    }

    try {
        // ✅ Prevent duplicate claim
        $check = $pdo->prepare("SELECT * FROM claims WHERE item_id = :item_id AND claimant_user_id = :uid");
        $check->execute(['item_id' => $item_id, 'uid' => $user_id]);

        if ($check->rowCount() > 0) {
            echo json_encode(['status' => 'exists', 'message' => 'You already submitted a claim for this item.']);
            exit;
        }

        // ✅ Insert claim (make sure you added these columns to your table)
        $stmt = $pdo->prepare("
            INSERT INTO claims (
                item_id, claimant_user_id, message, status, created_at,
                verification_q1, verification_q2, verification_q3, additional_description, proof_image
            ) VALUES (
                :item_id, :uid, 'Claim request submitted', 'Pending', NOW(),
                :q1, :q2, :q3, :desc, :proof
            )
        ");
        $stmt->execute([
            'item_id' => $item_id,
            'uid' => $user_id,
            'q1' => $verification_q1,
            'q2' => $verification_q2,
            'q3' => $verification_q3,
            'desc' => $additional_description,
            'proof' => $proof_image
        ]);

        // ✅ Add user notification
        $notif_stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message, created_at)
            VALUES (:user_id, :message, NOW())
        ");
        $notif_stmt->execute([
            'user_id' => $user_id,
            'message' => '📩 You have successfully submitted a claim request with verification details.'
        ]);

        // ✅ Admin notification
        try {
            $item_stmt = $pdo->prepare("SELECT item_name FROM reports WHERE report_id = :id");
            $item_stmt->execute(['id' => $item_id]);
            $item = $item_stmt->fetch(PDO::FETCH_ASSOC);
            $item_name = $item['item_name'] ?? 'Unknown Item';

            $admin_message = "User " . ($_SESSION['full_name'] ?? 'Unknown User') . " submitted a claim for '{$item_name}' (Pending verification).";
            $admin_notif_stmt = $pdo->prepare("
                INSERT INTO admin_notifications (message, is_read, created_at)
                VALUES (:message, 0, NOW())
            ");
            $admin_notif_stmt->execute(['message' => $admin_message]);
        } catch (PDOException $e) {
            // Silent fail for admin notifications
        }

        echo json_encode(['status' => 'success', 'message' => '✅ Claim request submitted successfully! Pending admin review.']);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}
?>
