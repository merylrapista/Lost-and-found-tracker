<?php
session_start();
require_once "conn.php";

header('Content-Type: application/json');

$lost = $pdo->query("
    SELECT r.*, u.full_name
    FROM reports r
    JOIN users u ON r.user_id = u.id
    WHERE r.type='Lost'
    ORDER BY
        CASE r.status
            WHEN 'Pending' THEN 1
            ELSE 2
        END,
        date_reported DESC
")->fetchAll(PDO::FETCH_ASSOC);

$found = $pdo->query("
    SELECT r.*, u.full_name
    FROM reports r
    JOIN users u ON r.user_id = u.id
    WHERE r.type='Found'
    ORDER BY
        CASE r.status
            WHEN 'Pending' THEN 1
            ELSE 2
        END,
        date_reported DESC
")->fetchAll(PDO::FETCH_ASSOC);


echo json_encode([
    'lost' => $lost,
    'found' => $found
]);
