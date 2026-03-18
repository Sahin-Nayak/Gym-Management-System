-- =============================================
-- GYM MANAGEMENT SYSTEM - DATABASE SCHEMA
-- =============================================
-- Run this SQL file in phpMyAdmin or MySQL CLI
-- Database: gym_management

CREATE DATABASE IF NOT EXISTS gym_management;
USE gym_management;

-- =============================================
-- 1. USERS TABLE (Auth for Admin/Trainer/Member)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','trainer','member') NOT NULL DEFAULT 'member',
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expiry DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- 2. MEMBERS TABLE
-- =============================================
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address TEXT,
    age INT,
    gender ENUM('male','female','other') DEFAULT 'male',
    weight DECIMAL(5,2) DEFAULT NULL,
    height DECIMAL(5,2) DEFAULT NULL,
    photo VARCHAR(255) DEFAULT 'default.png',
    emergency_contact_name VARCHAR(100) DEFAULT NULL,
    emergency_contact_phone VARCHAR(20) DEFAULT NULL,
    join_date DATE NOT NULL,
    plan_id INT DEFAULT NULL,
    trainer_id INT DEFAULT NULL,
    membership_start DATE DEFAULT NULL,
    membership_end DATE DEFAULT NULL,
    status ENUM('active','inactive','expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- 3. TRAINERS TABLE
-- =============================================
CREATE TABLE trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) DEFAULT NULL,
    experience INT DEFAULT 0,
    photo VARCHAR(255) DEFAULT 'default.png',
    bio TEXT,
    schedule TEXT,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- 4. MEMBERSHIP PLANS TABLE
-- =============================================
CREATE TABLE plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    duration_months INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    features TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- 5. PAYMENTS TABLE
-- =============================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    plan_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_mode ENUM('cash','upi','card','online') NOT NULL DEFAULT 'cash',
    transaction_id VARCHAR(100) DEFAULT NULL,
    status ENUM('paid','pending','failed','refunded') DEFAULT 'paid',
    invoice_number VARCHAR(50) UNIQUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- 6. ATTENDANCE TABLE
-- =============================================
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    check_in DATETIME NOT NULL,
    check_out DATETIME DEFAULT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- 7. CLASSES TABLE
-- =============================================
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    description TEXT,
    trainer_id INT DEFAULT NULL,
    day_of_week ENUM('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_capacity INT DEFAULT 30,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- 8. CLASS ENROLLMENTS TABLE
-- =============================================
CREATE TABLE class_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    member_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (class_id, member_id)
) ENGINE=InnoDB;

-- =============================================
-- 9. NOTIFICATIONS TABLE
-- =============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('payment','expiry','class','general') DEFAULT 'general',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- INSERT DEFAULT DATA
-- =============================================

-- Default Admin User (password: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Default Membership Plans
INSERT INTO plans (plan_name, duration_months, price, description, features) VALUES
('Basic', 1, 800.00, 'Basic monthly membership', 'Gym access,Locker facility'),
('Standard', 3, 2000.00, 'Standard quarterly membership', 'Gym access,Locker facility,1 Group class,Diet consultation'),
('Premium', 12, 7000.00, 'Premium yearly membership', 'Unlimited gym access,Locker facility,All group classes,Personal trainer,Diet plan,Supplements discount');

-- Sample Trainers
INSERT INTO users (username, email, password, role) VALUES
('trainer_raj', 'raj@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer'),
('trainer_priya', 'priya@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer');

INSERT INTO trainers (user_id, first_name, last_name, phone, email, specialization, experience) VALUES
(2, 'Raj', 'Kumar', '9876543210', 'raj@gym.com', 'Weight Training', 5),
(3, 'Priya', 'Sharma', '9876543211', 'priya@gym.com', 'Yoga & Cardio', 3);

-- Sample Members
INSERT INTO users (username, email, password, role) VALUES
('member_amit', 'amit@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('member_neha', 'neha@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('member_vikram', 'vikram@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member');

INSERT INTO members (user_id, first_name, last_name, phone, email, address, age, gender, weight, height, join_date, plan_id, trainer_id, membership_start, membership_end, status) VALUES
(4, 'Amit', 'Patel', '9988776655', 'amit@gmail.com', '123 MG Road, Mumbai', 28, 'male', 75.5, 175.0, '2025-01-15', 2, 1, '2025-01-15', '2025-04-15', 'active'),
(5, 'Neha', 'Gupta', '9988776656', 'neha@gmail.com', '456 Park Street, Kolkata', 24, 'female', 58.0, 162.0, '2025-02-01', 3, 2, '2025-02-01', '2026-02-01', 'active'),
(6, 'Vikram', 'Singh', '9988776657', 'vikram@gmail.com', '789 Nehru Place, Delhi', 32, 'male', 82.0, 180.0, '2024-12-01', 1, 1, '2024-12-01', '2025-01-01', 'expired');

-- Sample Payments
INSERT INTO payments (member_id, plan_id, amount, payment_date, payment_mode, status, invoice_number) VALUES
(1, 2, 2000.00, '2025-01-15', 'upi', 'paid', 'INV-20250115-001'),
(2, 3, 7000.00, '2025-02-01', 'card', 'paid', 'INV-20250201-001'),
(3, 1, 800.00, '2024-12-01', 'cash', 'paid', 'INV-20241201-001');

-- Sample Classes
INSERT INTO classes (class_name, description, trainer_id, day_of_week, start_time, end_time, max_capacity) VALUES
('Morning Yoga', 'Relaxing yoga session to start your day', 2, 'monday', '06:00:00', '07:00:00', 20),
('Zumba Fitness', 'High energy dance workout', 2, 'wednesday', '18:00:00', '19:00:00', 25),
('CrossFit Training', 'Intense cross-functional training', 1, 'tuesday', '07:00:00', '08:00:00', 15),
('Cardio Blast', 'Cardio focused workout session', 1, 'thursday', '17:00:00', '18:00:00', 20);

-- Sample Attendance
INSERT INTO attendance (member_id, check_in, check_out, date) VALUES
(1, '2025-03-10 06:30:00', '2025-03-10 08:00:00', '2025-03-10'),
(2, '2025-03-10 07:00:00', '2025-03-10 08:30:00', '2025-03-10'),
(1, '2025-03-11 06:45:00', NULL, '2025-03-11');
