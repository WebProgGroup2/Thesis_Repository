<?php
require_once "../connection.php";
session_start();

// Switch to thesis_repository database
mysqli_select_db($connection, 'thesis_repository');

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $del_school_id = $_GET['delete'];
    // Delete from student_table or faculty_table first (to avoid foreign key constraint errors)
    $connection->query("DELETE FROM student_table WHERE school_id = '" . $connection->real_escape_string($del_school_id) . "'");
    $connection->query("DELETE FROM faculty_table WHERE school_id = '" . $connection->real_escape_string($del_school_id) . "'");
    // Delete from account_table
    $del_stmt = $connection->
    prepare("DELETE FROM account_table WHERE school_id = ?");
    if ($del_stmt) {
        $del_stmt->bind_param('s', $del_school_id);
        $del_stmt->execute();
    }
    header('location: admin.php');
    exit();
}

//handle edit for accounts
$editMode = false;
$editData = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $_SESSION['edit_id'] = $_GET['edit'];
    header("Location: admin.php?editMode=1");
    exit();
}
if (isset($_GET['editMode']) && $_GET['editMode'] == 1 && isset($_SESSION['edit_id'])) {
    $edit_school_id = $_SESSION['edit_id'];
    $editMode = true;
    $edit_stmt = $connection->prepare("SELECT school_id, first_name, last_name, school_email, role FROM account_table WHERE school_id = ? LIMIT 1");
    $edit_stmt->bind_param('s', $edit_school_id);
    $edit_stmt->execute();
    $editData = $edit_stmt->get_result()->fetch_assoc();
}

