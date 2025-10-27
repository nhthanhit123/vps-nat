<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php?redirect=order.php?id=' . ($_GET['id'] ?? ''));
}

$package_id = $_GET['id'] ?? 0;
$package = getVpsPackage($package_id);

if (!$package) {
    redirect('packages.php');
}

$operating_systems = fetchOperatingSystems();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $os_id = $_POST['os_id'];
    $billing_cycle = $_POST['billing_cycle'];
    
    $errors = [];
    
    if (empty($os_id)) {
        $errors[] = 'Vui lòng chọn hệ điều hành';
    }
    
    if (empty($billing_cycle)) {
        $errors[] = 'Vui lòng chọn chu kỳ thanh toán';
    }
    
    $os = getOperatingSystem($os_id);
    
    if ($os && $os['min_ram_gb'] > 0) {
        $ram_gb = (int)filter_var($package['ram'], FILTER_SANITIZE_NUMBER_INT);
        if ($ram_gb < $os['min_ram_gb']) {
            $errors[] = "Hệ điều hành {$os['name']} yêu cầu tối thiểu {$os['min_ram_gb']}GB RAM";
        }
    }
    
    $total_price = calculatePrice($package['selling_price'], $billing_cycle);
    
    $user = getUser($_SESSION['user_id']);
    
    if ($user['balance'] < $total_price) {
        $errors[] = 'Số dư không đủ. Vui lòng nạp thêm tiền.';
    }
    
    if (empty($errors)) {
        $orderData = [
            'user_id' => $_SESSION['user_id'],
            'package_id' => $package_id,
            'os_id' => $os_id,
            'billing_cycle' => $billing_cycle,
            'price' => $package['selling_price'],
            'total_price' => $total_price,
            'status' => 'pending',
            'purchase_date' => date('Y-m-d'),
            'expiry_date' => date('Y-m-d', strtotime("+$billing_cycle months"))
        ];
        
        if (createVpsOrder($orderData)) {
            updateUserBalance($_SESSION['user_id'], -$total_price);
            
            $order = getOrder($orderData['user_id'], $orderData['user_id']);
            sendOrderNotification($order, $user, $package, $os);
            
            $_SESSION['success_message'] = 'Đặt hàng thành công! VPS của bạn sẽ được kích hoạt sớm.';
            redirect('services.php');
        } else {
            $errors[] = 'Đặt hàng thất bại. Vui lòng thử lại.';
        }
    }
}

$page_title = 'Đặt mua VPS - ' . SITE_NAME;
$page_description = 'Đặt mua VPS cao cấp với cấu hình mạnh mẽ';

ob_start();
?>

<!-- Breadcrumb -->
<section class="py-4 bg-white border-b">
    <div class="container mx-auto px-4">
        <nav class="flex items-center space-x-2 text-sm">
            <a href="index.php" class="text-gray-500 hover:text-red-600 smooth-transition">Trang chủ</a>
            <span class="text-gray-400">/</span>
            <a href="packages.php" class="text-gray-500 hover:text-red-600 smooth-transition">Gói VPS</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-900 font-medium">Đặt mua</span>
        </nav>
    </div>
</section>

