<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

// Get packages by category
$nat_packages = array_filter(fetchVpsPackages(), function($pkg) {
    return ($pkg['category'] ?? 'nat') === 'nat';
});

$cheap_packages = array_filter(fetchVpsPackages(), function($pkg) {
    return ($pkg['category'] ?? 'nat') === 'cheap';
});

$operating_systems = fetchOperatingSystems();

$page_title = 'Gói VPS - ' . SITE_NAME;
$page_description = 'Khám phá các gói VPS NAT và VPS Cheap chất lượng cao với giá cả phải chăng';

ob_start();
?>

<!-- Hero Section -->
<section class="gradient-primary text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Gói VPS Cao Cấp</h1>
            <p class="text-xl mb-8 text-teal-100 max-w-2xl mx-auto">
                Chọn giữa VPS NAT và VPS Cheap - Hiệu suất vượt trội với giá cả cạnh tranh
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-6 py-3">
                    <i class="fas fa-server mr-2"></i>
                    <span><?= count($nat_packages) ?> VPS NAT</span>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-6 py-3">
                    <i class="fas fa-dollar-sign mr-2"></i>
                    <span><?= count($cheap_packages) ?> VPS Cheap</span>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-6 py-3">
                    <i class="fas fa-shield-alt mr-2"></i>
                    <span>Bảo mật 99.9%</span>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-6 py-3">
                    <i class="fas fa-headset mr-2"></i>
                    <span>Hỗ trợ 24/7</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Tabs -->
<section class="py-8 bg-white sticky top-0 z-40 shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex flex-col sm:flex-row items-center justify-between">
            <div class="flex space-x-1 bg-gray-100 rounded-lg p-1 mb-4 sm:mb-0">
                <button onclick="switchCategory('nat')" id="natTab" 
                        class="category-tab px-6 py-3 rounded-lg font-semibold smooth-transition bg-white text-teal-600 shadow-sm">
                    <i class="fas fa-network-wired mr-2"></i>
                    VPS NAT
                    <span class="ml-2 bg-teal-100 text-teal-600 px-2 py-1 rounded-full text-xs"><?= count($nat_packages) ?></span>
                </button>
                <button onclick="switchCategory('cheap')" id="cheapTab" 
                        class="category-tab px-6 py-3 rounded-lg font-semibold smooth-transition text-gray-600 hover:text-gray-800">
                    <i class="fas fa-dollar-sign mr-2"></i>
                    VPS Cheap
                    <span class="ml-2 bg-gray-200 text-gray-600 px-2 py-1 rounded-full text-xs"><?= count($cheap_packages) ?></span>
                </button>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-filter mr-1"></i>
                    <span id="resultCount">Hiển thị <?= count($nat_packages) ?> gói</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VPS NAT Section -->
