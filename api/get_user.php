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
    echo json_encode(['error' => 'User ID required']);
    exit;
}

$userId = sanitizeInt($_GET['id']);
$user = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

if ($user) {
    // Remove password from response
    unset($user['password']);
    echo json_encode(['success' => true, 'data' => $user]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
}
?>