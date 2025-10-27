<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_contact':
                $contact_type = sanitize($_POST['contact_type']);
                $contact_value = sanitize($_POST['contact_value']);
                $display_name = sanitize($_POST['display_name']);
                $icon = sanitize($_POST['icon']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $sort_order = sanitizeInt($_POST['sort_order']);
                
                insertData('contact_settings', [
                    'contact_type' => $contact_type,
                    'contact_value' => $contact_value,
                    'display_name' => $display_name,
                    'icon' => $icon,
                    'is_active' => $is_active,
                    'sort_order' => $sort_order
                ]);
                
                $_SESSION['success_message'] = 'Thêm thông tin liên hệ thành công!';
                break;
                
            case 'update_contact':
                $id = sanitizeInt($_POST['id']);
                $contact_type = sanitize($_POST['contact_type']);
                $contact_value = sanitize($_POST['contact_value']);
                $display_name = sanitize($_POST['display_name']);
                $icon = sanitize($_POST['icon']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $sort_order = sanitizeInt($_POST['sort_order']);
                
                updateData('contact_settings', [
                    'contact_type' => $contact_type,
                    'contact_value' => $contact_value,
                    'display_name' => $display_name,
                    'icon' => $icon,
                    'is_active' => $is_active,
                    'sort_order' => $sort_order
                ], 'id = ?', [$id]);
                
                $_SESSION['success_message'] = 'Cập nhật thông tin liên hệ thành công!';
                break;
                
            case 'delete_contact':
                $id = sanitizeInt($_POST['id']);
                deleteData('contact_settings', 'id = ?', [$id]);
                $_SESSION['success_message'] = 'Xóa thông tin liên hệ thành công!';
                break;
                
            case 'toggle_contact':
                $id = sanitizeInt($_POST['id']);
                $contact = fetchOne("SELECT is_active FROM contact_settings WHERE id = ?", [$id]);
                if ($contact) {
                    $newStatus = $contact['is_active'] ? 0 : 1;
                    updateData('contact_settings', ['is_active' => $newStatus], 'id = ?', [$id]);
                    $_SESSION['success_message'] = 'Cập nhật trạng thái thành công!';
                }
                break;
        }
        redirect('contact.php');
    }
}

