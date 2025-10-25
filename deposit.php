<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php?redirect=deposit.php');
}

$bank_accounts = getBankAccounts();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = (float)$_POST['amount'];
    $bank_code = $_POST['bank_code'];
    $transaction_id = cleanInput($_POST['transaction_id'] ?? '');
    $notes = cleanInput($_POST['notes'] ?? '');
    
    $errors = [];
    
    if (empty($amount) || $amount <= 0) {
        $errors[] = 'Vui lòng nhập số tiền hợp lệ';
    } elseif ($amount < 10000) {
        $errors[] = 'Số tiền nạp tối thiểu là 10,000 VNĐ';
    } elseif ($amount > 50000000) {
        $errors[] = 'Số tiền nạp tối đa là 50,000,000 VNĐ';
    }
    
    if (empty($bank_code)) {
        $errors[] = 'Vui lòng chọn ngân hàng';
    }
    
    if (empty($errors)) {
        $depositData = [
            'user_id' => $_SESSION['user_id'],
            'amount' => $amount,
            'bank_code' => $bank_code,
            'bank_name' => '',
            'transaction_id' => $transaction_id,
            'notes' => $notes
        ];
        
        foreach ($bank_accounts as $bank) {
            if ($bank['bank_code'] == $bank_code) {
                $depositData['bank_name'] = $bank['bank_name'];
                break;
            }
        }
        
        if (createDeposit($depositData)) {
            $user = getUser($_SESSION['user_id']);
            sendDepositNotification($depositData, $user, $depositData);
            
            $_SESSION['success_message'] = 'Yêu cầu nạp tiền đã được gửi! Chúng tôi sẽ xác nhận sớm.';
            redirect('deposit.php');
        } else {
            $errors[] = 'Gửi yêu cầu thất bại. Vui lòng thử lại.';
        }
    }
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

$deposits = getUserDeposits($_SESSION['user_id']);

$page_title = 'Nạp tiền - ' . SITE_NAME;
$page_description = 'Nạp tiền vào tài khoản để sử dụng dịch vụ VPS';

ob_start();
?>

<!-- Hero Section -->
<section class="gradient-primary text-white py-12 relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Nạp tiền tài khoản</h1>
            <p class="text-lg text-green-100">Nạp tiền an toàn, nhanh chóng để sử dụng các dịch vụ VPS của chúng tôi</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 mt-6">
                <a href="#bank-transfer" class="bg-white text-green-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold smooth-transition hover-lift">
                    <i class="fas fa-university mr-2"></i>Chuyển khoản
                </a>
                <a href="card_recharge.php" class="border-2 border-white hover:bg-white hover:text-green-600 text-white px-6 py-3 rounded-lg font-semibold smooth-transition">
                    <i class="fas fa-credit-card mr-2"></i>Thẻ cào
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Current Balance -->
<section class="py-8 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-gradient-to-r from-green-600 to-green-400 rounded-2xl p-8 text-white text-center">
                <h3 class="text-xl font-semibold mb-2">Số dư hiện tại</h3>
                <p class="text-4xl font-bold mb-4"><?= formatPrice($_SESSION['balance']) ?></p>
                <p class="text-green-100">Nạp thêm tiền để sử dụng dịch vụ VPS cao cấp</p>
            </div>
        </div>
    </div>
</section>

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
                    <div class="bg-white rounded-2xl shadow-sm p-8" id="bank-transfer">
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
                                    <i class="fas fa-university mr-1 text-green-600"></i>
                                    Chọn ngân hàng <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($bank_accounts as $bank): ?>
                                        <label class="bank-card border-2 border-gray-200 rounded-xl p-4 cursor-pointer hover:border-green-500 smooth-transition">
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
                                <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6">
                                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-info-circle text-green-600 mr-2"></i>
                                        Thông tin chuyển khoản
                                    </h4>
                                    <div id="bankDetails"></div>
                                    <div class="mt-4 text-center">
                                        <img id="qrCode" class="mx-auto rounded-lg shadow-sm" style="max-height: 200px;">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Amount Input -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-dollar-sign mr-1 text-green-600"></i>
                                    Số tiền nạp <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="amount" id="amount" required
                                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           placeholder="Nhập số tiền" min="10000" max="50000000" step="1000">
                                    <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500">VNĐ</span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" onclick="setAmount(50000)" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm smooth-transition">50K</button>
                                    <button type="button" onclick="setAmount(100000)" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm smooth-transition">100K</button>
                                    <button type="button" onclick="setAmount(200000)" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm smooth-transition">200K</button>
                                    <button type="button" onclick="setAmount(500000)" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm smooth-transition">500K</button>
                                    <button type="button" onclick="setAmount(1000000)" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm smooth-transition">1M</button>
                                </div>
                            </div>
                            
                            <!-- Transaction ID -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-receipt mr-1 text-green-600"></i>
                                    Mã giao dịch (không bắt buộc)
                                </label>
                                <input type="text" name="transaction_id" id="transaction_id"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Nhập mã giao dịch nếu có">
                            </div>
                            
                            <!-- Notes -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-comment mr-1 text-green-600"></i>
                                    Ghi chú (không bắt buộc)
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                          placeholder="Nhập ghi chú nếu có"></textarea>
                            </div>
                            
                            <!-- Important Notice -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                <h4 class="font-semibold text-yellow-800 mb-3 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Lưu ý quan trọng
                                </h4>
                                <ul class="text-sm text-yellow-700 space-y-2">
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-yellow-600 mr-2 mt-1"></i>
                                        <span>Chuyển khoản đúng nội dung: <code class="bg-yellow-100 px-1 rounded">VPS <?= $_SESSION['username'] ?></code></span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-yellow-600 mr-2 mt-1"></i>
                                        <span>Chúng tôi sẽ xác nhận và cộng tiền trong vòng 1-30 phút</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-yellow-600 mr-2 mt-1"></i>
                                        <span>Nếu quá 15 phút chưa được cộng tiền, vui lòng liên hệ hỗ trợ</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-yellow-600 mr-2 mt-1"></i>
                                        <span>Số tiền nạp tối thiểu: 10,000 VNĐ</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-green-600 to-green-400 hover:from-green-700 hover:to-green-500 text-white py-4 px-6 rounded-lg font-semibold smooth-transition hover-lift">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Gửi yêu cầu nạp tiền
                            </button>
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
                                    <div class="font-semibold">1900 1234</div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-envelope mr-3"></i>
                                <div>
                                    <div class="text-sm opacity-90">Email</div>
                                    <div class="font-semibold">support@vpsnat.com</div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fab fa-telegram mr-3"></i>
                                <div>
                                    <div class="text-sm opacity-90">Telegram</div>
                                    <div class="font-semibold">@vpsnat_support</div>
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
                        <p class="text-sm font-mono text-teal-900 font-semibold">VPS <?= $_SESSION['username'] ?></p>
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