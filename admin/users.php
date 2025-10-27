<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle balance adjustment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adjust_balance'])) {
    $user_id = sanitizeInt($_POST['user_id']);
    $amount = (float)$_POST['amount'];
    $type = $_POST['type']; // 'add' or 'subtract'
    $reason = sanitize($_POST['reason']);
    
    $user = getUser($user_id);
    if (!$user) {
        $_SESSION['error'] = 'Người dùng không tồn tại';
    } else {
        $actualAmount = $type == 'subtract' ? -$amount : $amount;
        
        if (updateUserBalance($user_id, $actualAmount)) {
            // Create transaction record
            $transactionData = [
                'user_id' => $user_id,
                'amount' => $actualAmount,
                'bank_code' => 'ADMIN',
                'bank_name' => 'Admin Adjustment',
                'transaction_id' => 'ADJ_' . time(),
                'status' => 'completed',
                'notes' => $reason . ($type == 'subtract' ? ' (Trừ tiền)' : ' (Cộng tiền)')
            ];
            createDeposit($transactionData);
            
            $_SESSION['success'] = 'Điều chỉnh số dư thành công!';
        } else {
            $_SESSION['error'] = 'Điều chỉnh số dư thất bại!';
        }
    }
    redirect('users.php');
}

// Handle other user operations
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
            $_SESSION['success'] = 'Thêm người dùng thành công!';
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
    $status = $_POST['status'];
    $password = $_POST['password'] ?? '';
    
    $updateData = [
        'full_name' => $full_name,
        'phone' => $phone,
        'role' => $role,
        'status' => $status
    ];
    
    if (!empty($password)) {
        $updateData['password'] = $password;
    }
    
    if (updateUser($user_id, $updateData)) {
        $_SESSION['success'] = 'Cập nhật người dùng thành công!';
    } else {
        $errors[] = 'Cập nhật thất bại. Vui lòng thử lại.';
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = sanitizeInt($_GET['id']);
    
    // Don't allow deleting admin users or yourself
    $user = getUser($user_id);
    if ($user && $user['role'] != 'admin' && $user['id'] != $_SESSION['user_id']) {
        executeQuery("DELETE FROM users WHERE id = ?", [$user_id]);
        $_SESSION['success'] = 'Xóa người dùng thành công!';
    }
    
    redirect('users.php');
}

$users = fetchAll("SELECT * FROM users ORDER BY created_at DESC");

ob_start();
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Quản lý người dùng</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Admin</a></li>
                    <li class="breadcrumb-item active">Người dùng</li>
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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php foreach ($errors as $error): ?>
                    <?= htmlspecialchars($error) ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách người dùng</h3>
                <button type="button" class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#addModal">
                    <i class="fas fa-plus"></i> Thêm người dùng
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                                <th>Số dư</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone']) ?></td>
                                    <td class="font-weight-bold text-success"><?= formatPrice($user['balance']) ?></td>
                                    <td>
                                        <span class="badge <?= $user['role'] == 'admin' ? 'badge-danger' : 'badge-primary' ?>">
                                            <?= $user['role'] == 'admin' ? 'Admin' : 'User' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $user['status'] == 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                            <?= $user['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($user['created_at']) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" onclick="editUser(<?= $user['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="adjustBalance(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', <?= $user['balance'] ?>)">
                                            <i class="fas fa-wallet"></i>
                                        </button>
                                        <?php if ($user['role'] != 'admin' && $user['id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteUser(<?= $user['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add User Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="add_user" value="1">
                <div class="modal-header">
                    <h4 class="modal-title">Thêm người dùng mới</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Họ tên</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Điện thoại</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Số dư ban đầu</label>
                        <input type="number" name="balance" class="form-control" value="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Vai trò</label>
                        <select name="role" class="form-control">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
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

<!-- Edit User Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="update_user" value="1">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="modal-header">
                    <h4 class="modal-title">Cập nhật người dùng</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Họ tên</label>
                        <input type="text" name="full_name" id="editFullName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Điện thoại</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Password mới (để trống nếu không đổi)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Vai trò</label>
                        <select name="role" id="editRole" class="form-control">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status" id="editStatus" class="form-control">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Không hoạt động</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Balance Adjustment Modal -->
<div class="modal fade" id="balanceModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="adjust_balance" value="1">
                <input type="hidden" name="user_id" id="balanceUserId">
                <div class="modal-header">
                    <h4 class="modal-title">Điều chỉnh số dư</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Người dùng:</strong> <span id="balanceUsername"></span><br>
                        <strong>Số dư hiện tại:</strong> <span id="currentBalance"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Loại điều chỉnh</label>
                        <select name="type" id="adjustType" class="form-control" required onchange="updateBalanceLabel()">
                            <option value="add">Cộng tiền</option>
                            <option value="subtract">Trừ tiền</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label id="amountLabel">Số tiền cộng</label>
                        <input type="number" name="amount" class="form-control" required step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Lý do</label>
                        <textarea name="reason" class="form-control" required placeholder="Nhập lý do điều chỉnh số dư"></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Lưu ý:</strong> Hành động này sẽ không thể hoàn tác và sẽ được ghi lại trong lịch sử giao dịch.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Xác nhận điều chỉnh</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "ordering": true,
        "info": true,
        "paging": true,
        "searching": true,
        "pageLength": 25,
        "language": {
            "search": "Tìm kiếm:",
            "lengthMenu": "Hiển thị _MENU_ bản ghi",
            "info": "Hiển thị _START_ đến _END_ của _TOTAL_ bản ghi",
            "paginate": {
                "first": "Đầu",
                "last": "Cuối",
                "next": "Sau",
                "previous": "Trước"
            }
        }
    });
});

function editUser(id) {
    // Fetch user data and populate form
    fetch('api/get_user.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.data;
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editFullName').value = user.full_name;
                document.getElementById('editPhone').value = user.phone || '';
                document.getElementById('editRole').value = user.role;
                document.getElementById('editStatus').value = user.status;
                $('#editModal').modal('show');
            }
        });
}

function adjustBalance(userId, username, currentBalance) {
    document.getElementById('balanceUserId').value = userId;
    document.getElementById('balanceUsername').textContent = username;
    document.getElementById('currentBalance').textContent = formatPrice(currentBalance);
    $('#balanceModal').modal('show');
}

function updateBalanceLabel() {
    const type = document.getElementById('adjustType').value;
    const label = document.getElementById('amountLabel');
    label.textContent = type === 'add' ? 'Số tiền cộng' : 'Số tiền trừ';
}

function deleteUser(id) {
    if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
        window.location.href = 'users.php?action=delete&id=' + id;
    }
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
}
</script>

<?php
$content = ob_get_clean();
require_once 'includes/header.php';
echo $content;
require_once 'includes/footer.php';
?>