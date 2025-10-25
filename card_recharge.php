<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php?redirect=card_recharge.php');
}

$page_title = 'Nạp Thẻ Cào - ' . SITE_NAME;
$page_description = 'Nạp tiền vào tài khoản bằng thẻ cào điện thoại';

ob_start();
?>

<!-- Hero Section -->
<section class="gradient-primary text-white py-12 relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Nạp Thẻ Cào</h1>
            <p class="text-lg text-teal-100">Nạp tiền nhanh chóng bằng thẻ cào điện thoại</p>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<section class="py-4 bg-white border-b">
    <div class="container mx-auto px-4">
        <nav class="flex items-center space-x-2 text-sm">
            <a href="index.php" class="text-gray-500 hover:text-green-600 smooth-transition">Trang chủ</a>
            <span class="text-gray-400">/</span>
            <a href="deposit.php" class="text-gray-500 hover:text-green-600 smooth-transition">Nạp tiền</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-900 font-medium">Nạp thẻ cào</span>
        </nav>
    </div>
</section>

<!-- Main Content -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Card Recharge Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-green-600 to-green-400 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-credit-card text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Nạp Thẻ Cào</h2>
                                <p class="text-gray-600">Hỗ trợ các nhà mạng Viettel, MobiFone, VinaPhone</p>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold text-red-800 mb-2">Lỗi</h4>
                                        <p class="text-sm text-red-700"><?= htmlspecialchars($_SESSION['error_message']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold text-green-800 mb-2">Thành công</h4>
                                        <p class="text-sm text-green-700"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>

                        <form method="POST" id="cardRechargeForm" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-mobile-alt mr-1 text-green-600"></i>
                                        Nhà mạng <span class="text-red-500">*</span>
                                    </label>
                                    <select name="card_type" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="">-- Chọn nhà mạng --</option>
                                        <option value="VIETTEL">Viettel</option>
                                        <option value="MOBIFONE">MobiFone</option>
                                        <option value="VINAPHONE">VinaPhone</option>
                                        <option value="VIETNAMMOBILE">Vietnamobile</option>
                                        <option value="ZING">Zing</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-money-bill-wave mr-1 text-green-600"></i>
                                        Mệnh giá <span class="text-red-500">*</span>
                                    </label>
                                    <select name="card_amount" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="">-- Chọn mệnh giá --</option>
                                        <option value="10000">10.000 VNĐ</option>
                                        <option value="20000">20.000 VNĐ</option>
                                        <option value="30000">30.000 VNĐ</option>
                                        <option value="50000">50.000 VNĐ</option>
                                        <option value="100000">100.000 VNĐ</option>
                                        <option value="200000">200.000 VNĐ</option>
                                        <option value="300000">300.000 VNĐ</option>
                                        <option value="500000">500.000 VNĐ</option>
                                        <option value="1000000">1.000.000 VNĐ</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-barcode mr-1 text-green-600"></i>
                                    Mã thẻ <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="card_code" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Nhập mã thẻ (ví dụ: 123456789012)"
                                       maxlength="15">
                                <p class="text-xs text-gray-500 mt-1">Mã thẻ thường có 12-15 chữ số</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-hashtag mr-1 text-green-600"></i>
                                    Số seri <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="card_serial" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Nhập số seri (ví dụ: 123456789012)"
                                       maxlength="15">
                                <p class="text-xs text-gray-500 mt-1">Số seri thường có 12-15 chữ số</p>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold text-blue-800 mb-2">Lưu ý quan trọng</h4>
                                        <ul class="text-sm text-blue-700 space-y-1">
                                            <li>• Thẻ cào phải còn nguyên vẹn, chưa được sử dụng</li>
                                            <li>• Sai mệnh giá sẽ không được hoàn tiền</li>
                                            <li>• Thẻ lỗi hoặc đã sử dụng sẽ không được hoàn tiền</li>
                                            <li>• Phí xử lý: 20% giá trị thẻ</li>
                                            <li>• Số tiền nhận được = Mệnh giá - Phí xử lý</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-green-600 to-green-400 hover:from-green-700 hover:to-green-500 text-white py-4 px-6 rounded-lg font-semibold smooth-transition hover-lift">
                                <i class="fas fa-credit-card mr-2"></i>Nạp Thẻ Ngay
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Rate Info -->
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-percentage mr-2 text-green-600"></i>Tỷ giá nhận
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">Viettel</span>
                                <span class="text-sm font-bold text-green-600">80%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">MobiFone</span>
                                <span class="text-sm font-bold text-green-600">78%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">VinaPhone</span>
                                <span class="text-sm font-bold text-green-600">78%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">Vietnamobile</span>
                                <span class="text-sm font-bold text-green-600">75%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">Zing</span>
                                <span class="text-sm font-bold text-green-600">75%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Amount Calculator -->
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-calculator mr-2 text-green-600"></i>Máy tính
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mệnh giá thẻ</label>
                                <select id="calcAmount" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="10000">10.000 VNĐ</option>
                                    <option value="20000">20.000 VNĐ</option>
                                    <option value="50000">50.000 VNĐ</option>
                                    <option value="100000">100.000 VNĐ</option>
                                    <option value="200000">200.000 VNĐ</option>
                                    <option value="500000">500.000 VNĐ</option>
                                    <option value="1000000">1.000.000 VNĐ</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nhà mạng</label>
                                <select id="calcNetwork" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="0.8">Viettel (80%)</option>
                                    <option value="0.78">MobiFone (78%)</option>
                                    <option value="0.78">VinaPhone (78%)</option>
                                    <option value="0.75">Vietnamobile (75%)</option>
                                    <option value="0.75">Zing (75%)</option>
                                </select>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="text-center">
                                    <p class="text-sm text-gray-600 mb-1">Số tiền nhận được</p>
                                    <p class="text-2xl font-bold text-green-600" id="calcResult">8.000 VNĐ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Deposit Methods -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-wallet mr-2 text-green-600"></i>Phương thức khác
                        </h3>
                        <div class="space-y-3">
                            <a href="deposit.php" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-lg font-medium smooth-transition text-center">
                                <i class="fas fa-university mr-2"></i>Chuyển khoản ngân hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Calculator
function updateCalculator() {
    const amount = parseInt(document.getElementById('calcAmount').value);
    const rate = parseFloat(document.getElementById('calcNetwork').value);
    const result = Math.floor(amount * rate);
    document.getElementById('calcResult').textContent = result.toLocaleString('vi-VN') + ' VNĐ';
}

document.getElementById('calcAmount').addEventListener('change', updateCalculator);
document.getElementById('calcNetwork').addEventListener('change', updateCalculator);

// Form validation
document.getElementById('cardRechargeForm').addEventListener('submit', function(e) {
    const cardCode = document.querySelector('input[name="card_code"]').value;
    const cardSerial = document.querySelector('input[name="card_serial"]').value;
    
    if (cardCode.length < 12 || cardCode.length > 15) {
        e.preventDefault();
        alert('Mã thẻ phải có từ 12-15 chữ số');
        return;
    }
    
    if (cardSerial.length < 12 || cardSerial.length > 15) {
        e.preventDefault();
        alert('Số seri phải có từ 12-15 chữ số');
        return;
    }
    
    if (!/^\d+$/.test(cardCode)) {
        e.preventDefault();
        alert('Mã thẻ chỉ được chứa số');
        return;
    }
    
    if (!/^\d+$/.test(cardSerial)) {
        e.preventDefault();
        alert('Số seri chỉ được chứa số');
        return;
    }
});

// Auto-format card inputs
document.querySelector('input[name="card_code"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '');
});

