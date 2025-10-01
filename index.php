<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<?php
session_start();
if (isset($_SESSION['school_id'])) {
    echo '<p>Logged in as: ' . htmlspecialchars($_SESSION['first_name']) . ' ' . htmlspecialchars($_SESSION['last_name']) . ' (' . htmlspecialchars($_SESSION['role']) . ')</p>';
    echo '<a href="controller/logout.php"><button class="logout">Logout</button></a>';
} else if (!isset($_SESSION['school_id'])) {
    $_SESSION['Guest'] = uniqid('guest_', true); // Assign a unique ID to the guest
    echo '<p>Hello!</p>' . htmlspecialchars($_SESSION['Guest']);
    echo '<a href="controller/login.php"><button class="login">Login</button></a>';
}

?>

    <?php include "controller/thesis_list.php"; ?>
</body>
</html>
