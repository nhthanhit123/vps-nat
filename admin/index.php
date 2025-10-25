<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$page_title = 'Dashboard - Admin Panel';
$header_title = 'Good Morning, ' . $_SESSION['username'];
$header_description = 'Here\'s what\'s happening with your VPS business today.';

// Statistics
$totalUsers = count(fetchAll("SELECT id FROM users"));
$totalOrders = count(fetchAll("SELECT id FROM vps_orders"));
$totalDeposits = count(fetchAll("SELECT id FROM deposits"));
$totalPackages = count(fetchAll("SELECT id FROM vps_packages"));
$pendingOrders = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'pending'"));
$pendingDeposits = count(fetchAll("SELECT id FROM deposits WHERE status = 'pending'"));
$activeOrders = count(fetchAll("SELECT id FROM vps_orders WHERE status = 'active'"));

// Revenue calculations
$totalRevenue = fetchAll("SELECT SUM(total_price) as total FROM vps_orders WHERE status = 'active'")[0]['total'] ?? 0;
$monthlyRevenue = fetchAll("SELECT SUM(total_price) as total FROM vps_orders WHERE status = 'active' AND MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)")[0]['total'] ?? 0;

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

// Chart data (last 7 days)
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayOrders = count(fetchAll("SELECT id FROM vps_orders WHERE DATE(created_at) = '$date'"));
    $dayRevenue = fetchAll("SELECT SUM(total_price) as total FROM vps_orders WHERE DATE(created_at) = '$date' AND status = 'active'")[0]['total'] ?? 0;
    $chartData[] = [
        'date' => date('d/m', strtotime($date)),
        'orders' => $dayOrders,
        'revenue' => $dayRevenue
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
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">+12%</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($totalUsers) ?></h3>
        <p class="text-gray-600 text-sm">Total Users</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">+8%</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($totalOrders) ?></h3>
        <p class="text-gray-600 text-sm">Total Orders</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">+23%</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= formatPrice($totalRevenue) ?></h3>
        <p class="text-gray-600 text-sm">Total Revenue</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-red-600 bg-red-100 px-2 py-1 rounded"><?= $pendingOrders + $pendingDeposits ?></span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($pendingOrders + $pendingDeposits) ?></h3>
        <p class="text-gray-600 text-sm">Pending Tasks</p>
    </div>
</div>

<!-- Charts and Tables -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Revenue Chart -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Revenue Overview</h3>
            <select class="text-sm border border-gray-300 rounded-lg px-3 py-1">
                <option>Last 7 days</option>
                <option>Last 30 days</option>
                <option>Last 3 months</option>
            </select>
        </div>
        <canvas id="revenueChart" width="400" height="200"></canvas>
    </div>

    <!-- Traffic Sources -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Traffic Sources</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fab fa-facebook text-blue-600 text-sm"></i>
                    </div>
                    <span class="text-gray-700">Facebook</span>
                </div>
                <span class="font-semibold">35%</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center">
                        <i class="fab fa-instagram text-pink-600 text-sm"></i>
                    </div>
                    <span class="text-gray-700">Instagram</span>
                </div>
                <span class="font-semibold">25%</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fab fa-whatsapp text-green-600 text-sm"></i>
                    </div>
                    <span class="text-gray-700">Zalo</span>
                </div>
                <span class="font-semibold">20%</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fab fa-youtube text-red-600 text-sm"></i>
                    </div>
                    <span class="text-gray-700">YouTube</span>
                </div>
                <span class="font-semibold">15%</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-link text-gray-600 text-sm"></i>
                    </div>
                    <span class="text-gray-700">Direct</span>
                </div>
                <span class="font-semibold">5%</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
            <a href="orders.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="space-y-4">
            <?php if (empty($recentOrders)): ?>
                <p class="text-gray-500 text-center py-8">No recent orders</p>
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
            <h3 class="text-lg font-semibold text-gray-900">Recent Deposits</h3>
            <a href="deposits.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                View All <i class="fas fa-arrow-right ml-1"></i>
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

<script>
// Chart.js Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const chartData = <?= json_encode($chartData) ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.map(d => d.date),
        datasets: [{
            label: 'Revenue',
            data: chartData.map(d => d.revenue),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Orders',
            data: chartData.map(d => d.orders),
            borderColor: 'rgb(139, 92, 246)',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                ticks: {
                    callback: function(value) {
                        return '₫' + value.toLocaleString('vi-VN');
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include 'includes/header.php';
?>