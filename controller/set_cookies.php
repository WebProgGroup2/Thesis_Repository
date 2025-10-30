<?php
require_once 'my_cookies.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($_COOKIE['guest_id'])) {
        deleteMyCookie('guest_id');
    }

    if (!empty($data['first_name']))
        setMyCookie('first_name', $data['first_name']);
    if (!empty($data['role']))
        setMyCookie('role', $data['role']);
    if (!empty($data['school_id']))
        setMyCookie('school_id', $data['school_id']);

    echo json_encode(['status' => 'success']);
}
