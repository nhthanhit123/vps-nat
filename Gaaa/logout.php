<?php
require_once 'config.php';

session_destroy();

if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

redirect('index.php');
?>