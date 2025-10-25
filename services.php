<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php?redirect=services.php');
}

// Update session balance with latest data from database
$user = getUser($_SESSION['user_id']);
if ($user) {
    $_SESSION['balance'] = $user['balance'];
}

$orders = getUserOrders($_SESSION['user_id']);

if (isset($_GET['action']) && $_GET['action'] == 'renew' && isset($_GET['id'])) {
    $order_id = $_GET['id'];
    $order = getOrder($order_id, $_SESSION['user_id']);
    
    if (!$order) {
        redirect('services.php');
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $months = $_POST['months'];
        $renewal_price = calculatePrice($order['price'], $months);
        
        $user = getUser($_SESSION['user_id']);
        
        if ($user['balance'] < $renewal_price) {
            $error = 'Số dư không đủ. Vui lòng nạp thêm tiền.';
        } else {
            // Calculate new expiry date
            $current_expiry = $order['expiry_date'];
            if (empty($current_expiry) || $current_expiry === '0000-00-00') {
                // If no current expiry, start from today
                $new_expiry_date = date('Y-m-d', strtotime("+$months months"));
            } else {
                // If current expiry is in the past, start from today
                $today = date('Y-m-d');
                if (strtotime($current_expiry) < strtotime($today)) {
                    $new_expiry_date = date('Y-m-d', strtotime("+$months months"));
                } else {
                    // Extend from current expiry
                    $new_expiry_date = date('Y-m-d', strtotime($current_expiry . " +$months months"));
                }
            }
            
            $renewalData = [
                'order_id' => $order_id,
                'user_id' => $_SESSION['user_id'],
                'months' => $months,
                'price' => $renewal_price,
                'old_expiry_date' => $current_expiry,
                'new_expiry_date' => $new_expiry_date,
                'status' => 'completed'
            ];
            
            if (createRenewal($renewalData)) {
                updateUserBalance($_SESSION['user_id'], -$renewal_price);
                
                updateOrder($order_id, [
                    'expiry_date' => $new_expiry_date,
                    'status' => 'active'
                ]);
                
                // Update session balance
                $_SESSION['balance'] = $user['balance'] - $renewal_price;
                
                $_SESSION['success_message'] = 'Gia hạn thành công!';
                sendRenewalNotification($renewalData, $order, $user);
                redirect('services.php');
            } else {
                $error = 'Gia hạn thất bại. Vui lòng thử lại.';
            }
        }
    }
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

$page_title = 'Quản lý dịch vụ - ' . SITE_NAME;
$page_description = 'Quản lý và gia hạn các dịch vụ VPS của bạn';

ob_start();
?>

<!-- Hero Section -->
<section class="gradient-primary text-white py-12 relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Quản lý dịch vụ VPS</h1>
            <p class="text-lg text-teal-100">Quản lý và gia hạn các dịch vụ VPS của bạn</p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- User Balance Card -->
        <div class="bg-gradient-to-r from-teal-600 to-teal-400 rounded-2xl shadow-lg p-8 mb-8 text-white">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <h3 class="text-xl font-semibold mb-2">Số dư tài khoản</h3>
                    <p class="text-4xl font-bold"><?= formatPrice($_SESSION['balance']) ?></p>
                </div>
                <div class="flex space-x-4">
                    <a href="deposit.php" class="bg-white text-teal-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold smooth-transition hover-lift">
                        <i class="fas fa-plus-circle mr-2"></i>Nạp tiền
                    </a>
                    <a href="packages.php" class="border-2 border-white hover:bg-white hover:text-teal-600 text-white px-6 py-3 rounded-lg font-semibold smooth-transition">
                        <i class="fas fa-shopping-cart mr-2"></i>Mua VPS mới
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            <?= htmlspecialchars($success_message) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            <?= htmlspecialchars($error) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (empty($orders)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-sm p-16 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-server text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-600 mb-4">Chưa có dịch vụ nào</h3>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Bạn chưa mua gói VPS nào. Hãy chọn gói phù hợp với nhu cầu của bạn.</p>
                <a href="packages.php" class="bg-gradient-to-r from-teal-600 to-teal-400 hover:from-teal-700 hover:to-teal-500 text-white py-3 px-8 rounded-lg font-semibold smooth-transition hover-lift">
                    <i class="fas fa-shopping-cart mr-2"></i>Xem gói VPS
                </a>
            </div>
        <?php else: ?>
            <!-- Services List -->
            <div class="space-y-6">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover-lift">
                        <div class="p-8">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-4">
                                        <h3 class="text-2xl font-bold text-gray-900 mr-4">
                                            <?= htmlspecialchars($order['package_name']) ?>
                                        </h3>
                                        <span class="px-4 py-2 rounded-full text-sm font-semibold
                                            <?php
                                            switch($order['status']) {
                                                case 'active':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'expired':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                case 'cancelled':
                                                    echo 'bg-gray-100 text-gray-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php
                                            switch($order['status']) {
                                                case 'active':
                                                    echo 'Đang hoạt động';
                                                    break;
                                                case 'pending':
                                                    echo 'Chờ kích hoạt';
                                                    break;
                                                case 'expired':
                                                    echo 'Hết hạn';
                                                    break;
                                                case 'cancelled':
                                                    echo 'Đã hủy';
                                                    break;
                                                default:
                                                    echo $order['status'];
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex items-center mb-2">
                                                <i class="fas fa-desktop text-teal-600 mr-2"></i>
                                                <span class="text-sm text-gray-500">Hệ điều hành</span>
                                            </div>
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($order['os_name']) ?></p>
                                        </div>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex items-center mb-2">
                                                <i class="fas fa-calendar text-teal-600 mr-2"></i>
                                                <span class="text-sm text-gray-500">Chu kỳ</span>
                                            </div>
                                            <p class="font-medium text-gray-900"><?= $order['billing_cycle'] ?> tháng</p>
                                        </div>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex items-center mb-2">
                                                <i class="fas fa-tag text-teal-600 mr-2"></i>
                                                <span class="text-sm text-gray-500">Giá</span>
                                            </div>
                                            <p class="font-medium text-gray-900"><?= formatPrice($order['total_price']) ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php if ($order['status'] == 'active' && $order['ip_address']): ?>
                                        <div class="bg-gradient-to-r from-teal-50 to-blue-50 rounded-lg p-6 mb-6">
                                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                                <i class="fas fa-key text-teal-600 mr-2"></i>
                                                Thông tin đăng nhập
                                            </h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <span class="text-sm text-gray-500">IP Address:</span>
                                                    <p class="font-mono font-medium text-gray-900"><?= htmlspecialchars($order['ip_address']) ?></p>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-500">Username:</span>
                                                    <p class="font-mono font-medium text-gray-900"><?= htmlspecialchars($order['username'] ?? 'root') ?></p>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-500">Password:</span>
                                                    <div class="flex items-center">
                                                        <p class="font-mono font-medium text-gray-900 mr-2" id="password-<?= $order['id'] ?>">
                                                            <?= $order['password'] ? str_repeat('*', strlen($order['password'])) : 'N/A' ?>
                                                        </p>
                                                        <?php if ($order['password']): ?>
                                                            <button onclick="togglePassword(<?= $order['id'] ?>, '<?= htmlspecialchars($order['password']) ?>')" 
                                                                    class="text-teal-600 hover:text-teal-700 smooth-transition">
                                                                <i class="fas fa-eye" id="eye-<?= $order['id'] ?>"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-500">Ngày hết hạn:</span>
                                                    <p class="font-medium text-gray-900"><?= formatDate($order['expiry_date']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-calendar-alt mr-2"></i>
                                        Mua ngày: <?= formatDate($order['purchase_date']) ?>
                                        <?php if ($order['expiry_date']): ?>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-clock mr-2"></i>
                                            Hết hạn: <?= formatDate($order['expiry_date']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col space-y-3 mt-6 lg:mt-0 lg:ml-6">
                                    <?php if ($order['status'] == 'active'): ?>
                                        <button onclick="showRenewalModal(<?= $order['id'] ?>, <?= $order['price'] ?>, '<?= htmlspecialchars($order['package_name']) ?>', '<?= $order['expiry_date'] ?>')" 
                                                class="bg-green-500 hover:bg-green-600 text-white py-3 px-6 rounded-lg font-semibold smooth-transition hover-lift">
                                            <i class="fas fa-redo mr-2"></i>Gia hạn
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="showOrderDetails(<?= $order['id'] ?>)" 
                                            class="border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-6 rounded-lg font-semibold smooth-transition">
                                        <i class="fas fa-info-circle mr-2"></i>Chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Renewal Modal -->
<div id="renewalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Gia hạn VPS</h3>
                <button onclick="closeRenewalModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" id="renewalForm">
                <input type="hidden" name="order_id" id="renewal_order_id">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Gói dịch vụ
                    </label>
                    <p class="font-medium text-gray-900 p-3 bg-gray-50 rounded-lg" id="renewal_package_name"></p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hết hạn hiện tại
                    </label>
                    <p class="font-medium text-gray-900 p-3 bg-gray-50 rounded-lg" id="renewal_current_expiry"></p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Chọn thời gian gia hạn *
                    </label>
                    <select name="months" id="renewal_months" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <option value="">-- Chọn thời gian --</option>
                        <option value="1">1 tháng</option>
                        <option value="6">6 tháng (giảm 5%)</option>
                        <option value="12">12 tháng (giảm 10%)</option>
                        <option value="24">24 tháng (giảm 15%)</option>
                    </select>
                </div>
                
                <div class="mb-6 p-4 bg-gradient-to-r from-teal-50 to-blue-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Tổng tiền:</span>
                        <span class="text-xl font-bold text-teal-600" id="renewal_total_price">0 VNĐ</span>
                    </div>
                </div>
                
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-wallet text-yellow-600 mr-2"></i>
                        <span class="text-sm font-medium text-yellow-800">
                            Số dư: <?= formatPrice($_SESSION['balance']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-teal-600 to-teal-400 hover:from-teal-700 hover:to-teal-500 text-white py-3 px-4 rounded-lg font-semibold smooth-transition hover-lift">
                        <i class="fas fa-check mr-2"></i>Xác nhận gia hạn
                    </button>
                    <button type="button" onclick="closeRenewalModal()" 
                            class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-4 rounded-lg font-semibold smooth-transition">
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Chi tiết đơn hàng</h3>
                <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="orderDetailsContent"></div>
            
            <div class="mt-6">
                <button onclick="closeDetailsModal()" 
                        class="w-full border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-4 rounded-lg font-semibold smooth-transition">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPrice = 0;

function showRenewalModal(orderId, price, packageName, currentExpiry) {
    currentPrice = price;
    document.getElementById('renewal_order_id').value = orderId;
    document.getElementById('renewal_package_name').textContent = packageName;
    document.getElementById('renewal_current_expiry').textContent = formatDate(currentExpiry);
    document.getElementById('renewal_months').value = '';
    document.getElementById('renewal_total_price').textContent = '0 VNĐ';
    
    // Set form action dynamically
    document.getElementById('renewalForm').action = `services.php?action=renew&id=${orderId}`;
    
    document.getElementById('renewalModal').classList.remove('hidden');
}

function closeRenewalModal() {
    document.getElementById('renewalModal').classList.add('hidden');
}

function showOrderDetails(orderId) {
    // Fetch order details via AJAX or use existing data
    const orders = <?= json_encode($orders) ?>;
    const order = orders.find(o => o.id == orderId);
    
    if (!order) return;
    
    document.getElementById('orderDetailsContent').innerHTML = `
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-gray-800 mb-4">Thông tin dịch vụ</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Mã đơn:</span>
                            <span class="font-medium">#${order.id}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Gói VPS:</span>
                            <span class="font-medium">${order.package_name}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Hệ điều hành:</span>
                            <span class="font-medium">${order.os_name}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Trạng thái:</span>
                            <span class="font-medium">${getStatusText(order.status)}</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-800 mb-4">Thông tin thanh toán</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Giá gói:</span>
                            <span class="font-medium">${formatPrice(order.price)}/tháng</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Chu kỳ:</span>
                            <span class="font-medium">${order.billing_cycle} tháng</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tổng tiền:</span>
                            <span class="font-medium text-teal-600">${formatPrice(order.total_price)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ngày mua:</span>
                            <span class="font-medium">${formatDate(order.purchase_date)}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            ${order.ip_address ? `
            <div class="bg-gradient-to-r from-teal-50 to-blue-50 rounded-lg p-6">
                <h4 class="font-semibold text-gray-800 mb-4">Thông tin đăng nhập</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">IP Address:</span>
                        <p class="font-mono font-medium">${order.ip_address}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Username:</span>
                        <p class="font-mono font-medium">${order.username || 'root'}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Password:</span>
                        <p class="font-mono font-medium">${order.password || 'N/A'}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Hết hạn:</span>
                        <p class="font-medium">${formatDate(order.expiry_date)}</p>
                    </div>
                </div>
            </div>
            ` : ''}
            
            <div>
                <h4 class="font-semibold text-gray-800 mb-4">Lịch sử gia hạn</h4>
                <div id="renewalHistory_${orderId}">
                    <p class="text-gray-500 text-sm">Đang tải...</p>
                </div>
            </div>
        </div>
    `;
    
    // Load renewal history
    loadRenewalHistory(orderId);
    
    document.getElementById('detailsModal').classList.remove('hidden');
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}

function loadRenewalHistory(orderId) {
    // This would typically be an AJAX call to fetch renewal history
    // For now, we'll show a placeholder
    setTimeout(() => {
        const renewalHistory = <?= json_encode([]) ?>; // Empty array for now
        const historyHtml = renewalHistory.length > 0 
            ? renewalHistory.map(renewal => `
                <div class="border-l-4 border-teal-500 pl-4 mb-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-gray-900">Gia hạn ${renewal.months} tháng</p>
                            <p class="text-sm text-gray-600">${formatDate(renewal.created_at)}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-teal-600">${formatPrice(renewal.price)}</p>
                            <p class="text-xs text-gray-500">${renewal.status === 'completed' ? 'Hoàn thành' : 'Chờ xử lý'}</p>
                        </div>
                    </div>
                    ${renewal.old_expiry_date ? `<p class="text-xs text-gray-500 mt-1">Từ: ${formatDate(renewal.old_expiry_date)} → ${formatDate(renewal.new_expiry_date)}</p>` : ''}
                </div>
            `).join('')
            : '<p class="text-gray-500 text-sm">Chưa có lịch sử gia hạn nào.</p>';
        
        document.getElementById(`renewalHistory_${orderId}`).innerHTML = historyHtml;
    }, 500);
}

function getStatusText(status) {
    switch(status) {
        case 'active': return 'Đang hoạt động';
        case 'pending': return 'Chờ kích hoạt';
        case 'expired': return 'Hết hạn';
        case 'cancelled': return 'Đã hủy';
        default: return status;
    }
}

function formatDate(dateString) {
    if (!dateString || dateString === '0000-00-00') return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
}

function calculatePrice(basePrice, months) {
    const multipliers = {
        '1': 1,
        '6': 0.95,
        '12': 0.9,
        '24': 0.85
    };
    
    return basePrice * months * (multipliers[months] || 1);
}

function togglePassword(orderId, password) {
    const passwordEl = document.getElementById(`password-${orderId}`);
    const eyeEl = document.getElementById(`eye-${orderId}`);
    
    if (passwordEl.textContent.includes('*')) {
        passwordEl.textContent = password;
        eyeEl.classList.remove('fa-eye');
        eyeEl.classList.add('fa-eye-slash');
    } else {
        passwordEl.textContent = '*'.repeat(password.length);
        eyeEl.classList.remove('fa-eye-slash');
        eyeEl.classList.add('fa-eye');
    }
}

// Update renewal price when months change
document.getElementById('renewal_months')?.addEventListener('change', function() {
    const months = this.value;
    if (months) {
        const totalPrice = calculatePrice(currentPrice, months);
        document.getElementById('renewal_total_price').textContent = formatPrice(totalPrice);
    } else {
        document.getElementById('renewal_total_price').textContent = '0 VNĐ';
    }
});

// Close modals when clicking outside
document.getElementById('renewalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRenewalModal();
    }
});

document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetailsModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include 'includes/header.php';
?>