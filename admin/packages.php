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
            'name' => cleanInput($_POST['name']),
            'cpu' => cleanInput($_POST['cpu']),
            'ram' => cleanInput($_POST['ram']),
            'storage' => cleanInput($_POST['storage']),
            'bandwidth' => cleanInput($_POST['bandwidth']),
            'port_speed' => cleanInput($_POST['port_speed']),
            'original_price' => floatval($_POST['original_price']),
            'selling_price' => floatval($_POST['selling_price']),
            'category' => cleanInput($_POST['category']),
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
        $id = intval($_POST['id']);
        $data = [
            'name' => cleanInput($_POST['name']),
            'cpu' => cleanInput($_POST['cpu']),
            'ram' => cleanInput($_POST['ram']),
            'storage' => cleanInput($_POST['storage']),
            'bandwidth' => cleanInput($_POST['bandwidth']),
            'port_speed' => cleanInput($_POST['port_speed']),
            'original_price' => floatval($_POST['original_price']),
            'selling_price' => floatval($_POST['selling_price']),
            'category' => cleanInput($_POST['category']),
            'status' => cleanInput($_POST['status'])
        ];
        
        if (updateData('vps_packages', $data, 'id = ?', [$id])) {
            $_SESSION['success_message'] = 'Cập nhật gói VPS thành công!';
        } else {
            $_SESSION['error_message'] = 'Cập nhật gói VPS thất bại!';
        }
        redirect('packages.php');
    }
    
    if (isset($_POST['delete_package'])) {
        $id = intval($_POST['id']);
        if (deleteData('vps_packages', 'id = ?', [$id])) {
            $_SESSION['success_message'] = 'Xóa gói VPS thành công!';
        } else {
            $_SESSION['error_message'] = 'Xóa gói VPS thất bại!';
        }
        redirect('packages.php');
    }
    
    if (isset($_POST['toggle_status'])) {
        $id = intval($_POST['id']);
        $package = getVpsPackage($id);
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
$packages = fetchVpsPackages();

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
    <title>Quản lý gói VPS - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="gradient-bg text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold">
                        <i class="fas fa-cog mr-2"></i>Admin Panel
                    </h1>
                </div>
                
                <div class="flex items-center space-x-6">
                    <a href="../index.php" class="hover:text-cyan-300 transition">
                        <i class="fas fa-home mr-1"></i>Trang chủ
                    </a>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span><?= $_SESSION['username'] ?></span>
                    </div>
                    <a href="../logout.php" class="hover:text-cyan-300 transition">
                        <i class="fas fa-sign-out-alt mr-1"></i>Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg min-h-screen">
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="index.php" class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 p-3 rounded-lg transition">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 p-3 rounded-lg transition">
                            <i class="fas fa-users"></i>
                            <span>Quản lý người dùng</span>
                        </a>
                    </li>
                    <li>
                        <a href="orders.php" class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 p-3 rounded-lg transition">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Quản lý đơn hàng</span>
                        </a>
                    </li>
                    <li>
                        <a href="deposits.php" class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 p-3 rounded-lg transition">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Quản lý nạp tiền</span>
                        </a>
                    </li>
                    <li>
                        <a href="packages.php" class="flex items-center space-x-3 text-cyan-600 bg-cyan-50 p-3 rounded-lg">
                            <i class="fas fa-box"></i>
                            <span>Quản lý gói VPS</span>
                        </a>
                    </li>
                    <li>
                        <a href="os.php" class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 p-3 rounded-lg transition">
                            <i class="fas fa-desktop"></i>
                            <span>Quản lý OS</span>
                        </a>
                    </li>
                    <li>
                        <a href="renewals.php" class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 p-3 rounded-lg transition">
                            <i class="fas fa-redo"></i>
                            <span>Lịch sử gia hạn</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 p-3 rounded-lg transition">
                            <i class="fas fa-cog"></i>
                            <span>Cài đặt</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Messages -->
            <?php if (isset($success_message)): ?>
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                <?= htmlspecialchars($success_message) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                <?= htmlspecialchars($error_message) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Header Actions -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Quản lý gói VPS</h1>
                    <p class="text-gray-600">Quản lý các gói VPS và đồng bộ từ web mẹ</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="showAddModal()" 
                            class="bg-green-500 hover:bg-green-600 text-white py-2 px-6 rounded-lg font-semibold transition">
                        <i class="fas fa-plus mr-2"></i>Thêm gói mới
                    </button>
                    <form method="POST" class="inline">
                        <button type="submit" name="sync_packages" 
                                class="bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white py-2 px-6 rounded-lg font-semibold transition">
                            <i class="fas fa-sync mr-2"></i>Đồng bộ gói VPS
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sync Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-blue-800 mb-2">Thông tin đồng bộ</h3>
                        <div class="text-blue-700">
                            <p class="mb-2"><strong>Nguồn:</strong> <a href="https://thuevpsgiare.com.vn/vps/packages?category=vps-cheap-ip-nat" target="_blank" class="underline">thuevpsgiare.com.vn</a></p>
                            <p class="mb-2"><strong>Tự động tăng giá:</strong> +8% trên giá gốc</p>
                            <p class="mb-2"><strong>Số gói hiện tại:</strong> <?= count($packages) ?> gói</p>
                            <p><strong>Lần cập nhật cuối:</strong> <?= date('d/m/Y H:i:s') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packages Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Danh sách gói VPS</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên gói
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Danh mục
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cấu hình
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Port Speed
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IP
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Giá gốc
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Giá bán
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($packages)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Chưa có gói VPS nào</h3>
                                        <p class="text-gray-500 mb-6">Vui lòng nhấn nút "Đồng bộ gói VPS" để lấy dữ liệu</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($packages as $package): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($package['name']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= ($package['category'] ?? 'nat') === 'cheap' ? 'bg-green-100 text-green-800' : 'bg-cyan-100 text-cyan-800' ?>">
                                                <i class="fas fa-<?= ($package['category'] ?? 'nat') === 'cheap' ? 'dollar-sign' : 'network-wired' ?> mr-1"></i>
                                                <?= ($package['category'] ?? 'nat') === 'cheap' ? 'Cheap' : 'NAT' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <i class="fas fa-microchip text-gray-400 text-xs"></i>
                                                    <span><?= htmlspecialchars($package['cpu']) ?></span>
                                                </div>
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <i class="fas fa-memory text-gray-400 text-xs"></i>
                                                    <span><?= htmlspecialchars($package['ram']) ?></span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <i class="fas fa-hdd text-gray-400 text-xs"></i>
                                                    <span><?= htmlspecialchars($package['storage']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?= htmlspecialchars($package['port_speed']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?= htmlspecialchars($package['ip']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?= formatPrice($package['original_price']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-cyan-600">
                                                <?= formatPrice($package['selling_price']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $package['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= $package['status'] == 'active' ? 'Hoạt động' : 'Đã ẩn' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="showEditModal(<?= $package['id'] ?>)" 
                                                        class="text-cyan-600 hover:text-cyan-900">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa gói này?')">
                                                    <input type="hidden" name="id" value="<?= $package['id'] ?>">
                                                    <button type="submit" name="delete_package" 
                                                            class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="id" value="<?= $package['id'] ?>">
                                                    <button type="submit" name="toggle_status" 
                                                            class="text-gray-600 hover:text-gray-900"
                                                            title="<?= $package['status'] == 'active' ? 'Ẩn gói' : 'Hiện gói' ?>">
                                                        <i class="fas fa-<?= $package['status'] == 'active' ? 'eye-slash' : 'eye' ?>"></i>
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

            <!-- Statistics -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Tổng gói VPS</p>
                            <p class="text-3xl font-bold text-gray-900"><?= count($packages) ?></p>
                        </div>
                        <div class="text-3xl text-blue-500">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Giá trung bình</p>
                            <p class="text-3xl font-bold text-gray-900">
                                <?php
                                $avg_price = count($packages) > 0 ? array_sum(array_column($packages, 'selling_price')) / count($packages) : 0;
                                echo formatPrice($avg_price);
                                ?>
                            </p>
                        </div>
                        <div class="text-3xl text-green-500">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Gói đang hoạt động</p>
                            <p class="text-3xl font-bold text-gray-900">
                                <?php
                                $active_count = count(array_filter($packages, function($p) { return $p['status'] == 'active'; }));
                                echo $active_count;
                                ?>
                            </p>
                        </div>
                        <div class="text-3xl text-green-500">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Package Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Thêm gói VPS mới</h3>
                    <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tên gói *
                            </label>
                            <input type="text" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="VPS Basic">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Danh mục *
                            </label>
                            <select name="category" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                                <option value="">-- Chọn danh mục --</option>
                                <option value="nat">VPS NAT</option>
                                <option value="cheap">VPS Cheap</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Port Speed *
                            </label>
                            <input type="text" name="port_speed" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="500Mbps">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                IP *
                            </label>
                            <input type="text" name="ip" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="IP Nat">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                CPU *
                            </label>
                            <input type="text" name="cpu" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="1 Core">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                RAM *
                            </label>
                            <input type="text" name="ram" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="1 GB">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ổ cứng *
                            </label>
                            <input type="text" name="storage" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="20 GB SSD">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Băng thông
                            </label>
                            <input type="text" name="bandwidth"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="Unlimited">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Giá gốc (VNĐ) *
                            </label>
                            <input type="number" name="original_price" required step="1000" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="50000">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Giá bán (VNĐ) *
                            </label>
                            <input type="number" name="selling_price" required step="1000" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="52500">
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" name="add_package"
                                class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg font-semibold transition">
                            <i class="fas fa-plus mr-2"></i>Thêm gói
                        </button>
                        <button type="button" onclick="closeAddModal()"
                                class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-2 px-4 rounded-lg font-semibold transition">
                            Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Package Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Chỉnh sửa gói VPS</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form method="POST" id="editForm" class="space-y-4">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tên gói *
                            </label>
                            <input type="text" name="name" id="edit_name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Danh mục *
                            </label>
                            <select name="category" id="edit_category" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                                <option value="">-- Chọn danh mục --</option>
                                <option value="nat">VPS NAT</option>
                                <option value="cheap">VPS Cheap</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Port Speed *
                            </label>
                            <input type="text" name="edit_port_speed" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="500Mbps">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                IP *
                            </label>
                            <input type="text" name="edit_ip" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                                   placeholder="IP Nat">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                CPU *
                            </label>
                            <input type="text" name="cpu" id="edit_cpu" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                RAM *
                            </label>
                            <input type="text" name="ram" id="edit_ram" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ổ cứng *
                            </label>
                            <input type="text" name="storage" id="edit_storage" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Băng thông
                            </label>
                            <input type="text" name="bandwidth" id="edit_bandwidth"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Giá gốc (VNĐ) *
                            </label>
                            <input type="number" name="original_price" id="edit_original_price" required step="1000" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Giá bán (VNĐ) *
                            </label>
                            <input type="number" name="selling_price" id="edit_selling_price" required step="1000" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Trạng thái
                            </label>
                            <select name="status" id="edit_status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Đã ẩn</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" name="edit_package"
                                class="flex-1 bg-cyan-500 hover:bg-cyan-600 text-white py-2 px-4 rounded-lg font-semibold transition">
                            <i class="fas fa-save mr-2"></i>Cập nhật
                        </button>
                        <button type="button" onclick="closeEditModal()"
                                class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-2 px-4 rounded-lg font-semibold transition">
                            Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Package data for editing
        const packages = <?= json_encode($packages) ?>;
        
        function showAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }
        
        function showEditModal(id) {
            const package = packages.find(p => p.id == id);
            if (!package) return;
            
            document.getElementById('edit_id').value = package.id;
            document.getElementById('edit_name').value = package.name;
            document.getElementById('edit_category').value = package.category || 'nat';
            document.getElementById('edit_cpu').value = package.cpu;
            document.getElementById('edit_ram').value = package.ram;
            document.getElementById('edit_storage').value = package.storage;
            document.getElementById('edit_bandwidth').value = package.bandwidth || '';
            document.getElementById('edit_port_speed').value = package.port_speed;
            document.getElementById('edit_original_price').value = package.original_price;
            document.getElementById('edit_selling_price').value = package.selling_price;
            document.getElementById('edit_status').value = package.status;
            
            document.getElementById('editModal').classList.remove('hidden');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        
        // Close modals when clicking outside
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddModal();
            }
        });
        
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Auto-calculate selling price (+5%)
        function calculateSellingPrice(originalPrice) {
            return Math.round(originalPrice * 1.05);
        }
        
        // Add event listeners for price calculation
        document.querySelector('input[name="original_price"]')?.addEventListener('input', function() {
            const originalPrice = parseFloat(this.value) || 0;
            const sellingPriceInput = document.querySelector('input[name="selling_price"]');
            if (sellingPriceInput && !sellingPriceInput.dataset.manual) {
                sellingPriceInput.value = calculateSellingPrice(originalPrice);
            }
        });
        
        document.querySelector('input[name="selling_price"]')?.addEventListener('focus', function() {
            this.dataset.manual = 'true';
        });
        
        document.querySelector('#edit_original_price')?.addEventListener('input', function() {
            const originalPrice = parseFloat(this.value) || 0;
            const sellingPriceInput = document.querySelector('#edit_selling_price');
            if (sellingPriceInput && !sellingPriceInput.dataset.manual) {
                sellingPriceInput.value = calculateSellingPrice(originalPrice);
            }
        });
        
        document.querySelector('#edit_selling_price')?.addEventListener('focus', function() {
            this.dataset.manual = 'true';
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>