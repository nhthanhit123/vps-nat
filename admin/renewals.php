<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Hàm lấy thông tin gia hạn từng cái một
function getRenewalDetails($conn, $renewalId) {
    try {
        // Lấy thông tin gia hạn
        $renewalQuery = "SELECT r.*, o.vps_id, o.user_id, o.service_details, o.status as order_status 
                        FROM renewals r 
                        JOIN orders o ON r.order_id = o.id 
                        WHERE r.id = ?";
        $renewalStmt = $conn->prepare($renewalQuery);
        $renewalStmt->execute([$renewalId]);
        $renewal = $renewalStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$renewal) {
            return null;
        }
        
        // Lấy thông tin người dùng
        $userQuery = "SELECT id, username, email, full_name FROM users WHERE id = ?";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->execute([$renewal['user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return null;
        }
        
        // Lấy thông tin VPS
        $vpsQuery = "SELECT * FROM vps WHERE id = ?";
        $vpsStmt = $conn->prepare($vpsQuery);
        $vpsStmt->execute([$renewal['vps_id']]);
        $vps = $vpsStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vps) {
            return null;
        }
        
        // Lấy thông tin gói dịch vụ
        $packageQuery = "SELECT * FROM vps_packages WHERE id = ?";
        $packageStmt = $conn->prepare($packageQuery);
        $packageStmt->execute([$vps['package_id']]);
        $package = $packageStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$package) {
            return null;
        }
        
        // Lấy thông tin hệ điều hành
        $osQuery = "SELECT * FROM operating_systems WHERE id = ?";
        $osStmt = $conn->prepare($osQuery);
        $osStmt->execute([$vps['os_id']]);
        $os = $osStmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'renewal' => $renewal,
            'user' => $user,
            'vps' => $vps,
            'package' => $package,
            'os' => $os
        ];
        
    } catch (Exception $e) {
        error_log("Error getting renewal details: " . $e->getMessage());
        return null;
    }
}

// Hàm lấy danh sách gia hạn với thông tin cơ bản
function getRenewalsList($conn, $limit, $offset) {
    try {
        $query = "SELECT r.id, r.order_id, r.user_id, r.price, r.months, r.status, 
                        r.old_expiry_date, r.new_expiry_date, r.created_at,
                        u.username, u.full_name, u.email,
                        v.package_id, v.ip_address, v.hostname, v.expiry_date,
                        p.name as package_name, p.billing_cycle
                 FROM renewals r 
                 JOIN orders o ON r.order_id = o.id 
                 JOIN users u ON r.user_id = u.id
                 JOIN vps v ON o.vps_id = v.id
                 JOIN vps_packages p ON v.package_id = p.id
                 ORDER BY r.created_at DESC 
                 LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error getting renewals list: " . $e->getMessage());
        return [];
    }
}

// Lấy danh sách gia hạn
$renewals = getRenewalsList($conn, $limit, $offset);

// Lấy tổng số gia hạn
$totalRenewals = fetchOne("SELECT COUNT(*) as total FROM renewals")['total'];
$totalPages = ceil($totalRenewals / $limit);

// Statistics - lấy từng cái một
$completedCount = fetchOne("SELECT COUNT(*) as count FROM renewals WHERE status = 'completed'")['count'];
$pendingCount = fetchOne("SELECT COUNT(*) as count FROM renewals WHERE status = 'pending'")['count'];
$failedCount = fetchOne("SELECT COUNT(*) as count FROM renewals WHERE status = 'failed'")['count'];
$totalRenewalAmount = fetchOne("SELECT SUM(price) as total FROM renewals WHERE status = 'completed'")['total'] ?? 0;
$thisMonthCount = fetchOne("SELECT COUNT(*) as count FROM renewals WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")['count'];

$page_title = 'Lịch Sử Gia Hạn - Admin Panel';
$header_title = 'Lịch Sử Gia Hạn';
$header_description = 'Xem lịch sử tất cả các giao dịch gia hạn VPS';

ob_start();
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-redo text-blue-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded">Tổng</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($totalRenewals) ?></h3>
        <p class="text-gray-600 text-sm">Tổng Gia Hạn</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">Hoàn thành</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($completedCount) ?></h3>
        <p class="text-gray-600 text-sm">Gia Hạn Thành Công</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-yellow-600 bg-yellow-100 px-2 py-1 rounded">Chờ xử lý</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($pendingCount) ?></h3>
        <p class="text-gray-600 text-sm">Đang Chờ Xử Lý</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded">Doanh thu</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= formatPrice($totalRenewalAmount) ?></h3>
        <p class="text-gray-600 text-sm">Tổng Doanh Thu</p>
    </div>
</div>

<!-- Header Info -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Danh sách gia hạn</h2>
            <p class="text-gray-600 text-sm mt-1">Lịch sử tất cả các giao dịch gia hạn VPS</p>
        </div>
        <div class="text-right">
            <span class="text-sm text-gray-600">
                Hiển thị <?= count($renewals) ?> / <?= $totalRenewals ?> giao dịch
            </span>
        </div>
    </div>
</div>

