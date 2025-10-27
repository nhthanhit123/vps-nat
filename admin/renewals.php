<?php
require_once '../config.php';
require_once '../database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $renewal_id = sanitizeInt($_POST['renewal_id']);
    $status = sanitize($_POST['status']);
    
    if (updateData('renewals', ['status' => $status], 'id = ?', [$renewal_id])) {
        $_SESSION['success'] = 'Cập nhật trạng thái gia hạn thành công!';
    } else {
        $_SESSION['error'] = 'Cập nhật trạng thái gia hạn thất bại!';
    }
    redirect('renewals.php');
}

// Get renewals with proper joins
$renewals = fetchAll("
    SELECT r.*, u.username, u.full_name, u.email,
           vo.package_id, vo.ip_address, vo.username as vps_username, vo.password,
           vp.name as package_name, vp.selling_price,
           os.name as os_name
    FROM renewals r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN vps_orders vo ON r.order_id = vo.id
    LEFT JOIN vps_packages vp ON vo.package_id = vp.id
    LEFT JOIN operating_systems os ON vo.os_id = os.id
    ORDER BY r.created_at DESC
");

// Statistics
$completedCount = count(array_filter($renewals, fn($r) => $r['status'] == 'completed'));
$pendingCount = count(array_filter($renewals, fn($r) => $r['status'] == 'pending'));
$failedCount = count(array_filter($renewals, fn($r) => $r['status'] == 'failed'));
$totalRenewalAmount = array_sum(array_column(array_filter($renewals, fn($r) => $r['status'] == 'completed'), 'price'));
$thisMonthCount = count(array_filter($renewals, fn($r) => 
    $r['status'] == 'completed' && 
    date('Y-m', strtotime($r['created_at'])) == date('Y-m')
));

$page_title = 'Quản lý gia hạn - Admin';

ob_start();
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Quản lý gia hạn</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Admin</a></li>
                    <li class="breadcrumb-item active">Gia hạn</li>
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

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format(count($renewals)) ?></h3>
                        <p>Tổng gia hạn</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-redo"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($completedCount) ?></h3>
                        <p>Hoàn thành</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($pendingCount) ?></h3>
                        <p>Chờ xử lý</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format($failedCount) ?></h3>
                        <p>Thất bại</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Doanh thu gia hạn</h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">Tháng này: <?= number_format($thisMonthCount) ?> đơn</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 100%</span>
                                    <h5 class="description-header"><?= formatPrice($totalRenewalAmount) ?></h5>
                                    <span class="description-text">Tổng doanh thu</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> 0%</span>
                                    <h5 class="description-header"><?= formatPrice($totalRenewalAmount / max(count($renewals), 1)) ?></h5>
                                    <span class="description-text">Giá trung bình</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="description-block">
                                    <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> 0%</span>
                                    <h5 class="description-header"><?= number_format($completedCount / max(count($renewals), 1) * 100, 1) ?>%</h5>
                                    <span class="description-text">Tỷ lệ thành công</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Renewals Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách gia hạn</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="renewalsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Mã GH</th>
                                <th>Khách hàng</th>
                                <th>Gói VPS</th>
                                <th>Thời gian</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($renewals)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Chưa có lịch sử gia hạn nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($renewals as $renewal): ?>
                                    <tr>
                                        <td>#<?= str_pad($renewal['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($renewal['full_name'] ?? 'N/A') ?></strong>
                                            </div>
                                            <small class="text-muted"><?= htmlspecialchars($renewal['username']) ?></small>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($renewal['email'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($renewal['package_name'] ?? 'N/A') ?></strong>
                                            </div>
                                            <small class="text-muted">Đơn #<?= $renewal['order_id'] ?></small>
                                            <?php if ($renewal['ip_address']): ?>
                                                <br>
                                                <small class="text-muted">IP: <?= htmlspecialchars($renewal['ip_address']) ?></small>
                                            <?php endif; ?>
                                            <?php if ($renewal['old_expiry_date'] && $renewal['new_expiry_date']): ?>
                                                <br>
                                                <small class="text-success">
                                                    <?= date('d/m/Y', strtotime($renewal['old_expiry_date'])) ?> → 
                                                    <?= date('d/m/Y', strtotime($renewal['new_expiry_date'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= $renewal['months'] ?> tháng</strong>
                                        </td>
                                        <td class="font-weight-bold text-success">
                                            <?= formatPrice($renewal['price']) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $renewal['status'] == 'completed' ? 'badge-success' : ($renewal['status'] == 'pending' ? 'badge-warning' : 'badge-danger') ?>">
                                                <?= $renewal['status'] == 'completed' ? 'Hoàn thành' : ($renewal['status'] == 'pending' ? 'Chờ xử lý' : 'Thất bại') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= formatDate($renewal['created_at']) ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm" onclick="showDetails(<?= $renewal['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($renewal['status'] == 'pending'): ?>
                                                    <button type="button" class="btn btn-success btn-sm" onclick="updateStatus(<?= $renewal['id'] ?>, 'completed')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="updateStatus(<?= $renewal['id'] ?>, 'failed')">
                                                        <i class="fas fa-times"></i>
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
            </div>
        </div>
    </div>
</section>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Chi tiết gia hạn</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="renewalDetails">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Form -->
<form id="statusForm" method="POST" style="display: none;">
    <input type="hidden" name="update_status" value="1">
    <input type="hidden" name="renewal_id" id="renewalId">
    <input type="hidden" name="status" id="renewalStatus">
</form>

<script>
$(document).ready(function() {
    $('#renewalsTable').DataTable({
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

function showDetails(renewalId) {
    // Load renewal details via AJAX
    fetch('api/get_renewal.php?id=' + renewalId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const renewal = data.data;
                const detailsHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Thông tin gia hạn</h5>
                            <table class="table table-sm">
                                <tr><td>Mã gia hạn:</td><td>#${renewal.id}</td></tr>
                                <tr><td>Số tháng:</td><td>${renewal.months} tháng</td></tr>
                                <tr><td>Giá:</td><td>${formatPrice(renewal.price)}</td></tr>
                                <tr><td>Trạng thái:</td><td>${getStatusBadge(renewal.status)}</td></tr>
                                <tr><td>Ngày tạo:</td><td>${formatDate(renewal.created_at)}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Thông tin khách hàng</h5>
                            <table class="table table-sm">
                                <tr><td>Tên:</td><td>${renewal.full_name || 'N/A'}</td></tr>
                                <tr><td>Username:</td><td>${renewal.username}</td></tr>
                                <tr><td>Email:</td><td>${renewal.email || ''}</td></tr>
                            </table>
                        </div>
                    </div>
                    ${renewal.old_expiry_date && renewal.new_expiry_date ? `
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Thay đổi ngày hết hạn</h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-calendar-alt"></i>
                                    Từ: ${formatDate(renewal.old_expiry_date)} → ${formatDate(renewal.new_expiry_date)}
                                </div>
                            </div>
                        </div>
                    ` : ''}
                `;
                
                document.getElementById('renewalDetails').innerHTML = detailsHtml;
                $('#detailsModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Không thể tải thông tin chi tiết');
        });
}

function updateStatus(renewalId, status) {
    if (confirm(`Bạn có chắc chắn muốn đổi trạng thái thành ${status == 'completed' ? 'hoàn thành' : 'thất bại'}?`)) {
        document.getElementById('renewalId').value = renewalId;
        document.getElementById('renewalStatus').value = status;
        document.getElementById('statusForm').submit();
    }
}

function getStatusBadge(status) {
    const badges = {
        'completed': '<span class="badge badge-success">Hoàn thành</span>',
        'pending': '<span class="badge badge-warning">Chờ xử lý</span>',
        'failed': '<span class="badge badge-danger">Thất bại</span>'
    };
    return badges[status] || status;
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
}

function formatDate(dateString) {
    if (!dateString || dateString === '0000-00-00') return 'N/A';
    return new Date(dateString).toLocaleString('vi-VN');
}
</script>

<?php
$content = ob_get_clean();
require_once 'includes/header.php';
echo $content;
require_once 'includes/footer.php';
?>