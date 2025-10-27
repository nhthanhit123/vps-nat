<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_telegram'])) {
        $telegram_bot_token = cleanInput($_POST['telegram_bot_token']);
        $telegram_chat_id = cleanInput($_POST['telegram_chat_id']);
        
        // Update telegram settings
        $existing = fetchOne("SELECT id FROM telegram_settings LIMIT 1");
        if ($existing) {
            updateData('telegram_settings', [
                'bot_token' => $telegram_bot_token,
                'chat_id' => $telegram_chat_id,
                'is_active' => 1
            ], 'id = ?', [$existing['id']]);
        } else {
            insertData('telegram_settings', [
                'bot_token' => $telegram_bot_token,
                'chat_id' => $telegram_chat_id,
                'is_active' => 1
            ]);
        }
        
        $_SESSION['success_message'] = 'Cập nhật cài đặt Telegram thành công!';
        
        // Test Telegram connection if token and chat_id are provided
        if (!empty($telegram_bot_token) && !empty($telegram_chat_id)) {
            $test_message = "🔧 <b>Kiểm Tra Kết Nối</b>\n\nTelegram API đã được cấu hình thành công!\n\nThời gian: " . date('d/m/Y H:i:s');
            $result = sendTelegramNotification($test_message);
            
            if ($result) {
                $_SESSION['success_message'] .= ' Tin nhắn test đã được gửi!';
            } else {
                $_SESSION['error_message'] = ' Cấu hình thành công nhưng không thể gửi tin nhắn test. Vui lòng kiểm tra lại token và chat ID.';
            }
        }
        
        redirect('settings.php');
    }
    
    if (isset($_POST['save_site_settings'])) {
        $settings = [
            'site_name' => cleanInput($_POST['site_name']),
            'site_description' => cleanInput($_POST['site_description']),
            'company_name' => cleanInput($_POST['company_name']),
            'company_address' => cleanInput($_POST['company_address']),
            'company_email' => cleanInput($_POST['company_email']),
            'company_phone' => cleanInput($_POST['company_phone']),
            'company_hotline' => cleanInput($_POST['company_hotline']),
            'facebook_url' => cleanInput($_POST['facebook_url']),
            'telegram_url' => cleanInput($_POST['telegram_url']),
            'youtube_url' => cleanInput($_POST['youtube_url']),
            'maintenance_mode' => cleanInput($_POST['maintenance_mode'])
        ];
        
        foreach ($settings as $key => $value) {
            updateData('site_settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
        }
        
        $_SESSION['success_message'] = 'Cập nhật cài đặt website thành công!';
        redirect('settings.php');
    }
}

// Get current settings
$telegram_settings = fetchOne("SELECT * FROM telegram_settings LIMIT 1");
$site_settings = [];
$settings_data = fetchAll("SELECT setting_key, setting_value FROM site_settings");
foreach ($settings_data as $setting) {
    $site_settings[$setting['setting_key']] = $setting['setting_value'];
}

$page_title = 'Cài Đặt Hệ Thống - Admin Panel';
$header_title = 'Cài Đặt Hệ Thống';
$header_description = 'Quản lý cài đặt và cấu hình hệ thống';

ob_start();
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-cog text-blue-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded">Hệ thống</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">Cài đặt</h3>
        <p class="text-gray-600 text-sm">Quản lý cấu hình</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fab fa-telegram text-green-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">
                <?= $telegram_settings && $telegram_settings['is_active'] ? 'Đã kết nối' : 'Chưa kết nối' ?>
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">Telegram</h3>
        <p class="text-gray-600 text-sm">Thông báo tự động</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-globe text-purple-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded">Website</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">
            <?= htmlspecialchars($site_settings['site_name'] ?? 'VPS NAT') ?>
        </h3>
        <p class="text-gray-600 text-sm">Tên website</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-tools text-orange-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-orange-600 bg-orange-100 px-2 py-1 rounded">
                <?= ($site_settings['maintenance_mode'] ?? 'false') === 'true' ? 'Bảo trì' : 'Hoạt động' ?>
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">Trạng thái</h3>
        <p class="text-gray-600 text-sm">Chế độ website</p>
    </div>
</div>

