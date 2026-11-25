<!-- <?php
session_start();
require_once "conn.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION["user_id"];

try {
    // Lost items
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM reports WHERE category = 'Lost'");
    $lost_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Found items
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM reports WHERE category = 'Found'");
    $found_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // My reports
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM reports WHERE user_id = :uid");
    $stmt->execute(['uid' => $user_id]);
    $my_reports = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Notifications
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = :uid");
    $stmt->execute(['uid' => $user_id]);
    $notifications = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        'lost_count' => $lost_count,
        'found_count' => $found_count,
        'my_reports' => $my_reports,
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> -->
