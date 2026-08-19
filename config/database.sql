CREATE DATABASE IF NOT EXISTS harvestly
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE harvestly;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    district VARCHAR(80) DEFAULT NULL,
    city VARCHAR(80) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    profile_image VARCHAR(500) DEFAULT NULL,
    role ENUM('buyer', 'admin') NOT NULL DEFAULT 'buyer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    unit VARCHAR(40) NOT NULL DEFAULT 'kg',
    farmer VARCHAR(180) NOT NULL,
    rating DECIMAL(3,2) NOT NULL DEFAULT 0,
    reviews INT NOT NULL DEFAULT 0,
    fresh TINYINT(1) NOT NULL DEFAULT 1,
    organic TINYINT(1) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(700) DEFAULT NULL,
    description TEXT,
    harvest_date VARCHAR(80) DEFAULT NULL,
    farm VARCHAR(180) DEFAULT NULL,
    farmer_rating DECIMAL(3,2) DEFAULT 0,
    experience VARCHAR(80) DEFAULT NULL,
    delivery VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS carts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_product (cart_id, product_id),
    CONSTRAINT fk_ci_cart
        FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    CONSTRAINT fk_ci_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(40) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    city VARCHAR(80) NOT NULL,
    address VARCHAR(255) NOT NULL,
    postal VARCHAR(20) NOT NULL,
    payment_method ENUM('card', 'cash', 'bank') NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    delivery_fee DECIMAL(12,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    status ENUM(
        'Order Placed',
        'Accepted',
        'In Transit',
        'Out for Delivery',
        'Delivered',
        'Cancelled'
    ) NOT NULL DEFAULT 'Order Placed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    name VARCHAR(180) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    quantity INT NOT NULL,
    unit VARCHAR(40) DEFAULT 'kg',
    image VARCHAR(700) DEFAULT NULL,
    CONSTRAINT fk_oi_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(40) NOT NULL,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    action_label VARCHAR(80) DEFAULT NULL,
    action_url VARCHAR(500) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    order_id INT UNSIGNED NULL,
    farmer_rating TINYINT UNSIGNED NOT NULL,
    delivery_rating TINYINT UNSIGNED NOT NULL,
    quality_comment TEXT,
    delivery_comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_feedback_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS complaints (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    order_id INT UNSIGNED NULL,
    category VARCHAR(100) NOT NULL,
    details TEXT NOT NULL,
    status ENUM('Open', 'In Progress', 'Resolved', 'Closed') NOT NULL DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_complaint_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_complaint_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (name, email, phone, district, city, address, password_hash, role)
VALUES (
    'Nimal Perera',
    'demo@harvestly.local',
    '0771234567',
    'Colombo',
    'Colombo',
    '123 Farm Road, Colombo',
    '$2y$12$pcJlGEjIQqS4e2saBQxK6OhZSUtIeyaDiZJEFc/qWQePfUF2K1sam',
    'buyer'
)
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO products
    (name, price, unit, farmer, rating, reviews, fresh, organic, stock, image, description, harvest_date, farm, farmer_rating, experience, delivery)
SELECT * FROM (
    SELECT
        'Premium Carrots', 320.00, 'kg', 'Sunil Farms, Nuwara Eliya', 4.80, 124, 1, 1, 120,
        'https://images.unsplash.com/photo-1445282768818-728615cc910a?auto=format&fit=crop&w=900&q=80',
        'Crisp, sweet organic carrots harvested daily from Nuwara Eliya.',
        'Today', 'Green Valley Farm, Nuwara Eliya', 4.80, '15 Years Exp.', 'LKR 350 standard delivery fee.'
    UNION ALL
    SELECT
        'Vine Tomatoes', 260.00, 'kg', 'Green Valley, Badulla', 4.50, 89, 1, 0, 95,
        'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?auto=format&fit=crop&w=900&q=80',
        'Fresh vine tomatoes harvested directly from trusted farmers in Badulla.',
        'Today', 'Green Valley Farm, Badulla', 4.50, '10 Years Exp.', 'LKR 350 standard delivery fee.'
    UNION ALL
    SELECT
        'Fresh Spinach Bundle', 150.00, 'bundle', 'Green Valley Greens, Colombo', 4.70, 76, 1, 1, 70,
        'https://images.unsplash.com/photo-1576045057995-568f588f82fb?auto=format&fit=crop&w=900&q=80',
        'Fresh leafy spinach bundle from local growers.',
        'Today', 'Green Valley Greens', 4.70, '8 Years Exp.', 'LKR 350 standard delivery fee.'
    UNION ALL
    SELECT
        'Coconut', 180.00, 'each', 'Lanka Coconut Farm, Kurunegala', 4.60, 52, 1, 0, 200,
        'https://images.unsplash.com/photo-1553787434-dd9eb4ea4d0b?q=80&w=735&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'Fresh mature coconut supplied by local farmers.',
        'Today', 'Lanka Coconut Farm', 4.60, '12 Years Exp.', 'LKR 350 standard delivery fee.'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);
