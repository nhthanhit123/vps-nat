<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID required']);
    exit;
}

$orderId = sanitizeInt($_GET['order_id']);

// Verify the order belongs to the current user
$order = getOrder($orderId, $_SESSION['user_id']);
if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit;
}

$renewalHistory = getRenewalHistory($orderId);

echo json_encode(['success' => true, 'data' => $renewalHistory]);
?>