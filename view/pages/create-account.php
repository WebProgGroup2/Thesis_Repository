<?php
$BASE_URL = "http://" . $_SERVER['HTTP_HOST'] . "/thesis_repo/";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/create-account.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <title>Create Account</title>
</head>

<body>
    <div id="back-btn-wrapper">
        <button id="back-btn">GO TO HOMEPAGE</button>
    </div>
    <form id="form-wrapper">
        <div class="section" id="left-section">
            <img src="../assets/create-account.jpg" alt="illustration" id="illustration">
        </div>
        <div class="section" id="right-section">
            <div id="header" class="section-rows">
                <img src="../assets/LRC Logo.png" alt="lrc-logo" id="lrc-logo">
                <span id="section-label">LRC Thesis Repository</span>
            </div>
            <div id="input-fields-wrapper" class="section-rows">
                <span id="form-label">Create an account</span>
                <div id="input-fields">

                    <input type="text" id="school-id" class="text-fields" placeholder="School-ID" required>
                    <input type="text" id="email" class="text-fields" placeholder="School email" required>

                    <div id="personal-details" class="details-field">
                        <input type="text" id="firstname" class="text-fields" placeholder="Firstname" required>
                        <input type="text" id="lastname" class="text-fields" placeholder="Lastname" required>
                    </div>
                    <input type="password" name="password" id="password" class="text-fields" placeholder="Password"
                        required>
                </div>
            </div>
            <div id="selection-wrapper" class="section-rows">
                <select name="role" id="role-selection">
                    <option value="" disabled selected>Role</option>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                </select>
            </div>
            <div id="btns-wrapper" class="section-rows">
                <input type="submit" value="Create Account" id="create-btn" class="btns">
                <span>Have an account?</span>
                <input type="button" value="Sign In" id="sign-in-btn" class="btns">
            </div>

        </div>
    </form>

    <script>
        const baseURL = "<?php echo $BASE_URL; ?>";
    </script>
    <script src="<?php echo $BASE_URL; ?>View/js/create-account.js?v=<?php echo time(); ?>"></script>
</body>

</html>