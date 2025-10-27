USE vps_store;

-- SEO Settings table
CREATE TABLE IF NOT EXISTS seo_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_name VARCHAR(100) NOT NULL UNIQUE,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    og_title VARCHAR(255),
    og_description TEXT,
    og_image VARCHAR(255),
    canonical_url VARCHAR(255),
    robots TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Site Settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'image', 'number') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    target_page VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATETIME,
    end_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact Settings table
CREATE TABLE IF NOT EXISTS contact_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_type VARCHAR(50) NOT NULL,
    contact_value VARCHAR(255) NOT NULL,
    display_name VARCHAR(100),
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Telegram Settings table
CREATE TABLE IF NOT EXISTS telegram_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_token VARCHAR(255) NOT NULL,
    chat_id VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default SEO settings
INSERT INTO seo_settings (page_name, meta_title, meta_description, meta_keywords) VALUES 
('home', 'VPS NAT Giá Rẻ | VPS Chất Lượng Cao', 'Cung cấp dịch vụ VPS NAT giá rẻ nhất Việt Nam, chất lượng cao, hỗ trợ 24/7', 'vps nat, vps giá rẻ, vps chất lượng cao, hosting, server'),
('packages', 'Gói VPS | VPS NAT & VPS Cheap', 'Các gói VPS đa dạng với giá cả phải chăng, phù hợp mọi nhu cầu', 'gói vps, vps nat, vps cheap, giá vps, mua vps'),
('services', 'Quản Lý VPS | Dịch Vụ VPS', 'Dịch vụ quản lý VPS chuyên nghiệp, hỗ trợ kỹ thuật 24/7', 'quản lý vps, dịch vụ vps, hỗ trợ vps, kỹ thuật vps'),
('contact', 'Liên Hệ | VPS NAT', 'Thông tin liên hệ, hỗ trợ khách hàng 24/7', 'liên hệ vps, hỗ trợ vps, contact vps'),
('about', 'Về Chúng Tôi | VPS NAT', 'Giới thiệu về VPS NAT, đội ngũ và dịch vụ', 'về chúng tôi, giới thiệu vps nat, đội ngũ vps');

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES 
('site_name', 'VPS NAT', 'text', 'Tên website'),
('site_description', 'Dịch vụ VPS chất lượng cao với giá cả phải chăng', 'textarea', 'Mô tả website'),
('site_logo', 'https://i.ibb.co/7mzR5qs/image.png', 'image', 'Logo website'),
('site_favicon', '', 'image', 'Favicon website'),
('company_name', 'VPS NAT Company', 'text', 'Tên công ty'),
('company_address', 'Hà Nội, Việt Nam', 'textarea', 'Địa chỉ công ty'),
('company_email', 'hotronify@gmail.com', 'text', 'Email công ty'),
('company_phone', '0898 686 001', 'text', 'Số điện thoại công ty'),
('company_hotline', '0898 686 001', 'text', 'Hotline'),
('facebook_url', '#', 'text', 'URL Facebook'),
('telegram_url', '#', 'text', 'URL Telegram'),
('youtube_url', '#', 'text', 'URL YouTube'),
('cookie_expiry_days', '30', 'number', 'Số ngày lưu cookie'),
('maintenance_mode', 'false', 'text', 'Chế độ bảo trì');

-- Insert default contact settings
INSERT INTO contact_settings (contact_type, contact_value, display_name, icon, sort_order) VALUES 
('email', 'hotronify@gmail.com', 'Email', 'fas fa-envelope', 1),
('phone', '0898 686 001', 'Hotline', 'fas fa-phone', 2),
('facebook', 'https://facebook.com/nify.support', 'Facebook', 'fab fa-facebook', 3),
('telegram', 'https://t.me/nifysupport', 'Telegram', 'fab fa-telegram', 4),
('zalo', '0898 686 001', 'Zalo', 'fas fa-comments', 5);