<?php
require_once "connection.php";

if (isset($_POST['login'])) {
    $school_email = $_POST['school_email'];
    $password = $_POST['password'];

    $stmt = $connection->prepare("SELECT school_id, first_name, last_name, school_email, password, salt, role FROM account_table WHERE school_email = ? LIMIT 1");
    if (!$stmt) {
        die("Prepare failed: " . $connection->error);
    }

    $stmt->bind_param("s", $school_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if the salt exists in the database
        if (!isset($user['salt']) || empty($user['salt'])) {
            die("Error: Salt is missing for this user. Debug: " . print_r($user, true));
        }

        // Debugging: Log the entered password and salted password
        error_log("Entered Password: " . $password);
        $saltedPassword = $user['salt'] . $password;
        error_log("Salted Password: " . $saltedPassword);

        if (password_verify($saltedPassword, $user['password'])) {
            // Successful login
            session_start();
            $_SESSION['school_id'] = $user['school_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['school_email'] = $user['school_email'];
            if ($user['role'] === 'student' || $user['role'] === 'faculty') {
                header("Location: ../index.php");
            } else {
                header("Location: ../controller/account_type/admin.php");
            }
            exit();
        } else {
            error_log("Password verification failed. Debug: " . print_r($user, true));
            $loginError = "Invalid email or password.";
        }
    } else {
        $loginError = "Account not found.";
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../view/styles/login.css">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($loginError)) echo '<div class="alert alert-danger">' . htmlspecialchars($loginError) . '</div>'; ?>
    <form method="POST" action="../controller/login.php">
        <input type="email" name="school_email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" name="login" value="Login">
    </form>
</body>
</html>
