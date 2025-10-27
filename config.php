<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('ASSETS_URL', BASE_URL . '/assets');
define('SITE_NAME', 'VPS NAT');
define('SITE_DESCRIPTION', 'Dịch vụ VPS chất lượng cao với giá cả phải chăng');

$telegram_bot_token = '7024177675:AAE8rJEEvc8papBIwJK8ucnvmx0Tqt3dNxA';
$telegram_chat_id = '6049282066';

function getTelegramBotToken() {
    global $telegram_bot_token;
    return $telegram_bot_token;
}

function getTelegramChatId() {
    global $telegram_chat_id;
    return $telegram_chat_id;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    // $data = htmlspecialchars($data);
    return $data;
}

?>