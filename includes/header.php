<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? SITE_NAME ?></title>
    <meta name="description" content="<?= $page_description ?? SITE_DESCRIPTION ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        /* Gradient from green to white */
        .gradient-primary {
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 25%, #4ade80 50%, #86efac 75%, #ffffff 100%);
        }
        
        .gradient-secondary {
            background: linear-gradient(135deg, #15803d 0%, #16a34a 50%, #22c55e 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dark-glass {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Smooth transitions */
        .smooth-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #15803d, #16a34a);
        }
        
        /* Loading animation */
        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #22c55e;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Pulse animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Bar -->
    <div class="bg-gray-900 text-white py-2 text-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <span><i class="fas fa-envelope mr-2"></i>support@vpsnat.com</span>
                    <span><i class="fas fa-phone mr-2"></i>1900 1234</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="hover:text-green-400 transition"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="hover:text-green-400 transition"><i class="fab fa-telegram"></i></a>
                    <a href="#" class="hover:text-green-400 transition"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white shadow-sm sticky top-0 z-40 glass-effect">
        <nav class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-8">
                    <a href="/index.php" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-600 to-green-400 rounded-lg flex items-center justify-center">
                            <i class="fas fa-server text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold gradient-text">VPS NAT</span>
                    </a>
                    
                    <div class="hidden lg:flex items-center space-x-6">
                        <a href="/index.php" class="text-gray-700 hover:text-green-600 font-medium smooth-transition">Trang Chủ</a>
                        
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-green-600 font-medium smooth-transition flex items-center">
                                Dịch Vụ
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div class="absolute top-full left-0 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 bg-white mt-2 py-3 w-56 rounded-lg shadow-xl z-50 border border-gray-100">
                                <a href="/packages.php" class="block px-4 py-2 hover:bg-green-50 text-gray-700 hover:text-green-600 smooth-transition">
                                    <i class="fas fa-server mr-2"></i>VPS Giá Rẻ
                                </a>
                                <a href="/packages.php" class="block px-4 py-2 hover:bg-green-50 text-gray-700 hover:text-green-600 smooth-transition">
                                    <i class="fas fa-rocket mr-2"></i>VPS Cao Cấp
                                </a>
                                <a href="/services.php" class="block px-4 py-2 hover:bg-green-50 text-gray-700 hover:text-green-600 smooth-transition">
                                    <i class="fas fa-cog mr-2"></i>Quản Lý VPS
                                </a>
                            </div>
                        </div>
                        
                        <a href="/deposit.php" class="text-gray-700 hover:text-green-600 font-medium smooth-transition">Nạp Tiền</a>
                        
                        <a href="#" class="text-gray-700 hover:text-green-600 font-medium smooth-transition">Hỗ Trợ</a>
                    </div>
                </div>
                
                <div class="hidden lg:flex items-center space-x-4">
                    <?php if (isLoggedIn()): 
                        $user = getUser($_SESSION['user_id']);
                    ?>
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900"><?= $_SESSION['username'] ?></div>
                                <div class="text-xs text-green-600 font-semibold"><?= formatPrice($user['balance']) ?></div>
                            </div>
                            <div class="relative group">
                                <button class="w-10 h-10 bg-gradient-to-r from-green-600 to-green-400 rounded-full flex items-center justify-center text-white hover-lift">
                                    <i class="fas fa-user"></i>
                                </button>
                                <div class="absolute top-full right-0 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 bg-white mt-2 py-2 w-48 rounded-lg shadow-xl z-50 border border-gray-100">
                                    <a href="/profile.php" class="block px-4 py-2 hover:bg-green-50 text-gray-700 hover:text-green-600 smooth-transition">
                                        <i class="fas fa-user-circle mr-2"></i>Hồ Sơ
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <a href="/admin/" class="block px-4 py-2 hover:bg-green-50 text-gray-700 hover:text-green-600 smooth-transition">
                                            <i class="fas fa-cog mr-2"></i>Admin Panel
                                        </a>
                                    <?php endif; ?>
                                    <hr class="my-2">
                                    <a href="/logout.php" class="block px-4 py-2 hover:bg-red-50 text-gray-700 hover:text-red-600 smooth-transition">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Đăng Xuất
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login.php" class="text-gray-700 hover:text-green-600 font-medium smooth-transition">Đăng Nhập</a>
                        <a href="/register.php" class="bg-gradient-to-r from-green-600 to-green-400 hover:from-green-700 hover:to-green-500 text-white px-6 py-2 rounded-lg font-medium smooth-transition hover-lift">
                            Đăng Ký
                        </a>
                    <?php endif; ?>
                </div>
                
                <button class="lg:hidden" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-2xl text-gray-700"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden lg:hidden mt-4 pt-4 border-t border-gray-200">
                <div class="flex flex-col space-y-3">
                    <a href="/index.php" class="text-gray-700 hover:text-green-600 font-medium smooth-transition py-2">Trang Chủ</a>
                    
                    <div class="relative">
                        <button onclick="toggleMobileDropdown('servicesDropdown')" class="text-gray-700 hover:text-green-600 font-medium smooth-transition py-2 w-full text-left flex items-center justify-between">
                            Dịch Vụ
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div id="servicesDropdown" class="hidden pl-4 mt-2 space-y-2">
                            <a href="/packages.php" class="block text-gray-600 hover:text-green-600 smooth-transition py-2">
                                <i class="fas fa-server mr-2"></i>VPS Giá Rẻ
                            </a>
                            <a href="/packages.php" class="block text-gray-600 hover:text-green-600 smooth-transition py-2">
                                <i class="fas fa-rocket mr-2"></i>VPS Cao Cấp
                            </a>
                            <a href="/services.php" class="block text-gray-600 hover:text-green-600 smooth-transition py-2">
                                <i class="fas fa-cog mr-2"></i>Quản Lý VPS
                            </a>
                        </div>
                    </div>
                    
                    <a href="/deposit.php" class="text-gray-700 hover:text-green-600 font-medium smooth-transition py-2">Nạp Tiền</a>
                    <a href="#" class="text-gray-700 hover:text-green-600 font-medium smooth-transition py-2">Hỗ Trợ</a>
                    
                    <?php if (isLoggedIn()): 
                        $user = getUser($_SESSION['user_id']);
                    ?>
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-medium text-gray-900"><?= $_SESSION['username'] ?></span>
                                <span class="text-sm text-green-600 font-semibold"><?= formatPrice($user['balance']) ?></span>
                            </div>
                            <a href="/profile.php" class="block text-gray-600 hover:text-green-600 smooth-transition py-2">
                                <i class="fas fa-user-circle mr-2"></i>Hồ Sơ
                            </a>
                            <?php if (isAdmin()): ?>
                                <a href="/admin/" class="block text-gray-600 hover:text-green-600 smooth-transition py-2">
                                    <i class="fas fa-cog mr-2"></i>Admin Panel
                                </a>
                            <?php endif; ?>
                            <a href="/logout.php" class="block text-gray-600 hover:text-red-600 smooth-transition py-2">
                                <i class="fas fa-sign-out-alt mr-2"></i>Đăng Xuất
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="border-t border-gray-200 pt-3 space-y-2">
                            <a href="/login.php" class="block text-gray-700 hover:text-green-600 font-medium smooth-transition py-2">Đăng Nhập</a>
                            <a href="/register.php" class="bg-gradient-to-r from-green-600 to-green-400 hover:from-green-700 hover:to-green-500 text-white px-6 py-2 rounded-lg font-medium smooth-transition text-center">
                                Đăng Ký
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <!-- Newsletter Section -->
        <div class="bg-gray-800 py-8">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto text-center">
                    <h3 class="text-2xl font-bold mb-4">Đăng ký nhận tin khuyến mãi</h3>
                    <p class="text-gray-300 mb-6">Nhận thông tin về các gói VPS mới và ưu đãi đặc biệt</p>
                    <div class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                        <input type="email" placeholder="Email của bạn" class="flex-1 px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <button class="bg-gradient-to-r from-green-600 to-green-400 hover:from-green-700 hover:to-green-500 text-white px-6 py-3 rounded-lg font-medium smooth-transition hover-lift">
                            Đăng ký
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Footer Content -->
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-600 to-green-400 rounded-lg flex items-center justify-center">
                            <i class="fas fa-server text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold">VPS NAT</span>
                    </div>
                    <p class="text-gray-300 mb-4">Dịch vụ VPS chất lượng cao với giá cả phải chăng, uy tín hàng đầu Việt Nam.</p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center smooth-transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center smooth-transition">
                            <i class="fab fa-telegram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center smooth-transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Dịch vụ</h4>
                    <ul class="space-y-3 text-gray-300">
                        <li><a href="/packages.php" class="hover:text-green-400 smooth-transition"><i class="fas fa-chevron-right mr-2 text-xs"></i>VPS Giá Rẻ</a></li>
                        <li><a href="/packages.php" class="hover:text-green-400 smooth-transition"><i class="fas fa-chevron-right mr-2 text-xs"></i>VPS Cao Cấp</a></li>
                        <li><a href="/packages.php" class="hover:text-green-400 smooth-transition"><i class="fas fa-chevron-right mr-2 text-xs"></i>VPS Gaming</a></li>
                        <li><a href="/deposit.php" class="hover:text-green-400 smooth-transition"><i class="fas fa-chevron-right mr-2 text-xs"></i>Nạp Tiền</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Hỗ trợ</h4>
                    <ul class="space-y-3 text-gray-300">
                        <li><a href="#" class="hover:text-green-400 smooth-transition"><i class="fas fa-chevron-right mr-2 text-xs"></i>Trung tâm hỗ trợ</a></li>
                        <li><a href="#" class="hover:text-green-400 smooth-transition"><i class="fas fa-chevron-right mr-2 text-xs"></i>Hướng dẫn thanh toán</a></li>
                        <li><a href="#" class="hover:text-green-400 smooth-transition"><i class="fas fa-chevron-right mr-2 text-xs"></i>FAQ</a></li>
                        <li><a href="#" class="hover:text-green-400 smooth-transition"><i class="fas fa-chevron-right mr-2 text-xs"></i>Liên hệ</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Thông tin liên hệ</h4>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-green-400"></i>
                            <span>support@vpsnat.com</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3 text-green-400"></i>
                            <span>1900 1234</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-3 text-green-400"></i>
                            <span>Hà Nội, Việt Nam</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-clock mr-3 text-green-400"></i>
                            <span>24/7 Support</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Bottom Footer -->
        <div class="border-t border-gray-800">
            <div class="container mx-auto px-4 py-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-400 text-sm mb-4 md:mb-0">
                        &copy; <?= date('Y') ?> VPS NAT. All rights reserved.
                    </div>
                    <div class="flex space-x-6 text-gray-400 text-sm">
                        <a href="#" class="hover:text-green-400 smooth-transition">Điều khoản sử dụng</a>
                        <a href="#" class="hover:text-green-400 smooth-transition">Chính sách bảo mật</a>
                        <a href="#" class="hover:text-green-400 smooth-transition">Refund Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="fixed bottom-8 right-8 w-12 h-12 bg-gradient-to-r from-green-600 to-green-400 hover:from-green-700 hover:to-green-500 text-white rounded-full shadow-lg hidden items-center justify-center smooth-transition hover-lift z-30">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
        
        // Mobile dropdown toggle
        function toggleMobileDropdown(id) {
            const dropdown = document.getElementById(id);
            dropdown.classList.toggle('hidden');
        }
        
        // Loading functions
        function showLoading() {
            const loader = document.createElement('div');
            loader.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            loader.innerHTML = '<div class="loading-spinner"></div>';
            loader.id = 'loadingOverlay';
            document.body.appendChild(loader);
        }
        
        function hideLoading() {
            const loader = document.getElementById('loadingOverlay');
            if (loader) {
                loader.remove();
            }
        }
        
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 smooth-transition`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Back to top button
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove('hidden');
                backToTopButton.classList.add('flex');
            } else {
                backToTopButton.classList.add('hidden');
                backToTopButton.classList.remove('flex');
            }
        });
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe elements with animation class
        document.addEventListener('DOMContentLoaded', () => {
            const animatedElements = document.querySelectorAll('.hover-lift, .float-animation');
            animatedElements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'all 0.6s ease-out';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>