<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $page = sanitize($_GET['page'] ?? '');
    $notifications = getActiveNotifications($page);
    echo json_encode($notifications);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load notifications']);
}
?>