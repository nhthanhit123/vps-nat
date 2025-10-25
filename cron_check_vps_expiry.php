<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

// Check VPS expiry (run daily)
echo "=== VPS Expiry Check - " . date('Y-m-d H:i:s') . " ===\n";

try {
    // Get VPS expiring in next 3 days
    $expiring_vps = checkVpsExpiry();
    
    if (empty($expiring_vps)) {
        echo "No VPS expiring in the next 3 days.\n";
        exit;
    }
    
    echo "Found " . count($expiring_vps) . " VPS expiring soon:\n";
    
    foreach ($expiring_vps as $vps) {
        $expiry_date = new DateTime($vps['expiry_date']);
        $today = new DateTime();
        $days_left = $today->diff($expiry_date)->days;
        
        echo "- VPS #{$vps['id']} ({$vps['package_name']}) - User: {$vps['username']} - Expires in {$days_left} days\n";
        
        // Check if we should send notification
        $notification_key = "vps_expiry_{$vps['id']}_" . date('Y-m-d');
        $notification_file = __DIR__ . '/notifications.json';
        $notifications = [];
        
        if (file_exists($notification_file)) {
            $notifications = json_decode(file_get_contents($notification_file), true) ?: [];
        }
        
        // Send notification if not sent today
        if (!isset($notifications[$notification_key])) {
            if (sendExpiryNotification($vps, $days_left)) {
                $notifications[$notification_key] = [
                    'sent_at' => date('Y-m-d H:i:s'),
                    'days_left' => $days_left
                ];
                
                file_put_contents($notification_file, json_encode($notifications, JSON_PRETTY_PRINT));
                echo "  - Notification sent for VPS #{$vps['id']}\n";
            } else {
                echo "  - Failed to send notification for VPS #{$vps['id']}\n";
            }
        } else {
            echo "  - Notification already sent today for VPS #{$vps['id']}\n";
        }
        
        // Update status based on days left
        if ($days_left <= 0) {
            // VPS already expired
            updateVpsStatus($vps['id'], 'expired');
            echo "  - Status updated to: expired\n";
        } elseif ($days_left <= 1) {
            // VPS expires today or tomorrow
            updateVpsStatus($vps['id'], 'pending_renewal');
            echo "  - Status updated to: pending_renewal\n";
        }
    }
    
    // Check for VPS that have been expired for more than 3 days
    $sql = "SELECT vo.*, u.username, u.email, u.full_name, vp.name as package_name 
            FROM vps_orders vo 
            LEFT JOIN users u ON vo.user_id = u.id 
            LEFT JOIN vps_packages vp ON vo.package_id = vp.id 
            WHERE vo.status = 'expired' 
            AND vo.expiry_date < DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY)";
    
    $long_expired_vps = fetchAll($sql);
    
    if (!empty($long_expired_vps)) {
        echo "\n" . count($long_expired_vps) . " VPS expired for more than 3 days:\n";
        
        foreach ($long_expired_vps as $vps) {
            echo "- VPS #{$vps['id']} ({$vps['package_name']}) - User: {$vps['username']} - Expired: " . formatDate($vps['expiry_date']) . "\n";
            
            // Send final notification if not sent
            $notification_key = "vps_final_expiry_{$vps['id']}";
            if (!isset($notifications[$notification_key])) {
                $message = "🚫 <b>VPS đã hết hạn quá 3 ngày</b>\n\n";
                $message .= "👤 <b>Khách hàng:</b> {$vps['full_name']} ({$vps['username']})\n";
                $message .= "📧 <b>Email:</b> {$vps['email']}\n\n";
                $message .= "🖥️ <b>Gói VPS:</b> {$vps['package_name']}\n";
                $message .= "📅 <b>Ngày hết hạn:</b> " . formatDate($vps['expiry_date']) . "\n";
                $message .= "⚠️ <b>Trạng thái:</b> Đã tắt\n\n";
                $message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/orders.php";
                
                if (sendTelegramNotification($message)) {
                    $notifications[$notification_key] = [
                        'sent_at' => date('Y-m-d H:i:s')
                    ];
                    
                    file_put_contents($notification_file, json_encode($notifications, JSON_PRETTY_PRINT));
                    echo "  - Final notification sent for VPS #{$vps['id']}\n";
                }
            }
        }
    }
    
    echo "\n=== Check completed successfully ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    
    // Send error notification
    $error_message = "🚨 <b>Cronjob Error</b>\n\n";
    $error_message .= "❌ <b>Error:</b> " . $e->getMessage() . "\n";
    $error_message .= "📅 <b>Time:</b> " . date('Y-m-d H:i:s') . "\n";
    $error_message .= "🔗 <b>Script:</b> VPS Expiry Check";
    
    sendTelegramNotification($error_message);
}
?>