// Get all contact settings
$contacts = fetchAll("SELECT * FROM contact_settings ORDER BY sort_order ASC, id ASC");

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
    <title>Quản lý liên hệ - Hệ thống Admin</title>
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
                        <i class="fas fa-phone text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Quản lý liên hệ</h1>
                        <p class="text-slate-400 text-sm">Thông tin liên hệ và mạng xã hội</p>
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
                        <a href="contact.php" class="flex items-center space-x-3 bg-cyan-600 text-white p-3 rounded-lg">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Tổng liên hệ</p>
                            <p class="text-2xl font-bold text-white"><?= number_format(count($contacts)) ?></p>
                        </div>
                        <div class="bg-cyan-600 p-3 rounded-lg">
                            <i class="fas fa-phone text-white"></i>
                        </div>
                    </div>
                </div>
                
                <?php
                $activeCount = count(array_filter($contacts, fn($c) => $c['is_active']));
                ?>
                
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Đang hoạt động</p>
                            <p class="text-2xl font-bold text-green-400"><?= number_format($activeCount) ?></p>
                        </div>
                        <div class="bg-green-600 p-3 rounded-lg">
                            <i class="fas fa-check text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Loại liên hệ</p>
                            <p class="text-2xl font-bold text-blue-400"><?= number_format(count(array_unique(array_column($contacts, 'contact_type')))) ?></p>
                        </div>
                        <div class="bg-blue-600 p-3 rounded-lg">
                            <i class="fas fa-list text-white"></i>
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

            <!-- Header Actions -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-white">Thông tin liên hệ</h2>
                    <p class="text-slate-400 text-sm">Quản lý thông tin liên hệ và mạng xã hội</p>
                </div>
                <button onclick="showAddModal()" 
                        class="btn-primary text-white py-2 px-6 rounded-lg font-medium transition flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Thêm liên hệ</span>
                </button>
            </div>

            <!-- Contact List -->
            <div class="card-dark rounded-lg overflow-hidden">
                <div class="p-6 border-b border-slate-700">
                    <h3 class="text-lg font-semibold text-white">Danh sách liên hệ</h3>
                </div>
                <div class="p-6 space-y-4">
                    <?php if (empty($contacts)): ?>
                        <div class="text-center py-8 text-slate-400">
                            <i class="fas fa-phone text-4xl mb-3"></i>
                            <p>Chưa có thông tin liên hệ nào</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($contacts as $contact): ?>
                            <div class="bg-slate-700 rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-cyan-600 rounded-lg flex items-center justify-center">
                                        <i class="<?= $contact['icon'] ?> text-white"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-white"><?= htmlspecialchars($contact['display_name']) ?></div>
                                        <div class="text-sm text-slate-400"><?= htmlspecialchars($contact['contact_value']) ?></div>
                                        <div class="text-xs text-slate-500">Loại: <?= ucfirst($contact['contact_type']) ?></div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_contact">
                                        <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                        <button type="submit" class="inline-flex items-center">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                <?= $contact['is_active'] ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' ?>">
                                                <?= $contact['is_active'] ? 'Hoạt động' : 'Đã khóa' ?>
                                            </span>
                                        </button>
                                    </form>
                                    <button onclick="editContact(<?= htmlspecialchars(json_encode($contact)) ?>)" 
                                            class="text-cyan-400 hover:text-cyan-300 transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Xóa liên hệ này?')">
                                        <input type="hidden" name="action" value="delete_contact">
                                        <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-300 transition">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-dark rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-white mb-4" id="modalTitle">Thêm liên hệ</h3>
                <form method="POST" id="contactForm">
                    <input type="hidden" name="action" id="formAction" value="add_contact">
                    <input type="hidden" name="id" id="contactId">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Loại liên hệ *</label>
                            <select name="contact_type" id="contactType" required class="input-dark w-full px-3 py-2 rounded-md">
                                <option value="">Chọn loại</option>
                                <option value="email">Email</option>
                                <option value="phone">Điện thoại</option>
                                <option value="facebook">Facebook</option>
                                <option value="telegram">Telegram</option>
                                <option value="zalo">Zalo</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="youtube">YouTube</option>
                                <option value="website">Website</option>
                                <option value="address">Địa chỉ</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Tên hiển thị *</label>
                            <input type="text" name="display_name" id="displayName" required 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="VD: Hotline, Email, Facebook">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Giá trị liên hệ *</label>
                            <input type="text" name="contact_value" id="contactValue" required 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="VD: 0898 686 001, email@example.com, https://facebook.com/page">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Icon</label>
                            <select name="icon" id="iconClass" class="input-dark w-full px-3 py-2 rounded-md">
                                <option value="fas fa-envelope">Email</option>
                                <option value="fas fa-phone">Điện thoại</option>
                                <option value="fab fa-facebook">Facebook</option>
                                <option value="fab fa-telegram">Telegram</option>
                                <option value="fas fa-comments">Zalo</option>
                                <option value="fab fa-whatsapp">WhatsApp</option>
                                <option value="fab fa-youtube">YouTube</option>
                                <option value="fas fa-globe">Website</option>
                                <option value="fas fa-map-marker-alt">Địa chỉ</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Thứ tự</label>
                                <input type="number" name="sort_order" id="sortOrder" value="0" min="0"
                                       class="input-dark w-full px-3 py-2 rounded-md">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Trạng thái</label>
                                <div class="flex items-center mt-3">
                                    <input type="checkbox" name="is_active" id="isActive" checked class="mr-2">
                                    <label for="isActive" class="text-sm text-slate-300">Hoạt động</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="hideModal()" 
                                class="px-4 py-2 text-slate-300 hover:text-white transition">
                            Hủy
                        </button>
                        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-md">
                            Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Thêm liên hệ';
            document.getElementById('formAction').value = 'add_contact';
            document.getElementById('contactForm').reset();
            document.getElementById('contactModal').classList.remove('hidden');
        }

        function hideModal() {
            document.getElementById('contactModal').classList.add('hidden');
        }

        function editContact(contact) {
            document.getElementById('modalTitle').textContent = 'Chỉnh sửa liên hệ';
            document.getElementById('formAction').value = 'update_contact';
            document.getElementById('contactId').value = contact.id;
            document.getElementById('contactType').value = contact.contact_type;
            document.getElementById('displayName').value = contact.display_name;
            document.getElementById('contactValue').value = contact.contact_value;
            document.getElementById('iconClass').value = contact.icon;
            document.getElementById('sortOrder').value = contact.sort_order;
            document.getElementById('isActive').checked = contact.is_active;
            document.getElementById('contactModal').classList.remove('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('contactModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideModal();
            }
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>