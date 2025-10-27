<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_deposit'])) {
    $deposit_id = sanitizeInt($_POST['deposit_id']);
    $deposit = fetchOne("SELECT * FROM deposits WHERE id = ?", [$deposit_id]);
    
    if ($deposit && $deposit['status'] == 'pending') {
        updateData('deposits', ['status' => 'completed'], 'id = ?', [$deposit_id]);
        updateUserBalance($deposit['user_id'], $deposit['amount']);
        
        $_SESSION['success_message'] = 'Duyệt nạp tiền thành công!';
        redirect('deposits.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reject_deposit'])) {
    $deposit_id = sanitizeInt($_POST['deposit_id']);
    $deposit = fetchOne("SELECT * FROM deposits WHERE id = ?", [$deposit_id]);
    
    if ($deposit && $deposit['status'] == 'pending') {
        updateData('deposits', ['status' => 'failed'], 'id = ?', [$deposit_id]);
        
        $_SESSION['success_message'] = 'Từ chối nạp tiền thành công!';
        redirect('deposits.php');
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $deposit_id = sanitizeInt($_GET['id']);
    deleteData('deposits', 'id = ?', [$deposit_id]);
    $_SESSION['success_message'] = 'Xóa giao dịch thành công!';
    redirect('deposits.php');
}

$filter = isset($_GET['filter']) ? sanitize($_GET['filter']) : '';
$page = isset($_GET['page']) ? sanitizeInt($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$whereClause = '';
$params = [];

switch($filter) {
    case 'pending':
        $whereClause = 'WHERE d.status = ?';
        $params = ['pending'];
        break;
    case 'completed':
        $whereClause = 'WHERE d.status = ?';
        $params = ['completed'];
        break;
    case 'failed':
        $whereClause = 'WHERE d.status = ?';
        $params = ['failed'];
        break;
}

$sql = "SELECT d.*, u.username, u.full_name, u.email 
        FROM deposits d 
        LEFT JOIN users u ON d.user_id = u.id 
        $whereClause 
        ORDER BY d.created_at DESC 
        LIMIT $limit OFFSET $offset";

$deposits = fetchAll($sql, $params);

$countSql = "SELECT COUNT(*) as total FROM deposits d $whereClause";
$totalResult = fetchOne($countSql, $params);
$totalDeposits = $totalResult['total'];
$totalPages = ceil($totalDeposits / $limit);

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

ob_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nạp tiền - Hệ thống Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dark-theme {
            background: #0f172a;
            color: #e2e8f0;
        }
        .sidebar-dark {
            background: #1e293b;
            border-right: 1px solid #334155;
        }
        .card-dark {
            background: #1e293b;
            border: 1px solid #334155;
        }
        .table-dark {
            background: #1e293b;
            color: #e2e8f0;
        }
        .table-dark th {
            background: #334155;
            color: #f1f5f9;
        }
        .table-dark tr:hover {
            background: #334155;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0e7490 0%, #155e75 100%);
        }
        .modal-dark {
            background: #1e293b;
            border: 1px solid #334155;
        }
        .input-dark {
            background: #0f172a;
            border: 1px solid #334155;
            color: #e2e8f0;
        }
        .input-dark:focus {
            border-color: #0891b2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.1);
        }
    </style>
</head>
<body class="dark-theme min-h-screen">
    <!-- Header -->
    <header class="bg-slate-800 border-b border-slate-700 shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="bg-cyan-600 p-2 rounded-lg">
                        <i class="fas fa-money-bill-wave text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Quản lý nạp tiền</h1>
                        <p class="text-slate-400 text-sm">Duyệt và quản lý giao dịch nạp tiền</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-6">
                    <a href="../index.php" class="text-slate-300 hover:text-cyan-400 transition flex items-center space-x-2">
                        <i class="fas fa-home"></i>
                        <span>Trang chủ</span>
                    </a>
                    <div class="flex items-center space-x-3 bg-slate-700 px-4 py-2 rounded-lg">
                        <i class="fas fa-user-circle text-cyan-400 text-xl"></i>
                        <span class="text-white font-medium"><?= htmlspecialchars($_SESSION['username']) ?></span>
                    </div>
                    <a href="../logout.php" class="text-slate-300 hover:text-red-400 transition flex items-center space-x-2">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng xuất</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 sidebar-dark min-h-screen">
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="index.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-tachometer-alt w-5"></i>
                            <span>Tổng quan</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-users w-5"></i>
                            <span>Người dùng</span>
                        </a>
                    </li>
                    <li>
                        <a href="orders.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-shopping-cart w-5"></i>
                            <span>Đơn hàng</span>
                        </a>
                    </li>
                    <li>
                        <a href="deposits.php" class="flex items-center space-x-3 bg-cyan-600 text-white p-3 rounded-lg">
                            <i class="fas fa-money-bill-wave w-5"></i>
                            <span>Nạp tiền</span>
                        </a>
                    </li>
                    <li>
                        <a href="packages.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-box w-5"></i>
                            <span>Gói VPS</span>
                        </a>
                    </li>
                    <li>
                        <a href="renewals.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-redo w-5"></i>
                            <span>Gia hạn</span>
                        </a>
                    </li>
                    <li>
                        <a href="seo.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-search w-5"></i>
                            <span>SEO</span>
                        </a>
                    </li>
                    <li>
                        <a href="notifications.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-bell w-5"></i>
                            <span>Thông báo</span>
                        </a>
                    </li>
                    <li>
                        <a href="contact.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-phone w-5"></i>
                            <span>Liên hệ</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-cog w-5"></i>
                            <span>Cài đặt</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Tổng giao dịch</p>
                            <p class="text-2xl font-bold text-white"><?= number_format($totalDeposits) ?></p>
                        </div>
                        <div class="bg-cyan-600 p-3 rounded-lg">
                            <i class="fas fa-exchange-alt text-white"></i>
                        </div>
                    </div>
                </div>
                
                <?php
                $pendingCount = count(fetchAll("SELECT id FROM deposits WHERE status = 'pending'"));
                $completedCount = count(fetchAll("SELECT id FROM deposits WHERE status = 'completed'"));
                $totalAmount = fetchOne("SELECT SUM(amount) as total FROM deposits WHERE status = 'completed'")['total'] ?? 0;
                ?>
                
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Chờ duyệt</p>
                            <p class="text-2xl font-bold text-yellow-400"><?= number_format($pendingCount) ?></p>
                        </div>
                        <div class="bg-yellow-600 p-3 rounded-lg">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Đã duyệt</p>
                            <p class="text-2xl font-bold text-green-400"><?= number_format($completedCount) ?></p>
                        </div>
                        <div class="bg-green-600 p-3 rounded-lg">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Tổng tiền</p>
                            <p class="text-2xl font-bold text-cyan-400"><?= number_format($totalAmount, 0, ',', '.') ?> VNĐ</p>
                        </div>
                        <div class="bg-cyan-600 p-3 rounded-lg">
                            <i class="fas fa-money-bill-wave text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-900 border border-green-700 text-green-100 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium"><?= htmlspecialchars($success_message) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card-dark rounded-lg p-6 mb-6">
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Lọc theo trạng thái</label>
                        <select onchange="window.location.href='?filter='+this.value" 
                                class="input-dark px-3 py-2 rounded-md">
                            <option value="">Tất cả</option>
                            <option value="pending" <?= $filter == 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="completed" <?= $filter == 'completed' ? 'selected' : '' ?>>Đã duyệt</option>
                            <option value="failed" <?= $filter == 'failed' ? 'selected' : '' ?>>Đã từ chối</option>
                        </select>
                    </div>
                    
                    <div class="flex-1 text-right">
                        <span class="text-sm text-slate-400">
                            Hiển thị <?= number_format(count($deposits)) ?> / <?= number_format($totalDeposits) ?> giao dịch
                        </span>
                    </div>
                </div>
            </div>

            <!-- Deposits Table -->
            <div class="card-dark rounded-lg overflow-hidden">
                <div class="p-6 border-b border-slate-700">
                    <h2 class="text-xl font-semibold text-white">Danh sách giao dịch</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full table-dark">
                        <thead>
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Mã GD</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Khách hàng</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Ngân hàng</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Số tiền</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Mã GD</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Ngày tạo</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <?php if (empty($deposits)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fas fa-money-bill-wave text-4xl mb-3"></i>
                                        <p>Không có giao dịch nào</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($deposits as $deposit): ?>
                                    <tr class="hover:bg-slate-700 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                                            #<?= $deposit['id'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white"><?= htmlspecialchars($deposit['full_name']) ?></div>
                                            <div class="text-sm text-slate-400"><?= htmlspecialchars($deposit['username']) ?></div>
                                            <div class="text-xs text-slate-500"><?= htmlspecialchars($deposit['email']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                            <?= htmlspecialchars($deposit['bank_name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-cyan-400">
                                            <?= number_format($deposit['amount'], 0, ',', '.') ?> VNĐ
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                            <?= htmlspecialchars($deposit['transaction_id'] ?: 'N/A') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                <?php
                                                switch($deposit['status']) {
                                                    case 'completed':
                                                        echo 'bg-green-900 text-green-200';
                                                        break;
                                                    case 'pending':
                                                        echo 'bg-yellow-900 text-yellow-200';
                                                        break;
                                                    case 'failed':
                                                        echo 'bg-red-900 text-red-200';
                                                        break;
                                                }
                                                ?>">
                                                <?php
                                                switch($deposit['status']) {
                                                    case 'completed':
                                                        echo 'Đã duyệt';
                                                        break;
                                                    case 'pending':
                                                        echo 'Chờ duyệt';
                                                        break;
                                                    case 'failed':
                                                        echo 'Đã từ chối';
                                                        break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                            <?= date('d/m/Y H:i', strtotime($deposit['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <?php if ($deposit['status'] == 'pending'): ?>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="deposit_id" value="<?= $deposit['id'] ?>">
                                                        <button type="submit" name="approve_deposit" 
                                                                class="text-green-400 hover:text-green-300 transition"
                                                                onclick="return confirm('Duyệt giao dịch này?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="deposit_id" value="<?= $deposit['id'] ?>">
                                                        <button type="submit" name="reject_deposit" 
                                                                class="text-red-400 hover:text-red-300 transition"
                                                                onclick="return confirm('Từ chối giao dịch này?')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <a href="?action=delete&id=<?= $deposit['id'] ?>" 
                                                   class="text-red-400 hover:text-red-300 transition"
                                                   onclick="return confirm('Xóa giao dịch này?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="bg-slate-800 px-4 py-3 flex items-center justify-between border-t border-slate-700">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-md text-slate-300 bg-slate-700 hover:bg-slate-600">
                                    Trước
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>" 
                                   class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-md text-slate-300 bg-slate-700 hover:bg-slate-600">
                                    Sau
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-slate-400">
                                    Hiển thị <span class="font-medium text-white"><?= ($offset + 1) ?></span> đến 
                                    <span class="font-medium text-white"><?= min($offset + $limit, $totalDeposits) ?></span> của 
                                    <span class="font-medium text-white"><?= $totalDeposits ?></span> kết quả
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>" 
                                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-600 bg-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-600">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <a href="?page=<?= $i ?>&filter=<?= $filter ?>" 
                                           class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                                               <?= $i == $page 
                                                   ? 'z-10 bg-cyan-600 border-cyan-600 text-white' 
                                                   : 'border-slate-600 bg-slate-700 text-slate-300 hover:bg-slate-600' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>" 
                                           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-600 bg-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-600">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>