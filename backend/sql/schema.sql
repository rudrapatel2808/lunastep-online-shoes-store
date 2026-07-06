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

-- Seed Data (Optional, for testing)
INSERT INTO categories (name, description) VALUES
('Running', 'Running shoes'),
('Casual', 'Casual everyday shoes'),
('Trail', 'Off-road trail shoes'),
('Performance', 'High performance athletic shoes');

INSERT INTO products (category_id, brand, name, description, base_price, discount_price, image_url) VALUES
(1, 'Nike', 'Men''s Nike T-Shirt Shoes', 'Lightweight and responsive running shoes.', 240.00, 189.00, 'img/runner.png'),
(2, 'BrandX', 'Quilted Gleit With Hood', 'Classic casual shoes perfect for everyday wear.', 159.00, NULL, 'img/casual.png'),
(3, 'BrandY', 'Jogers with Black strip', 'Rugged trail shoes built for off-road adventures.', 260.00, 214.00, 'img/trail.png'),
(4, 'Rolex', 'Rolex Gold Gilet Shoes', 'High-performance athletic shoes.', 175.00, NULL, 'img/performance.png');

INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES
('Admin', 'User', 'admin@shoestore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- password: password
('Manager', 'User', 'manager@shoestore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager'); -- password: password
