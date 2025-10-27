<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['sync_packages'])) {
        // Sync packages from source
        header('Location: ../index.php?update=1');
        exit();
    }
    
    if (isset($_POST['add_package'])) {
        $data = [
            'name' => sanitize($_POST['name']),
            'cpu' => sanitize($_POST['cpu']),
            'ram' => sanitize($_POST['ram']),
            'storage' => sanitize($_POST['storage']),
            'bandwidth' => sanitize($_POST['bandwidth']),
            'port_speed' => sanitize($_POST['port_speed']),
            'ip' => sanitize($_POST['ip']),
            'original_price' => floatval($_POST['original_price']),
            'selling_price' => floatval($_POST['selling_price']),
            'category' => sanitize($_POST['category']),
            'status' => 'active'
        ];
        
        if (insertData('vps_packages', $data)) {
            $_SESSION['success_message'] = 'Thêm gói VPS thành công!';
        } else {
            $_SESSION['error_message'] = 'Thêm gói VPS thất bại!';
        }
        redirect('packages.php');
    }
    
    if (isset($_POST['edit_package'])) {
        $id = sanitizeInt($_POST['id']);
        $data = [
            'name' => sanitize($_POST['name']),
            'cpu' => sanitize($_POST['cpu']),
            'ram' => sanitize($_POST['ram']),
            'storage' => sanitize($_POST['storage']),
            'bandwidth' => sanitize($_POST['bandwidth']),
            'port_speed' => sanitize($_POST['port_speed']),
            'ip' => sanitize($_POST['ip']),
            'original_price' => floatval($_POST['original_price']),
            'selling_price' => floatval($_POST['selling_price']),
            'category' => sanitize($_POST['category']),
            'status' => sanitize($_POST['status'])
        ];
        
        if (updateData('vps_packages', $data, 'id = ?', [$id])) {
            $_SESSION['success_message'] = 'Cập nhật gói VPS thành công!';
        } else {
            $_SESSION['error_message'] = 'Cập nhật gói VPS thất bại!';
        }
        redirect('packages.php');
    }
    
    if (isset($_POST['delete_package'])) {
        $id = sanitizeInt($_POST['id']);
        if (deleteData('vps_packages', 'id = ?', [$id])) {
            $_SESSION['success_message'] = 'Xóa gói VPS thành công!';
        } else {
            $_SESSION['error_message'] = 'Xóa gói VPS thất bại!';
        }
        redirect('packages.php');
    }
    
    if (isset($_POST['toggle_status'])) {
        $id = sanitizeInt($_POST['id']);
        $package = fetchOne("SELECT * FROM vps_packages WHERE id = ?", [$id]);
        if ($package) {
            $new_status = $package['status'] == 'active' ? 'inactive' : 'active';
            if (updateData('vps_packages', ['status' => $new_status], 'id = ?', [$id])) {
                $_SESSION['success_message'] = 'Cập nhật trạng thái thành công!';
            }
        }
        redirect('packages.php');
    }
}

// Get packages
$packages = fetchAll("SELECT * FROM vps_packages ORDER BY category, selling_price ASC");

// Handle messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