<!-- Settings Tabs -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button onclick="showTab('telegram')" id="telegram-tab" 
                    class="tab-button py-4 px-6 border-b-2 border-cyan-500 text-cyan-600 font-medium">
                <i class="fab fa-telegram mr-2"></i>Telegram
            </button>
            <button onclick="showTab('site')" id="site-tab" 
                    class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium">
                <i class="fas fa-globe mr-2"></i>Website
            </button>
            <button onclick="showTab('system')" id="system-tab" 
                    class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium">
                <i class="fas fa-info-circle mr-2"></i>Hệ thống
            </button>
        </nav>
    </div>

    <!-- Telegram Settings -->
    <div id="telegram-content" class="tab-content p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Cài đặt Telegram Bot</h3>
        
        <form method="POST" class="space-y-6">
            <input type="hidden" name="save_telegram" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Bot Token *
                    </label>
                    <input type="text" name="telegram_bot_token" required
                           value="<?= htmlspecialchars($telegram_settings['bot_token'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                           placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
                    <p class="mt-1 text-sm text-gray-500">
                        Token của Telegram Bot
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Chat ID *
                    </label>
                    <input type="text" name="telegram_chat_id" required
                           value="<?= htmlspecialchars($telegram_settings['chat_id'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                           placeholder="-123456789">
                    <p class="mt-1 text-sm text-gray-500">
                        Chat ID để nhận thông báo
                    </p>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" 
                        class="btn-primary text-white px-6 py-2 rounded-lg font-medium hover:shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>Lưu cài đặt Telegram
                </button>
            </div>
        </form>
        
        <!-- Telegram Guide -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="font-semibold text-blue-900 mb-4">
                <i class="fas fa-info-circle mr-2"></i>Hướng dẫn setup Telegram Bot
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h5 class="font-medium text-blue-800 mb-2">Các bước tạo Bot:</h5>
                    <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                        <li>Mở Telegram và tìm @BotFather</li>
                        <li>Gửi lệnh <code class="bg-blue-100 px-1 rounded">/newbot</code></li>
                        <li>Tên bot: VPS Store Bot</li>
                        <li>Username: vpsstore_bot (phải kết thúc bằng _bot)</li>
                        <li>Sao chép Bot Token được cung cấp</li>
                    </ol>
                </div>
                <div>
                    <h5 class="font-medium text-blue-800 mb-2">Cách lấy Chat ID:</h5>
                    <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                        <li>Thêm bot vào nhóm nhận thông báo</li>
                        <li>Gửi một tin nhắn bất kỳ vào nhóm</li>
                        <li>Truy cập API getUpdates</li>
                        <li>Tìm chat.id trong kết quả trả về</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Site Settings -->
    <div id="site-content" class="tab-content p-6 hidden">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Cài đặt Website</h3>
        
        <form method="POST" class="space-y-6">
            <input type="hidden" name="save_site_settings" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tên website</label>
                    <input type="text" name="site_name" 
                           value="<?= htmlspecialchars($site_settings['site_name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tên công ty</label>
                    <input type="text" name="company_name" 
                           value="<?= htmlspecialchars($site_settings['company_name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả website</label>
                <textarea name="site_description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500"><?= htmlspecialchars($site_settings['site_description'] ?? '') ?></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ công ty</label>
                <textarea name="company_address" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500"><?= htmlspecialchars($site_settings['company_address'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email công ty</label>
                    <input type="email" name="company_email" 
                           value="<?= htmlspecialchars($site_settings['company_email'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                    <input type="tel" name="company_phone" 
                           value="<?= htmlspecialchars($site_settings['company_phone'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hotline</label>
                    <input type="tel" name="company_hotline" 
                           value="<?= htmlspecialchars($site_settings['company_hotline'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Chế độ bảo trì</label>
                    <select name="maintenance_mode" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
                        <option value="false" <?= ($site_settings['maintenance_mode'] ?? 'false') === 'false' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="true" <?= ($site_settings['maintenance_mode'] ?? 'false') === 'true' ? 'selected' : '' ?>>Bảo trì</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Facebook URL</label>
                    <input type="url" name="facebook_url" 
                           value="<?= htmlspecialchars($site_settings['facebook_url'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telegram URL</label>
                    <input type="url" name="telegram_url" 
                           value="<?= htmlspecialchars($site_settings['telegram_url'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">YouTube URL</label>
                    <input type="url" name="youtube_url" 
                           value="<?= htmlspecialchars($site_settings['youtube_url'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" 
                        class="btn-primary text-white px-6 py-2 rounded-lg font-medium hover:shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>Lưu cài đặt Website
                </button>
            </div>
        </form>
    </div>

    <!-- System Info -->
    <div id="system-content" class="tab-content p-6 hidden">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Thông tin Hệ thống</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-6 bg-gray-50 rounded-lg">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-database text-green-600 text-2xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1">Database</h4>
                <p class="text-sm text-gray-600">
                    <?php
                    $db_test = fetchOne("SELECT 1 as test");
                    echo $db_test ? 'Kết nối thành công' : 'Lỗi kết nối';
                    ?>
                </p>
            </div>
            
            <div class="text-center p-6 bg-gray-50 rounded-lg">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fab fa-telegram text-blue-600 text-2xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1">Telegram API</h4>
                <p class="text-sm text-gray-600">
                    <?php
                    if ($telegram_settings && $telegram_settings['is_active'] && 
                        !empty($telegram_settings['bot_token']) && !empty($telegram_settings['chat_id'])) {
                        echo 'Đã cấu hình';
                    } else {
                        echo 'Chưa cấu hình';
                    }
                    ?>
                </p>
            </div>
            
            <div class="text-center p-6 bg-gray-50 rounded-lg">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-server text-purple-600 text-2xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1">Server Info</h4>
                <p class="text-sm text-gray-600">
                    PHP: <?= PHP_VERSION ?><br>
                    Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?>
                </p>
            </div>
        </div>
        
        <div class="mt-8">
            <h4 class="font-semibold text-gray-800 mb-4">Các thông báo sẽ gửi qua Telegram:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-sm text-gray-700">Đơn hàng VPS mới</span>
                </div>
                <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-sm text-gray-700">Yêu cầu gia hạn VPS</span>
                </div>
                <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-sm text-gray-700">Yêu cầu nạp tiền</span>
                </div>
                <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-sm text-gray-700">Truy cập admin</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.classList.remove('border-cyan-500', 'text-cyan-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-cyan-500', 'text-cyan-600');
}
</script>

<?php
$content = ob_get_clean();
require_once 'includes/header.php';
?>