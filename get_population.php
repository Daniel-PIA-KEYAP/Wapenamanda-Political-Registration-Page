<?php
session_start();
include 'db.php';

if (!isset($_SESSION['candidate_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$constituency = $_SESSION['constituency'] ?? 'Tsak';

$stmt = $pdo->prepare(
    "SELECT ward, COUNT(*) AS total
     FROM users
     WHERE constituency = ?
     GROUP BY ward
     ORDER BY total DESC"
);
$stmt->execute([$constituency]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'wards'  => array_column($data, 'ward'),
    'counts' => array_column($data, 'total')
]);
