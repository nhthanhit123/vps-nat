<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/database.php';

// Security functions
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitizeInt($input) {
    return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

function sanitizeEmail($input) {
    return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
}

function sanitizeUrl($input) {
    return filter_var(trim($input), FILTER_SANITIZE_URL);
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function isValidPassword($password) {
    return strlen($password) >= 8;
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

function rateLimitCheck($key, $limit = 5, $window = 300) {
    $cache_key = "rate_limit_" . $key;
    if (!isset($_SESSION[$cache_key])) {
        $_SESSION[$cache_key] = ['count' => 0, 'start' => time()];
    }
    
    $data = $_SESSION[$cache_key];
    if (time() - $data['start'] > $window) {
        $_SESSION[$cache_key] = ['count' => 1, 'start' => time()];
        return true;
    }
    
    if ($data['count'] >= $limit) {
        return false;
    }
    
    $_SESSION[$cache_key]['count']++;
    return true;
}

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
    return fetchOne($sql, [sanitizeInt($id)]);
}

function getOperatingSystem($id) {
    $sql = "SELECT * FROM operating_systems WHERE id = ? AND status = 'active'";
    return fetchOne($sql, [sanitizeInt($id)]);
}

function getUser($id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    return fetchOne($sql, [sanitizeInt($id)]);
}

function getUserByEmail($email) {
    $sql = "SELECT * FROM users WHERE email = ?";
    return fetchOne($sql, [sanitizeEmail($email)]);
}

function getUserByUsername($username) {
    $sql = "SELECT * FROM users WHERE username = ?";
    return fetchOne($sql, [sanitize($username)]);
}

function createUser($data) {
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    return insertData('users', $data);
}

function updateUser($id, $data) {
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    return updateData('users', $data, 'id = ?', [sanitizeInt($id)]);
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
    return fetchAll($sql, [sanitizeInt($userId)]);
}

function getOrder($id, $userId = null) {
    $sql = "SELECT vo.*, vp.name as package_name, os.name as os_name 
            FROM vps_orders vo 
            LEFT JOIN vps_packages vp ON vo.package_id = vp.id 
            LEFT JOIN operating_systems os ON vo.os_id = os.id 
            WHERE vo.id = ?";
    $params = [sanitizeInt($id)];
    
    if ($userId) {
        $sql .= " AND vo.user_id = ?";
        $params[] = sanitizeInt($userId);
    }
    
    return fetchOne($sql, $params);
}

function updateOrder($id, $data) {
    return updateData('vps_orders', $data, 'id = ?', [sanitizeInt($id)]);
}

function createRenewal($data) {
    return insertData('renewals', $data);
}

function getOrderRenewals($orderId) {
    $sql = "SELECT * FROM renewals WHERE order_id = ? ORDER BY created_at DESC";
    return fetchAll($sql, [sanitizeInt($orderId)]);
}

function createDeposit($data) {
    return insertData('deposits', $data);
}

function getUserDeposits($userId) {
    $sql = "SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC";
    return fetchAll($sql, [sanitizeInt($userId)]);
}

function updateDeposit($id, $data) {
    return updateData('deposits', $data, 'id = ?', [sanitizeInt($id)]);
}

function getBankAccounts() {
    $sql = "SELECT * FROM bank_accounts WHERE status = 'active' ORDER BY bank_name ASC";
    return fetchAll($sql);
}

function updateUserBalance($userId, $amount) {
    $sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
    $stmt = executeQuery($sql, [(float)$amount, sanitizeInt($userId)]);
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
    $message .= "👤 <b>Khách hàng:</b> " . sanitize($user['full_name']) . " (" . sanitize($user['username']) . ")\n";
    $message .= "📧 <b>Email:</b> " . sanitize($user['email']) . "\n";
    $message .= "📱 <b>Điện thoại:</b> " . sanitize($user['phone']) . "\n\n";
    $message .= "🖥️ <b>Gói VPS:</b> " . sanitize($package['name']) . "\n";
    $message .= "💾 <b>Cấu hình:</b> " . sanitize($package['cpu']) . " - " . sanitize($package['ram']) . " - " . sanitize($package['storage']) . "\n";
    $message .= "🌍 <b>Vị trí:</b> " . sanitize($package['location']) . "\n";
    $message .= "💻 <b>Hệ điều hành:</b> " . sanitize($os['name']) . "\n";
    $message .= "⏰ <b>Chu kỳ:</b> " . sanitize($order['billing_cycle']) . " tháng\n";
    $message .= "💰 <b>Giá:</b> " . formatPrice($order['total_price']) . "\n";
    $message .= "📅 <b>Thời gian:</b> " . formatDate($order['created_at']) . "\n\n";
    $message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/orders.php";
    
    return sendTelegramNotification($message);
}

function sendRenewalNotification($renewal, $order, $user) {
    $message = "🔄 <b>Gia hạn VPS</b>\n\n";
    $message .= "👤 <b>Khách hàng:</b> " . sanitize($user['full_name']) . " (" . sanitize($user['username']) . ")\n";
    $message .= "📧 <b>Email:</b> " . sanitize($user['email']) . "\n\n";
    $message .= "🖥️ <b>Mã đơn:</b> #" . sanitizeInt($order['id']) . "\n";
    $message .= "⏰ <b>Gia hạn:</b> " . sanitizeInt($renewal['months']) . " tháng\n";
    $message .= "💰 <b>Giá:</b> " . formatPrice($renewal['price']) . "\n";
    $message .= "📅 <b>Thời gian:</b> " . formatDate($renewal['created_at']) . "\n\n";
    $message .= "🔗 <b>Link quản lý:</b> " . BASE_URL . "/admin/renewals.php";
    
    return sendTelegramNotification($message);
}

function sendDepositNotification($deposit, $user, $bank) {
    $message = "💰 <b>Yêu cầu nạp tiền</b>\n\n";
    $message .= "👤 <b>Khách hàng:</b> " . sanitize($user['full_name']) . " (" . sanitize($user['username']) . ")\n";
    $message .= "📧 <b>Email:</b> " . sanitize($user['email']) . "\n\n";
    $message .= "💳 <b>Ngân hàng:</b> " . sanitize($bank['bank_name']) . "\n";
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
    return fetchAll($sql, [sanitizeInt($orderId)]);
}

function formatPrice($price) {
    return number_format((float)$price, 0, ',', '.') . ' VNĐ';
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

// New functions for contact and notifications
function getContactSettings() {
    $sql = "SELECT * FROM contact_settings WHERE is_active = 1 ORDER BY sort_order ASC";
    return fetchAll($sql);
}

function getActiveNotifications($page = null) {
    $sql = "SELECT * FROM notifications WHERE is_active = 1 AND 
            (start_date IS NULL OR start_date <= NOW()) AND 
            (end_date IS NULL OR end_date >= NOW())";
    $params = [];
    
    if ($page) {
        $sql .= " AND (target_page = ? OR target_page IS NULL)";
        $params[] = sanitize($page);
    }
    
    $sql .= " ORDER BY created_at DESC";
    return fetchAll($sql, $params);
}

function getSiteSettings() {
    $settings = fetchAll("SELECT setting_key, setting_value FROM site_settings");
    $result = [];
    foreach ($settings as $setting) {
        $result[$setting['setting_key']] = $setting['setting_value'];
    }
    return $result;
}

function getSeoSettings($page) {
    $seo = fetchOne("SELECT * FROM seo_settings WHERE page_name = ?", [sanitize($page)]);
    return $seo ?: [];
}

function checkPendingOrders() {
    $sql = "SELECT vo.*, u.username, u.email, vp.name as package_name 
            FROM vps_orders vo 
            LEFT JOIN users u ON vo.user_id = u.id 
            LEFT JOIN vps_packages vp ON vo.package_id = vp.id 
            WHERE vo.status = 'pending' 
            ORDER BY vo.created_at ASC";
    return fetchAll($sql);
}

function checkPendingRenewals() {
    $sql = "SELECT r.*, vo.*, u.username, u.email, vp.name as package_name 
            FROM renewals r 
            LEFT JOIN vps_orders vo ON r.order_id = vo.id 
            LEFT JOIN users u ON r.user_id = u.id 
            LEFT JOIN vps_packages vp ON vo.package_id = vp.id 
            WHERE r.status = 'pending' 
            ORDER BY r.created_at ASC";
    return fetchAll($sql);
}

function sendTelegramNotificationToAdmin($message) {
    $telegramSettings = fetchOne("SELECT * FROM telegram_settings WHERE is_active = 1");
    if (!$telegramSettings) {
        return false;
    }
    
    $botToken = $telegramSettings['bot_token'];
    $chatId = $telegramSettings['chat_id'];
    
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

function sendAdminAccessNotification() {
    // Only send once per session to avoid spam
    if (isset($_SESSION['admin_access_notified'])) {
        return;
    }
    
    $username = $_SESSION['username'] ?? 'Unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $time = date('d/m/Y H:i:s');
    
    // Get user info
    $user = fetchOne("SELECT full_name, email FROM users WHERE username = ?", [$username]);
    
    $message = "🔐 <b>Truy cập Admin Panel</b>\n\n";
    $message .= "👤 <b>Admin:</b> " . sanitize($username) . "\n";
    if ($user) {
        $message .= "📧 <b>Email:</b> " . sanitize($user['email']) . "\n";
        $message .= "👨‍💼 <b>Họ tên:</b> " . sanitize($user['full_name']) . "\n";
    }
    $message .= "🌐 <b>IP Address:</b> " . sanitize($ip) . "\n";
    $message .= "📱 <b>Device:</b> " . substr(sanitize($userAgent), 0, 50) . "...\n";
    $message .= "⏰ <b>Thời gian:</b> " . $time . "\n";
    $message .= "🔗 <b>Link:</b> " . BASE_URL . "/admin";
    
    sendTelegramNotificationToAdmin($message);
    
    // Mark as notified for this session
    $_SESSION['admin_access_notified'] = true;
}
?>