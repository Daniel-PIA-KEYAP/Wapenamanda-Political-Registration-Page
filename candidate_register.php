<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $ward         = trim($_POST['ward'] ?? '');
    $constituency = $_POST['constituency'] ?? '';
    $email        = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';

    if (!$name || !$ward || !$email || !$password) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Check for duplicate email
    $check = $pdo->prepare("SELECT candidate_id FROM candidates WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Email already registered.']);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        "INSERT INTO candidates (name, ward, constituency, email, password_hash)
         VALUES (?,?,?,?,?)"
    );
    $stmt->execute([$name, $ward, $constituency ?: null, $email, $passwordHash]);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Candidate registered successfully!']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
