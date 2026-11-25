<?php
session_start();
require_once "conn.php";
header('Content-Type: application/json');

// Fetch all claim records with verification data
$query = "
    SELECT 
        c.*, 
        i.item_name, 
        i.category, 
        u1.full_name AS claimant_name, 
        u2.full_name AS reporter_name
    FROM claims c
    JOIN reports i ON c.item_id = i.report_id
    JOIN users u1 ON c.claimant_user_id = u1.id
    JOIN users u2 ON i.user_id = u2.id
    ORDER BY c.created_at DESC
";

$stmt = $pdo->query($query);
$claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate by status
$pending = [];
$approved = [];
$rejected = [];

foreach ($claims as $c) {
    switch ($c['status']) {
        case 'Pending': $pending[] = $c; break;
        case 'Approved': $approved[] = $c; break;
        case 'Rejected': $rejected[] = $c; break;
    }
}

echo json_encode([
    'pending' => $pending,
    'approved' => $approved,
    'rejected' => $rejected
]);
?>
