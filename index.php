<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

// Only fetch packages from database, no auto-sync
$all_packages = fetchVpsPackages();
$nat_packages = array_filter($all_packages, function($pkg) {
    return ($pkg['category'] ?? 'nat') === 'nat';
});
$cheap_packages = array_filter($all_packages, function($pkg) {
    return ($pkg['category'] ?? 'nat') === 'cheap';
});

$page_title = 'Trang chủ - ' . SITE_NAME;
$page_description = 'Dịch vụ VPS chất lượng cao với giá cả phải chăng';

ob_start();
?>

<!-- Hero Section -->
<section class="gradient-primary text-white py-20 relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 float-animation">
                VPS CHUYÊN NGHIỆP
                <span class="block text-3xl md:text-4xl mt-2 text-teal-100">GIÁ RẺ NHẤT VIỆT NAM</span>
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-teal-50 max-w-2xl mx-auto">
                Cung cấp dịch vụ VPS chất lượng cao với tốc độ vượt trội, bảo mật tối đa và hỗ trợ 24/7
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="packages.php" class="bg-white text-teal-600 hover:bg-gray-100 px-8 py-4 rounded-lg font-semibold smooth-transition hover-lift text-lg">
                    <i class="fas fa-rocket mr-2"></i>Xem gói VPS
                </a>
                <a href="register.php" class="border-2 border-white hover:bg-white hover:text-teal-600 text-white px-8 py-4 rounded-lg font-semibold smooth-transition text-lg">
                    <i class="fas fa-user-plus mr-2"></i>Đăng ký ngay
                </a>
            </div>
        </div>
    </div>
    
    <!-- Floating elements -->
    <div class="absolute top-10 left-10 w-20 h-20 bg-white opacity-10 rounded-full float-animation"></div>
    <div class="absolute bottom-10 right-10 w-32 h-32 bg-white opacity-5 rounded-full float-animation" style="animation-delay: 1s;"></div>
    <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-white opacity-5 rounded-full float-animation" style="animation-delay: 2s;"></div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Tại sao chọn chúng tôi?</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Chúng tôi mang đến giải pháp VPS tối ưu với công nghệ hiện đại và đội ngũ chuyên nghiệp
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center p-8 bg-gradient-to-br from-teal-50 to-white rounded-2xl hover-lift">
                <div class="w-16 h-16 bg-gradient-to-r from-teal-600 to-teal-400 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-tachometer-alt text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3 text-gray-800">Tốc độ Vượt Trội</h3>
                <p class="text-gray-600">SSD NVMe với tốc độ đọc ghi vượt trội, mạng 1Gbps</p>
            </div>
            
            <div class="text-center p-8 bg-gradient-to-br from-teal-50 to-white rounded-2xl hover-lift">
                <div class="w-16 h-16 bg-gradient-to-r from-teal-600 to-teal-400 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3 text-gray-800">Bảo Mật Tối Đa</h3>
                <p class="text-gray-600">Firewall mạnh mẽ, DDoS protection và backup hàng ngày</p>
            </div>
            
            <div class="text-center p-8 bg-gradient-to-br from-teal-50 to-white rounded-2xl hover-lift">
                <div class="w-16 h-16 bg-gradient-to-r from-teal-600 to-teal-400 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-headset text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3 text-gray-800">Hỗ Trợ 24/7</h3>
                <p class="text-gray-600">Đội ngũ chuyên nghiệp luôn sẵn sàng hỗ trợ mọi lúc</p>
            </div>
            
            <div class="text-center p-8 bg-gradient-to-br from-teal-50 to-white rounded-2xl hover-lift">
                <div class="w-16 h-16 bg-gradient-to-r from-teal-600 to-teal-400 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-dollar-sign text-white text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3 text-gray-800">Giá Rẻ Nhất</h3>
                <p class="text-gray-600">Cam kết giá tốt nhất thị trường với chất lượng hàng đầu</p>
            </div>
        </div>
    </div>
</section>

