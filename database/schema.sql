-- ProPrint Solutions Database Schema
-- Run this SQL in phpMyAdmin or MySQL

-- Create database
CREATE DATABASE IF NOT EXISTS proprint_db;
USE proprint_db;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table (updated with new fields)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    size VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Insert default admin user
-- Username: admin
-- Password: admin123
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$8K1p/a0dL1LXMIgoEDFrOOemTRbL0hF.vP0eLQh5uLPFAWO9p3K6e')
ON DUPLICATE KEY UPDATE username = username;

-- Insert default categories
INSERT INTO categories (name) VALUES
('Thamboolam Bags'),
('Wedding Invitation Cards'),
('Flex Banners'),
('Posters'),
('Medals & Trophies'),
('UV Prints'),
('Custom Designs')
ON DUPLICATE KEY UPDATE name = name;
