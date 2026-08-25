-- ============================================================
-- Real Estate Management System - Database Schema
-- Import this file into phpMyAdmin (or run via MySQL CLI)
-- ============================================================

CREATE DATABASE IF NOT EXISTS real_estate_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE real_estate_db;

-- ------------------------------------------------------------
-- Table: users
-- Stores THREE kinds of accounts, told apart by the "role" column:
--   admin    -> full access to the Admin Dashboard
--   staff    -> same access as admin (kept separate for future use)
--   customer -> a normal visitor who signed up on the public
--               Register page (register.php); can log in but
--               cannot open the Admin Dashboard.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,               -- always a bcrypt hash, never plain text
    role ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin account -> username: admin | password: Admin@123
-- (password below is a bcrypt hash generated with PHP password_hash())
INSERT INTO users (full_name, username, email, password, role)
VALUES ('System Administrator', 'admin', 'admin@realestate.com',
        '$2y$10$VEwKRzR44lgKwyKdw7zmTuVh8BXPZlW6sBBeLobdJB2JuCoowdckO', 'admin');
-- NOTE: The hash above corresponds to the password "Admin@123".

-- ------------------------------------------------------------
-- Table: properties
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_ref VARCHAR(50) UNIQUE,
    title VARCHAR(150) NOT NULL,
    type ENUM('House','Apartment','Land','Office','Commercial','Villa') NOT NULL DEFAULT 'Apartment',
    price DECIMAL(12,2) NOT NULL,
    location VARCHAR(150) NOT NULL,
    description TEXT,
    bedrooms INT DEFAULT 0,
    bathrooms INT DEFAULT 0,
    size DECIMAL(10,2) DEFAULT 0,          -- size in square meters
    status ENUM('For Sale','For Rent','Sold') NOT NULL DEFAULT 'For Sale',
    image VARCHAR(255) DEFAULT 'no-image.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample properties
INSERT INTO properties (title, type, price, location, description, bedrooms, bathrooms, size, status, image) VALUES
('Modern Family Villa', 'Villa', 250000.00, 'Hargeisa, Jigjiga Yar', 'A spacious modern villa with a private garden, open-plan kitchen, and abundant natural light. Perfect for families looking for comfort and style.', 4, 3, 320.00, 'For Sale', 'no-image.jpg'),
('Cozy City Apartment', 'Apartment', 1200.00, 'Hargeisa, State House', 'A cozy two-bedroom apartment located in the heart of the city, close to shops, restaurants, and public transport.', 2, 1, 85.00, 'For Rent', 'no-image.jpg'),
('Luxury Penthouse Suite', 'Apartment', 450000.00, 'Berbera, Coastal Road', 'An elegant penthouse with panoramic ocean views, high ceilings, and premium finishes throughout.', 3, 2, 210.00, 'For Sale', 'no-image.jpg'),
('Downtown Studio', 'Studio', 850.00, 'Hargeisa, 26 June', 'A compact and efficient studio ideal for students or young professionals, fully furnished.', 1, 1, 40.00, 'For Rent', 'no-image.jpg'),
('Suburban Family House', 'House', 315000.00, 'Boorama', 'A charming family house with a large backyard, garage, and quiet neighborhood setting.', 5, 3, 260.00, 'Sold', 'no-image.jpg');

-- ------------------------------------------------------------
-- Table: messages (customer contact messages)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    subject VARCHAR(150),
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: settings
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('site_name', 'Guryo Samo'),
('site_tagline', 'Helping people find the right place to call home. Browse our listings or reach out and our team will help you find a property that fits your needs.'),
('address', 'Airport Road, Hargeisa, Somaliland'),
('contact_phone', '+252 63 4567890'),
('contact_email', 'info@guryosamo.com'),
('social_facebook', '#'),
('social_whatsapp', '#'),
('social_tiktok', '#');
USE real_estate_db;

CREATE TABLE IF NOT EXISTS property_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    media_type VARCHAR(50) DEFAULT 'image',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(30),
    specialization VARCHAR(100),
    commission_rate DECIMAL(5,2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    agent_id INT,
    customer_name VARCHAR(100),
    type VARCHAR(50),
    amount DECIMAL(15,2) DEFAULT 0.00,
    commission DECIMAL(15,2) DEFAULT 0.00,
    transaction_date DATE,
    status VARCHAR(50) DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_ref VARCHAR(50) UNIQUE,
    customer_name VARCHAR(100),
    property_id INT,
    amount DECIMAL(15,2) DEFAULT 0.00,
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'pending',
    issue_date DATE,
    due_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT,
    amount DECIMAL(15,2) DEFAULT 0.00,
    payment_method VARCHAR(50),
    reference VARCHAR(100),
    payment_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_ref VARCHAR(50) UNIQUE,
    payment_id INT,
    issued_to VARCHAR(100),
    amount DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(100),
    allocated_amount DECIMAL(15,2) DEFAULT 0.00,
    spent_amount DECIMAL(15,2) DEFAULT 0.00,
    period_type VARCHAR(50),
    period_label VARCHAR(100),
    project_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    description TEXT,
    amount DECIMAL(15,2) DEFAULT 0.00,
    scope VARCHAR(50),
    property_id INT NULL,
    project_id INT NULL,
    construction_project_id INT NULL,
    status VARCHAR(50) DEFAULT 'approved',
    expense_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    description TEXT,
    total_investment DECIMAL(15,2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'active',
    start_date DATE,
    end_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contractors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    type VARCHAR(50),
    company VARCHAR(100),
    phone VARCHAR(30),
    email VARCHAR(100),
    speciality VARCHAR(100),
    project_id INT NULL,
    daily_rate DECIMAL(15,2) DEFAULT 0.00,
    rating DECIMAL(3,1) DEFAULT 0.0,
    status VARCHAR(50) DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS construction_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    description TEXT,
    property_id INT NULL,
    status VARCHAR(50) DEFAULT 'planning',
    progress INT DEFAULT 0,
    budget DECIMAL(15,2) DEFAULT 0.00,
    spent DECIMAL(15,2) DEFAULT 0.00,
    start_date DATE,
    end_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS construction_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    title VARCHAR(150),
    description TEXT,
    assigned_to INT NULL,
    priority VARCHAR(50) DEFAULT 'medium',
    status VARCHAR(50) DEFAULT 'pending',
    due_date DATE NULL,
    completed_at DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS construction_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    name VARCHAR(150),
    category VARCHAR(50),
    unit VARCHAR(20),
    quantity DECIMAL(15,2) DEFAULT 0.00,
    unit_cost DECIMAL(15,2) DEFAULT 0.00,
    supplier VARCHAR(100),
    stock_level DECIMAL(15,2) DEFAULT 0.00,
    reorder_point DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS construction_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    title VARCHAR(150),
    type VARCHAR(50),
    notes TEXT,
    issued_date DATE NULL,
    expiry_date DATE NULL,
    file_name VARCHAR(255),
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NULL,
    construction_project_id INT NULL,
    agent_id INT NULL,
    customer_name VARCHAR(100),
    customer_email VARCHAR(100),
    customer_phone VARCHAR(30),
    type VARCHAR(50),
    scheduled_at DATETIME,
    notes TEXT,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
