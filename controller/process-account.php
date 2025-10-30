<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/connection.php';


$json = file_get_contents('php://input');
$data = json_decode($json, true);


if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing JSON data']);
    exit;
}

//validating entries
try {

    $schoolId = trim($data['schoolId'] ?? '');
    $email = trim($data['email'] ?? '');
    $firstname = trim($data['firstname'] ?? '');
    $lastname = trim($data['lastname'] ?? '');
    $password = $data['password'] ?? '';
    $role = trim($data['role'] ?? '');

    $required = [
        'schoolId' => $schoolId,
        'email' => $email,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'password' => $password,
        'role' => $role
    ];

    $missingFields = [];
    foreach ($required as $field => $value) {
        if ($value === '') {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missingFields));
    }

    if (!preg_match('/^\d{4}-\d{6}$/', $schoolId)) {
        throw new Exception('School ID must be in format: YYYY-NNNNNN (e.g., 2021-123456)');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format.');
    }
    $allowedDomains = [
        'students.nu-laguna.edu.ph',
        'faculty.nu-laguna.edu.ph',
        'admin.nu-laguna.edu.ph'
    ];

    $emailDomain = strtolower(substr($email, strrpos($email, '@') + 1));
    if (!in_array($emailDomain, $allowedDomains)) {
        throw new Exception('Email must be from NU Laguna domain (students.nu-laguna.edu.ph, faculty.nu-laguna.edu.ph, or admin.nu-laguna.edu.ph)');
    }
    if (!in_array($role, ['student', 'faculty', 'admin'])) {
        throw new Exception('Invalid role selected.');
    }
    if (strlen($firstname) > 100 || strlen($lastname) > 100) {
        throw new Exception('First and last names must be 100 characters or less.');
    }
    if (strlen($schoolId) > 11) {
        throw new Exception('School ID must be 11 characters or less.');
    }
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long.');
    }
   
    $salt = base64_encode(random_bytes(16)); 
    $saltedPassword = $salt . $password;
    $hashedPassword = password_hash($saltedPassword, PASSWORD_DEFAULT);

    $checkStmt = $conn->prepare("SELECT school_id, school_email FROM account_table WHERE school_id = ? OR school_email = ?");
    $checkStmt->execute([$schoolId, $email]);
    $existingAccount = $checkStmt->fetch();

    if ($existingAccount) {
        if ($existingAccount['school_id'] === $schoolId) {
            throw new Exception('An account with this School ID already exists.');
        } else {
            throw new Exception('An account with this email already exists.');
        }
    }

    $stmt = $conn->prepare("INSERT INTO account_table (school_id, first_name, last_name, school_email, password, salt, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$schoolId, $firstname, $lastname, $email, $hashedPassword, $salt, $role]);

    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully',
        'data' => [
            'schoolId' => $schoolId,
            'email' => $email,
            'name' => $firstname . ' ' . $lastname,
            'role' => $role
        ]
    ]);

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo json_encode(['success' => false, 'message' => 'Account with this School ID or email already exists.']);
    } else {
        error_log("Database error in process-account.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>