document.querySelector('input[name="card_serial"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '');
});
</script>

<?php
$content = ob_get_clean();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_type = $_POST['card_type'];
    $card_amount = intval($_POST['card_amount']);
    $card_code = cleanInput($_POST['card_code']);
    $card_serial = cleanInput($_POST['card_serial']);
    
    $user = getUser($_SESSION['user_id']);
    
    // Create card payment request
    $card_data = [
        'card_type' => $card_type,
        'card_amount' => $card_amount,
        'card_code' => $card_code,
        'card_serial' => $card_serial,
        'request_id' => uniqid('card_') . time()
    ];
    
    $result = createCardPayment($card_data);
    
    if (isset($result['error'])) {
        $_SESSION['error_message'] = 'Lỗi kết nối đến hệ thống xử lý thẻ. Vui lòng thử lại sau.';
    } elseif ($result['status'] === 'success') {
        // Create deposit record
        $deposit_data = [
            'user_id' => $_SESSION['user_id'],
            'amount' => $result['actual_amount'],
            'bank_code' => $card_type,
            'bank_name' => $card_type,
            'transaction_id' => $result['transaction_id'],
            'status' => 'pending',
            'notes' => "Nạp thẻ cào - Mã thẻ: {$card_code} - Seri: {$card_serial}"
        ];
        
        if (createDeposit($deposit_data)) {
            $_SESSION['success_message'] = 'Yêu cầu nạp thẻ đã được gửi thành công! Vui lòng chờ xử lý trong vài phút.';
        } else {
            $_SESSION['error_message'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    } else {
        $_SESSION['error_message'] = $result['message'] ?? 'Có lỗi xảy ra. Vui lòng thử lại.';
    }
    
    redirect('card_recharge.php');
}

include 'includes/header.php';
echo $content;
include 'includes/footer.php';
?>