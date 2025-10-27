<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

// Get all banks with API links
$banks = fetchAll("SELECT * FROM bank_accounts WHERE status = 'active' AND apibanklink IS NOT NULL AND apibanklink != ''");

foreach ($banks as $bank) {
    try {
        // Parse API URL to get token and account number
        $apiUrl = parse_url($bank['apibanklink']);
        parse_str($apiUrl['query'], $params);
        
        if (!isset($params['token']) || !isset($params['numberAccount'])) {
            continue;
        }
        
        $token = $params['token'];
        $accountNumber = $params['numberAccount'];
        
        // Build API URL
        $apiEndpoint = "https://apibank.vddns.site/?token={$token}&numberAccount={$accountNumber}";
        
        // Call API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            continue;
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['status']) || $data['status'] !== 200 || !isset($data['transactions'])) {
            continue;
        }
        
        // Process transactions
        foreach ($data['transactions'] as $transaction) {
            // Check if transaction contains "KVMVPS" in content
            if (strpos($transaction['content'], 'KVMVPS') !== false && $transaction['amountIn'] > 0) {
                // Extract username from content
                $username = extractUsernameFromContent($transaction['content']);
                
                if ($username) {
                    // Find user by username
                    $user = fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
                    
                    if ($user) {
                        // Check if this transaction is already processed
                        $existingDeposit = fetchOne(
                            "SELECT * FROM deposits WHERE transaction_id = ? AND bank_code = ?",
                            [$transaction['transactionId'], $bank['bank_code']]
                        );
                        
                        if (!$existingDeposit) {
                            // Create deposit record
                            $depositData = [
                                'user_id' => $user['id'],
                                'amount' => $transaction['amountIn'],
                                'bank_code' => $bank['bank_code'],
                                'bank_name' => $bank['bank_name'],
                                'transaction_id' => $transaction['transactionId'],
                                'status' => 'completed',
                                'notes' => "Nạp tự động từ {$bank['bank_name']} - {$transaction['content']}"
                            ];
                            
                            if (createDeposit($depositData)) {
                                // Update user balance
                                updateUserBalance($user['id'], $transaction['amountIn']);
                                
                                // Send notification
                                $message = "💰 <b>Nạp tiền tự động thành công</b>\n\n";
                                $message .= "👤 <b>Khách hàng:</b> " . sanitize($user['full_name']) . " (" . sanitize($user['username']) . ")\n";
                                $message .= "💳 <b>Ngân hàng:</b> " . sanitize($bank['bank_name']) . "\n";
                                $message .= "💰 <b>Số tiền:</b> " . formatPrice($transaction['amountIn']) . "\n";
                                $message .= "🆔 <b>Mã GD:</b> " . sanitize($transaction['transactionId']) . "\n";
                                $message .= "📅 <b>Thời gian:</b> " . sanitize($transaction['date']) . "\n\n";
                                $message .= "✅ <b>Trạng thái:</b> Đã cộng tiền tự động";
                                
                                sendTelegramNotificationToAdmin($message);
                            }
                        }
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        // Log error if needed
        error_log("Error checking bank {$bank['id']}: " . $e->getMessage());
    }
}

function extractUsernameFromContent($content) {
    // Pattern to find "KVMVPS username" in content
    if (preg_match('/KVMVPS\s+(\w+)/i', $content, $matches)) {
        return $matches[1];
    }
    return null;
}

echo "Deposit check completed at " . date('Y-m-d H:i:s') . "\n";
?>