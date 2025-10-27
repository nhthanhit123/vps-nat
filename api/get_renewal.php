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
    echo json_encode(['error' => 'Renewal ID required']);
    exit;
}

$renewalId = sanitizeInt($_GET['id']);

$renewal = fetchOne("
    SELECT r.*, u.username, u.full_name, u.email,
           vo.package_id, vo.ip_address, vo.username as vps_username, vo.password,
           vp.name as package_name, vp.selling_price,
           os.name as os_name
    FROM renewals r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN vps_orders vo ON r.order_id = vo.id
    LEFT JOIN vps_packages vp ON vo.package_id = vp.id
    LEFT JOIN operating_systems os ON vo.os_id = os.id
    WHERE r.id = ?
", [$renewalId]);

if ($renewal) {
    echo json_encode(['success' => true, 'data' => $renewal]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Renewal not found']);
}
?>