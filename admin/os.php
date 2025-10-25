<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_os'])) {
        $data = [
            'name' => cleanInput($_POST['name']),
            'min_ram_gb' => intval($_POST['min_ram_gb']),
            'status' => 'active'
        ];
        
        if (insertData('operating_systems', $data)) {
            $_SESSION['success_message'] = 'Thêm hệ điều hành thành công!';
        } else {
            $_SESSION['error_message'] = 'Thêm hệ điều hành thất bại!';
        }
        redirect('os.php');
    }
    
    if (isset($_POST['edit_os'])) {
        $id = intval($_POST['id']);
        $data = [
            'name' => cleanInput($_POST['name']),
            'min_ram_gb' => intval($_POST['min_ram_gb']),
            'status' => cleanInput($_POST['status'])
        ];
        
        if (updateData('operating_systems', $data, 'id = ?', [$id])) {
            $_SESSION['success_message'] = 'Cập nhật hệ điều hành thành công!';
        } else {
            $_SESSION['error_message'] = 'Cập nhật hệ điều hành thất bại!';
        }
        redirect('os.php');
    }
    
    if (isset($_POST['delete_os'])) {
        $id = intval($_POST['id']);
        if (deleteData('operating_systems', 'id = ?', [$id])) {
            $_SESSION['success_message'] = 'Xóa hệ điều hành thành công!';
        } else {
            $_SESSION['error_message'] = 'Xóa hệ điều hành thất bại!';
        }
        redirect('os.php');
    }
    
    if (isset($_POST['toggle_status'])) {
        $id = intval($_POST['id']);
        $os = getOperatingSystem($id);
        if ($os) {
            $new_status = $os['status'] == 'active' ? 'inactive' : 'active';
            if (updateData('operating_systems', ['status' => $new_status], 'id = ?', [$id])) {
                $_SESSION['success_message'] = 'Cập nhật trạng thái thành công!';
            }
        }
        redirect('os.php');
    }
}

