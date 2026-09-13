-- =========================================================
-- MEDIQUICK PHARMACY DATABASE
-- Database: mediquickdb
-- =========================================================

CREATE DATABASE IF NOT EXISTS mediquickdb
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE mediquickdb;


-- =========================================================
-- 1. USERS TABLE
-- =========================================================

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS prescriptions;
DROP TABLE IF EXISTS inquiries;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;


CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,

    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    phone VARCHAR(20) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    address VARCHAR(255) NOT NULL,

    city VARCHAR(80) NOT NULL,

    -- 1 = Pending
    -- 0 = Approved
    status TINYINT NOT NULL DEFAULT 1,

    -- customer / staff / admin
    role ENUM('customer','staff','admin')
    NOT NULL DEFAULT 'customer',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- 2. PRODUCTS TABLE
-- =========================================================

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    category VARCHAR(80) NOT NULL,

    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    stock_qty INT NOT NULL DEFAULT 0,

    description TEXT,

    dosage VARCHAR(255),

    safety_info TEXT,

    requires_prescription TINYINT(1) NOT NULL DEFAULT 0,

    image_url VARCHAR(500),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- 3. PRESCRIPTIONS TABLE
-- =========================================================

CREATE TABLE prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    file_name VARCHAR(255) NOT NULL,

    file_path VARCHAR(500) NOT NULL,

    status ENUM('Pending','Approved','Rejected')
    NOT NULL DEFAULT 'Pending',

    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_prescription_user
    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE
);


-- =========================================================
-- 4. ORDERS TABLE
-- =========================================================

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    payment_method VARCHAR(50) NOT NULL,

    shipping_address VARCHAR(255) NOT NULL,

    status ENUM(
        'Pending',
        'Processing',
        'Shipped',
        'Delivered',
        'Cancelled'
    ) NOT NULL DEFAULT 'Pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_order_user
    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE
);


-- =========================================================
-- 5. ORDER ITEMS TABLE
-- =========================================================

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,

    order_id INT NOT NULL,

    product_id INT NOT NULL,

    quantity INT NOT NULL,

    unit_price DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_order_item_order
    FOREIGN KEY (order_id)
    REFERENCES orders(order_id)
    ON DELETE CASCADE,

    CONSTRAINT fk_order_item_product
    FOREIGN KEY (product_id)
    REFERENCES products(product_id)
    ON DELETE RESTRICT
);


-- =========================================================
-- 6. INQUIRIES TABLE
-- =========================================================

