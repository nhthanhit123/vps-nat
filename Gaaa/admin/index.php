<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$page_title = 'Tổng Quan - Admin Panel';
$header_title = 'Chào buổi sáng, ' . $_SESSION['username'];
$header_description = 'Đây là những gì đang diễn ra với doanh nghiệp VPS của bạn hôm nay.';

// Statistics
$totalUsers = count(fetchAll("SELECT id FROM users"));
$totalOrders = count(fetchAll("SELECT id FROM vps_orders"));
$totalDeposits = count(fetchAll("SELECT id FROM deposits"));
$totalPackages = count(fetchAll("SELECT id FROM vps_packages"));
$pendingOrders = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'pending'"));
$pendingDeposits = count(fetchAll("SELECT id FROM deposits WHERE status = 'pending'"));
$activeOrders = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'active'"));
$expiredOrders = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'expired'"));
$expiringSoonOrders = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'active' AND expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY) AND expiry_date > CURRENT_DATE"));

// Revenue calculations
$totalRevenue = fetchAll("SELECT SUM(total_price) as total FROM vps_orders WHERE status = 'active'")[0]['total'] ?? 0;
$monthlyRevenue = fetchAll("SELECT SUM(total_price) as total FROM vps_orders WHERE status = 'active' AND MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)")[0]['total'] ?? 0;
$dailyRevenue = fetchAll("SELECT SUM(total_price) as total FROM vps_orders WHERE status = 'active' AND DATE(created_at) = CURRENT_DATE")[0]['total'] ?? 0;
$yearlyRevenue = fetchAll("SELECT SUM(total_price) as total FROM vps_orders WHERE status = 'active' AND YEAR(created_at) = YEAR(CURRENT_DATE)")[0]['total'] ?? 0;

// VPS Statistics
$totalVpsActive = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'active'"));
$totalVpsPending = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'pending'"));
$totalVpsExpired = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'expired'"));
$totalVpsExpiringSoon = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'active' AND expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY) AND expiry_date > CURRENT_DATE"));

// Package statistics
$natPackagesSold = count(fetchAll("SELECT vo.id FROM vps_orders vo LEFT JOIN vps_packages vp ON vo.package_id = vp.id WHERE vp.category = 'nat' AND vo.status = 'active'"));
$cheapPackagesSold = count(fetchAll("SELECT vo.id FROM vps_orders vo LEFT JOIN vps_packages vp ON vo.package_id = vp.id WHERE vp.category = 'cheap' AND vo.status = 'active'"));