<section id="natSection" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Category Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-teal-100 rounded-full mb-4">
                <i class="fas fa-network-wired text-teal-600 text-2xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mb-4">VPS NAT</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                VPS NAT với địa chỉ IP chia sẻ, tiết kiệm chi phí, phù hợp cho website và ứng dụng cơ bản
            </p>
            <div class="flex flex-wrap justify-center gap-4 mt-6">
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span class="text-sm">IP chia sẻ</span>
                </div>
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span class="text-sm">Tiết kiệm chi phí</span>
                </div>
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span class="text-sm">Dễ sử dụng</span>
                </div>
            </div>
        </div>

        <!-- NAT Packages Grid -->
        <div id="natPackagesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if (!empty($nat_packages)): ?>
                <?php foreach ($nat_packages as $package): ?>
                    <div class="package-card bg-white rounded-2xl shadow-lg hover-lift overflow-hidden border-2 border-transparent hover:border-teal-200" 
                         data-category="nat"
                         data-price="<?= $package['selling_price'] ?>"
                         data-name="<?= htmlspecialchars($package['name']) ?>"
                         data-cpu="<?= htmlspecialchars($package['cpu']) ?>"
                         data-ram="<?= htmlspecialchars($package['ram']) ?>"
                         data-port_speed="<?= htmlspecialchars($package['port_speed']) ?>"
                         data-storage="<?= htmlspecialchars($package['storage']) ?>">
                        
                        <!-- Popular Badge -->
                        <?php if ($package['selling_price'] <= 100000): ?>
                            <div class="absolute top-4 right-4 bg-gradient-to-r from-red-500 to-pink-500 text-white px-3 py-1 rounded-full text-xs font-semibold z-10 shadow-lg">
                                <i class="fas fa-fire mr-1"></i>Phổ biến
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Badge -->
                        <div class="absolute top-4 left-4 bg-teal-600 text-white px-3 py-1 rounded-full text-xs font-semibold z-10 shadow-lg">
                            <i class="fas fa-network-wired mr-1"></i>NAT
                        </div>
                        
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-teal-600 to-teal-400 p-6 text-white text-center">
                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($package['name']) ?></h3>
                            <div class="text-3xl font-bold">
                                <?= formatPrice($package['selling_price']) ?>
                                <span class="text-sm font-normal">/tháng</span>
                            </div>
                            <?php if ($package['original_price'] > $package['selling_price']): ?>
                                <div class="text-sm line-through mt-1 opacity-75">
                                    <?= formatPrice($package['original_price']) ?>
                                </div>
                                <div class="text-xs bg-white/20 backdrop-blur-sm px-2 py-1 rounded-full mt-2 inline-block">
                                    Tiết kiệm <?= number_format((($package['original_price'] - $package['selling_price']) / $package['original_price']) * 100, 0) ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Features -->
                        <div class="p-6">
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-microchip text-teal-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['cpu']) ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-memory text-teal-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['ram']) ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-hdd text-teal-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['storage']) ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-wifi text-teal-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['bandwidth'] ?? 'Unlimited') ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-globe text-teal-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['port_speed']) ?></span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="space-y-3">
                                <a href="order.php?id=<?= $package['id'] ?>" 
                                   class="w-full bg-gradient-to-r from-teal-600 to-teal-400 hover:from-teal-700 hover:to-teal-500 text-white py-3 px-4 rounded-lg font-semibold smooth-transition hover-lift text-center block">
                                    <i class="fas fa-shopping-cart mr-2"></i>Đặt mua ngay
                                </a>
                                <button onclick="showPackageDetails(<?= htmlspecialchars(json_encode($package)) ?>)" 
                                        class="w-full border border-teal-500 hover:bg-teal-50 text-teal-600 py-3 px-4 rounded-lg font-semibold smooth-transition">
                                    <i class="fas fa-info-circle mr-2"></i>Xem chi tiết
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-network-wired text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-600 mb-2">Đang cập nhật gói VPS NAT</h3>
                    <p class="text-gray-500 mb-6">Vui lòng quay lại sau hoặc liên hệ hỗ trợ</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- VPS Cheap Section -->