<!-- Renewals Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã GH</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gói VPS</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($renewals)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-redo text-4xl mb-3 text-gray-300"></i>
                            <p class="text-gray-500">Chưa có lịch sử gia hạn nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($renewals as $renewal): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?= str_pad($renewal['id'], 6, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($renewal['full_name'] ?? 'N/A') ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($renewal['username']) ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($renewal['email'] ?? '') ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium"><?= htmlspecialchars($renewal['package_name'] ?? 'N/A') ?></div>
                                <div class="text-xs text-gray-500">Đơn #<?= $renewal['order_id'] ?></div>
                                <?php if ($renewal['ip_address']): ?>
                                <div class="text-xs text-gray-500">IP: <?= htmlspecialchars($renewal['ip_address']) ?></div>
                                <?php endif; ?>
                                <?php if ($renewal['old_expiry_date'] && $renewal['new_expiry_date']): ?>
                                <div class="text-xs text-green-600">
                                    <?= date('d/m/Y', strtotime($renewal['old_expiry_date'])) ?> → 
                                    <?= date('d/m/Y', strtotime($renewal['new_expiry_date'])) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium"><?= $renewal['months'] ?> tháng</div>
                                <?php if ($renewal['billing_cycle']): ?>
                                <div class="text-xs text-gray-500">Chu kỳ: <?= $renewal['billing_cycle'] ?>th</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-cyan-600">
                                <?= formatPrice($renewal['price']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php
                                    switch($renewal['status']) {
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
                                    <?php
                                    switch($renewal['status']) {
                                        case 'completed':
                                            echo 'Hoàn thành';
                                            break;
                                        case 'pending':
                                            echo 'Chờ xử lý';
                                            break;
                                        case 'failed':
                                            echo 'Thất bại';
                                            break;
                                        default:
                                            echo $renewal['status'];
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($renewal['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="showRenewalDetails(<?= $renewal['id'] ?>)" 
                                        class="text-cyan-600 hover:text-cyan-900 transition-colors">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Trước
                    </a>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" 
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Tiếp
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Hiển thị <span class="font-medium"><?= ($offset + 1) ?></span> đến 
                        <span class="font-medium"><?= min($offset + $limit, $totalRenewals) ?></span> của 
                        <span class="font-medium"><?= $totalRenewals ?></span> kết quả
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?= $i ?>" 
                               class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                                   <?= $i == $page 
                                       ? 'z-10 bg-cyan-50 border-cyan-500 text-cyan-600' 
                                       : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Renewal Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-2/3 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Chi Tiết Gia Hạn</h3>
            <div id="renewalDetailsContent"></div>
            <div class="flex justify-end mt-6">
                <button onclick="closeDetailsModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showRenewalDetails(renewalId) {
    showLoading();
    
    // Gọi AJAX để lấy chi tiết gia hạn
    fetch(`ajax_get_renewal_details.php?id=${renewalId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const details = data.details;
                let html = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3">Thông tin gia hạn</h4>
                            <div class="space-y-2">
                                <p class="text-sm"><span class="font-medium">Mã gia hạn:</span> #${details.renewal.id}</p>
                                <p class="text-sm"><span class="font-medium">Trạng thái:</span> 
                                    <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(details.renewal.status)}">
                                        ${getStatusText(details.renewal.status)}
                                    </span>
                                </p>
                                <p class="text-sm"><span class="font-medium">Số tháng:</span> ${details.renewal.months} tháng</p>
                                <p class="text-sm"><span class="font-medium">Giá:</span> ${details.renewal.price}</p>
                                <p class="text-sm"><span class="font-medium">Ngày tạo:</span> ${new Date(details.renewal.created_at).toLocaleString('vi-VN')}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3">Thông tin khách hàng</h4>
                            <div class="space-y-2">
                                <p class="text-sm"><span class="font-medium">Họ tên:</span> ${details.user.full_name}</p>
                                <p class="text-sm"><span class="font-medium">Username:</span> ${details.user.username}</p>
                                <p class="text-sm"><span class="font-medium">Email:</span> ${details.user.email}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3">Thông tin VPS</h4>
                            <div class="space-y-2">
                                <p class="text-sm"><span class="font-medium">IP Address:</span> ${details.vps.ip_address}</p>
                                <p class="text-sm"><span class="font-medium">Hostname:</span> ${details.vps.hostname}</p>
                                <p class="text-sm"><span class="font-medium">Ngày hết hạn:</span> ${new Date(details.vps.expiry_date).toLocaleDateString('vi-VN')}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3">Thông tin gói dịch vụ</h4>
                            <div class="space-y-2">
                                <p class="text-sm"><span class="font-medium">Gói:</span> ${details.package.name}</p>
                                <p class="text-sm"><span class="font-medium">Chu kỳ:</span> ${details.package.billing_cycle} tháng</p>
                                <p class="text-sm"><span class="font-medium">HĐH:</span> ${details.os ? details.os.name : 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                if (details.renewal.old_expiry_date && details.renewal.new_expiry_date) {
                    html += `
                        <div class="mt-4 p-3 bg-green-50 rounded-lg">
                            <p class="text-sm text-green-800">
                                <span class="font-medium">Gia hạn từ:</span> 
                                ${new Date(details.renewal.old_expiry_date).toLocaleDateString('vi-VN')} → 
                                ${new Date(details.renewal.new_expiry_date).toLocaleDateString('vi-VN')}
                            </p>
                        </div>
                    `;
                }
                
                document.getElementById('renewalDetailsContent').innerHTML = html;
            } else {
                document.getElementById('renewalDetailsContent').innerHTML = 
                    '<p class="text-red-500">Không thể tải thông tin chi tiết</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('renewalDetailsContent').innerHTML = 
                '<p class="text-red-500">Đã xảy ra lỗi khi tải thông tin</p>';
        })
        .finally(() => {
            document.getElementById('detailsModal').classList.remove('hidden');
            hideLoading();
        });
}

function getStatusClass(status) {
    switch(status) {
        case 'completed': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'failed': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'completed': return 'Hoàn thành';
        case 'pending': return 'Chờ xử lý';
        case 'failed': return 'Thất bại';
        default: return status;
    }
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}
</script>

<?php
$content = ob_get_clean();
require_once 'includes/header.php';
?>