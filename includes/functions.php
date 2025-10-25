<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/database.php';

function fetchVpsPackages() {
    $sql = "SELECT * FROM vps_packages WHERE status = 'active' ORDER BY selling_price ASC";
    return fetchAll($sql);
}

function fetchOperatingSystems() {
    $sql = "SELECT * FROM operating_systems WHERE status = 'active' ORDER BY name ASC";
    return fetchAll($sql);
}

function getVpsPackage($id) {
    $sql = "SELECT * FROM vps_packages WHERE id = ? AND status = 'active'";
    return fetchOne($sql, [$id]);
}

function getOperatingSystem($id) {
    $sql = "SELECT * FROM operating_systems WHERE id = ? AND status = 'active'";
    return fetchOne($sql, [$id]);
}

function getUser($id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    return fetchOne($sql, [$id]);
}

function getUserByEmail($email) {
    $sql = "SELECT * FROM users WHERE email = ?";
    return fetchOne($sql, [$email]);
}

function getUserByUsername($username) {
    $sql = "SELECT * FROM users WHERE username = ?";
    return fetchOne($sql, [$username]);
}

function createUser($data) {
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    return insertData('users', $data);
}

function updateUser($id, $data) {
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    return updateData('users', $data, 'id = ?', [$id]);
}

function createVpsOrder($data) {
    return insertData('vps_orders', $data);
}

function getUserOrders($userId) {
    $sql = "SELECT vo.*, vp.name as package_name, os.name as os_name 
            FROM vps_orders vo 
            LEFT JOIN vps_packages vp ON vo.package_id = vp.id 
            LEFT JOIN operating_systems os ON vo.os_id = os.id 
            WHERE vo.user_id = ? 
            ORDER BY vo.created_at DESC";
    return fetchAll($sql, [$userId]);
}

function getOrder($id, $userId = null) {
    $sql = "SELECT vo.*, vp.name as package_name, os.name as os_name 
            FROM vps_orders vo 
            LEFT JOIN vps_packages vp ON vo.package_id = vp.id 
            LEFT JOIN operating_systems os ON vo.os_id = os.id 
            WHERE vo.id = ?";
    $params = [$id];
    
    if ($userId) {
        $sql .= " AND vo.user_id = ?";
        $params[] = $userId;
    }
    
    return fetchOne($sql, $params);
}

function updateOrder($id, $data) {
    return updateData('vps_orders', $data, 'id = ?', [$id]);
}

function createRenewal($data) {
    return insertData('renewals', $data);
}

function getOrderRenewals($orderId) {
    $sql = "SELECT * FROM renewals WHERE order_id = ? ORDER BY created_at DESC";
    return fetchAll($sql, [$orderId]);
}

function createDeposit($data) {
    return insertData('deposits', $data);
}

function getUserDeposits($userId) {
    $sql = "SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC";
    return fetchAll($sql, [$userId]);
}

function updateDeposit($id, $data) {
    return updateData('deposits', $data, 'id = ?', [$id]);
}

function getBankAccounts() {
    $sql = "SELECT * FROM bank_accounts WHERE status = 'active' ORDER BY bank_name ASC";
    return fetchAll($sql);
}

function updateUserBalance($userId, $amount) {
    $sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
    $stmt = executeQuery($sql, [$amount, $userId]);
    return $stmt->affected_rows;
}

function calculatePrice($basePrice, $months) {
    $multipliers = [
        '1' => 1,
        '6' => 6,
        '12' => 12,
        '24' => 24
    ];
    
    return $basePrice * ($multipliers[$months] ?? 1);
}

function sendOrderNotification($order, $user, $package, $os) {
    $message = "🆕 <b>Đơn hàng VPS mới</b>\n\n";
    $message .= "👤 <b>Khách hàng:</b> {$user['full_name']} ({$user['username']})\n";
    $message .= "📧 <b>Email:</b> {$user['email']}\n";
    $message .= "📱 <b>Điện thoại:</b> {$user['phone']}\n\n";
    $message .= "🖥️ <b>Gói VPS:</b> {$package['name']}\n";
    $message .= "💾 <b>Cấu hình:</b> {$package['cpu']} - {$package['ram']} - {$package['storage']}\n";
    $message .= "🌍 <b>Vị trí:</b> {$package['location']}\n";
    $message .= "💻 <b>Hệ điều hành:</b> {$os['name']}\n";
    $message .= "⏰ <b>Chu kỳ:</b> {$order['billing_cycle']} tháng\n";
    $message .= "💰 <b>Giá:</b> " . formatPrice($order['total_price']) . "\n";
    $message .= "📅 <b>Thời gian:</b> " . formatDate($order['created_at']) . "\n\n";
    $message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/orders.php";
    
    return sendTelegramNotification($message);
}