<section id="cheapSection" class="py-16 bg-white hidden">
    <div class="container mx-auto px-4">
        <!-- Category Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mb-4">VPS Cheap</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                VPS giá rẻ với địa chỉ IP riêng, hiệu năng cao, phù hợp cho doanh nghiệp và dự án chuyên nghiệp
            </p>
            <div class="flex flex-wrap justify-center gap-4 mt-6">
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span class="text-sm">IP riêng</span>
                </div>
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span class="text-sm">Hiệu năng cao</span>
                </div>
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span class="text-sm">Giá siêu rẻ</span>
                </div>
            </div>
        </div>

        <!-- Cheap Packages Grid -->
        <div id="cheapPackagesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if (!empty($cheap_packages)): ?>
                <?php foreach ($cheap_packages as $package): ?>
                    <div class="package-card bg-white rounded-2xl shadow-lg hover-lift overflow-hidden border-2 border-transparent hover:border-green-200" 
                         data-category="cheap"
                         data-price="<?= $package['selling_price'] ?>"
                         data-name="<?= htmlspecialchars($package['name']) ?>"
                         data-cpu="<?= htmlspecialchars($package['cpu']) ?>"
                         data-ram="<?= htmlspecialchars($package['ram']) ?>"
                         data-port_speed="<?= htmlspecialchars($package['port_speed']) ?>"
                         data-storage="<?= htmlspecialchars($package['storage']) ?>">
                        
                        <!-- Popular Badge -->
                        <?php if ($package['selling_price'] <= 150000): ?>
                            <div class="absolute top-4 right-4 bg-gradient-to-r from-green-500 to-emerald-500 text-white px-3 py-1 rounded-full text-xs font-semibold z-10 shadow-lg">
                                <i class="fas fa-fire mr-1"></i>Giá tốt
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Badge -->
                        <div class="absolute top-4 left-4 bg-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold z-10 shadow-lg">
                            <i class="fas fa-dollar-sign mr-1"></i>Cheap
                        </div>
                        
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-green-600 to-green-400 p-6 text-white text-center">
                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($package['name']) ?></h3>
                            <div class="text-3xl font-bold">
                                <?= formatPrice($package['selling_price']) ?>
                                <span class="text-sm font-normal">/tháng</span>
                            </div>
                            <?php if ($package['original_price'] > $package['selling_price']): ?>
                                <div class="text-sm line-through mt-1 opacity-75">
                                    <?= formatPrice($package['original_price']) ?>
                                </div>
                                <div class="text-xs bg-white/20 backdrop-blur-sm px-2 py-1 rounded-full mt-2 inline-block">
                                    Tiết kiệm <?= number_format((($package['original_price'] - $package['selling_price']) / $package['original_price']) * 100, 0) ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Features -->
                        <div class="p-6">
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-microchip text-green-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['cpu']) ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-memory text-green-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['ram']) ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-hdd text-green-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['storage']) ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-wifi text-green-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['bandwidth'] ?? 'Unlimited') ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-globe text-green-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($package['port_speed']) ?></span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="space-y-3">
                                <a href="order.php?id=<?= $package['id'] ?>" 
                                   class="w-full bg-gradient-to-r from-green-600 to-green-400 hover:from-green-700 hover:to-green-500 text-white py-3 px-4 rounded-lg font-semibold smooth-transition hover-lift text-center block">
                                    <i class="fas fa-shopping-cart mr-2"></i>Đặt mua ngay
                                </a>
                                <button onclick="showPackageDetails(<?= htmlspecialchars(json_encode($package)) ?>)" 
                                        class="w-full border border-green-500 hover:bg-green-50 text-green-600 py-3 px-4 rounded-lg font-semibold smooth-transition">
                                    <i class="fas fa-info-circle mr-2"></i>Xem chi tiết
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-dollar-sign text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-600 mb-2">Đang cập nhật gói VPS Cheap</h3>
                    <p class="text-gray-500 mb-6">Vui lòng quay lại sau hoặc liên hệ hỗ trợ</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Package Details Modal -->
<div id="packageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800" id="modalTitle">Chi tiết gói VPS</h3>
                <button onclick="closePackageModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modalContent">
                <!-- Content will be dynamically inserted -->
            </div>
        </div>
    </div>
</div>

