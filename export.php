<?php
session_start();
include 'db.php';

if (!isset($_SESSION['candidate_id']) || !isset($_SESSION['constituency'])) {
    header("Location: login.html");
    exit;
}

$constituency = $_SESSION['constituency'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="users_' . $constituency . '_' . date('Ymd') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Name', 'Surname', 'Ward', 'Constituency', 'Age', 'Employment Status', 'Marital Status', 'Email', 'Phone', 'Created At']);

$stmt = $pdo->prepare(
    "SELECT name, surname, ward, constituency, age, employment_status, marital_status, email, phone, created_at
     FROM users
     WHERE constituency = ?
     ORDER BY created_at DESC"
);
$stmt->execute([$constituency]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