CREATE TABLE inquiries (
    inquiry_id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(120) NOT NULL,

    email VARCHAR(150) NOT NULL,

    message TEXT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- 7. ADMIN ACCOUNT
-- =========================================================
-- Email: admin@mediquick.lk
-- Password: Admin@123
--
-- The password hash below is for the password
-- "Admin@123".
-- =========================================================

INSERT INTO users
(
    first_name,
    last_name,
    email,
    phone,
    password,
    address,
    city,
    status,
    role
)
VALUES
(
    'MediQuick',
    'Administrator',
    'admin@mediquick.lk',
    '0771234567',
    '$2y$10$wH9mQhQ5YhX2vL9fM6P5uOQ7G9pQ1J9m2Yw8W8x7uQ6xQx2xQxQxO',
    'MediQuick Pharmacy, Kurunegala',
    'Kurunegala',
    0,
    'admin'
);


-- =========================================================
-- 8. SAMPLE CUSTOMER
-- =========================================================

INSERT INTO users
(
    first_name,
    last_name,
    email,
    phone,
    password,
    address,
    city,
    status,
    role
)
VALUES
(
    'Test',
    'Customer',
    'customer@mediquick.lk',
    '0712345678',
    '$2y$10$wH9mQhQ5YhX2vL9fM6P5uOQ7G9pQ1J9m2Yw8W8x7uQ6xQx2xQxQxO',
    'Main Street',
    'Kurunegala',
    0,
    'customer'
);


-- =========================================================
-- 9. SAMPLE PRODUCTS
-- =========================================================

INSERT INTO products
(
    name,
    category,
    price,
    stock_qty,
    description,
    dosage,
    safety_info,
    requires_prescription,
    image_url
)
VALUES

(
    'Paracetamol 500mg',
    'OTC Medicines',
    150.00,
    100,
    'Paracetamol tablets commonly used to relieve mild to moderate pain and reduce fever.',
    'Adults: Follow the dosage instructions on the product label or as advised by a healthcare professional.',
    'Do not exceed the recommended dose. Consult a healthcare professional if symptoms continue.',
    0,
    'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=800&q=80'
),

(
    'Vitamin C 500mg',
    'Wellness',
    850.00,
    50,
    'Vitamin C supplement supporting normal immune system function.',
    'Use according to the product label or healthcare professional advice.',
    'Do not exceed the recommended daily amount.',
    0,
    'https://images.unsplash.com/photo-1607619056574-7b8d3ee536b2?auto=format&fit=crop&w=800&q=80'
),

(
    'Digital Thermometer',
    'Personal Care',
    1200.00,
    30,
    'Digital thermometer designed for convenient temperature measurement.',
    'Place the thermometer according to the manufacturer instructions.',
    'Clean and disinfect the thermometer after use.',
    0,
    'https://images.unsplash.com/photo-1584634731339-252c581abfc5?auto=format&fit=crop&w=800&q=80'
),

(
    'Amoxicillin 500mg',
    'Prescription Medicines',
    950.00,
    40,
    'Prescription antibiotic used to treat certain bacterial infections.',
    'Use only according to a valid prescription and healthcare professional instructions.',
    'Prescription required. Complete the prescribed course unless advised otherwise by your healthcare professional.',
    1,
    'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=800&q=80'
),

(
    'Hand Sanitizer 100ml',
    'Personal Care',
    450.00,
    80,
    'Alcohol-based hand sanitizer for convenient hand hygiene.',
    'Apply an adequate amount to hands and rub until dry.',
    'For external use only. Keep away from eyes and flames.',
    0,
    'https://images.unsplash.com/photo-1584483766114-2cea6facdf57?auto=format&fit=crop&w=800&q=80'
),

(
    'Multivitamin Tablets',
    'Wellness',
    1450.00,
    60,
    'Daily multivitamin supplement containing a combination of essential vitamins and minerals.',
    'Take according to the instructions on the product packaging.',
    'Do not exceed the recommended daily dose.',
    0,
    'https://images.unsplash.com/photo-1550572017-edd951aa8ca3?auto=format&fit=crop&w=800&q=80'
),

(
    'Ibuprofen 200mg',
    'OTC Medicines',
    300.00,
    75,
    'Over-the-counter medicine commonly used for temporary relief of pain and inflammation.',
    'Use according to the package directions or healthcare professional advice.',
    'Take with food if appropriate. Do not exceed the recommended dose.',
    0,
    'https://images.unsplash.com/photo-1471864190281-a93a3070b6de?auto=format&fit=crop&w=800&q=80'
),

(
    'Antiseptic Cream',
    'Personal Care',
    650.00,
    45,
    'Topical antiseptic cream for minor cuts, scratches and skin protection.',
    'Apply a small amount to the affected area as directed.',
    'For external use only. Avoid contact with eyes.',
    0,
    'https://images.unsplash.com/photo-1603398938378-e54eab446dde?auto=format&fit=crop&w=800&q=80'
),

(
    'Cough Syrup',
    'OTC Medicines',
    780.00,
    35,
    'Cough relief product intended for temporary relief of common cough symptoms.',
    'Follow the dosage instructions provided on the product packaging.',
    'Do not exceed the recommended dosage. Consult a healthcare professional for persistent symptoms.',
    0,
    'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=800&q=80'
),

(
    'Moisturizing Lotion',
    'Personal Care',
    1100.00,
    40,
    'Daily moisturizing lotion designed to help maintain soft and hydrated skin.',
    'Apply to clean skin as required.',
    'For external use only. Stop use if irritation occurs.',
    0,
    'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8?auto=format&fit=crop&w=800&q=80'
);


-- =========================================================
-- DATABASE SETUP COMPLETE
-- =========================================================