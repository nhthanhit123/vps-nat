<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settings = [
        'site_name' => cleanInput($_POST['site_name'] ?? SITE_NAME),
        'site_description' => cleanInput($_POST['site_description'] ?? SITE_DESCRIPTION),
        'site_logo' => cleanInput($_POST['site_logo'] ?? ''),
        'site_favicon' => cleanInput($_POST['site_favicon'] ?? ''),
        'primary_color' => cleanInput($_POST['primary_color'] ?? '#16a34a'),
        'secondary_color' => cleanInput($_POST['secondary_color'] ?? '#22c55e'),
        'hero_title' => cleanInput($_POST['hero_title'] ?? ''),
        'hero_subtitle' => cleanInput($_POST['hero_subtitle'] ?? ''),
        'hero_cta_text' => cleanInput($_POST['hero_cta_text'] ?? ''),
        'hero_cta_url' => cleanInput($_POST['hero_cta_url'] ?? ''),
        'features_title' => cleanInput($_POST['features_title'] ?? ''),
        'features_subtitle' => cleanInput($_POST['features_subtitle'] ?? ''),
        'contact_email' => cleanInput($_POST['contact_email'] ?? 'support@vpsnat.com'),
        'contact_phone' => cleanInput($_POST['contact_phone'] ?? '1900 1234'),
        'contact_address' => cleanInput($_POST['contact_address'] ?? 'Hà Nội, Việt Nam'),
        'social_facebook' => cleanInput($_POST['social_facebook'] ?? ''),
        'social_telegram' => cleanInput($_POST['social_telegram'] ?? ''),
        'social_youtube' => cleanInput($_POST['social_youtube'] ?? ''),
        'footer_copyright' => cleanInput($_POST['footer_copyright'] ?? ''),
        'analytics_code' => cleanInput($_POST['analytics_code'] ?? ''),
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
        'maintenance_message' => cleanInput($_POST['maintenance_message'] ?? 'Website đang bảo trì. Vui lòng quay lại sau.')
    ];
    
    // Save settings to database or file
    saveWebsiteSettings($settings);
    
    $_SESSION['success_message'] = 'Cập nhật cài đặt thành công!';
    redirect('website_settings.php');
}

// Get current settings
$current_settings = getWebsiteSettings();

$page_title = 'Thiết lập Website';
$header_title = 'Thiết lập Website';
$header_description = 'Quản lý cài đặt và giao diện website';

ob_start();
?>