ob_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý gói VPS - Hệ thống Admin</title>
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
                        <i class="fas fa-box text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Quản lý gói VPS</h1>
                        <p class="text-slate-400 text-sm">Quản lý và đồng bộ gói VPS</p>
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
                        <a href="deposits.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-money-bill-wave w-5"></i>
                            <span>Nạp tiền</span>
                        </a>
                    </li>
                    <li>
                        <a href="packages.php" class="flex items-center space-x-3 bg-cyan-600 text-white p-3 rounded-lg">
                            <i class="fas fa-box w-5"></i>
                            <span>Gói VPS</span>
                        </a>
                    </li>
                    <li>
                        <a href="os.php" class="flex items-center space-x-3 text-slate-300 hover:bg-slate-700 hover:text-cyan-400 p-3 rounded-lg transition">
                            <i class="fas fa-desktop w-5"></i>
                            <span>Hệ điều hành</span>
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
                            <p class="text-slate-400 text-sm">Tổng gói VPS</p>
                            <p class="text-2xl font-bold text-white"><?= number_format(count($packages)) ?></p>
                        </div>
                        <div class="bg-cyan-600 p-3 rounded-lg">
                            <i class="fas fa-box text-white"></i>
                        </div>
                    </div>
                </div>
                
                <?php
                $natCount = count(array_filter($packages, fn($p) => ($p['category'] ?? 'nat') === 'nat'));
                $cheapCount = count(array_filter($packages, fn($p) => ($p['category'] ?? 'nat') === 'cheap'));
                $activeCount = count(array_filter($packages, fn($p) => $p['status'] === 'active'));
                ?>
                
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Gói NAT</p>
                            <p class="text-2xl font-bold text-blue-400"><?= number_format($natCount) ?></p>
                        </div>
                        <div class="bg-blue-600 p-3 rounded-lg">
                            <i class="fas fa-network-wired text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Gói Cheap</p>
                            <p class="text-2xl font-bold text-green-400"><?= number_format($cheapCount) ?></p>
                        </div>
                        <div class="bg-green-600 p-3 rounded-lg">
                            <i class="fas fa-dollar-sign text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Đang hoạt động</p>
                            <p class="text-2xl font-bold text-green-400"><?= number_format($activeCount) ?></p>
                        </div>
                        <div class="bg-green-600 p-3 rounded-lg">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
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
            
            <?php if (isset($error_message)): ?>
                <div class="bg-red-900 border border-red-700 text-red-100 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium"><?= htmlspecialchars($error_message) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Header Actions -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-white">Danh sách gói VPS</h2>
                    <p class="text-slate-400 text-sm">Quản lý các gói VPS và đồng bộ từ nguồn</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="showAddModal()" 
                            class="bg-green-600 hover:bg-green-700 text-white py-2 px-6 rounded-lg font-medium transition flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Thêm gói mới</span>
                    </button>
                    <form method="POST" class="inline">
                        <button type="submit" name="sync_packages" 
                                class="btn-primary text-white py-2 px-6 rounded-lg font-medium transition flex items-center space-x-2">
                            <i class="fas fa-sync"></i>
                            <span>Đồng bộ gói VPS</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sync Info -->
            <div class="card-dark rounded-lg p-6 mb-8 border-l-4 border-cyan-600">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-cyan-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-white mb-2">Thông tin đồng bộ</h3>
                        <div class="text-slate-300">
                            <p class="mb-2"><strong>Nguồn:</strong> <a href="https://thuevpsgiare.com.vn/vps/packages?category=vps-cheap-ip-nat" target="_blank" class="text-cyan-400 hover:text-cyan-300 underline">thuevpsgiare.com.vn</a></p>
                            <p class="mb-2"><strong>Tự động tăng giá:</strong> +8% trên giá gốc</p>
                            <p class="mb-2"><strong>Số gói hiện tại:</strong> <?= number_format(count($packages)) ?> gói</p>
                            <p><strong>Lần cập nhật cuối:</strong> <?= date('d/m/Y H:i:s') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packages Table -->
            <div class="card-dark rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-dark">
                        <thead>
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">
                                    Tên gói
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">
                                    Danh mục
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">
                                    Cấu hình
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">
                                    Port Speed
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">
                                    IP
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">
                                    Giá gốc
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">
                                    Giá bán
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <?php if (empty($packages)): ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fas fa-box-open text-6xl mb-4"></i>
                                        <h3 class="text-xl font-semibold text-white mb-2">Chưa có gói VPS nào</h3>
                                        <p class="text-slate-400 mb-6">Vui lòng nhấn nút "Đồng bộ gói VPS" để lấy dữ liệu</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($packages as $package): ?>
                                    <tr class="hover:bg-slate-700 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white">
                                                <?= htmlspecialchars($package['name']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= ($package['category'] ?? 'nat') === 'cheap' ? 'bg-green-900 text-green-200' : 'bg-cyan-900 text-cyan-200' ?>">
                                                <i class="fas fa-<?= ($package['category'] ?? 'nat') === 'cheap' ? 'dollar-sign' : 'network-wired' ?> mr-1"></i>
                                                <?= ($package['category'] ?? 'nat') === 'cheap' ? 'Cheap' : 'NAT' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-white">
                                                <div><i class="fas fa-microchip text-cyan-400 mr-1"></i> <?= htmlspecialchars($package['cpu']) ?></div>
                                                <div><i class="fas fa-memory text-cyan-400 mr-1"></i> <?= htmlspecialchars($package['ram']) ?></div>
                                                <div><i class="fas fa-hdd text-cyan-400 mr-1"></i> <?= htmlspecialchars($package['storage']) ?></div>
                                                <div><i class="fas fa-tachometer-alt text-cyan-400 mr-1"></i> <?= htmlspecialchars($package['bandwidth']) ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                            <?= htmlspecialchars($package['port_speed']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                            <?= htmlspecialchars($package['ip'] ?? 'N/A') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                            <?= number_format($package['original_price'], 0, ',', '.') ?> VNĐ
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-cyan-400">
                                            <?= number_format($package['selling_price'], 0, ',', '.') ?> VNĐ
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                <?= $package['status'] == 'active' ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' ?>">
                                                <?= $package['status'] == 'active' ? 'Hoạt động' : 'Đã khóa' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="editPackage(<?= htmlspecialchars(json_encode($package)) ?>)" 
                                                        class="text-cyan-400 hover:text-cyan-300 transition">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <form method="POST" class="inline" onsubmit="return confirm('Xóa gói VPS này?')">
                                                    <input type="hidden" name="id" value="<?= $package['id'] ?>">
                                                    <button type="submit" name="delete_package" 
                                                            class="text-red-400 hover:text-red-300 transition">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="id" value="<?= $package['id'] ?>">
                                                    <button type="submit" name="toggle_status" 
                                                            class="<?= $package['status'] == 'active' ? 'text-yellow-400 hover:text-yellow-300' : 'text-green-400 hover:text-green-300' ?> transition"
                                                            title="<?= $package['status'] == 'active' ? 'Khóa' : 'Mở' ?>">
                                                        <i class="fas fa-<?= $package['status'] == 'active' ? 'lock' : 'unlock' ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Package Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-dark rounded-lg max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-white mb-4">Thêm gói VPS mới</h3>
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Tên gói *</label>
                            <input type="text" name="name" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục *</label>
                            <select name="category" required class="input-dark w-full px-3 py-2 rounded-md">
                                <option value="nat">NAT</option>
                                <option value="cheap">Cheap</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">CPU *</label>
                            <input type="text" name="cpu" required 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="VD: 1.00 vCore">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">RAM *</label>
                            <input type="text" name="ram" required 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="VD: 1 GB">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Ổ cứng *</label>
                            <input type="text" name="storage" required 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="VD: 15 GB SSD">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Băng thông *</label>
                            <input type="text" name="bandwidth" required 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="VD: Unlimited Bandwidth">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Port Speed *</label>
                            <input type="text" name="port_speed" required 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="VD: 30Mbps">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">IP</label>
                            <input type="text" name="ip" 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="VD: 01 IP NAT">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Giá gốc (VNĐ) *</label>
                            <input type="number" name="original_price" required step="0.01"
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Giá bán (VNĐ) *</label>
                            <input type="number" name="selling_price" required step="0.01"
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="hideAddModal()" 
                                class="px-4 py-2 text-slate-300 hover:text-white transition">
                            Hủy
                        </button>
                        <button type="submit" name="add_package" 
                                class="btn-primary text-white px-4 py-2 rounded-md">
                            Thêm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Package Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-dark rounded-lg max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-white mb-4">Chỉnh sửa gói VPS</h3>
                <form method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Tên gói *</label>
                            <input type="text" name="name" id="edit_name" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục *</label>
                            <select name="category" id="edit_category" required class="input-dark w-full px-3 py-2 rounded-md">
                                <option value="nat">NAT</option>
                                <option value="cheap">Cheap</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">CPU *</label>
                            <input type="text" name="cpu" id="edit_cpu" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">RAM *</label>
                            <input type="text" name="ram" id="edit_ram" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Ổ cứng *</label>
                            <input type="text" name="storage" id="edit_storage" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Băng thông *</label>
                            <input type="text" name="bandwidth" id="edit_bandwidth" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Port Speed *</label>
                            <input type="text" name="port_speed" id="edit_port_speed" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">IP</label>
                            <input type="text" name="ip" id="edit_ip" 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Giá gốc (VNĐ) *</label>
                            <input type="number" name="original_price" id="edit_original_price" required step="0.01"
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Giá bán (VNĐ) *</label>
                            <input type="number" name="selling_price" id="edit_selling_price" required step="0.01"
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Trạng thái</label>
                            <select name="status" id="edit_status" class="input-dark w-full px-3 py-2 rounded-md">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Đã khóa</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="hideEditModal()" 
                                class="px-4 py-2 text-slate-300 hover:text-white transition">
                            Hủy
                        </button>
                        <button type="submit" name="edit_package" 
                                class="btn-primary text-white px-4 py-2 rounded-md">
                            Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function hideAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function editPackage(package) {
            document.getElementById('edit_id').value = package.id;
            document.getElementById('edit_name').value = package.name;
            document.getElementById('edit_category').value = package.category || 'nat';
            document.getElementById('edit_cpu').value = package.cpu;
            document.getElementById('edit_ram').value = package.ram;
            document.getElementById('edit_storage').value = package.storage;
            document.getElementById('edit_bandwidth').value = package.bandwidth;
            document.getElementById('edit_port_speed').value = package.port_speed;
            document.getElementById('edit_ip').value = package.ip || '';
            document.getElementById('edit_original_price').value = package.original_price;
            document.getElementById('edit_selling_price').value = package.selling_price;
            document.getElementById('edit_status').value = package.status;
            
            document.getElementById('editModal').classList.remove('hidden');
        }

        function hideEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddModal();
            }
        });

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideEditModal();
            }
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>