// Get operating systems
$os_list = fetchOperatingSystems();

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
    <title>Quản lý Hệ điều hành - Admin Panel</title>
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
                        <a href="packages.php" class="flex items-center space-x-3 text-gray-700 hover:bg-gray-100 p-3 rounded-lg transition">
                            <i class="fas fa-box"></i>
                            <span>Quản lý gói VPS</span>
                        </a>
                    </li>
                    <li>
                        <a href="os.php" class="flex items-center space-x-3 text-cyan-600 bg-cyan-50 p-3 rounded-lg">
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
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Quản lý Hệ điều hành</h1>
                    <p class="text-gray-600">Quản lý các hệ điều hành có sẵn cho VPS</p>
                </div>
                <button onclick="showAddModal()" 
                        class="bg-green-500 hover:bg-green-600 text-white py-2 px-6 rounded-lg font-semibold transition">
                    <i class="fas fa-plus mr-2"></i>Thêm OS mới
                </button>
            </div>

            <!-- OS Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-blue-800 mb-2">Thông tin hệ điều hành</h3>
                        <div class="text-blue-700">
                            <p class="mb-2"><strong>Số OS hiện tại:</strong> <?= count($os_list) ?> hệ điều hành</p>
                            <p class="mb-2"><strong>RAM tối thiểu:</strong> Được sử dụng để lọc OS phù hợp với gói VPS</p>
                            <p><strong>Lưu ý:</strong> Các hệ điều hành sẽ hiển thị cho khách hàng khi đặt hàng VPS</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OS Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Danh sách hệ điều hành</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên hệ điều hành
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    RAM tối thiểu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loại
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
                            <?php if (empty($os_list)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <i class="fas fa-desktop text-6xl text-gray-300 mb-4"></i>
                                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Chưa có hệ điều hành nào</h3>
                                        <p class="text-gray-500 mb-6">Vui lòng thêm hệ điều hành để khách hàng có thể lựa chọn</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($os_list as $os): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center">
                                                    <?php
                                                    $icon_class = 'fa-desktop';
                                                    $icon_color = 'text-gray-400';
                                                    if (strpos(strtolower($os['name']), 'ubuntu') !== false) {
                                                        $icon_class = 'fa-ubuntu';
                                                        $icon_color = 'text-orange-500';
                                                    } elseif (strpos(strtolower($os['name']), 'centos') !== false) {
                                                        $icon_class = 'fa-centos';
                                                        $icon_color = 'text-blue-600';
                                                    } elseif (strpos(strtolower($os['name']), 'debian') !== false) {
                                                        $icon_class = 'fa-debian';
                                                        $icon_color = 'text-red-600';
                                                    } elseif (strpos(strtolower($os['name']), 'windows') !== false) {
                                                        $icon_class = 'fa-windows';
                                                        $icon_color = 'text-blue-500';
                                                    }
                                                    ?>
                                                    <i class="fab <?= $icon_class ?> <?= $icon_color ?> text-xl"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?= htmlspecialchars($os['name']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?= $os['min_ram_gb'] ?> GB
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php
                                                if (strpos(strtolower($os['name']), 'windows') !== false) {
                                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Windows</span>';
                                                } else {
                                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Linux</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $os['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= $os['status'] == 'active' ? 'Hoạt động' : 'Đã ẩn' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="showEditModal(<?= $os['id'] ?>)" 
                                                        class="text-cyan-600 hover:text-cyan-900">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa hệ điều hành này?')">
                                                    <input type="hidden" name="id" value="<?= $os['id'] ?>">
                                                    <button type="submit" name="delete_os" 
                                                            class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="id" value="<?= $os['id'] ?>">
                                                    <button type="submit" name="toggle_status" 
                                                            class="text-gray-600 hover:text-gray-900"
                                                            title="<?= $os['status'] == 'active' ? 'Ẩn OS' : 'Hiện OS' ?>">
                                                        <i class="fas fa-<?= $os['status'] == 'active' ? 'eye-slash' : 'eye' ?>"></i>
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
                            <p class="text-gray-500 text-sm">Tổng hệ điều hành</p>
                            <p class="text-3xl font-bold text-gray-900"><?= count($os_list) ?></p>
                        </div>
                        <div class="text-3xl text-blue-500">
                            <i class="fas fa-desktop"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Hệ điều hành Linux</p>
                            <p class="text-3xl font-bold text-gray-900">
                                <?php
                                $linux_count = count(array_filter($os_list, function($os) { 
                                    return strpos(strtolower($os['name']), 'windows') === false; 
                                }));
                                echo $linux_count;
                                ?>
                            </p>
                        </div>
                        <div class="text-3xl text-green-500">
                            <i class="fab fa-linux"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Hệ điều hành Windows</p>
                            <p class="text-3xl font-bold text-gray-900">
                                <?php
                                $windows_count = count(array_filter($os_list, function($os) { 
                                    return strpos(strtolower($os['name']), 'windows') !== false; 
                                }));
                                echo $windows_count;
                                ?>
                            </p>
                        </div>
                        <div class="text-3xl text-blue-500">
                            <i class="fab fa-windows"></i>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add OS Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Thêm hệ điều hành mới</h3>
                    <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tên hệ điều hành *
                        </label>
                        <input type="text" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                               placeholder="Ubuntu-22.04">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            RAM tối thiểu (GB) *
                        </label>
                        <input type="number" name="min_ram_gb" required min="1" step="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500"
                               placeholder="1">
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" name="add_os"
                                class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg font-semibold transition">
                            <i class="fas fa-plus mr-2"></i>Thêm OS
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

    <!-- Edit OS Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Chỉnh sửa hệ điều hành</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form method="POST" id="editForm" class="space-y-4">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tên hệ điều hành *
                        </label>
                        <input type="text" name="name" id="edit_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            RAM tối thiểu (GB) *
                        </label>
                        <input type="number" name="min_ram_gb" id="edit_min_ram_gb" required min="1" step="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Trạng thái
                        </label>
                        <select name="status" id="edit_status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-cyan-500 focus:border-cyan-500">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Đã ẩn</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" name="edit_os"
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
        // OS data for editing
        const osList = <?= json_encode($os_list) ?>;
        
        function showAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }
        
        function showEditModal(id) {
            const os = osList.find(o => o.id == id);
            if (!os) return;
            
            document.getElementById('edit_id').value = os.id;
            document.getElementById('edit_name').value = os.name;
            document.getElementById('edit_min_ram_gb').value = os.min_ram_gb;
            document.getElementById('edit_status').value = os.status;
            
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
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>