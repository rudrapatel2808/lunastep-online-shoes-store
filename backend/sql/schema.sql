-- backend/sql/schema.sql
-- Run this in your MySQL instance (e.g. phpMyAdmin)

CREATE DATABASE IF NOT EXISTS shoestore_db;
USE shoestore_db;

-- 1. users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  role ENUM('customer', 'admin', 'manager') DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  description TEXT
);

-- 3. products
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT,
  brand VARCHAR(50),
  name VARCHAR(100) NOT NULL,
  description TEXT,
  base_price DECIMAL(10, 2) NOT NULL,
  discount_price DECIMAL(10, 2),
  image_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 4. product_variants
CREATE TABLE IF NOT EXISTS product_variants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  size VARCHAR(10) NOT NULL,
  color VARCHAR(20) NOT NULL,
  sku VARCHAR(50) UNIQUE NOT NULL,
  stock_quantity INT DEFAULT 0,
  low_stock_threshold INT DEFAULT 5,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 5. orders
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  order_number VARCHAR(50) UNIQUE NOT NULL,
  total_amount DECIMAL(10, 2) NOT NULL,
  status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  shipping_address TEXT NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 6. order_items
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  variant_id INT,
  quantity INT NOT NULL,
  price_at_purchase DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
);

-- 7. reviews
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  user_id INT,
  rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 8. cart_items (Server-side cart for logged-in users)
CREATE TABLE IF NOT EXISTS cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  variant_id INT NOT NULL,
  quantity INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
);


-- ============================================
-- SEED DATA
-- ============================================

-- Categories
INSERT INTO categories (name, description) VALUES
('Running', 'Running shoes for speed and comfort'),
('Casual', 'Casual everyday shoes'),
('Trail', 'Off-road trail shoes for adventure'),
('Performance', 'High performance athletic shoes');

-- Products
INSERT INTO products (category_id, brand, name, description, base_price, discount_price, image_url) VALUES
(1, 'Nike', 'Men''s Nike T-Shirt Shoes', 'Lightweight and responsive running shoes designed for speed and comfort. Features breathable mesh upper and cushioned midsole.', 240.00, 189.00, 'img/runner.png'),
(2, 'BrandX', 'Quilted Gleit With Hood', 'Classic casual shoes perfect for everyday wear. Premium leather upper with soft interior lining for all-day comfort.', 159.00, NULL, 'img/casual.png'),
(3, 'BrandY', 'Jogers with Black Strip', 'Rugged trail shoes built for off-road adventures. Aggressive tread pattern and waterproof upper for any terrain.', 260.00, 214.00, 'img/trail.png'),
(4, 'Rolex', 'Rolex Gold Gilet Shoes', 'High-performance athletic shoes with advanced cushioning technology. Designed for serious athletes who demand the best.', 175.00, NULL, 'img/performance.png'),
(1, 'Nike', 'AeroFlex Runner X2', 'Next-generation running shoe with carbon fiber plate and energy-return foam. Perfect for marathons and daily training.', 249.00, 199.00, 'img/runner.png'),
(2, 'BrandX', 'Urban Forge Chelsea', 'Sleek chelsea boot style sneaker hybrid. Perfect for smart-casual occasions with waterproof leather.', 145.00, NULL, 'img/casual.png'),
(3, 'BrandY', 'Nimbus Trail Pro', 'Professional trail running shoes with GORE-TEX waterproofing and Vibram outsole. Conquers any mountain.', 280.00, 229.00, 'img/trail.png'),
(4, 'Rolex', 'Volt Lab Sprint', 'Competition-grade sprinting shoes with minimal weight and maximum energy return. Track and field certified.', 195.00, NULL, 'img/performance.png');

-- Product Variants (Sizes & Colors with Stock)
INSERT INTO product_variants (product_id, size, color, sku, stock_quantity, low_stock_threshold) VALUES
-- Nike T-Shirt Shoes
(1, '8', 'Black', 'NIK-TSH-BLK-8', 15, 5),
(1, '9', 'Black', 'NIK-TSH-BLK-9', 22, 5),
(1, '10', 'Black', 'NIK-TSH-BLK-10', 18, 5),
(1, '9', 'Red', 'NIK-TSH-RED-9', 8, 5),
(1, '10', 'Red', 'NIK-TSH-RED-10', 3, 5),
-- Quilted Gleit
(2, '8', 'Brown', 'QG-BRN-8', 10, 5),
(2, '9', 'Brown', 'QG-BRN-9', 12, 5),
(2, '10', 'Brown', 'QG-BRN-10', 7, 5),
-- Jogers
(3, '9', 'Black', 'JOG-BLK-9', 20, 5),
(3, '10', 'Black', 'JOG-BLK-10', 14, 5),
(3, '11', 'Green', 'JOG-GRN-11', 2, 5),
-- Rolex Gold Gilet
(4, '9', 'Gold', 'RLX-GLD-9', 6, 5),
(4, '10', 'Gold', 'RLX-GLD-10', 9, 5),
-- AeroFlex Runner
(5, '9', 'Blue', 'AFX-BLU-9', 3, 5),
(5, '10', 'Blue', 'AFX-BLU-10', 11, 5),
(5, '11', 'Black', 'AFX-BLK-11', 0, 5),
-- Urban Forge
(6, '8', 'Black', 'UFC-BLK-8', 5, 5),
(6, '9', 'Black', 'UFC-BLK-9', 8, 5),
-- Nimbus Trail Pro
(7, '10', 'Green', 'NTP-GRN-10', 4, 5),
(7, '11', 'Green', 'NTP-GRN-11', 2, 5),
(7, '9', 'Black', 'NTP-BLK-9', 16, 5),
-- Volt Lab Sprint
(8, '9', 'Gold', 'VLS-GLD-9', 7, 5),
(8, '10', 'Red', 'VLS-RED-10', 1, 5),
(8, '11', 'Black', 'VLS-BLK-11', 13, 5);

-- Admin & Manager Users (password: "password" for both)
INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES
('Admin', 'User', 'admin@shoestore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Manager', 'User', 'manager@shoestore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager');

-- Demo Customer (password: "password")
INSERT INTO users (first_name, last_name, email, password_hash, phone, role) VALUES
('Demo', 'Customer', 'demo@shoestore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1 555-0123', 'customer');

-- Sample Order
INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, payment_method) VALUES
(3, 'ORD-DEMO001', 403.00, 'delivered', '123 Main St, New York, NY 10001', 'cod'),
(3, 'ORD-DEMO002', 189.00, 'shipped', '123 Main St, New York, NY 10001', 'card'),
(3, 'ORD-DEMO003', 229.00, 'pending', '123 Main St, New York, NY 10001', 'upi');

-- Sample Order Items
INSERT INTO order_items (order_id, variant_id, quantity, price_at_purchase) VALUES
(1, 1, 1, 189.00),
(1, 9, 1, 214.00),
(2, 2, 1, 189.00),
(3, 19, 1, 229.00);

-- Sample Reviews
INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
(1, 3, 5, 'Amazing running shoes! Very comfortable and lightweight. Highly recommend for daily runs.'),
(3, 3, 4, 'Great trail shoes. The grip is excellent but they take a few days to break in.'),
(5, 3, 5, 'Best running shoes I have ever owned. The carbon plate really makes a difference!');
