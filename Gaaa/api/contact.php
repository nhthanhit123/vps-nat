<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $contacts = getContactSettings();
    echo json_encode($contacts);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load contact settings']);
}
?>