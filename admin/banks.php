<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    'bank_code' => sanitize($_POST['bank_code']),
                    'bank_name' => sanitize($_POST['bank_name']),
                    'account_number' => sanitize($_POST['account_number']),
                    'account_name' => sanitize($_POST['account_name']),
                    'qr_code_url' => sanitizeUrl($_POST['qr_code_url']),
                    'apibanklink' => sanitizeUrl($_POST['apibanklink']),
                    'status' => sanitize($_POST['status'])
                ];
                insertData('bank_accounts', $data);
                $_SESSION['success'] = 'Thêm ngân hàng thành công!';
                break;
                
            case 'edit':
                $id = sanitizeInt($_POST['id']);
                $data = [
                    'bank_code' => sanitize($_POST['bank_code']),
                    'bank_name' => sanitize($_POST['bank_name']),
                    'account_number' => sanitize($_POST['account_number']),
                    'account_name' => sanitize($_POST['account_name']),
                    'qr_code_url' => sanitizeUrl($_POST['qr_code_url']),
                    'apibanklink' => sanitizeUrl($_POST['apibanklink']),
                    'status' => sanitize($_POST['status'])
                ];
                updateData('bank_accounts', $data, 'id = ?', [$id]);
                $_SESSION['success'] = 'Cập nhật ngân hàng thành công!';
                break;
                
            case 'delete':
                $id = sanitizeInt($_POST['id']);
                executeQuery("DELETE FROM bank_accounts WHERE id = ?", [$id]);
                $_SESSION['success'] = 'Xóa ngân hàng thành công!';
                break;
        }
        redirect('banks.php');
    }
}

$banks = getBankAccounts();
$page_title = 'Quản lý ngân hàng - Admin';

ob_start();
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Quản lý ngân hàng</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Admin</a></li>
                    <li class="breadcrumb-item active">Ngân hàng</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách ngân hàng</h3>
                <button type="button" class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#addModal">
                    <i class="fas fa-plus"></i> Thêm ngân hàng
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã ngân hàng</th>
                                <th>Tên ngân hàng</th>
                                <th>Số tài khoản</th>
                                <th>Chủ tài khoản</th>
                                <th>API Bank Link</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($banks)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Chưa có ngân hàng nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($banks as $bank): ?>
                                    <tr>
                                        <td><?= $bank['id'] ?></td>
                                        <td><?= htmlspecialchars($bank['bank_code']) ?></td>
                                        <td><?= htmlspecialchars($bank['bank_name']) ?></td>
                                        <td><?= htmlspecialchars($bank['account_number']) ?></td>
                                        <td><?= htmlspecialchars($bank['account_name']) ?></td>
                                        <td>
                                            <?php if ($bank['apibanklink']): ?>
                                                <span class="badge badge-success">Có API</span><br>
                                                <small class="text-muted"><?= substr($bank['apibanklink'], 0, 30) ?>...</small>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Không có API</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $bank['status'] == 'active' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= $bank['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" onclick="editBank(<?= $bank['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteBank(<?= $bank['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h4 class="modal-title">Thêm ngân hàng mới</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Mã ngân hàng</label>
                        <input type="text" name="bank_code" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tên ngân hàng</label>
                        <input type="text" name="bank_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Số tài khoản</label>
                        <input type="text" name="account_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Chủ tài khoản</label>
                        <input type="text" name="account_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>QR Code URL</label>
                        <input type="url" name="qr_code_url" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>API Bank Link</label>
                        <textarea name="apibanklink" class="form-control" rows="3" placeholder="https://apibank.vddns.site/?token=token&numberAccount=sotk"></textarea>
                        <small class="form-text text-muted">Để trống nếu muốn xử lý nạp tiền thủ công</small>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Không hoạt động</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Cập Nhật Ngân Hàng</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mã Ngân Hàng *</label>
                        <input type="text" name="bank_code" id="editBankCode" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tên Ngân Hàng *</label>
                        <input type="text" name="bank_name" id="editBankName" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Số Tài Khoản *</label>
                        <input type="text" name="account_number" id="editAccountNumber" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chủ Tài Khoản *</label>
                        <input type="text" name="account_name" id="editAccountName" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code URL</label>
                    <input type="url" name="qr_code_url" id="editQrCodeUrl"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">API Bank Link</label>
                    <textarea name="apibanklink" id="editApibanklink" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Để trống nếu muốn xử lý nạp tiền thủ công</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trạng Thái</label>
                    <select name="status" id="editStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Không hoạt động</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeEditModal()"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        Hủy
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">QR Code Thanh Toán</h3>
                <button onclick="closeQRModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex justify-center">
                <img id="qrImage" src="" alt="QR Code" class="max-w-full h-auto rounded-lg shadow-md">
            </div>
            <div class="flex justify-end mt-4">
                <button onclick="closeQRModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- API Details Modal -->
<div id="apiModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Chi Tiết API Bank Link</h3>
                <button onclick="closeAPIModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <code id="apiDetails" class="text-sm text-gray-800 break-all"></code>
            </div>
            <div class="flex justify-end mt-4">
                <button onclick="closeAPIModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Modal functions
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function closeQRModal() {
    document.getElementById('qrModal').classList.add('hidden');
}

function closeAPIModal() {
    document.getElementById('apiModal').classList.add('hidden');
}

function showQRCode(url) {
    document.getElementById('qrImage').src = url;
    document.getElementById('qrModal').classList.remove('hidden');
}

function showAPIDetails(apiLink) {
    document.getElementById('apiDetails').textContent = apiLink;
    document.getElementById('apiModal').classList.remove('hidden');
}

function editBank(id) {
    // For now, we'll use the banks data from PHP
    // In production, you might want to fetch via AJAX
    const banks = <?= json_encode($banks) ?>;
    const bank = banks.find(b => b.id == id);
    
    if (bank) {
        document.getElementById('editId').value = bank.id;
        document.getElementById('editBankCode').value = bank.bank_code;
        document.getElementById('editBankName').value = bank.bank_name;
        document.getElementById('editAccountNumber').value = bank.account_number;
        document.getElementById('editAccountName').value = bank.account_name;
        document.getElementById('editQrCodeUrl').value = bank.qr_code_url || '';
        document.getElementById('editApibanklink').value = bank.apibanklink || '';
        document.getElementById('editStatus').value = bank.status;
        document.getElementById('editModal').classList.remove('hidden');
    }
}

function deleteBank(id) {
    if (confirm('Bạn có chắc chắn muốn xóa ngân hàng này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('fixed')) {
        if (event.target.id === 'addModal') closeAddModal();
        if (event.target.id === 'editModal') closeEditModal();
        if (event.target.id === 'qrModal') closeQRModal();
        if (event.target.id === 'apiModal') closeAPIModal();
    }
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddModal();
        closeEditModal();
        closeQRModal();
        closeAPIModal();
    }
});
</script>

<?php
$content = ob_get_clean();
require_once 'includes/header.php';
echo $content;
?>