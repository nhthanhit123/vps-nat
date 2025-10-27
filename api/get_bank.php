<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bank ID required']);
    exit;
}

$bankId = sanitizeInt($_GET['id']);
$bank = fetchOne("SELECT * FROM bank_accounts WHERE id = ?", [$bankId]);

if ($bank) {
    echo json_encode(['success' => true, 'data' => $bank]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Bank not found']);
}
?>