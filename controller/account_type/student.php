<?php
require_once "../connection.php";
session_start();

if (!isset($_SESSION['school_id'])) {
    echo '<div style="color:red;">No student ID found. Please login.</div>';
    exit();
}

$school_id = $_SESSION['school_id'];
$stmt = $connection->prepare("SELECT first_name, last_name, age FROM student_table WHERE school_id = ? LIMIT 1");
if ($stmt === false) {
    die('<div style="color:red;">Prepare failed: ' . htmlspecialchars($connection->error) . '</div>');
}
$stmt->bind_param("s", $school_id);
$stmt->execute();
$result = $stmt->get_result();
$first_name = "Student";
$last_name = "";

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $age = $row['age'];
  
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h2>
        <h4>Role: <?php echo htmlspecialchars($_SESSION['role']); ?>!</h4>

        <button class="btn btn-info mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#editInfo" aria-expanded="false" aria-controls="editInfo">
            Edit Information
        </button>
        <div class="collapse" id="editInfo">
            <div class="card card-body">
                <form method="POST" action="">
                    <div class="mb-2">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                    </div>
                    <div class="mb-2">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                    </div>
                    <div class="mb-2">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="updateStudentInfo">Save Changes</button>
                </form>
            </div>
        </div>

        <?php
        if (isset($_POST['updateStudentInfo'])) {
            $edit_first_name = $_POST['first_name'];
            $edit_last_name = $_POST['last_name'];
            $edit_age = $_POST['age'];
            $update_stmt = $connection->prepare("UPDATE student_table SET first_name=?, last_name=?, age=? WHERE school_id=?");
            $update_stmt->bind_param("sssi", $edit_first_name, $edit_last_name, $edit_age, $school_id);
            if ($update_stmt->execute()) {
                echo '<div class="alert alert-success mt-3" id="successMsg">Information updated successfully!</div>';
                
            } else {
                echo '<div class="alert alert-danger mt-3">Failed to update information.</div>';
            }
            $update_stmt->close();
            header("Refresh:2"); // Refresh the page after 2 seconds to show updated info
         
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
