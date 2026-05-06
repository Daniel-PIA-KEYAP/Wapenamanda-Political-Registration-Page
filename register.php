<?php
include 'db.php';

define('UPLOAD_DIR', 'uploads/');

$ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
$ALLOWED_MIMES      = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

/**
 * Safely handle a file upload with MIME type and extension validation.
 * Returns the saved path on success, or null if no file or on validation failure.
 */
function handleUpload(string $fileKey, string $prefix): ?string
{
    global $ALLOWED_EXTENSIONS, $ALLOWED_MIMES;

    if (empty($_FILES[$fileKey]['name']) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmpPath = $_FILES[$fileKey]['tmp_name'];
    $origExt = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));

    if (!in_array($origExt, $ALLOWED_EXTENSIONS, true)) {
        return null;
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpPath);
    if (!in_array($mimeType, $ALLOWED_MIMES, true)) {
        return null;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0700, true);
    }

    $safeFile    = uniqid($prefix, true) . '.' . $origExt;
    $destination = UPLOAD_DIR . $safeFile;

    return move_uploaded_file($tmpPath, $destination) ? $destination : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $surname      = trim($_POST['surname'] ?? '');
    $ward         = trim($_POST['ward'] ?? '');
    $tribe        = trim($_POST['tribe'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $age          = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    $dob          = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $employment   = $_POST['employmentStatus'] ?? '';
    $company      = trim($_POST['company'] ?? '');
    $studyLevel   = $_POST['studyLevel'] ?? '';
    $schoolName   = trim($_POST['schoolName'] ?? '');
    $marital      = $_POST['maritalStatus'] ?? '';
    $constituency = $_POST['constituency'] ?? '';

    // Only store conditional fields when relevant
    $company    = ($employment === 'employed' || $employment === 'selfEmployed') ? ($company ?: null) : null;
    $studyLevel = $employment === 'student'  ? ($studyLevel ?: null) : null;
    $schoolName = $employment === 'student'  ? ($schoolName ?: null) : null;

    $idImagePath = handleUpload('idImage', 'id_');

    $stmt = $pdo->prepare(
        "INSERT INTO users
            (name, surname, ward, tribe, phone, email, id_image, age, dob,
             employment_status, company, study_level, school_name,
             marital_status, constituency)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    $stmt->execute([
        $name, $surname, $ward, $tribe, $phone, $email, $idImagePath,
        $age, $dob, $employment ?: null, $company, $studyLevel, $schoolName,
        $marital ?: null, $constituency ?: null
    ]);
    $userId = $pdo->lastInsertId();

    // Spouse registration
    if ($marital === 'married' && !empty($_POST['spouseName'])) {
        $spouseImagePath = handleUpload('spouseIdImage', 'spouse_');

        $spouseAge = !empty($_POST['spouseAge']) ? (int)$_POST['spouseAge'] : null;
        $spouseDob = !empty($_POST['spouseDob']) ? $_POST['spouseDob'] : null;

        $spouseStmt = $pdo->prepare(
            "INSERT INTO spouses
                (user_id, name, surname, ward, tribe, phone, email, id_image, age, dob)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $spouseStmt->execute([
            $userId,
            trim($_POST['spouseName'] ?? ''),
            trim($_POST['spouseSurname'] ?? ''),
            trim($_POST['spouseWard'] ?? ''),
            trim($_POST['spouseTribe'] ?? ''),
            trim($_POST['spousePhone'] ?? ''),
            trim($_POST['spouseEmail'] ?? ''),
            $spouseImagePath,
            $spouseAge,
            $spouseDob
        ]);
    }

    // Children registration
    $childrenCount = (int)($_POST['childrenCount'] ?? 0);
    for ($i = 1; $i <= $childrenCount; $i++) {
        $childName    = trim($_POST["child{$i}Name"] ?? '');
        $childSurname = trim($_POST["child{$i}Surname"] ?? '');
        $childAge     = !empty($_POST["child{$i}Age"]) ? (int)$_POST["child{$i}Age"] : null;
        $childDob     = !empty($_POST["child{$i}Dob"]) ? $_POST["child{$i}Dob"] : null;

        if ($childName !== '') {
            $childStmt = $pdo->prepare(
                "INSERT INTO children (user_id, name, surname, age, dob)
                 VALUES (?,?,?,?,?)"
            );
            $childStmt->execute([$userId, $childName, $childSurname, $childAge, $childDob]);
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
