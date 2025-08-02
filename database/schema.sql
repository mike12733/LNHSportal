-- LNHS Documents Request Portal Database Schema
CREATE DATABASE IF NOT EXISTS lnhs_portal;
USE lnhs_portal;

-- Users table (students, alumni, admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    user_type ENUM('student', 'alumni', 'admin') NOT NULL,
    phone_number VARCHAR(15),
    address TEXT,
    year_graduated YEAR NULL,
    course VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Document types table
CREATE TABLE document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_name VARCHAR(100) NOT NULL,
    description TEXT,
    processing_days INT DEFAULT 3,
    fee DECIMAL(8,2) DEFAULT 0.00,
    requirements TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Document requests table
CREATE TABLE document_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_type_id INT NOT NULL,
    purpose TEXT NOT NULL,
    preferred_release_date DATE,
    quantity INT DEFAULT 1,
    status ENUM('pending', 'processing', 'approved', 'denied', 'ready_for_pickup', 'completed') DEFAULT 'pending',
    admin_notes TEXT,
    uploaded_id_path VARCHAR(255),
    uploaded_requirements_path VARCHAR(255),
    processed_by INT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_type_id) REFERENCES document_types(id),
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

-- Request status history table
CREATE TABLE request_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES document_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_id INT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('email', 'sms', 'portal') DEFAULT 'portal',
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES document_requests(id) ON DELETE SET NULL
);

-- Admin logs table
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_table VARCHAR(50),
    target_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Insert default document types
INSERT INTO document_types (document_name, description, processing_days, fee, requirements) VALUES
('Certificate of Enrollment', 'Official certificate confirming student enrollment', 3, 50.00, 'Valid ID, Student ID'),
('Good Moral Certificate', 'Certificate of good moral character', 5, 75.00, 'Valid ID, Student ID, Clearance Form'),
('Transcript of Records', 'Official academic transcript', 7, 100.00, 'Valid ID, Student ID, Request Form'),
('Diploma Copy', 'Certified true copy of diploma', 10, 150.00, 'Valid ID, Original Diploma, Request Form'),
('Certificate of Graduation', 'Official graduation certificate', 5, 80.00, 'Valid ID, Student ID');

-- Insert default admin user
INSERT INTO users (student_id, email, password, first_name, last_name, user_type, status) VALUES
('ADMIN001', 'admin@lnhs.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active');