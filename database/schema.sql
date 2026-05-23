-- Grocery Shop Database Schema
CREATE DATABASE IF NOT EXISTS grocery_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE grocery_db;

-- Admins
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'admin',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mobile VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Grocery items
CREATE TABLE IF NOT EXISTS items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255) DEFAULT 'default.png',
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('available','unavailable') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Stock history
CREATE TABLE IF NOT EXISTS stock (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    transaction_type ENUM('in','out') NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Shopping cart
CREATE TABLE IF NOT EXISTS cart (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY user_item_unique (user_id, item_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cod','online') NOT NULL DEFAULT 'cod',
    payment_status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    delivery_status ENUM('pending','confirmed','delivered','cancelled') NOT NULL DEFAULT 'pending',
    address TEXT NOT NULL,
    status ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Order items
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Delivery
CREATE TABLE IF NOT EXISTS delivery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    delivery_person VARCHAR(150),
    status ENUM('assigned','in_transit','delivered','failed') NOT NULL DEFAULT 'assigned',
    assigned_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Payments
CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(100),
    payment_method ENUM('cod','online') NOT NULL DEFAULT 'cod',
    status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed data for initial testing
INSERT IGNORE INTO admins (name, email, password) VALUES
('Admin User', 'admin@example.com', '$2y$10$gCZ5tYW5eD9Dl5bJ5vc6W.vskehIrHmb33e2J7RdXU49htTflSMH2');

INSERT IGNORE INTO users (name, email, mobile, address, password) VALUES
('Anu', 'anu@example.com', '9876543210', '123 Green Lane, City', '$2y$10$KbQ9bMzRzKf7hQ1Kx9bZKuZL5P8rN2XI4s6fG7wTo7w9uYvG93dS6');

INSERT IGNORE INTO categories (name, description, status) VALUES
('Oils', 'Cooking oils and edible oils.', 'active'),
('Fruits', 'Fresh fruits and berries.', 'active'),
('Vegetables', 'Daily vegetables and greens.', 'active');

INSERT IGNORE INTO items (category_id, name, slug, description, image, price, stock_qty, status) VALUES
(1, 'Cooking Oil', 'cooking-oil', 'Premium edible cooking oil for everyday use.', 'default.svg', 179.99, 25, 'available'),
(2, 'Fresh Apples', 'fresh-apples', 'Crisp and juicy apples sourced locally.', 'default.svg', 119.50, 40, 'available'),
(3, 'Organic Spinach', 'organic-spinach', 'Healthy organic spinach full of vitamins.', 'default.svg', 49.00, 60, 'available');
