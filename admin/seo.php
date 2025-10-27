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
            case 'update_seo':
                $page_name = sanitize($_POST['page_name']);
                $meta_title = sanitize($_POST['meta_title']);
                $meta_description = sanitize($_POST['meta_description']);
                $meta_keywords = sanitize($_POST['meta_keywords']);
                $og_title = sanitize($_POST['og_title']);
                $og_description = sanitize($_POST['og_description']);
                $og_image = sanitize($_POST['og_image']);
                $canonical_url = sanitize($_POST['canonical_url']);
                $robots = sanitize($_POST['robots']);
                
                // Check if record exists
                $existing = fetchOne("SELECT id FROM seo_settings WHERE page_name = ?", [$page_name]);
                
                if ($existing) {
                    updateData('seo_settings', [
                        'meta_title' => $meta_title,
                        'meta_description' => $meta_description,
                        'meta_keywords' => $meta_keywords,
                        'og_title' => $og_title,
                        'og_description' => $og_description,
                        'og_image' => $og_image,
                        'canonical_url' => $canonical_url,
                        'robots' => $robots
                    ], 'page_name = ?', [$page_name]);
                } else {
                    insertData('seo_settings', [
                        'page_name' => $page_name,
                        'meta_title' => $meta_title,
                        'meta_description' => $meta_description,
                        'meta_keywords' => $meta_keywords,
                        'og_title' => $og_title,
                        'og_description' => $og_description,
                        'og_image' => $og_image,
                        'canonical_url' => $canonical_url,
                        'robots' => $robots
                    ]);
                }
                
                $_SESSION['success_message'] = 'Cập nhật SEO thành công!';
                break;
                
            case 'delete_seo':
                $page_name = sanitize($_POST['page_name']);
                deleteData('seo_settings', 'page_name = ?', [$page_name]);
                $_SESSION['success_message'] = 'Xóa cài đặt SEO thành công!';
                break;
        }
        redirect('seo.php');
    }
}

// Get all SEO settings
$seoSettings = fetchAll("SELECT * FROM seo_settings ORDER BY page_name");