function sendRenewalNotification($renewal, $order, $user) {
    $message = "🔄 <b>Gia hạn VPS</b>\n\n";
    $message .= "👤 <b>Khách hàng:</b> {$user['full_name']} ({$user['username']})\n";
    $message .= "📧 <b>Email:</b> {$user['email']}\n\n";
    $message .= "🖥️ <b>Mã đơn:</b> #{$order['id']}\n";
    $message .= "⏰ <b>Gia hạn:</b> {$renewal['months']} tháng\n";
    $message .= "💰 <b>Giá:</b> " . formatPrice($renewal['price']) . "\n";
    $message .= "📅 <b>Thời gian:</b> " . formatDate($renewal['created_at']) . "\n\n";
    $message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/renewals.php";
    
    return sendTelegramNotification($message);
}

function sendDepositNotification($deposit, $user, $bank) {
    $message = "💰 <b>Yêu cầu nạp tiền</b>\n\n";
    $message .= "👤 <b>Khách hàng:</b> {$user['full_name']} ({$user['username']})\n";
    $message .= "📧 <b>Email:</b> {$user['email']}\n\n";
    $message .= "💳 <b>Ngân hàng:</b> {$bank['bank_name']}\n";
    $message .= "💰 <b>Số tiền:</b> " . formatPrice($deposit['amount']) . "\n";
    $message .= "📅 <b>Thời gian:</b> " . formatDate($deposit['created_at']) . "\n\n";
    $message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/deposits.php";
    
    return sendTelegramNotification($message);
}

function getRenewalHistory($orderId) {
    $sql = "SELECT r.*, u.username 
            FROM renewals r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE r.order_id = ? 
            ORDER BY r.created_at DESC";
    return fetchAll($sql, [$orderId]);
}

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function formatDate($date) {
    if (empty($date) || $date === '0000-00-00') {
        return 'N/A';
    }
    return date('d/m/Y H:i', strtotime($date));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sendTelegramNotification($message) {
    // Implementation for Telegram notification
    return true;
}

// Website Settings Functions
function getWebsiteSettings() {
    $settings_file = __DIR__ . '/../website_settings.json';
    if (file_exists($settings_file)) {
        $json = file_get_contents($settings_file);
        return json_decode($json, true) ?: [];
    }
    return [];
}

function saveWebsiteSettings($settings) {
    $settings_file = __DIR__ . '/../website_settings.json';
    $json = json_encode($settings, JSON_PRETTY_PRINT);
    return file_put_contents($settings_file, $json) !== false;
}

function getWebsiteSetting($key, $default = null) {
    $settings = getWebsiteSettings();
    return $settings[$key] ?? $default;
}

// CardV2 API Functions
function createCardPayment($data) {
    $api_key = getWebsiteSetting('cardv2_api_key');
    $api_url = 'https://api.cardv2.vn/api/create';
    
    $post_data = [
        'api_key' => $api_key,
        'card_type' => $data['card_type'],
        'card_amount' => $data['card_amount'],
        'card_code' => $data['card_code'],
        'card_serial' => $data['card_serial'],
        'request_id' => $data['request_id'] ?? uniqid(),
        'callback_url' => BASE_URL . '/cardv2_callback.php'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        return json_decode($response, true);
    }
    
    return ['error' => 'API request failed'];
}

function checkCardStatus($request_id) {
    $api_key = getWebsiteSetting('cardv2_api_key');
    $api_url = 'https://api.cardv2.vn/api/check';
    
    $post_data = [
        'api_key' => $api_key,
        'request_id' => $request_id
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        return json_decode($response, true);
    }
    
    return ['error' => 'API request failed'];
}

// VPS Expiry Check Functions
function checkVpsExpiry() {
    $sql = "SELECT vo.*, u.username, u.email, u.full_name, vp.name as package_name 
            FROM vps_orders vo 
            LEFT JOIN users u ON vo.user_id = u.id 
            LEFT JOIN vps_packages vp ON vo.package_id = vp.id 
            WHERE vo.status = 'active' 
            AND vo.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY)
            AND vo.expiry_date >= CURRENT_DATE()";
    
    return fetchAll($sql);
}

function updateVpsStatus($order_id, $status) {
    return updateData('vps_orders', ['status' => $status], 'id = ?', [$order_id]);
}

function sendExpiryNotification($order, $days_left) {
    $message = "⚠️ <b>VPS sắp hết hạn</b>\n\n";
    $message .= "👤 <b>Khách hàng:</b> {$order['full_name']} ({$order['username']})\n";
    $message .= "📧 <b>Email:</b> {$order['email']}\n\n";
    $message .= "🖥️ <b>Gói VPS:</b> {$order['package_name']}\n";
    $message .= "📅 <b>Ngày hết hạn:</b> " . formatDate($order['expiry_date']) . "\n";
    $message .= "⏰ <b>Còn lại:</b> {$days_left} ngày\n\n";
    $message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/orders.php";
    
    return sendTelegramNotification($message);
}
?>