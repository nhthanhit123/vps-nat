<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$page_title = 'Quản Lý Thông Báo - Admin Panel';
$header_title = 'Quản Lý Thông Báo';
$header_description = 'Quản lý thông báo và cảnh báo website';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_notification':
                $title = cleanInput($_POST['title']);
                $message = cleanInput($_POST['message']);
                $type = cleanInput($_POST['type']);
                $target_page = cleanInput($_POST['target_page']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
                $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
                
                if (insertData('notifications', [
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'target_page' => $target_page,
                    'is_active' => $is_active,
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ])) {
                    $_SESSION['success_message'] = 'Thêm thông báo thành công!';
                } else {
                    $_SESSION['error_message'] = 'Thêm thông báo thất bại!';
                }
                break;
                
            case 'updateData_notification':
                $id = (int)$_POST['id'];
                $title = cleanInput($_POST['title']);
                $message = cleanInput($_POST['message']);
                $type = cleanInput($_POST['type']);
                $target_page = cleanInput($_POST['target_page']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
                $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
                
                if (updateData('notifications', [
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'target_page' => $target_page,
                    'is_active' => $is_active,
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ], 'id = ?', [$id])) {
                    $_SESSION['success_message'] = 'Cập nhật thông báo thành công!';
                } else {
                    $_SESSION['error_message'] = 'Cập nhật thông báo thất bại!';
                }
                break;
                
            case 'deleteData_notification':
                $id = (int)$_POST['id'];
                if (deleteData('notifications', 'id = ?', [$id])) {
                    $_SESSION['success_message'] = 'Xóa thông báo thành công!';
                } else {
                    $_SESSION['error_message'] = 'Xóa thông báo thất bại!';
                }
                break;
                
            case 'toggle_notification':
                $id = (int)$_POST['id'];
                $notification = fetchOne("SELECT is_active FROM notifications WHERE id = ?", [$id]);
                if ($notification) {
                    $newStatus = $notification['is_active'] ? 0 : 1;
                    updateData('notifications', ['is_active' => $newStatus], 'id = ?', [$id]);
                    $_SESSION['success_message'] = 'Cập nhật trạng thái thành công!';
                }
                break;
        }
        redirect('notifications.php');
    }
}

// Get all notifications
$notifications = fetchAll("SELECT * FROM notifications ORDER BY created_at DESC");

// Statistics
$total_notifications = count($notifications);
$active_notifications = count(fetchAll("SELECT id FROM notifications WHERE is_active = 1"));
$info_notifications = count(fetchAll("SELECT id FROM notifications WHERE type = 'info'"));
$success_notifications = count(fetchAll("SELECT id FROM notifications WHERE type = 'success'"));

function getNotificationColor($type) {
    switch($type) {
        case 'success': return 'green';
        case 'warning': return 'yellow';
        case 'error': return 'red';
        default: return 'blue';
    }
}

function getNotificationTypeLabel($type) {
    switch($type) {
        case 'info': return 'Thông tin';
        case 'success': return 'Thành công';
        case 'warning': return 'Cảnh báo';
        case 'error': return 'Lỗi';
        default: return $type;
    }
}

ob_start();
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-bell text-blue-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded">Tổng</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($total_notifications) ?></h3>
        <p class="text-gray-600 text-sm">Tổng Thông Báo</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">Hoạt động</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($active_notifications) ?></h3>
        <p class="text-gray-600 text-sm">Đang Hiển Thị</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-info-circle text-blue-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded">Info</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($info_notifications) ?></h3>
        <p class="text-gray-600 text-sm">Thông Tin</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">Success</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($success_notifications) ?></h3>
        <p class="text-gray-600 text-sm">Thành Công</p>
    </div>
</div>

<!-- Header Actions -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Danh sách thông báo</h2>
            <p class="text-gray-600 text-sm mt-1">Quản lý thông báo và cảnh báo website</p>
        </div>
        <button onclick="showAddModal()" 
                class="btn-primary text-white px-6 py-2 rounded-lg font-medium hover:shadow-lg transition-all">
            <i class="fas fa-plus mr-2"></i>Thêm Thông Báo
        </button>
    </div>
</div>