// Handle update action
if (isset($_POST['updateAccount'])) {
    $school_id = $_POST['school_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $school_email = $_POST['school_email'];
    $role = $_POST['role'];
    $update_query = "UPDATE account_table SET first_name=?, last_name=?, school_email=?, role=? WHERE school_id=?";
    $stmt = $connection->prepare($update_query);
    $stmt ->bind_param("sssss", $first_name, $last_name, $school_email, $role, $school_id);
    $stmt ->execute();
    if($stmt){
        if($role === 'student'){
            $update_student_query = "UPDATE student_table SET first_name=?, last_name=?, school_email=? WHERE school_id=?";
            $stmt = $connection->prepare($update_student_query);
            $stmt->bind_param("sssss", $first_name, $last_name, $school_email, $school_id);
            $stmt->execute();
        }
        else if($role === 'faculty'){
            $update_faculty_query = "UPDATE faculty_table SET first_name=?, last_name=?, school_email=? WHERE school_id=?";
            $stmt = $connection->prepare($update_faculty_query);
            $stmt->bind_param("sssss", $first_name, $last_name, $school_email, $school_id);
            $stmt->execute();
        }
        $stmt->close();
        unset($_SESSION['edit_id']);
        header("Location: admin.php");
        exit();
    } else {
        echo "Error preparing statement: " . $connection->error;
    } 
}

// Handle account creation and role assignment
if (isset($_POST['accountSubmit'])) {
    $school_id = $_POST['school_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $school_email = $_POST['school_email'];

    //salt the password
    $salt = base64_encode(random_bytes(16)); // 16-byte random salt
    $saltedPassword = $salt.$_POST['password'];
    $password = password_hash($saltedPassword, PASSWORD_DEFAULT); // Secure password hash
    $role = isset($_POST['role']) ? $_POST['role'] : 'error';

    // Update the INSERT query to include the salt column
    $insertquery = "INSERT INTO account_table (school_id, first_name, last_name, school_email, password, salt, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($insertquery);
    if ($stmt === false) {
        echo '<div class="alert alert-danger">Prepare failed: ' . htmlspecialchars($connection->error) . '</div>';
    } else {
        $stmt->bind_param('sssssss', $school_id, $first_name, $last_name, $school_email, $password, $salt, $role);
        if (!$stmt->execute()) {
            echo '<div class="alert alert-danger">Insert failed: ' . htmlspecialchars($stmt->error) . '</div>';
        } else {
            // Insert into student_table or faculty_table with all required columns
            if ($role === 'student') {
                $studentInsert = "INSERT INTO student_table (school_id, first_name, last_name, program, age, city, street, zip_code) VALUES (?, ?, ?, '', 0, '', '', '')";
                $stmt2 = $connection->prepare($studentInsert);
                if ($stmt2) {
                    $stmt2->bind_param('sss', $school_id, $first_name, $last_name);
                    $stmt2->execute();
                }
            } elseif ($role === 'faculty') {
                $facultyInsert = "INSERT INTO faculty_table (school_id, first_name, last_name, program, age, home_address) VALUES (?, ?, ?, '', 0, '')";
                $stmt2 = $connection->prepare($facultyInsert);
                if ($stmt2) {
                    $stmt2->bind_param('sss', $school_id, $first_name, $last_name);
                    $stmt2->execute();
                }
            }
            header('location: admin.php');
            exit();
        }
    }
}
$editThesisMode = false;
$editThesisData = null;
// Handle edit action for thesis
if (isset($_GET['editThesis']) && !empty($_GET['editThesis'])) {
    $edit_thesis_id = intval($_GET['editThesis']);
    $editThesisMode = true;
    $stmt = $connection->prepare("SELECT thesis_id, title, author, abstract, publication_date, publication_place, subject, list, advance, keyword, thesis_image, pdf_filename FROM thesis_table WHERE thesis_id = ? LIMIT 1");
    $stmt->bind_param('i', $edit_thesis_id);
    $stmt->execute();
    $editThesisData = $stmt->get_result()->fetch_assoc();
}

// Handle update action for thesis (all fields)
if (isset($_POST['updateThesis'])) {
    $thesis_id = intval($_POST['thesis_id']);
    $title = $_POST['title'];
    $author = $_POST['author'];
    $abstract = $_POST['abstract'];
    $publication_date = $_POST['publication_date'];
    $publication_place = $_POST['publication_place'];
    $subject = $_POST['subject'];
    $list = $_POST['list'];
    $advance = $_POST['advance'];
    $keyword = $_POST['keyword'];
    $thesis_image = $_POST['existing_image'];
    $pdf_filename = $_POST['existing_pdf'] ?? '';
    if (isset($_FILES['thesis_image']) && $_FILES['thesis_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['thesis_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('thesis_', true) . '.' . $ext;
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir);
        $dest = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['thesis_image']['tmp_name'], $dest)) {
            $thesis_image = $filename;
        } else {
            $thesisError = 'Failed to move uploaded file.';
        }
    }
    if (isset($_FILES['thesis_pdf']) && $_FILES['thesis_pdf']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['thesis_pdf']['name'], PATHINFO_EXTENSION);
        $pdfname = uniqid('thesis_pdf_', true) . '.' . $ext;
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir);
        $dest = $uploadDir . $pdfname;
        if (move_uploaded_file($_FILES['thesis_pdf']['tmp_name'], $dest)) {
            $pdf_filename = $pdfname;
        } else {
            $thesisError = 'Failed to move uploaded PDF.';
        }
    }
    $stmt = $connection->prepare("UPDATE thesis_table SET title=?, author=?, abstract=?, publication_date=?, publication_place=?, subject=?, list=?, advance=?, keyword=?, thesis_image=?, pdf_filename=? WHERE thesis_id=?");
    if ($stmt) {
        $stmt->bind_param('sssssssssssi',
         $title, 
         $author, 
         $abstract, 
         $publication_date, 
         $publication_place, 
         $subject, 
         $list, 
         $advance, 
         $keyword, 
         $thesis_image, 
         $pdf_filename, 
         $thesis_id);
        if (!$stmt->execute()) {
            $thesisError = 'Database update failed: ' . htmlspecialchars($stmt->error);
        } else {
            header('location: admin.php');
            exit();
        }
    } else {
        $thesisError = 'Prepare failed: ' . htmlspecialchars($connection->error);
    }
}
$thesisError = '';
if (isset($_POST['uploadThesisImage'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $thesis_image = '';
    $pdf_filename = '';
    if (isset($_FILES['thesis_image']) && $_FILES['thesis_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['thesis_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('thesis_', true) . '.' . $ext;
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir);
        $dest = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['thesis_image']['tmp_name'], $dest)) {
            $thesis_image = $filename;
        } else {
            $thesisError = 'Failed to move uploaded file.';
        }
    }
    if (isset($_FILES['thesis_pdf']) && $_FILES['thesis_pdf']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['thesis_pdf']['name'], PATHINFO_EXTENSION);
        $pdfname = uniqid('thesis_pdf_', true) . '.' . $ext;
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir);
        $dest = $uploadDir . $pdfname;
        if (move_uploaded_file($_FILES['thesis_pdf']['tmp_name'], $dest)) {
            $pdf_filename = $pdfname;
        } else {
            $thesisError = 'Failed to move uploaded PDF.';
        }
    }
    if ($thesis_image || $pdf_filename) {
        $stmt = $connection->prepare("INSERT INTO thesis_table (title, author, abstract, publication_date, publication_place, subject, list, advance, keyword, thesis_image, pdf_filename) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('ssssssssssss', $title, $author, $_POST['abstract'] ?? '', $_POST['publication_date'] ?? '', $_POST['publication_place'] ?? '', $_POST['subject'] ?? '', $_POST['list'] ?? '', $_POST['advance'] ?? '', $_POST['keyword'] ?? '', $thesis_image, $pdf_filename);
            if (!$stmt->execute()) {
                $thesisError = 'Database insert failed: ' . htmlspecialchars($stmt->error);
            }
        } else {
            $thesisError = 'Prepare failed: ' . htmlspecialchars($connection->error);
        }
    }
}