// Recent data
$recentOrders = fetchAll("SELECT vo.*, u.username, u.full_name, vp.name as package_name 
                          FROM vps_orders vo 
                          LEFT JOIN users u ON vo.user_id = u.id 
                          LEFT JOIN vps_packages vp ON vo.package_id = vp.id 
                          ORDER BY vo.created_at DESC LIMIT 5");

$recentDeposits = fetchAll("SELECT d.*, u.username, u.full_name 
                            FROM deposits d 
                            LEFT JOIN users u ON d.user_id = u.id 
                            ORDER BY d.created_at DESC LIMIT 5");

// Top customers
$topCustomers = fetchAll("SELECT u.*, COUNT(vo.id) as order_count, SUM(vo.total_price) as total_spent
                          FROM users u 
                          LEFT JOIN vps_orders vo ON u.id = vo.user_id AND vo.status = 'active'
                          GROUP BY u.id 
                          ORDER BY total_spent DESC 
                          LIMIT 5");

// Monthly statistics for current year
$monthlyStats = [];
for ($i = 1; $i <= 12; $i++) {
    $monthRevenue = fetchAll("SELECT SUM(total_price) as total FROM vps_orders WHERE status = 'active' AND MONTH(created_at) = ? AND YEAR(created_at) = YEAR(CURRENT_DATE)", [$i])[0]['total'] ?? 0;
    $monthOrders = count(fetchAll("SELECT id FROM vps_orders WHERE MONTH(created_at) = ? AND YEAR(created_at) = YEAR(CURRENT_DATE)", [$i]));
    
    $monthlyStats[] = [
        'month' => date('M', mktime(0, 0, 0, $i, 1)),
        'revenue' => $monthRevenue,
        'orders' => $monthOrders
    ];
}

ob_start();
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">Hoạt động</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($totalUsers) ?></h3>
        <p class="text-gray-600 text-sm">Tổng Người Dùng</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-server text-green-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded"><?= $totalVpsActive ?> Hoạt động</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($totalOrders) ?></h3>
        <p class="text-gray-600 text-sm">Tổng Đơn Hàng VPS</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">+23%</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= formatPrice($totalRevenue) ?></h3>
        <p class="text-gray-600 text-sm">Tổng Doanh Thu</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-red-600 bg-red-100 px-2 py-1 rounded"><?= $pendingOrders + $pendingDeposits ?></span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($pendingOrders + $pendingDeposits) ?></h3>
        <p class="text-gray-600 text-sm">Tác Vụ Chờ Xử Lý</p>
    </div>
</div>

<!-- Revenue Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-600 text-sm">Doanh Thu Hàng Ngày</span>
            <i class="fas fa-calendar-day text-blue-500"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900"><?= formatPrice($dailyRevenue) ?></h3>
        <p class="text-xs text-gray-500">Hôm nay</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-600 text-sm">Doanh Thu Hàng Tháng</span>
            <i class="fas fa-calendar-alt text-green-500"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900"><?= formatPrice($monthlyRevenue) ?></h3>
        <p class="text-xs text-gray-500">Tháng này</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-600 text-sm">Doanh Thu Hàng Năm</span>
            <i class="fas fa-calendar text-purple-500"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900"><?= formatPrice($yearlyRevenue) ?></h3>
        <p class="text-xs text-gray-500">Năm nay</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-600 text-sm">Tổng Doanh Thu</span>
            <i class="fas fa-chart-line text-orange-500"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900"><?= formatPrice($totalRevenue) ?></h3>
        <p class="text-xs text-gray-500">Tất cả thời gian</p>
    </div>
</div>

<!-- VPS Status Overview -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between mb-2">
            <span class="text-green-100">VPS Đang Hoạt Động</span>
            <i class="fas fa-check-circle text-green-200"></i>
        </div>
        <h3 class="text-2xl font-bold"><?= number_format($totalVpsActive) ?></h3>
        <p class="text-green-100 text-sm">Đang chạy</p>
    </div>

    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between mb-2">
            <span class="text-yellow-100">VPS Chờ Xử Lý</span>
            <i class="fas fa-clock text-yellow-200"></i>
        </div>
        <h3 class="text-2xl font-bold"><?= number_format($totalVpsPending) ?></h3>
        <p class="text-yellow-100 text-sm">Chờ thiết lập</p>
    </div>

    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between mb-2">
            <span class="text-orange-100">Sắp Hết Hạn</span>
            <i class="fas fa-exclamation-triangle text-orange-200"></i>
        </div>
        <h3 class="text-2xl font-bold"><?= number_format($totalVpsExpiringSoon) ?></h3>
        <p class="text-orange-100 text-sm">Trong 7 ngày</p>
    </div>

    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between mb-2">
            <span class="text-red-100">VPS Hết Hạn</span>
            <i class="fas fa-times-circle text-red-200"></i>
        </div>
        <h3 class="text-2xl font-bold"><?= number_format($totalVpsExpired) ?></h3>
        <p class="text-red-100 text-sm">Cần gia hạn</p>
    </div>
</div>

<!-- Package Distribution -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Phân Bổ Gói Dịch Vụ</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-network-wired text-teal-600 text-sm"></i>
                    </div>
                    <span class="text-gray-700">VPS NAT</span>
                </div>
                <div class="text-right">
                    <span class="font-semibold"><?= number_format($natPackagesSold) ?></span>
                    <span class="text-gray-500 text-sm ml-2">
                        <?= $totalOrders > 0 ? round(($natPackagesSold / $totalOrders) * 100, 1) : 0 ?>%
                    </span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-rocket text-red-600 text-sm"></i>
                    </div>
                    <span class="text-gray-700">VPS Cheap</span>
                </div>
                <div class="text-right">
                    <span class="font-semibold"><?= number_format($cheapPackagesSold) ?></span>
                    <span class="text-gray-500 text-sm ml-2">
                        <?= $totalOrders > 0 ? round(($cheapPackagesSold / $totalOrders) * 100, 1) : 0 ?>%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Khách Hàng Hàng Đầu</h3>
            <a href="users.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Xem Tất Cả <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="space-y-4">
            <?php if (empty($topCustomers)): ?>
                <p class="text-gray-500 text-center py-4">Không tìm thấy khách hàng</p>
            <?php else: ?>
                <?php foreach ($topCustomers as $customer): ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 text-sm font-semibold"><?= substr($customer['username'], 0, 1) ?></span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($customer['username']) ?></p>
                                <p class="text-xs text-gray-500"><?= $customer['order_count'] ?> đơn hàng</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900"><?= formatPrice($customer['total_spent']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Đơn Hàng Gần Đây</h3>
            <a href="orders.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Xem Tất Cả <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="space-y-4">
            <?php if (empty($recentOrders)): ?>
                <p class="text-gray-500 text-center py-8">Không có đơn hàng gần đây</p>
            <?php else: ?>
                <?php foreach ($recentOrders as $order): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($order['package_name']) ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($order['username']) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900"><?= formatPrice($order['total_price']) ?></p>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
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
                                    default:
                                        echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Deposits -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Nạp Tiền Gần Đây</h3>
            <a href="deposits.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Xem Tất Cả <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="space-y-4">
            <?php if (empty($recentDeposits)): ?>
                <p class="text-gray-500 text-center py-8">No recent deposits</p>
            <?php else: ?>
                <?php foreach ($recentDeposits as $deposit): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($deposit['username']) ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($deposit['bank_name']) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900"><?= formatPrice($deposit['amount']) ?></p>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                <?php
                                switch($deposit['status']) {
                                    case 'completed':
                                        echo 'bg-green-100 text-green-800';
                                        break;
                                    case 'pending':
                                        echo 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'failed':
                                        echo 'bg-red-100 text-red-800';
                                        break;
                                    default:
                                        echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?= ucfirst($deposit['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'includes/header.php';
?>