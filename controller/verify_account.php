<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../controller/my_cookies.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['school_id'], $input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing input.']);
    exit;
}

$school_id = trim($input['school_id']);
$password = trim($input['password']);

try {
    $stmt = $conn->prepare("SELECT * FROM account_table WHERE school_id = :school_id");
    $stmt->execute(['school_id' => $school_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
        exit;
    }

    if (!isset($account['salt']) || $account['salt'] === '') {
        echo json_encode(['success' => false, 'message' => 'Salt missing for this account.']);
        exit;
    }

    $saltedPassword = $account['salt'] . $password;

    if (password_verify($saltedPassword, $account['password'])) {
        echo json_encode([
            'success' => true,
            'first_name' => $account['first_name'],
            'role' => $account['role'],
            'school_id' => $account['school_id']
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>