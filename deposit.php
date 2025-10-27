<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php?redirect=deposit.php');
}

$bank_accounts = getBankAccounts();
$deposits = getUserDeposits($_SESSION['user_id']);
$page_title = 'Nạp tiền - ' . SITE_NAME;
$page_description = 'Nạp tiền vào tài khoản để sử dụng dịch vụ VPS';

ob_start();
?>

<!-- Main Content -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <?php if (isset($success_message)): ?>
            <div class="max-w-4xl mx-auto mb-8">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                <?= htmlspecialchars($success_message) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Deposit Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Thông tin chuyển khoản</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold text-red-800 mb-2">Lỗi</h4>
                                        <ul class="text-sm text-red-700 space-y-1">
                                            <?php foreach ($errors as $error): ?>
                                                <li>• <?= htmlspecialchars($error) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="depositForm">
                            <!-- Bank Selection -->
                            <div class="mb-8">
                                <label class="block text-sm font-medium text-gray-700 mb-4">
                                    <i class="fas fa-university mr-1 text-teal-600"></i>
                                    Chọn ngân hàng <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($bank_accounts as $bank): ?>
                                        <label class="bank-card border-2 border-gray-200 rounded-xl p-4 cursor-pointer hover:border-teal-500 smooth-transition">
                                            <input type="radio" name="bank_code" value="<?= $bank['bank_code'] ?>" 
                                                   class="sr-only bank-radio" required>
                                            <div class="flex items-center">
                                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold mr-4">
                                                    <?= strtoupper(substr($bank['bank_code'], 0, 2)) ?>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($bank['bank_name']) ?></div>
                                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($bank['account_number']) ?></div>
                                                    <div class="text-xs text-gray-400"><?= htmlspecialchars($bank['account_name']) ?></div>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Selected Bank Info -->
                            <div id="selectedBankInfo" class="hidden mb-8">
                                <div class="bg-gradient-to-r from-teal-50 to-blue-50 rounded-xl p-6">
                                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-info-circle text-teal-600 mr-2"></i>
                                        Thông tin chuyển khoản
                                    </h4>
                                    <div id="bankDetails"></div>
                                    <div class="mt-4 text-center">
                                        <img id="qrCode" class="mx-auto rounded-lg shadow-sm" style="max-height: 200px;">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Deposit History -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Lịch sử nạp tiền</h3>
                        
                        <?php if (empty($deposits)): ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-history text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-gray-500">Chưa có lịch sử nạp tiền</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                <?php foreach (array_slice($deposits, 0, 10) as $deposit): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 smooth-transition">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <div class="font-semibold text-gray-900">
                                                    <?= formatPrice($deposit['amount']) ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?= htmlspecialchars($deposit['bank_name']) ?>
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                <?php
                                                switch($deposit['status']) {
                                                    case 'completed':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'pending':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'failed':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php
                                                switch($deposit['status']) {
                                                    case 'completed':
                                                        echo 'Hoàn thành';
                                                        break;
                                                    case 'pending':
                                                        echo 'Chờ xử lý';
                                                        break;
                                                    case 'failed':
                                                        echo 'Thất bại';
                                                        break;
                                                    default:
                                                        echo $deposit['status'];
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            <?= formatDate($deposit['created_at']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Support Info -->
                    <div class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl shadow-sm p-6 text-white">
                        <h3 class="text-lg font-bold mb-4">
                            <i class="fas fa-headset mr-2"></i>Hỗ trợ 24/7
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-phone mr-3"></i>
                                <div>
                                    <div class="text-sm opacity-90">Hotline</div>
                                    <div class="font-semibold">0898 686 001</div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-envelope mr-3"></i>
                                <div>
                                    <div class="text-sm opacity-90">Email</div>
                                    <div class="font-semibold">hotronify@gmail.com</div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fab fa-telegram mr-3"></i>
                                <div>
                                    <div class="text-sm opacity-90">Fanpage</div>
                                    <div class="font-semibold">https://fb.com/nify.support</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Benefits -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Lợi ích khi nạp tiền</h3>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                                <span>Xử lý nhanh chóng trong 1-30 phút</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                                <span>Nhiều phương thức thanh toán</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                                <span>Bảo mật và an toàn tuyệt đối</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                                <span>Hỗ trợ 24/7 khi có sự cố</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const bankAccounts = <?= json_encode($bank_accounts) ?>;

function setAmount(amount) {
    document.getElementById('amount').value = amount;
}

// Bank selection handling
document.querySelectorAll('.bank-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove previous selection
        document.querySelectorAll('.bank-card').forEach(card => {
            card.classList.remove('border-teal-500', 'bg-teal-50', 'border-2');
            card.classList.add('border-gray-200');
        });
        
        // Add selection to current
        const selectedCard = this.closest('.bank-card');
        selectedCard.classList.remove('border-gray-200');
        selectedCard.classList.add('border-2', 'border-teal-500', 'bg-teal-50');
        
        // Show bank details
        const bankCode = this.value;
        const bank = bankAccounts.find(b => b.bank_code === bankCode);
        
        if (bank) {
            document.getElementById('selectedBankInfo').classList.remove('hidden');
            document.getElementById('bankDetails').innerHTML = `
                <div class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600 text-sm">Ngân hàng:</span>
                            <div class="font-semibold">${bank.bank_name}</div>
                        </div>
                        <div>
                            <span class="text-gray-600 text-sm">Số tài khoản:</span>
                            <div class="font-semibold font-mono">${bank.account_number}</div>
                        </div>
                        <div>
                            <span class="text-gray-600 text-sm">Chủ tài khoản:</span>
                            <div class="font-semibold">${bank.account_name}</div>
                        </div>
                        <div>
                            <span class="text-gray-600 text-sm">Chi nhánh:</span>
                            <div class="font-semibold">${bank.branch || 'Toàn quốc'}</div>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-white rounded-lg border border-teal-200">
                        <p class="text-xs text-teal-800 font-medium mb-1">Nội dung chuyển khoản:</p>
                        <p class="text-sm font-mono text-teal-900 font-semibold">KVMVPS <?= $_SESSION['username'] ?></p>
                    </div>
                </div>
            `;
            
            if (bank.qr_code_url) {
                document.getElementById("qrCode").src = bank.qr_code_url;
                document.getElementById("qrCode").style.display = 'block';
            } else {
                document.getElementById("qrCode").style.display = 'none';
            }
        }
    });
});

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
}

// Form validation
document.getElementById('depositForm').addEventListener('submit', function(e) {
    const amount = parseFloat(document.getElementById('amount').value);
    const selectedBank = document.querySelector('.bank-radio:checked');
    
    if (!selectedBank) {
        e.preventDefault();
        showToast('Vui lòng chọn ngân hàng', 'error');
        return false;
    }
    
    if (!amount || amount < 10000) {
        e.preventDefault();
        showToast('Số tiền nạp tối thiểu là 10,000 VNĐ', 'error');
        return false;
    }
    
    if (amount > 50000000) {
        e.preventDefault();
        showToast('Số tiền nạp tối đa là 50,000,000 VNĐ', 'error');
        return false;
    }
    
    // Show loading
    showToast('Đang gửi yêu cầu...', 'info');
});

// Auto-select first bank on page load
document.addEventListener('DOMContentLoaded', function() {
    const firstBank = document.querySelector('.bank-radio');
    if (firstBank) {
        firstBank.checked = true;
        firstBank.dispatchEvent(new Event('change'));
    }
});
</script>

<?php
$content = ob_get_clean();
include 'includes/header.php';
?>