<script>
function switchCategory(category) {
    const natSection = document.getElementById('natSection');
    const cheapSection = document.getElementById('cheapSection');
    const natTab = document.getElementById('natTab');
    const cheapTab = document.getElementById('cheapTab');
    const resultCount = document.getElementById('resultCount');
    
    if (category === 'nat') {
        natSection.classList.remove('hidden');
        cheapSection.classList.add('hidden');
        
        natTab.classList.add('bg-white', 'text-teal-600', 'shadow-sm');
        natTab.classList.remove('text-gray-600', 'hover:text-gray-800');
        
        cheapTab.classList.remove('bg-white', 'text-green-600', 'shadow-sm');
        cheapTab.classList.add('text-gray-600', 'hover:text-gray-800');
        
        resultCount.textContent = `Hiển thị <?= count($nat_packages) ?> gói`;
    } else {
        natSection.classList.add('hidden');
        cheapSection.classList.remove('hidden');
        
        cheapTab.classList.add('bg-white', 'text-green-600', 'shadow-sm');
        cheapTab.classList.remove('text-gray-600', 'hover:text-gray-800');
        
        natTab.classList.remove('bg-white', 'text-teal-600', 'shadow-sm');
        natTab.classList.add('text-gray-600', 'hover:text-gray-800');
        
        resultCount.textContent = `Hiển thị <?= count($cheap_packages) ?> gói`;
    }
}

function showPackageDetails(package) {
    const modal = document.getElementById('packageModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    
    modalTitle.textContent = package.name;
    
    const categoryBadge = package.category === 'cheap' 
        ? '<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold"><i class="fas fa-dollar-sign mr-1"></i>VPS Cheap</span>'
        : '<span class="bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-sm font-semibold"><i class="fas fa-network-wired mr-1"></i>VPS NAT</span>';
    
    modalContent.innerHTML = `
        <div class="space-y-6">
            <div class="text-center">
                ${categoryBadge}
                <div class="text-3xl font-bold text-gray-800 mt-4">${formatPrice(package.selling_price)}/tháng</div>
                ${package.original_price > package.selling_price ? `
                    <div class="text-lg text-gray-500 line-through">${formatPrice(package.original_price)}/tháng</div>
                    <div class="text-sm text-green-600 font-semibold">Tiết kiệm ${Math.round(((package.original_price - package.selling_price) / package.original_price) * 100)}%</div>
                ` : ''}
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center text-gray-600 mb-2">
                        <i class="fas fa-microchip mr-2"></i>
                        <span class="text-sm font-medium">CPU</span>
                    </div>
                    <div class="text-lg font-semibold text-gray-800">${package.cpu}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center text-gray-600 mb-2">
                        <i class="fas fa-memory mr-2"></i>
                        <span class="text-sm font-medium">RAM</span>
                    </div>
                    <div class="text-lg font-semibold text-gray-800">${package.ram}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center text-gray-600 mb-2">
                        <i class="fas fa-hdd mr-2"></i>
                        <span class="text-sm font-medium">Ổ cứng</span>
                    </div>
                    <div class="text-lg font-semibold text-gray-800">${package.storage}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center text-gray-600 mb-2">
                        <i class="fas fa-wifi mr-2"></i>
                        <span class="text-sm font-medium">Băng thông</span>
                    </div>
                    <div class="text-lg font-semibold text-gray-800">${package.bandwidth || 'Unlimited'}</div>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center text-gray-600 mb-2">
                    <i class="fas fa-globe mr-2"></i>
                    <span class="text-sm font-medium">Vị trí</span>
                </div>
                <div class="text-lg font-semibold text-gray-800">${package.location}</div>
            </div>
            
            <div class="flex space-x-4">
                <a href="order.php?id=${package.id}" class="flex-1 bg-gradient-to-r ${package.category === 'cheap' ? 'from-green-600 to-green-400 hover:from-green-700 hover:to-green-500' : 'from-teal-600 to-teal-400 hover:from-teal-700 hover:to-teal-500'} text-white py-3 px-6 rounded-lg font-semibold smooth-transition text-center">
                    <i class="fas fa-shopping-cart mr-2"></i>Đặt mua ngay
                </a>
                <button onclick="closePackageModal()" class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-6 rounded-lg font-semibold smooth-transition">
                    Đóng
                </button>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closePackageModal() {
    document.getElementById('packageModal').classList.add('hidden');
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

// Close modal when clicking outside
document.getElementById('packageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePackageModal();
    }
});
</script>

<?php
$content = ob_get_clean();

include 'includes/header.php';
echo $content;
include 'includes/footer.php';
?>