<!-- Notifications Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiêu đề</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trang mục tiêu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lịch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($notifications)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-bell text-4xl mb-3 text-gray-300"></i>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">Chưa có thông báo nào</h3>
                            <p class="text-gray-500 mb-6">Tạo thông báo đầu tiên để hiển thị cho người dùng</p>
                            <button onclick="showAddModal()" 
                                    class="btn-primary text-white px-6 py-2 rounded-lg font-medium">
                                <i class="fas fa-plus mr-2"></i>Thêm Thông Báo Đầu Tiên
                            </button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-<?= getNotificationColor($notification['type']) ?>-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-bell text-<?= getNotificationColor($notification['type']) ?>-600 text-sm"></i>
                                    </div>
                                    <span class="font-medium text-gray-900"><?= htmlspecialchars($notification['title']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate" title="<?= htmlspecialchars($notification['message']) ?>">
                                    <?= htmlspecialchars($notification['message']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= getNotificationColor($notification['type']) ?>-100 text-<?= getNotificationColor($notification['type']) ?>-800">
                                    <?= getNotificationTypeLabel($notification['type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $notification['target_page'] ? ucfirst($notification['target_page']) : 'Tất cả trang' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_notification">
                                    <input type="hidden" name="id" value="<?= $notification['id'] ?>">
                                    <button type="submit" class="inline-flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $notification['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                            <?= $notification['is_active'] ? 'Hoạt động' : 'Không hoạt động' ?>
                                        </span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if ($notification['start_date'] && $notification['end_date']): ?>
                                    <?= date('d/m', strtotime($notification['start_date'])) ?> - <?= date('d/m', strtotime($notification['end_date'])) ?>
                                <?php elseif ($notification['start_date']): ?>
                                    Từ <?= date('d/m', strtotime($notification['start_date'])) ?>
                                <?php elseif ($notification['end_date']): ?>
                                    Đến <?= date('d/m', strtotime($notification['end_date'])) ?>
                                <?php else: ?>
                                    Luôn luôn
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button onclick="editNotification(<?= $notification['id'] ?>)" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors" 
                                            title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="deleteData_notification">
                                        <input type="hidden" name="id" value="<?= $notification['id'] ?>">
                                        <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn xóa thông báo này?')" 
                                                class="text-red-600 hover:text-red-900 transition-colors" 
                                                title="Xóa">
                                            <i class="fas fa-trash"></i>
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

<!-- Add/Edit Modal -->
<div id="notificationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 mb-4" id="modalTitle">Thêm Thông Báo</h3>
        <form method="POST" id="notificationForm">
            <input type="hidden" name="action" id="formAction" value="add_notification">
            <input type="hidden" name="id" id="notificationId">
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề *</label>
                        <input type="text" name="title" id="notificationTitle" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                               placeholder="Nhập tiêu đề thông báo">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Loại *</label>
                        <select name="type" id="notificationType" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <option value="info">Thông tin</option>
                            <option value="success">Thành công</option>
                            <option value="warning">Cảnh báo</option>
                            <option value="error">Lỗi</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nội dung *</label>
                    <textarea name="message" id="notificationMessage" rows="3" required 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                              placeholder="Nhập nội dung thông báo"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trang mục tiêu</label>
                        <select name="target_page" id="targetPage" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <option value="">Tất cả trang</option>
                            <option value="home">Trang chủ</option>
                            <option value="packages">Gói dịch vụ</option>
                            <option value="services">Dịch vụ</option>
                            <option value="contact">Liên hệ</option>
                            <option value="about">Về chúng tôi</option>
                            <option value="register">Đăng ký</option>
                            <option value="login">Đăng nhập</option>
                            <option value="deposit">Nạp tiền</option>
                            <option value="profile">Hồ sơ</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                        <div class="flex items-center mt-3">
                            <input type="checkbox" name="is_active" id="isActive" checked class="mr-2">
                            <label for="isActive" class="text-sm text-gray-700">Hoạt động</label>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ngày bắt đầu</label>
                        <input type="datetime-local" name="start_date" id="startDate" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ngày kết thúc</label>
                        <input type="datetime-local" name="end_date" id="endDate" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Hủy
                </button>
                <button type="submit" 
                        class="btn-primary text-white px-4 py-2 rounded-lg">
                    <span id="submitButtonText">Thêm</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Thêm Thông Báo';
    document.getElementById('formAction').value = 'add_notification';
    document.getElementById('submitButtonText').textContent = 'Thêm';
    document.getElementById('notificationForm').reset();
    document.getElementById('notificationModal').classList.remove('hidden');
}

function editNotification(id) {
    // Fetch notification data via AJAX or use data from page
    showLoading();
    
    // For now, let's use a simple approach - in production you'd fetch this via AJAX
    const notifications = <?= json_encode($notifications) ?>;
    const notification = notifications.find(n => n.id == id);
    
    if (notification) {
        document.getElementById('modalTitle').textContent = 'Chỉnh Sửa Thông Báo';
        document.getElementById('formAction').value = 'updateData_notification';
        document.getElementById('submitButtonText').textContent = 'Cập nhật';
        document.getElementById('notificationId').value = notification.id;
        document.getElementById('notificationTitle').value = notification.title;
        document.getElementById('notificationMessage').value = notification.message;
        document.getElementById('notificationType').value = notification.type;
        document.getElementById('targetPage').value = notification.target_page || '';
        document.getElementById('isActive').checked = notification.is_active == 1;
        document.getElementById('startDate').value = notification.start_date ? notification.start_date.slice(0, 16) : '';
        document.getElementById('endDate').value = notification.end_date ? notification.end_date.slice(0, 16) : '';
        
        document.getElementById('notificationModal').classList.remove('hidden');
    }
    
    hideLoading();
}

function closeModal() {
    document.getElementById('notificationModal').classList.add('hidden');
    document.getElementById('notificationForm').reset();
}

// Close modal when clicking outside
document.getElementById('notificationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php
$content = ob_get_clean();
require_once 'includes/header.php';
?>