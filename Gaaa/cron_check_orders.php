<?php
require_once 'includes/functions.php';

// Check if this script is being run from command line or cron
if (php_sapi_name() !== 'cli') {
    // Add a simple authentication for web access
    $token = $_GET['token'] ?? '';
    if ($token !== 'secure_token_12345') {
        http_response_code(403);
        die('Access denied');
    }
}

try {
    // Check pending VPS orders
    $pendingOrders = checkPendingOrders();
    
    if (!empty($pendingOrders)) {
        $message = "⚠️ <b>Cảnh báo: Có đơn hàng VPS chưa xử lý</b>\n\n";
        
        foreach ($pendingOrders as $order) {
            $message .= "🆕 <b>Mã đơn:</b> #" . $order['id'] . "\n";
            $message .= "👤 <b>Khách hàng:</b> " . sanitize($order['username']) . "\n";
            $message .= "📧 <b>Email:</b> " . sanitize($order['email']) . "\n";
            $message .= "🖥️ <b>Gói VPS:</b> " . sanitize($order['package_name']) . "\n";
            $message .= "📅 <b>Ngày đặt:</b> " . formatDate($order['created_at']) . "\n";
            $message .= "💰 <b>Giá:</b> " . formatPrice($order['total_price']) . "\n\n";
        }
        
        $message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/orders.php";
        
        sendTelegramNotificationToAdmin($message);
    }
    
    // Check pending renewals
    $pendingRenewals = checkPendingRenewals();
    
    if (!empty($pendingRenewals)) {
        $message = "⚠️ <b>Cảnh báo: Có yêu cầu gia hạn VPS chưa xử lý</b>\n\n";
        
        foreach ($pendingRenewals as $renewal) {
            $message .= "🔄 <b>Mã gia hạn:</b> #" . $renewal['id'] . "\n";
            $message .= "🆕 <b>Mã đơn:</b> #" . $renewal['order_id'] . "\n";
            $message .= "👤 <b>Khách hàng:</b> " . sanitize($renewal['username']) . "\n";
            $message .= "📧 <b>Email:</b> " . sanitize($renewal['email']) . "\n";
            $message .= "🖥️ <b>Gói VPS:</b> " . sanitize($renewal['package_name']) . "\n";
            $message .= "⏰ <b>Gia hạn:</b> " . sanitizeInt($renewal['months']) . " tháng\n";
            $message .= "📅 <b>Ngày yêu cầu:</b> " . formatDate($renewal['created_at']) . "\n";
            $message .= "💰 <b>Giá:</b> " . formatPrice($renewal['price']) . "\n\n";
        }
        
        $message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/renewals.php";
        
        sendTelegramNotificationToAdmin($message);
    }
    
    // Log the check
    error_log("Order check completed at " . date('Y-m-d H:i:s'));
    
} catch (Exception $e) {
    error_log("Error in order check: " . $e->getMessage());
}
?>