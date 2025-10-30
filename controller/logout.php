<?php
require_once 'my_cookies.php'; 


deleteMyCookie('first_name');
deleteMyCookie('role');
deleteMyCookie('school_id');


session_start();
$_SESSION = [];
session_destroy();


header("Location: " . $BASE_URL . "index.php");
exit();
