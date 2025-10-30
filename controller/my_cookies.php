<?php

$BASE_URL = "http://" . $_SERVER['HTTP_HOST'] . "/thesis_repo/";


function setMyCookie($name, $value, $expiryDays = 1)
{
    setcookie($name, $value, time() + (86400 * $expiryDays), "/");
}


function getMyCookie($name)
{
    return $_COOKIE[$name] ?? null;
}


function deleteMyCookie($name)
{
    setcookie($name, '', time() - 3600, "/");
}


function requireLoginCookies()
{

    global $BASE_URL; 
    if (session_status() === PHP_SESSION_NONE)
        session_start();

    $userId = getMyCookie('school_id');
    $role = getMyCookie('role');

    if (!$userId || $role !== 'admin') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        header("Location: " . $BASE_URL . "index.php");
        exit(); 
    }

}
?>