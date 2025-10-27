<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $role = $_POST['role'];
    $balance = (float)$_POST['balance'];
    
    $errors = [];
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $errors[] = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    }
    
    if (getUserByUsername($username)) {
        $errors[] = 'Tên đăng nhập đã tồn tại';
    }
    
    if (getUserByEmail($email)) {
        $errors[] = 'Email đã tồn tại';
    }
    
    if (empty($errors)) {
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $full_name,
            'phone' => $phone,
            'role' => $role,
            'balance' => $balance
        ];
        
        if (createUser($userData)) {
            $_SESSION['success_message'] = 'Thêm người dùng thành công!';
            redirect('users.php');
        } else {
            $errors[] = 'Thêm người dùng thất bại. Vui lòng thử lại.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = sanitizeInt($_POST['user_id']);
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $role = $_POST['role'];
    $balance = (float)$_POST['balance'];
    $status = $_POST['status'];
    $password = $_POST['password'] ?? '';
    
    $updateData = [
        'full_name' => $full_name,
        'phone' => $phone,
        'role' => $role,
        'balance' => $balance,
        'status' => $status
    ];
    
    if (!empty($password)) {
        $updateData['password'] = $password;
    }
    
    if (updateUser($user_id, $updateData)) {
        $_SESSION['success_message'] = 'Cập nhật người dùng thành công!';
        redirect('users.php');
    } else {
        $errors[] = 'Cập nhật thất bại. Vui lòng thử lại.';
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = sanitizeInt($_GET['id']);
    
    // Don't allow deleting admin users or yourself
    $user = getUser($user_id);
    if ($user && $user['role'] != 'admin' && $user['id'] != $_SESSION['user_id']) {
        deleteData('users', 'id = ?', [$user_id]);
        $_SESSION['success_message'] = 'Xóa người dùng thành công!';
    }
    
    redirect('users.php');
}

$page = isset($_GET['page']) ? sanitizeInt($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$users = fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$totalUsers = fetchOne("SELECT COUNT(*) as total FROM users")['total'];
$totalPages = ceil($totalUsers / $limit);

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
    <title>Quản lý người dùng - Hệ thống Admin</title>
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
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Quản lý người dùng</h1>
                        <p class="text-slate-400 text-sm">Hệ thống quản trị VPS</p>
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
                        <a href="users.php" class="flex items-center space-x-3 bg-cyan-600 text-white p-3 rounded-lg">
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
                            <p class="text-slate-400 text-sm">Tổng người dùng</p>
                            <p class="text-2xl font-bold text-white"><?= number_format($totalUsers) ?></p>
                        </div>
                        <div class="bg-cyan-600 p-3 rounded-lg">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Admin</p>
                            <p class="text-2xl font-bold text-white"><?= count(array_filter($users, fn($u) => $u['role'] == 'admin')) ?></p>
                        </div>
                        <div class="bg-purple-600 p-3 rounded-lg">
                            <i class="fas fa-user-shield text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Người dùng</p>
                            <p class="text-2xl font-bold text-white"><?= count(array_filter($users, fn($u) => $u['role'] == 'user')) ?></p>
                        </div>
                        <div class="bg-blue-600 p-3 rounded-lg">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Hoạt động</p>
                            <p class="text-2xl font-bold text-white"><?= count(array_filter($users, fn($u) => $u['status'] == 'active')) ?></p>
                        </div>
                        <div class="bg-green-600 p-3 rounded-lg">
                            <i class="fas fa-check-circle text-white"></i>
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

            <?php if (!empty($errors)): ?>
                <div class="bg-red-900 border border-red-700 text-red-100 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <ul class="text-sm">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="card-dark rounded-lg overflow-hidden">
                <div class="p-6 border-b border-slate-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-white">Danh sách người dùng</h2>
                        <button onclick="showAddUserModal()" 
                                class="btn-primary text-white py-2 px-6 rounded-lg font-medium transition flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Thêm người dùng</span>
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full table-dark">
                        <thead>
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Thông tin</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Liên hệ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Số dư</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Vai trò</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Ngày tạo</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fas fa-users text-4xl mb-3"></i>
                                        <p>Không có người dùng nào</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="hover:bg-slate-700 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                                            <?= $user['id'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white"><?= htmlspecialchars($user['full_name']) ?></div>
                                            <div class="text-sm text-slate-400">@<?= htmlspecialchars($user['username']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-white"><?= htmlspecialchars($user['email']) ?></div>
                                            <div class="text-sm text-slate-400"><?= htmlspecialchars($user['phone'] ?: 'N/A') ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-cyan-400">
                                            <?= number_format($user['balance'], 0, ',', '.') ?> VNĐ
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                <?= $user['role'] == 'admin' ? 'bg-purple-900 text-purple-200' : 'bg-blue-900 text-blue-200' ?>">
                                                <?= $user['role'] == 'admin' ? 'Admin' : 'Người dùng' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                <?= $user['status'] == 'active' ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' ?>">
                                                <?= $user['status'] == 'active' ? 'Hoạt động' : 'Đã khóa' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                            <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="showEditUserModal(<?= htmlspecialchars(json_encode($user)) ?>)" 
                                                        class="text-cyan-400 hover:text-cyan-300 transition">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <?php if ($user['role'] != 'admin' && $user['id'] != $_SESSION['user_id']): ?>
                                                    <button onclick="confirmDelete(<?= $user['id'] ?>)" 
                                                            class="text-red-400 hover:text-red-300 transition">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
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
                                <a href="?page=<?= $page - 1 ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-md text-slate-300 bg-slate-700 hover:bg-slate-600">
                                    Trước
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>" 
                                   class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-md text-slate-300 bg-slate-700 hover:bg-slate-600">
                                    Sau
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-slate-400">
                                    Hiển thị <span class="font-medium text-white"><?= ($offset + 1) ?></span> đến 
                                    <span class="font-medium text-white"><?= min($offset + $limit, $totalUsers) ?></span> của 
                                    <span class="font-medium text-white"><?= $totalUsers ?></span> kết quả
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?= $page - 1 ?>" 
                                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-600 bg-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-600">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <a href="?page=<?= $i ?>" 
                                           class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                                               <?= $i == $page 
                                                   ? 'z-10 bg-cyan-600 border-cyan-600 text-white' 
                                                   : 'border-slate-600 bg-slate-700 text-slate-300 hover:bg-slate-600' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?= $page + 1 ?>" 
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

    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-dark rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Thêm người dùng mới</h3>
                <form method="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Tên đăng nhập *</label>
                            <input type="text" name="username" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Email *</label>
                            <input type="email" name="email" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Mật khẩu *</label>
                            <input type="password" name="password" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Họ tên *</label>
                            <input type="text" name="full_name" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Số điện thoại</label>
                            <input type="text" name="phone" 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Vai trò</label>
                            <select name="role" class="input-dark w-full px-3 py-2 rounded-md">
                                <option value="user">Người dùng</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Số dư (VNĐ)</label>
                            <input type="number" name="balance" step="0.01" value="0" 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="hideAddUserModal()" 
                                class="px-4 py-2 text-slate-300 hover:text-white transition">
                            Hủy
                        </button>
                        <button type="submit" name="add_user" 
                                class="btn-primary text-white px-4 py-2 rounded-md">
                            Thêm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-dark rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Cập nhật người dùng</h3>
                <form method="POST">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Họ tên *</label>
                            <input type="text" name="full_name" id="edit_full_name" required 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Số điện thoại</label>
                            <input type="text" name="phone" id="edit_phone" 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Vai trò</label>
                            <select name="role" id="edit_role" class="input-dark w-full px-3 py-2 rounded-md">
                                <option value="user">Người dùng</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Trạng thái</label>
                            <select name="status" id="edit_status" class="input-dark w-full px-3 py-2 rounded-md">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Đã khóa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Số dư (VNĐ)</label>
                            <input type="number" name="balance" id="edit_balance" step="0.01" 
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Mật khẩu mới</label>
                            <input type="password" name="password" id="edit_password" 
                                   placeholder="Để trống nếu không đổi"
                                   class="input-dark w-full px-3 py-2 rounded-md">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="hideEditUserModal()" 
                                class="px-4 py-2 text-slate-300 hover:text-white transition">
                            Hủy
                        </button>
                        <button type="submit" name="update_user" 
                                class="btn-primary text-white px-4 py-2 rounded-md">
                            Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function hideAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
        }

        function showEditUserModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_full_name').value = user.full_name;
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.status;
            document.getElementById('edit_balance').value = user.balance;
            document.getElementById('edit_password').value = '';
            document.getElementById('editUserModal').classList.remove('hidden');
        }

        function hideEditUserModal() {
            document.getElementById('editUserModal').classList.add('hidden');
        }

        function confirmDelete(userId) {
            if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
                window.location.href = '?action=delete&id=' + userId;
            }
        }

        // Close modals when clicking outside
        document.getElementById('addUserModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddUserModal();
            }
        });

        document.getElementById('editUserModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideEditUserModal();
            }
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>