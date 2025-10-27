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
        $os = fetchOne("SELECT * FROM operating_systems WHERE id = ?", [$id]);
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
$os_list = fetchAll("SELECT * FROM operating_systems ORDER BY name ASC");

// Statistics
$total_os = count($os_list);
$active_os = count(fetchAll("SELECT id FROM operating_systems WHERE status = 'active'"));
$inactive_os = count(fetchAll("SELECT id FROM operating_systems WHERE status = 'inactive'"));
$linux_os = count(fetchAll("SELECT id FROM operating_systems WHERE name NOT LIKE '%windows%'"));
$windows_os = count(fetchAll("SELECT id FROM operating_systems WHERE name LIKE '%windows%'"));

$page_title = 'Quản Lý Hệ Điều Hành - Admin Panel';
$header_title = 'Quản Lý Hệ Điều Hành';
$header_description = 'Quản lý các hệ điều hành có sẵn cho VPS';

ob_start();
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-desktop text-blue-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded">Tổng</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($total_os) ?></h3>
        <p class="text-gray-600 text-sm">Tổng HĐH</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded">Hoạt động</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($active_os) ?></h3>
        <p class="text-gray-600 text-sm">Đang Hoạt Động</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fab fa-linux text-orange-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-orange-600 bg-orange-100 px-2 py-1 rounded">Linux</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($linux_os) ?></h3>
        <p class="text-gray-600 text-sm">HĐH Linux</p>
    </div>

    <div class="stat-card rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fab fa-windows text-blue-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded">Windows</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900"><?= number_format($windows_os) ?></h3>
        <p class="text-gray-600 text-sm">HĐH Windows</p>
    </div>
</div>

<!-- Header Actions -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Danh sách hệ điều hành</h2>
            <p class="text-gray-600 text-sm mt-1">Quản lý các hệ điều hành có sẵn cho VPS</p>
        </div>
        <button onclick="showAddModal()" 
                class="btn-primary text-white px-6 py-2 rounded-lg font-medium hover:shadow-lg transition-all">
            <i class="fas fa-plus mr-2"></i>Thêm HĐH mới
        </button>
    </div>
</div>

<!-- OS Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
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
                            <button onclick="showAddModal()" 
                                    class="btn-primary text-white px-6 py-2 rounded-lg font-medium">
                                <i class="fas fa-plus mr-2"></i>Thêm HĐH đầu tiên
                            </button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($os_list as $os): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
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
                                        echo '<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Windows</span>';
                                    } else {
                                        echo '<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Linux</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?= $os['status'] == 'active' 
                                        ? 'bg-green-100 text-green-800' 
                                        : 'bg-red-100 text-red-800' ?>">
                                    <?= $os['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button onclick="toggleStatus(<?= $os['id'] ?>)" 
                                            class="text-<?= $os['status'] == 'active' ? 'yellow' : 'green' ?>-600 hover:text-<?= $os['status'] == 'active' ? 'yellow' : 'green' ?>-900 transition-colors" 
                                            title="<?= $os['status'] == 'active' ? 'Tắt' : 'Bật' ?>">
                                        <i class="fas fa-<?= $os['status'] == 'active' ? 'pause' : 'play' ?>"></i>
                                    </button>
                                    
                                    <button onclick="showEditModal(<?= $os['id'] ?>, '<?= htmlspecialchars($os['name']) ?>', <?= $os['min_ram_gb'] ?>, '<?= $os['status'] ?>')" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors" 
                                            title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button onclick="confirmDelete('os.php', <?= $os['id'] ?>)" 
                                            class="text-red-600 hover:text-red-900 transition-colors" 
                                            title="Xóa">
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

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Thêm Hệ Điều Hành Mới</h3>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tên hệ điều hành</label>
                    <input type="text" name="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                           placeholder="VD: Ubuntu-22.04">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">RAM tối thiểu (GB)</label>
                    <input type="number" name="min_ram_gb" required min="1" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                           placeholder="1">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAddModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Hủy
                    </button>
                    <button type="submit" name="add_os" 
                            class="btn-primary text-white px-4 py-2 rounded-lg">
                        Thêm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Chỉnh Sửa Hệ Điều Hành</h3>
            <form method="POST">
                <input type="hidden" name="id" id="editId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tên hệ điều hành</label>
                    <input type="text" name="name" id="editName" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">RAM tối thiểu (GB)</label>
                    <input type="number" name="min_ram_gb" id="editMinRam" required min="1" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                    <select name="status" id="editStatus" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Không hoạt động</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Hủy
                    </button>
                    <button type="submit" name="edit_os" 
                            class="btn-primary text-white px-4 py-2 rounded-lg">
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

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

function showEditModal(id, name, minRam, status) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editMinRam').value = minRam;
    document.getElementById('editStatus').value = status;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}


function confirmDelete(page, id) {
    if (confirm('Bạn có chắc chắn muốn xóa hệ điều hành này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = page;
        form.innerHTML = '<input type="hidden" name="delete_os" value="1"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleStatus(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="toggle_status" value="1"><input type="hidden" name="id" value="' + id + '">';
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
$content = ob_get_clean();
require_once 'includes/header.php';
?>