<!-- Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </p>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Settings Navigation -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Cài đặt</h3>
            </div>
            <nav class="p-2">
                <a href="#general" class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-lg mb-1">
                    <i class="fas fa-cog mr-2"></i>Tổng quan
                </a>
                <a href="#appearance" class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-lg mb-1">
                    <i class="fas fa-palette mr-2"></i>Giao diện
                </a>
                <a href="#content" class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-lg mb-1">
                    <i class="fas fa-edit mr-2"></i>Nội dung
                </a>
                <a href="#contact" class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-lg mb-1">
                    <i class="fas fa-address-card mr-2"></i>Liên hệ
                </a>
                <a href="#advanced" class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-lg">
                    <i class="fas fa-tools mr-2"></i>Nâng cao
                </a>
            </nav>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="lg:col-span-2">
        <form method="POST" class="space-y-6">
            <!-- General Settings -->
            <section id="general" class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-cog mr-2 text-green-600"></i>Tổng quan
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Cài đặt cơ bản của website</p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tên Website</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($current_settings['site_name'] ?? SITE_NAME) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả Website</label>
                        <textarea name="site_description" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"><?= htmlspecialchars($current_settings['site_description'] ?? SITE_DESCRIPTION) ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo URL</label>
                            <input type="url" name="site_logo" value="<?= htmlspecialchars($current_settings['site_logo'] ?? '') ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="https://example.com/logo.png">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Favicon URL</label>
                            <input type="url" name="site_favicon" value="<?= htmlspecialchars($current_settings['site_favicon'] ?? '') ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="https://example.com/favicon.ico">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Appearance Settings -->
            <section id="appearance" class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-palette mr-2 text-green-600"></i>Giao diện
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Tùy chỉnh màu sắc và giao diện</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Màu chính</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" name="primary_color" value="<?= htmlspecialchars($current_settings['primary_color'] ?? '#16a34a') ?>" 
                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                <input type="text" value="<?= htmlspecialchars($current_settings['primary_color'] ?? '#16a34a') ?>" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" readonly>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Màu phụ</label>
                            <div class="flex items-center space-x-2">
                                <input type="color" name="secondary_color" value="<?= htmlspecialchars($current_settings['secondary_color'] ?? '#22c55e') ?>" 
                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                <input type="text" value="<?= htmlspecialchars($current_settings['secondary_color'] ?? '#22c55e') ?>" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Xem trước</h4>
                        <div class="space-y-2">
                            <div class="h-8 rounded" style="background-color: <?= htmlspecialchars($current_settings['primary_color'] ?? '#16a34a') ?>"></div>
                            <div class="h-8 rounded" style="background-color: <?= htmlspecialchars($current_settings['secondary_color'] ?? '#22c55e') ?>"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Content Settings -->
            <section id="content" class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-edit mr-2 text-green-600"></i>Nội dung trang chủ
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Tùy chỉnh nội dung hiển thị trên trang chủ</p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề Hero</label>
                        <input type="text" name="hero_title" value="<?= htmlspecialchars($current_settings['hero_title'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="VPS CHUYÊN NGHIỆP">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề phụ Hero</label>
                        <input type="text" name="hero_subtitle" value="<?= htmlspecialchars($current_settings['hero_subtitle'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="GIÁ RẺ NHẤT VIỆT NAM">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nút CTA Text</label>
                            <input type="text" name="hero_cta_text" value="<?= htmlspecialchars($current_settings['hero_cta_text'] ?? '') ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Xem gói VPS">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nút CTA URL</label>
                            <input type="text" name="hero_cta_url" value="<?= htmlspecialchars($current_settings['hero_cta_url'] ?? '') ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="/packages.php">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề Features</label>
                        <input type="text" name="features_title" value="<?= htmlspecialchars($current_settings['features_title'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Tại sao chọn chúng tôi?">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề phụ Features</label>
                        <textarea name="features_subtitle" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Chúng tôi mang đến giải pháp VPS tối ưu với công nghệ hiện đại"><?= htmlspecialchars($current_settings['features_subtitle'] ?? '') ?></textarea>
                    </div>
                </div>
            </section>

            <!-- Contact Settings -->
            <section id="contact" class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-address-card mr-2 text-green-600"></i>Thông tin liên hệ
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Cài đặt thông tin liên hệ và mạng xã hội</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($current_settings['contact_email'] ?? 'support@vpsnat.com') ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Điện thoại</label>
                            <input type="text" name="contact_phone" value="<?= htmlspecialchars($current_settings['contact_phone'] ?? '1900 1234') ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ</label>
                        <input type="text" name="contact_address" value="<?= htmlspecialchars($current_settings['contact_address'] ?? 'Hà Nội, Việt Nam') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Facebook</label>
                            <input type="url" name="social_facebook" value="<?= htmlspecialchars($current_settings['social_facebook'] ?? '') ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="https://facebook.com/...">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telegram</label>
                            <input type="url" name="social_telegram" value="<?= htmlspecialchars($current_settings['social_telegram'] ?? '') ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="https://t.me/...">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">YouTube</label>
                            <input type="url" name="social_youtube" value="<?= htmlspecialchars($current_settings['social_youtube'] ?? '') ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="https://youtube.com/...">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Footer Copyright</label>
                        <input type="text" name="footer_copyright" value="<?= htmlspecialchars($current_settings['footer_copyright'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="© 2024 VPS NAT. All rights reserved.">
                    </div>
                </div>
            </section>

            <!-- Advanced Settings -->
            <section id="advanced" class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-tools mr-2 text-green-600"></i>Cài đặt nâng cao
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Các cài đặt kỹ thuật và bảo trì</p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Google Analytics Code</label>
                        <textarea name="analytics_code" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="<!-- Google Analytics -->"><?= htmlspecialchars($current_settings['analytics_code'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Chế độ bảo trì</h4>
                                <p class="text-sm text-gray-600">Khi bật, website sẽ hiển thị thông báo bảo trì</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="maintenance_mode" value="1" <?= ($current_settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Thông báo bảo trì</label>
                            <textarea name="maintenance_message" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"><?= htmlspecialchars($current_settings['maintenance_message'] ?? 'Website đang bảo trì. Vui lòng quay lại sau.') ?></textarea>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Save Button -->
            <div class="flex justify-end space-x-4">
                <a href="index.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 smooth-transition">
                    Hủy
                </a>
                <button type="submit" class="btn-primary text-white px-6 py-2 rounded-lg font-medium">
                    <i class="fas fa-save mr-2"></i>Lưu cài đặt
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Color picker sync
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    colorInput.addEventListener('input', function() {
        const textInput = this.parentElement.querySelector('input[type="text"]');
        if (textInput) {
            textInput.value = this.value;
        }
    });
});

// Smooth scroll to sections
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include 'includes/header.php';
?>