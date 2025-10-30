<?php
$BASE_URL = "http://" . $_SERVER['HTTP_HOST'] . "/thesis_repo/";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>
    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/sign-in.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body>

    <div id="back-btn-wrapper">
        <button id="back-btn">GO TO HOMEPAGE</button>
    </div>
    <form id="form-wrapper">
        <div class="form-section" id="header">
            <span id="header-label">NU-L Thesis Repository</span>
        </div>
        <div class="form-section" id="body">
            <div id="input-fields-section">
                <div class="text-field-wrapper">
                    <label for="school-id" id="id-label" class="labels">
                        <div class="icon-wrapper">
                            <span class="material-symbols-outlined">
                                person
                            </span>
                        </div>
                    </label>
                    <input type="text" name="school-id" id="school-id" class="text-fields" placeholder="School-ID"
                        required>
                </div>

                <div class="text-field-wrapper">
                    <label for="password" id="password-label" class="labels">
                        <div class="icon-wrapper">
                            <span class="material-symbols-outlined">
                                lock
                            </span>
                        </div>
                    </label>
                    <input type="password" name="password" id="password" class="text-fields" placeholder="Password"
                        required>
                </div>
            </div>
            <div id="buttons-section">
                <div class="btn-wrapper"><button type="submit" id="sign-in-btn" class="functional-btn">SIGN IN</button>
                </div>
                <div class="btn-wrapper"> <button type="button" id="create-btn" class="functional-btn">CREATE AN
                        ACCOUNT</button></div>
            </div>
        </div>
    </form>
    <script>
        const baseURL = "<?php echo $BASE_URL; ?>";
    </script>
    <script src="<?php echo $BASE_URL; ?>View/js/sign-in.js?v=<?php echo time(); ?>"></script>
</body>

</html>