// Get specific page settings if requested
$currentPageSettings = null;
if (isset($_GET['page'])) {
    $currentPageSettings = fetchOne("SELECT * FROM seo_settings WHERE page_name = ?", [sanitize($_GET['page'])]);
}

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
    <title>Quản lý SEO - Hệ thống Admin</title>
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
                        <i class="fas fa-search text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Quản lý SEO</h1>
                        <p class="text-slate-400 text-sm">Tối ưu hóa công cụ tìm kiếm</p>
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
                        <a href="seo.php" class="flex items-center space-x-3 bg-cyan-600 text-white p-3 rounded-lg">
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
                            <p class="text-slate-400 text-sm">Tổng trang</p>
                            <p class="text-2xl font-bold text-white"><?= count($seoSettings) ?></p>
                        </div>
                        <div class="bg-cyan-600 p-3 rounded-lg">
                            <i class="fas fa-file text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Đã tối ưu</p>
                            <p class="text-2xl font-bold text-white"><?= count(array_filter($seoSettings, fn($s) => !empty($s['meta_title']))) ?></p>
                        </div>
                        <div class="bg-green-600 p-3 rounded-lg">
                            <i class="fas fa-check text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">Cần tối ưu</p>
                            <p class="text-2xl font-bold text-white"><?= count(array_filter($seoSettings, fn($s) => empty($s['meta_title']))) ?></p>
                        </div>
                        <div class="bg-yellow-600 p-3 rounded-lg">
                            <i class="fas fa-exclamation text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="card-dark rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm">OG Images</p>
                            <p class="text-2xl font-bold text-white"><?= count(array_filter($seoSettings, fn($s) => !empty($s['og_image']))) ?></p>
                        </div>
                        <div class="bg-purple-600 p-3 rounded-lg">
                            <i class="fas fa-image text-white"></i>
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

            <!-- SEO Settings Table -->
            <div class="card-dark rounded-lg overflow-hidden">
                <div class="p-6 border-b border-slate-700">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-white">Cài đặt SEO</h2>
                        <button onclick="showAddModal()" 
                                class="btn-primary text-white py-2 px-6 rounded-lg font-medium transition flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Thêm trang mới</span>
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full table-dark">
                        <thead>
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Trang</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Tiêu đề</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Mô tả</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Cập nhật</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <?php if (empty($seoSettings)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fas fa-search text-4xl mb-3"></i>
                                        <p>Chưa có cài đặt SEO nào</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($seoSettings as $setting): ?>
                                    <tr class="hover:bg-slate-700 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-cyan-600 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-file text-white text-sm"></i>
                                                </div>
                                                <span class="font-medium text-white"><?= ucfirst($setting['page_name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-white max-w-xs truncate" title="<?= htmlspecialchars($setting['meta_title']) ?>">
                                                <?= htmlspecialchars($setting['meta_title']) ?: '<span class="text-slate-400">Chưa đặt</span>' ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-slate-400 max-w-xs truncate" title="<?= htmlspecialchars($setting['meta_description']) ?>">
                                                <?= htmlspecialchars($setting['meta_description']) ?: '<span class="text-slate-500">Chưa đặt</span>' ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                            <?= date('d/m/Y H:i', strtotime($setting['updated_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="editPage('<?= $setting['page_name'] ?>')" 
                                                        class="text-cyan-400 hover:text-cyan-300 transition">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deletePage('<?= $setting['page_name'] ?>')" 
                                                        class="text-red-400 hover:text-red-300 transition">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Edit Form (shown when editing a page) -->
            <?php if ($currentPageSettings): ?>
                <div class="card-dark rounded-lg p-6 mt-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-white">Chỉnh sửa SEO: <?= ucfirst($currentPageSettings['page_name']) ?></h3>
                        <a href="seo.php" class="text-slate-400 hover:text-white transition">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="update_seo">
                        <input type="hidden" name="page_name" value="<?= $currentPageSettings['page_name'] ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Meta Title</label>
                                <input type="text" name="meta_title" value="<?= htmlspecialchars($currentPageSettings['meta_title']) ?>" 
                                       class="input-dark w-full px-3 py-2 rounded-md"
                                       placeholder="Nhập meta title (50-60 ký tự)" maxlength="60">
                                <p class="text-xs text-slate-500 mt-1">Khuyến nghị: 50-60 ký tự</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">OG Title</label>
                                <input type="text" name="og_title" value="<?= htmlspecialchars($currentPageSettings['og_title']) ?>" 
                                       class="input-dark w-full px-3 py-2 rounded-md"
                                       placeholder="Tiêu đề Open Graph">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Meta Description</label>
                                <textarea name="meta_description" rows="3" 
                                          class="input-dark w-full px-3 py-2 rounded-md"
                                          placeholder="Nhập meta description (150-160 ký tự)" maxlength="160"><?= htmlspecialchars($currentPageSettings['meta_description']) ?></textarea>
                                <p class="text-xs text-slate-500 mt-1">Khuyến nghị: 150-160 ký tự</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">OG Description</label>
                                <textarea name="og_description" rows="3" 
                                          class="input-dark w-full px-3 py-2 rounded-md"
                                          placeholder="Mô tả Open Graph"><?= htmlspecialchars($currentPageSettings['og_description']) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Meta Keywords</label>
                                <input type="text" name="meta_keywords" value="<?= htmlspecialchars($currentPageSettings['meta_keywords']) ?>" 
                                       class="input-dark w-full px-3 py-2 rounded-md"
                                       placeholder="keyword1, keyword2, keyword3">
                                <p class="text-xs text-slate-500 mt-1">Phân cách bằng dấu phẩy</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">OG Image</label>
                                <input type="url" name="og_image" value="<?= htmlspecialchars($currentPageSettings['og_image']) ?>" 
                                       class="input-dark w-full px-3 py-2 rounded-md"
                                       placeholder="https://example.com/image.jpg">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Canonical URL</label>
                                <input type="url" name="canonical_url" value="<?= htmlspecialchars($currentPageSettings['canonical_url']) ?>" 
                                       class="input-dark w-full px-3 py-2 rounded-md"
                                       placeholder="https://example.com/page">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Robots Meta</label>
                                <select name="robots" class="input-dark w-full px-3 py-2 rounded-md">
                                    <option value="index,follow" <?= $currentPageSettings['robots'] === 'index,follow' ? 'selected' : '' ?>>index, follow</option>
                                    <option value="noindex,follow" <?= $currentPageSettings['robots'] === 'noindex,follow' ? 'selected' : '' ?>>noindex, follow</option>
                                    <option value="index,nofollow" <?= $currentPageSettings['robots'] === 'index,nofollow' ? 'selected' : '' ?>>index, nofollow</option>
                                    <option value="noindex,nofollow" <?= $currentPageSettings['robots'] === 'noindex,nofollow' ? 'selected' : '' ?>>noindex, nofollow</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <a href="seo.php" class="px-4 py-2 border border-slate-600 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                                Hủy
                            </a>
                            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-md">
                                <i class="fas fa-save mr-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-dark rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Thêm SEO cho trang mới</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_seo">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Tên trang</label>
                            <select name="page_name" required class="input-dark w-full px-3 py-2 rounded-md">
                                <option value="">Chọn trang</option>
                                <option value="home">Trang chủ</option>
                                <option value="packages">Gói VPS</option>
                                <option value="services">Dịch vụ</option>
                                <option value="contact">Liên hệ</option>
                                <option value="about">Về chúng tôi</option>
                                <option value="register">Đăng ký</option>
                                <option value="login">Đăng nhập</option>
                                <option value="deposit">Nạp tiền</option>
                                <option value="profile">Hồ sơ</option>
                                <option value="order">Đặt hàng</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Meta Title</label>
                            <input type="text" name="meta_title" 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="Nhập meta title">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Meta Description</label>
                            <textarea name="meta_description" rows="3" 
                                      class="input-dark w-full px-3 py-2 rounded-md"
                                      placeholder="Nhập meta description"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Meta Keywords</label>
                            <input type="text" name="meta_keywords" 
                                   class="input-dark w-full px-3 py-2 rounded-md"
                                   placeholder="keyword1, keyword2, keyword3">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="hideAddModal()" 
                                class="px-4 py-2 text-slate-300 hover:text-white transition">
                            Hủy
                        </button>
                        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-md">
                            Thêm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_seo">
        <input type="hidden" name="page_name" id="delete_page_name">
    </form>

    <script>
        function showAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function hideAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function editPage(pageName) {
            window.location.href = '?page=' + pageName;
        }

        function deletePage(pageName) {
            if (confirm('Bạn có chắc chắn muốn xóa cài đặt SEO của trang này?')) {
                document.getElementById('delete_page_name').value = pageName;
                document.getElementById('deleteForm').submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddModal();
            }
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>