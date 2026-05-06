<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE email = ?");
    $stmt->execute([$email]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($candidate && password_verify($password, $candidate['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['candidate_id']   = $candidate['candidate_id'];
        $_SESSION['candidate_name'] = $candidate['name'];
        $_SESSION['constituency']   = $candidate['constituency'];
        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: login.html?error=1");
        exit;
    }
}

header("Location: login.html");
exit;