$displayQuery = "SELECT  school_id, first_name, last_name, school_email, date_created, role FROM account_table";
$result = mysqli_query($connection, $displayQuery);
if (!$result) {
    echo '<div class="alert alert-danger">Error fetching accounts: ' . htmlspecialchars(mysqli_error($connection)) . '</div>';
    $result = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <a href="../login.php" target="_blank" class="btn btn-secondary mb-3">Login</a>
   

    <?php
        echo '<div class="alert alert-info">Logged in as: ' . htmlspecialchars($_SESSION['first_name']) . ' (' . htmlspecialchars($_SESSION['role'] ?? 'admin') . ')</div>';
         echo '<a href="../logout.php"><button class="logout">Logout</button></a>';
    ?>
   
    <h1>Account Management (Admin Panel)</h1>

    <?php if ($editMode && $editData): ?>
        <h3>Edit Account</h3>
        <form id="accountForm" action="admin.php" method="POST" class="mb-4">
            <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($editData['school_id']); ?>">
            <input type="text" value="<?php echo htmlspecialchars($editData['school_id']); ?>" disabled class="form-control mb-2">
            <input type="text" name="first_name" id="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($editData['first_name']); ?>" required><br>
            <input type="text" name="last_name" id="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($editData['last_name']); ?>" required><br>
            <input type="email" name="school_email" id="school_email" placeholder="Email" value="<?php echo htmlspecialchars($editData['school_email']); ?>" required pattern="^[a-zA-Z0-9._%+-]+@nu-laguna\.edu\.ph$" title="Email must end with @nu-laguna.edu.ph"><br>
            <select name="role" id="role" required>
                <option value="student" <?php if ($editData['role']==='student') echo 'selected'; ?>>Student</option>
                <option value="faculty" <?php if ($editData['role']==='faculty') echo 'selected'; ?>>Faculty</option>
                <option value="admin" <?php if ($editData['role']==='admin') echo 'selected'; ?>>Admin</option>
            </select><br>
            <input type="submit" value="Update Account" name="updateAccount">
            <a href="admin.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
        <script>
    function updateEmailPlaceholderAndValue(roleId, firstNameId, lastNameId, emailId) {
        const role = document.getElementById(roleId).value;
        const firstName = document.getElementById(firstNameId).value.trim().toLowerCase();
        const lastName = document.getElementById(lastNameId).value.trim().toLowerCase();
        const emailInput = document.getElementById(emailId);

        if (role === 'student') {
            emailInput.placeholder = 'firstnamelastname@students.nu-laguna.edu.ph';
            if (firstName && lastName) {
                emailInput.value = `${firstName}${lastName}@students.nu-laguna.edu.ph`;
            }
        } else if (role === 'faculty') {
            emailInput.placeholder = 'firstnamelastname@faculty.nu-laguna.edu.ph';
            if (firstName && lastName) {
                emailInput.value = `${firstName}${lastName}@faculty.nu-laguna.edu.ph`;
            }
        } else if (role === 'admin') {
            emailInput.placeholder = 'firstnamelastname@admin.nu-laguna.edu.ph';
            if (firstName && lastName) {
                emailInput.value = `${firstName}${lastName}@admin.nu-laguna.edu.ph`;
            }
        } else {
            emailInput.placeholder = 'Email';
            emailInput.value = '';
        }
    }

    document.getElementById('role').addEventListener('change', () => updateEmailPlaceholderAndValue('role', 'first_name', 'last_name', 'school_email'));
    document.getElementById('first_name').addEventListener('input', () => updateEmailPlaceholderAndValue('role', 'first_name', 'last_name', 'school_email'));
    document.getElementById('last_name').addEventListener('input', () => updateEmailPlaceholderAndValue('role', 'first_name', 'last_name', 'school_email'));
    </script>
   
    <?php else: ?>
    <form id="addAccountForm" action="admin.php" method="POST" class="mb-4">
        <input type="text" name="school_id" placeholder="School ID" required><br>
        <input type="text" name="first_name" id="add_first_name" placeholder="First Name" required><br>
        <input type="text" name="last_name" id="add_last_name" placeholder="Last Name" required><br>
        <input type="email" name="school_email" id="add_school_email" placeholder="Email" required pattern="^[a-zA-Z0-9._%+-]+@nu-laguna\.edu\.ph$" title="Email must end with @nu-laguna.edu.ph"><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="role" id="add_role" required>
            <option value="">Select Role</option>
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
            <option value="admin">Admin</option>
        </select><br>
        <input type="submit" value="Add Account" name="accountSubmit">
    </form>
    <script>
    function updateEmailPlaceholderAndValue(roleId, firstNameId, lastNameId, emailId) {
        const role = document.getElementById(roleId).value;
        const firstName = document.getElementById(firstNameId).value.trim().toLowerCase();
        const lastName = document.getElementById(lastNameId).value.trim().toLowerCase();
        const emailInput = document.getElementById(emailId);

        if (role === 'student') {
            emailInput.placeholder = 'firstnamelastname@students.nu-laguna.edu.ph';
            if (firstName && lastName) {
                emailInput.value = `${firstName}${lastName}@students.nu-laguna.edu.ph`;
            }
        } else if (role === 'faculty') {
            emailInput.placeholder = 'firstnamelastname@faculty.nu-laguna.edu.ph';
            if (firstName && lastName) {
                emailInput.value = `${firstName}${lastName}@faculty.nu-laguna.edu.ph`;
            }
        } else if (role === 'admin') {
            emailInput.placeholder = 'firstnamelastname@admin.nu-laguna.edu.ph';
            if (firstName && lastName) {
                emailInput.value = `${firstName}${lastName}@admin.nu-laguna.edu.ph`;
            }
        } else {
            emailInput.placeholder = 'Email';
            emailInput.value = '';
        }
    }

    document.getElementById('add_role').addEventListener('change', () => updateEmailPlaceholderAndValue('add_role', 'add_first_name', 'add_last_name', 'add_school_email'));
    document.getElementById('add_first_name').addEventListener('input', () => updateEmailPlaceholderAndValue('add_role', 'add_first_name', 'add_last_name', 'add_school_email'));
    document.getElementById('add_last_name').addEventListener('input', () => updateEmailPlaceholderAndValue('add_role', 'add_first_name', 'add_last_name', 'add_school_email'));
    </script>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>School ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Date Created</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rowNumber = 1;
            while ($row = mysqli_fetch_array($result)) {
                echo "<tr>";
                echo "<td>" . $rowNumber . "</td>";
                echo "<td>" . ($row['school_id']) . "</td>";
                echo "<td>" . ($row['first_name']) . "</td>";
                echo "<td>" . ($row['last_name']) . "</td>";
                echo "<td>" . ($row['school_email']) . "</td>";
                echo "<td>" . ($row['date_created']) . "</td>";
                echo "<td>" . ($row['role']) . "</td>";
                echo '<td>';
                echo '<a href="admin.php?edit=' . urlencode($row['school_id']) . '" class="btn btn-sm btn-primary me-1">Edit</a>';
                echo '<a href="admin.php?delete=' . urlencode($row['school_id']) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this account?\')">Delete</a>';
                echo '</td>';
                echo "</tr>";
                $rowNumber++;
            }
            ?>
        </tbody>
    </table>



</body>
</html>