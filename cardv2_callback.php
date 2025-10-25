<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

// Get callback data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log callback for debugging
file_put_contents('cardv2_callback.log', date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

$request_id = $data['request_id'] ?? '';
$status = $data['status'] ?? '';
$amount = $data['amount'] ?? 0;
$actual_amount = $data['actual_amount'] ?? 0;
$transaction_id = $data['transaction_id'] ?? '';
$message = $data['message'] ?? '';

// Find the deposit record
$sql = "SELECT * FROM deposits WHERE notes LIKE ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1";
$deposit = fetchOne($sql, ["%{$request_id}%"]);

if (!$deposit) {
    http_response_code(404);
    echo json_encode(['error' => 'Deposit not found']);
    exit;
}

if ($status === 'success') {
    // Update deposit status
    updateDeposit($deposit['id'], [
        'status' => 'completed',
        'notes' => $deposit['notes'] . " - Success: {$message}"
    ]);
    
    // Update user balance
    updateUserBalance($deposit['user_id'], $actual_amount);
    
    // Get user info for notification
    $user = getUser($deposit['user_id']);
    
    // Send notification
    $notification_message = "💰 <b>Nạp thẻ cào thành công</b>\n\n";
    $notification_message .= "👤 <b>Khách hàng:</b> {$user['full_name']} ({$user['username']})\n";
    $notification_message .= "📧 <b>Email:</b> {$user['email']}\n";
    $notification_message .= "💳 <b>Mệnh giá:</b> " . formatPrice($amount) . "\n";
    $notification_message .= "💰 <b>Thực nhận:</b> " . formatPrice($actual_amount) . "\n";
    $notification_message .= "🆔 <b>Mã GD:</b> {$transaction_id}\n";
    $notification_message .= "📅 <b>Thời gian:</b> " . date('d/m/Y H:i') . "\n\n";
    $notification_message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/deposits.php";
    
    sendTelegramNotification($notification_message);
    
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Deposit processed successfully']);
    
} elseif ($status === 'failed') {
    // Update deposit status
    updateDeposit($deposit['id'], [
        'status' => 'failed',
        'notes' => $deposit['notes'] . " - Failed: {$message}"
    ]);
    
    // Get user info for notification
    $user = getUser($deposit['user_id']);
    
    // Send notification
    $notification_message = "❌ <b>Nạp thẻ cào thất bại</b>\n\n";
    $notification_message .= "👤 <b>Khách hàng:</b> {$user['full_name']} ({$user['username']})\n";
    $notification_message .= "📧 <b>Email:</b> {$user['email']}\n";
    $notification_message .= "💳 <b>Mệnh giá:</b> " . formatPrice($amount) . "\n";
    $notification_message .= "❌ <b>Lý do:</b> {$message}\n";
    $notification_message .= "🆔 <b>Mã GD:</b> {$transaction_id}\n";
    $notification_message .= "📅 <b>Thời gian:</b> " . date('d/m/Y H:i') . "\n\n";
    $notification_message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/deposits.php";
    
    sendTelegramNotification($notification_message);
    
    http_response_code(200);
    echo json_encode(['status' => 'failed', 'message' => 'Deposit failed']);
    
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
}
?>