<!-- VPS Categories Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Lựa chọn VPS phù hợp</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Chúng tôi cung cấp 2 loại VPS với đặc điểm và giá thành khác nhau
            </p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
            <!-- VPS NAT Card -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden hover-lift">
                <div class="bg-gradient-to-r from-teal-600 to-teal-400 p-8 text-white text-center relative">
                    <div class="absolute top-4 right-4 bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-fire mr-1"></i>Phổ biến
                    </div>
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-network-wired text-3xl"></i>
                    </div>
                    <h3 class="text-3xl font-bold mb-2">VPS NAT</h3>
                    <p class="text-teal-100">IP chia sẻ - Tiết kiệm chi phí</p>
                </div>
                
                <div class="p-8">
                    <div class="text-center mb-6">
                        <div class="text-4xl font-bold text-teal-600 mb-2">
                            <?php 
                            $nat_min_price = !empty($nat_packages) ? min(array_column($nat_packages, 'selling_price')) : 0;
                            echo formatPrice($nat_min_price);
                            ?>
                        </div>
                        <div class="text-gray-500">/tháng bắt đầu</div>
                    </div>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-teal-500 mr-3"></i>
                            <span>IP chia sẻ tiết kiệm chi phí</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-teal-500 mr-3"></i>
                            <span>Phù hợp website cơ bản</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-teal-500 mr-3"></i>
                            <span>Dễ dàng quản lý</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-teal-500 mr-3"></i>
                            <span>Bandwidth không giới hạn</span>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="packages.php#nat" class="w-full bg-gradient-to-r from-teal-600 to-teal-400 hover:from-teal-700 hover:to-teal-500 text-white py-3 px-6 rounded-lg font-semibold smooth-transition hover-lift text-center block">
                            <i class="fas fa-eye mr-2"></i>Xem gói VPS NAT
                        </a>
                        <div class="text-center text-sm text-gray-500">
                            <i class="fas fa-server mr-1"></i>
                            <?= count($nat_packages) ?> gói có sẵn
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- VPS Cheap Card -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden hover-lift">
                <div class="bg-gradient-to-r from-green-600 to-green-400 p-8 text-white text-center relative">
                    <div class="absolute top-4 right-4 bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-bolt mr-1"></i>Hiệu năng cao
                    </div>
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-dollar-sign text-3xl"></i>
                    </div>
                    <h3 class="text-3xl font-bold mb-2">VPS Cheap</h3>
                    <p class="text-green-100">IP riêng - Hiệu năng vượt trội</p>
                </div>
                
                <div class="p-8">
                    <div class="text-center mb-6">
                        <div class="text-4xl font-bold text-green-600 mb-2">
                            <?php 
                            $cheap_min_price = !empty($cheap_packages) ? min(array_column($cheap_packages, 'selling_price')) : 0;
                            echo formatPrice($cheap_min_price);
                            ?>
                        </div>
                        <div class="text-gray-500">/tháng bắt đầu</div>
                    </div>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>IP riêng độc lập</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Hiệu năng cao cấp</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Phù hợp doanh nghiệp</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Tối ưu cho ứng dụng lớn</span>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="packages.php#cheap" class="w-full bg-gradient-to-r from-green-600 to-green-400 hover:from-green-700 hover:to-green-500 text-white py-3 px-6 rounded-lg font-semibold smooth-transition hover-lift text-center block">
                            <i class="fas fa-eye mr-2"></i>Xem gói VPS Cheap
                        </a>
                        <div class="text-center text-sm text-gray-500">
                            <i class="fas fa-server mr-1"></i>
                            <?= count($cheap_packages) ?> gói có sẵn
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Popular Packages Preview -->
        <div class="text-center mb-12">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Gói nổi bật</h3>
            <p class="text-gray-600">Các gói VPS được lựa chọn nhiều nhất</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php 
            $featured_packages = array_merge(
                array_slice($nat_packages, 0, 2), 
                array_slice($cheap_packages, 0, 2)
            );
            ?>
            <?php foreach ($featured_packages as $package): ?>
                <div class="bg-white rounded-2xl shadow-lg hover-lift overflow-hidden">
                    <div class="bg-gradient-to-r <?= ($package['category'] ?? 'nat') === 'cheap' ? 'from-green-600 to-green-400' : 'from-teal-600 to-teal-400' ?> p-4 text-white text-center relative">
                        <div class="absolute top-2 left-2 bg-white/20 backdrop-blur-sm px-2 py-1 rounded text-xs">
                            <?= ($package['category'] ?? 'nat') === 'cheap' ? 'Cheap' : 'NAT' ?>
                        </div>
                        <h4 class="text-lg font-bold"><?= htmlspecialchars($package['name']) ?></h4>
                    </div>
                    
                    <div class="p-6">
                        <div class="text-center mb-4">
                            <div class="text-2xl font-bold <?= ($package['category'] ?? 'nat') === 'cheap' ? 'text-green-600' : 'text-teal-600' ?>">
                                <?= formatPrice($package['selling_price']) ?>
                            </div>
                            <div class="text-gray-500 text-sm">/tháng</div>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-gray-600 text-sm">
                                <i class="fas fa-microchip w-4 text-<?= ($package['category'] ?? 'nat') === 'cheap' ? 'green-500' : 'teal-500' ?>"></i>
                                <span class="ml-2"><?= htmlspecialchars($package['cpu']) ?></span>
                            </div>
                            <div class="flex items-center text-gray-600 text-sm">
                                <i class="fas fa-memory w-4 text-<?= ($package['category'] ?? 'nat') === 'cheap' ? 'green-500' : 'teal-500' ?>"></i>
                                <span class="ml-2"><?= htmlspecialchars($package['ram']) ?></span>
                            </div>
                            <div class="flex items-center text-gray-600 text-sm">
                                <i class="fas fa-hdd w-4 text-<?= ($package['category'] ?? 'nat') === 'cheap' ? 'green-500' : 'teal-500' ?>"></i>
                                <span class="ml-2"><?= htmlspecialchars($package['storage']) ?></span>
                            </div>
                        </div>
                        
                        <?php
                        $packageId = $conn->query("SELECT id FROM vps_packages WHERE name = '{$package['name']}'")->fetch_array()['id'] ?? 0;
                        ?>
                        <a href="/order.php?id=<?= $packageId ?>" class="w-full bg-gradient-to-r <?= ($package['category'] ?? 'nat') === 'cheap' ? 'from-green-600 to-green-400 hover:from-green-700 hover:to-green-500' : 'from-teal-600 to-teal-400 hover:from-teal-700 hover:to-teal-500' ?> text-white py-2 px-4 rounded-lg font-semibold smooth-transition hover-lift text-center block text-sm">
                            <i class="fas fa-shopping-cart mr-1"></i>Đặt mua
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($nat_packages) && empty($cheap_packages)): ?>
            <div class="text-center py-12">
                <i class="fas fa-server text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-600 mb-2">Đang cập nhật gói VPS</h3>
                <p class="text-gray-500 mb-6">Vui lòng quay lại sau hoặc liên hệ hỗ trợ</p>
                <a href="contact.php" class="bg-gradient-to-r from-teal-600 to-teal-400 hover:from-teal-700 hover:to-teal-500 text-white px-6 py-3 rounded-lg font-semibold smooth-transition">
                    <i class="fas fa-phone mr-2"></i>Liên hệ hỗ trợ
                </a>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-12">
            <a href="packages.php" class="inline-flex items-center text-teal-600 hover:text-teal-700 font-semibold text-lg">
                <i class="fas fa-th mr-2"></i>Xem tất cả gói VPS
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-20 gradient-secondary text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-4">Con số biết nói</h2>
            <p class="text-xl text-teal-100">Sự tin tưởng của khách hàng là động lực của chúng tôi</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="hover-lift">
                <div class="text-5xl font-bold mb-2 pulse-animation">500+</div>
                <div class="text-teal-100">Khách hàng tin dùng</div>
            </div>
            <div class="hover-lift">
                <div class="text-5xl font-bold mb-2 pulse-animation">1000+</div>
                <div class="text-teal-100">VPS đang hoạt động</div>
            </div>
            <div class="hover-lift">
                <div class="text-5xl font-bold mb-2 pulse-animation">99.9%</div>
                <div class="text-teal-100">Uptime cam kết</div>
            </div>
            <div class="hover-lift">
                <div class="text-5xl font-bold mb-2 pulse-animation">24/7</div>
                <div class="text-teal-100">Hỗ trợ kỹ thuật</div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Khách hàng nói gì?</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Những đánh giá từ khách hàng thực tế về dịch vụ của chúng tôi
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-gray-50 p-8 rounded-2xl hover-lift">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-teal-600 to-teal-400 rounded-full flex items-center justify-center text-white font-bold mr-4">
                        TN
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Trần Nam</h4>
                        <div class="text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"Dịch vụ VPS rất ổn định, tốc độ nhanh và hỗ trợ nhiệt tình. Rất hài lòng!"</p>
            </div>
            
            <div class="bg-gray-50 p-8 rounded-2xl hover-lift">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-teal-600 to-teal-400 rounded-full flex items-center justify-center text-white font-bold mr-4">
                        PM
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Phương Mai</h4>
                        <div class="text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"Giá cả rất hợp lý so với chất lượng. Đã dùng 6 tháng và rất ổn định."</p>
            </div>
            
            <div class="bg-gray-50 p-8 rounded-2xl hover-lift">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-teal-600 to-teal-400 rounded-full flex items-center justify-center text-white font-bold mr-4">
                        LQ
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Lê Quân</h4>
                        <div class="text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"Hỗ trợ kỹ thuật rất nhanh chóng, giải quyết vấn đề hiệu quả. Highly recommend!"</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 gradient-primary text-white text-center">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-4xl font-bold mb-6">Sẵn sàng nâng cấp hệ thống?</h2>
            <p class="text-xl mb-8 text-teal-100">
                Đăng ký ngay hôm nay để nhận ưu đãi đặc biệt và trải nghiệm dịch vụ VPS chuyên nghiệp
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="packages.php" class="bg-white text-teal-600 hover:bg-gray-100 px-8 py-4 rounded-lg font-semibold smooth-transition hover-lift text-lg">
                    <i class="fas fa-rocket mr-2"></i>Bắt đầu ngay
                </a>
                <a href="#" class="border-2 border-white hover:bg-white hover:text-teal-600 text-white px-8 py-4 rounded-lg font-semibold smooth-transition text-lg">
                    <i class="fas fa-phone mr-2"></i>Tư vấn miễn phí
                </a>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include 'includes/header.php';
?>