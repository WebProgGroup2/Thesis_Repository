<?php
require_once "../../connection.php";
session_start();

if (!isset($_SESSION['school_id'])) {
    echo '<div style="color:red;">No faculty ID found. Please login.</div>';
    exit();
}

$school_id = $_SESSION['school_id'];
$stmt = $connection->prepare("SELECT first_name FROM faculty_table WHERE school_id = ? LIMIT 1");
$stmt->bind_param("s", $school_id);
$stmt->execute();
$result = $stmt->get_result();
$first_name = "Faculty";
if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $first_name = $row['first_name'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h2>
</body>
</html>