<!-- Main Content -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Package Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm p-8 mb-8">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r <?= ($package['category'] ?? 'nat') !== 'cheap' ? 'from-teal-600 to-teal-400' : 'from-red-600 to-red-400' ?> rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-server text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($package['name']) ?></h2>
                                <div class="flex items-center space-x-2">
                                    <span class="bg-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-100 text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-800 px-3 py-1 rounded-full text-sm font-semibold">
                                        <i class="fas fa-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'dollar-sign' : 'network-wired' ?> mr-1"></i>
                                        VPS <?= ($package['category'] ?? 'nat') !== 'cheap' ? 'Cheap' : 'NAT' ?>
                                    </span>
                                    <span class="text-gray-600">Gói VPS cao cấp</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="space-y-4">
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-microchip text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">CPU</div>
                                        <div class="font-semibold"><?= htmlspecialchars($package['cpu']) ?></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-memory text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">RAM</div>
                                        <div class="font-semibold"><?= htmlspecialchars($package['ram']) ?></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-hdd text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Ổ cứng</div>
                                        <div class="font-semibold"><?= htmlspecialchars($package['storage']) ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-wifi text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Băng thông</div>
                                        <div class="font-semibold"><?= htmlspecialchars($package['bandwidth'] ?? 'Unlimited') ?></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-globe text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Tốc độ port</div>
                                        <div class="font-semibold"><?= htmlspecialchars($package['port_speed']) ?></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-shield-alt text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">IP</div>
                                        <div class="font-semibold"><?= htmlspecialchars($package['ip']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    </div>
                    
                    <!-- Order Form -->
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Thông tin đặt hàng</h3>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold text-red-800 mb-2">Lỗi đặt hàng</h4>
                                        <ul class="text-sm text-red-700 space-y-1">
                                            <?php foreach ($errors as $error): ?>
                                                <li>• <?= htmlspecialchars($error) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="orderForm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-desktop mr-1 text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600"></i>
                                        Hệ điều hành <span class="text-red-500">*</span>
                                    </label>
                                    <select name="os_id" id="os_id" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-500 focus:border-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-500">
                                        <option value="">-- Chọn hệ điều hành --</option>
                                        <?php foreach ($operating_systems as $os): ?>
                                            <option value="<?= $os['id'] ?>" 
                                                    data-min-ram="<?= $os['min_ram_gb'] ?>">
                                                <?= htmlspecialchars($os['name']) ?>
                                                <?php if ($os['min_ram_gb'] > 1): ?>
                                                    (Yêu cầu tối thiểu <?= $os['min_ram_gb'] ?>GB RAM)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar mr-1 text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600"></i>
                                        Chu kỳ thanh toán <span class="text-red-500">*</span>
                                    </label>
                                    <select name="billing_cycle" id="billing_cycle" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-500 focus:border-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-500">
                                        <option value="">-- Chọn chu kỳ --</option>
                                        <option value="1">1 tháng</option>
                                        <option value="6">6 tháng</option>
                                        <option value="12">12 tháng</option>
                                        <option value="24">24 tháng</option>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600 to-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-400 hover:from-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-700 hover:to-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-500 text-white py-4 px-6 rounded-lg font-semibold smooth-transition hover-lift">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Xác nhận đặt hàng
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Tóm tắt đơn hàng</h3>
                        
                        <!-- Package Info -->
                        <div class="bg-gradient-to-br from-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-50 to-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'emerald' : 'blue' ?>-50 rounded-lg p-4 mb-6">
                            <div class="text-center mb-4">
                                <div class="text-2xl font-bold text-<?= ($package['category'] ?? 'nat') !== 'cheap' ? 'teal' : 'red' ?>-600">
                                    <?= formatPrice($package['selling_price']) ?>
                                </div>
                                <div class="text-sm text-gray-600">/tháng</div>
                            </div>
                            <?php if ($package['original_price'] > $package['selling_price']): ?>
                                <div class="text-center">
                                    <span class="text-sm text-gray-500 line-through">
                                        <?= formatPrice($package['original_price']) ?>
                                    </span>
                                    <div class="text-xs bg-teal-100 text-teal-800 px-2 py-1 rounded-full mt-1 inline-block">
                                        Tiết kiệm <?= number_format((($package['original_price'] - $package['selling_price']) / $package['original_price']) * 100, 0) ?>%
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Price Breakdown -->
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Giá gói VPS:</span>
                                <span id="package_price"><?= formatPrice($package['selling_price']) ?>/tháng</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Chu kỳ:</span>
                                <span id="cycle_text" class="font-medium">-</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Chiết khấu:</span>
                                <span id="discount" class="text-teal-600 font-medium">-</span>
                            </div>
                            <div class="border-t pt-3">
                                <div class="flex justify-between text-lg font-bold text-gray-900">
                                    <span>Tổng cộng:</span>
                                    <span id="total_price" class="text-red-600">0 VNĐ</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Balance -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-wallet text-yellow-600 mr-2"></i>
                                    <span class="text-sm font-medium text-yellow-800">
                                        Số dư tài khoản:
                                    </span>
                                </div>
                                <span class="text-lg font-bold text-yellow-800">
                                    <?= formatPrice($_SESSION['balance']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Benefits -->
                        <div class="border-t pt-6">
                            <h4 class="font-semibold text-gray-900 mb-3">Lợi ích khi đặt hàng</h4>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-teal-500 mr-2 mt-0.5"></i>
                                    <span>Kích hoạt tức thì sau khi thanh toán</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-teal-500 mr-2 mt-0.5"></i>
                                    <span>Hỗ trợ kỹ thuật 24/7</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-teal-500 mr-2 mt-0.5"></i>
                                    <span>Đảm bảo uptime 99.9%</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-teal-500 mr-2 mt-0.5"></i>
                                    <span>Hoàn tiền trong 7 ngày</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const packagePrice = <?= $package['selling_price'] ?>;
const packageRam = <?= (int)filter_var($package['ram'], FILTER_SANITIZE_NUMBER_INT) ?>;

document.getElementById('billing_cycle').addEventListener('change', updatePrice);
document.getElementById('os_id').addEventListener('change', checkRamRequirement);

function updatePrice() {
    const cycle = document.getElementById('billing_cycle').value;
    const totalPriceEl = document.getElementById('total_price');
    const cycleTextEl = document.getElementById('cycle_text');
    const discountEl = document.getElementById('discount');
    
    if (!cycle) {
        totalPriceEl.textContent = '0 VNĐ';
        cycleTextEl.textContent = '-';
        discountEl.textContent = '-';
        return;
    }
    
    let totalPrice = packagePrice;
    let cycleText = '';
    let discount = 0;
    
    switch(cycle) {
        case '1':
            cycleText = '1 tháng';
            break;
        case '6':
            totalPrice = packagePrice * 6;
            cycleText = '6 tháng';
            discount = '5%';
            break;
        case '12':
            totalPrice = packagePrice * 12;
            cycleText = '12 tháng';
            discount = '10%';
            break;
        case '24':
            totalPrice = packagePrice * 24;
            cycleText = '24 tháng';
            discount = '15%';
            break;
    }
    
    totalPriceEl.textContent = formatPrice(totalPrice);
    cycleTextEl.textContent = cycleText;
    discountEl.textContent = discount || '0%';
}

function checkRamRequirement() {
    const osSelect = document.getElementById('os_id');
    const selectedOption = osSelect.options[osSelect.selectedIndex];
    const minRam = parseInt(selectedOption.dataset.minRam) || 0;
    
    if (minRam > 0 && packageRam < minRam) {
        osSelect.setCustomValidity(`Hệ điều hành này yêu cầu tối thiểu ${minRam}GB RAM`);
        showToast(`Hệ điều hành này yêu cầu tối thiểu ${minRam}GB RAM`, 'error');
    } else {
        osSelect.setCustomValidity('');
    }
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
}

// Initialize
updatePrice();

// Form validation
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const billingCycle = document.getElementById('billing_cycle').value;
    const osId = document.getElementById('os_id').value;
    
    if (!billingCycle || !osId) {
        e.preventDefault();
        showToast('Vui lòng điền đầy đủ thông tin', 'error');
        return false;
    }
    
    const totalPrice = parseInt(document.getElementById('total_price').textContent.replace(/[^\d]/g, ''));
    <?php
    $user = getUserByUsername($_SESSION['username']);
    ?>
    const userBalance = <?=$user['balance'] ?? 0;?>;
    
    if (totalPrice > userBalance) {
        e.preventDefault();
        showToast('Số dư không đủ. Vui lòng nạp thêm tiền.', 'error');
        return false;
    }
});
</script>

<?php
$content = ob_get_clean();
include 'includes/header.php';
?>