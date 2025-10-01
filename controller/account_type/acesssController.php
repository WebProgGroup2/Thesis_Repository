<?php
require_once "../connection.php";
session_start();

if (!isset($_SESSION['school_id'])) {
    echo '<div style="color:red;">No user ID found. Please login.</div>';
    exit();
}

$school_id = $_SESSION['school_id'];
$role = $_SESSION['role'];

// Display user-specific dashboard
if ($role === 'student') {
    echo '<h2>Welcome, Student!</h2>';
    echo '<p>This is your dashboard.</p>';
} elseif ($role === 'faculty') {
    echo '<h2>Welcome, Faculty!</h2>';
    echo '<p>This is your dashboard.</p>';
} else {
    echo '<div style="color:red;">Invalid role. Access denied.</div>';
    exit();
}
?>