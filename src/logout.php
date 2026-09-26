<?php
require 'config.php';
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    session_destroy();
}

header('Location: login.php');
exit;
