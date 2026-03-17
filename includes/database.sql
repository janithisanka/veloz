-- Veloz Autohaus Colombo - Database Schema

CREATE DATABASE IF NOT EXISTS veloz_autohaus;
USE veloz_autohaus;

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Car categories/types
CREATE TABLE IF NOT EXISTS car_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Car brands
CREATE TABLE IF NOT EXISTS car_brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cars table
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_id INT,
    category_id INT,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    condition_type ENUM('brand_new', 'recondition') NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    mileage INT DEFAULT 0,
    engine_capacity VARCHAR(50),
    fuel_type ENUM('petrol', 'diesel', 'hybrid', 'electric') NOT NULL,
    transmission ENUM('automatic', 'manual', 'cvt') NOT NULL,
    color VARCHAR(50),
    body_type VARCHAR(50),
    seats INT DEFAULT 5,
    features TEXT,
    description TEXT,
    main_image VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES car_brands(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES car_categories(id) ON DELETE SET NULL
);

-- Car images table
CREATE TABLE IF NOT EXISTS car_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Quote requests table
CREATE TABLE IF NOT EXISTS quote_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    location VARCHAR(100),
    message TEXT,
    preferred_contact ENUM('email', 'phone', 'whatsapp') DEFAULT 'phone',
    status ENUM('pending', 'contacted', 'converted', 'closed') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL
);

-- Contact inquiries table
CREATE TABLE IF NOT EXISTS contact_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customer gallery (imported cars + happy customers)
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    gallery_type ENUM('delivery', 'imported', 'customer') DEFAULT 'delivery',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Social media posts / news
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    image_path VARCHAR(255),
    post_type ENUM('news', 'social', 'promo') DEFAULT 'news',
    external_link VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pre-orders table
CREATE TABLE IF NOT EXISTS preorders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    year_from INT,
    year_to INT,
    budget_min DECIMAL(15,2),
    budget_max DECIMAL(15,2),
    fuel_preference ENUM('any','petrol','diesel','hybrid','electric') DEFAULT 'any',
    transmission_preference ENUM('any','automatic','manual','cvt') DEFAULT 'any',
    color_preference VARCHAR(100),
    additional_notes TEXT,
    status ENUM('pending','sourcing','found','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admins (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@velozautohaus.lk');

-- Insert default categories
INSERT INTO car_categories (name, description) VALUES
('Sedan', 'Comfortable 4-door passenger cars'),
('SUV', 'Sport Utility Vehicles'),
('Hatchback', 'Compact cars with rear door'),
('Van', 'Multi-purpose vehicles'),
('Wagon', 'Station wagons'),
('Coupe', 'Two-door sports cars'),
('Pickup', 'Utility trucks');

-- Insert popular Japanese brands
INSERT INTO car_brands (name) VALUES
('Toyota'), ('Honda'), ('Nissan'), ('Mazda'), ('Suzuki'),
('Mitsubishi'), ('Subaru'), ('Lexus'), ('Daihatsu'), ('Isuzu');

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('phone', '+94 76 088 1409'),
('email', 'info@velozautohaus.lk'),
('address', 'Colombo, Sri Lanka'),
('whatsapp', '+94760881409'),
('facebook', 'https://facebook.com/velozautohaus'),
('instagram', 'https://instagram.com/velozautohaus'),
('youtube', ''),
('tiktok', ''),
('company_name', 'Veloz Autohaus Colombo'),
('tagline', 'Premium Japanese Vehicle Imports'),
('about_text', 'Veloz Autohaus Colombo is Sri Lanka''s premier Japanese vehicle importer. Part of the Veloz Autohaus family, we offer the lowest prices on brand new and reconditioned vehicles direct from Japan. Quality assured, transparent pricing, and complete after-sales support.');

-- Insert sample cars
INSERT INTO cars (brand_id, category_id, model, year, condition_type, price, mileage, engine_capacity, fuel_type, transmission, color, body_type, seats, features, description, is_featured, is_available) VALUES
(1, 1, 'Corolla Axio', 2023, 'recondition', 8500000, 25000, '1500cc', 'hybrid', 'automatic', 'Pearl White', 'Sedan', 5, 'Push Start, Cruise Control, Lane Assist, Reverse Camera, Alloy Wheels', 'Toyota Corolla Axio Hybrid - Excellent fuel efficiency with premium features. Grade 4.5 verified.', 1, 1),
(1, 2, 'RAV4', 2024, 'brand_new', 18500000, 0, '2500cc', 'hybrid', 'automatic', 'Attitude Black', 'SUV', 5, 'Sunroof, Leather Seats, 360 Camera, Lane Departure, Adaptive Cruise', 'Brand New Toyota RAV4 Hybrid - Latest model with full options. Factory warranty included.', 1, 1),
(2, 1, 'Civic', 2023, 'recondition', 9200000, 18000, '1500cc', 'petrol', 'cvt', 'Crystal Black', 'Sedan', 5, 'Turbo Engine, Honda Sensing, Sunroof, Premium Audio', 'Honda Civic Turbo RS - Sporty sedan with turbocharged performance.', 1, 1),
(3, 1, 'Leaf', 2023, 'recondition', 7800000, 22000, 'Electric', 'electric', 'automatic', 'Brilliant Silver', 'Hatchback', 5, 'ProPilot, e-Pedal, 40kWh Battery, Fast Charging', 'Nissan Leaf - Zero emission electric vehicle. Perfect for eco-conscious drivers.', 1, 1),
(4, 2, 'CX-5', 2023, 'recondition', 12500000, 15000, '2200cc', 'diesel', 'automatic', 'Soul Red', 'SUV', 5, 'BOSE Audio, Heads-up Display, Power Tailgate, Leather Interior', 'Mazda CX-5 Diesel - Premium SUV with excellent build quality.', 0, 1),
(5, 3, 'Swift', 2024, 'brand_new', 5800000, 0, '1200cc', 'hybrid', 'automatic', 'Speedy Blue', 'Hatchback', 5, 'Mild Hybrid, Safety Package, Touchscreen, Apple CarPlay', 'Brand New Suzuki Swift Hybrid - Fuel efficient and fun to drive.', 1, 1);
