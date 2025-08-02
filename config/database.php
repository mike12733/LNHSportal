<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lnhs_portal');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
function createTables($pdo) {
    // Users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'alumni', 'admin') NOT NULL,
        student_id VARCHAR(20),
        year_graduated INT,
        contact_number VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Document types table
    $sql = "CREATE TABLE IF NOT EXISTS document_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        processing_days INT DEFAULT 3,
        fee DECIMAL(10,2) DEFAULT 0.00,
        is_active BOOLEAN DEFAULT TRUE
    )";
    $pdo->exec($sql);

    // Document requests table
    $sql = "CREATE TABLE IF NOT EXISTS document_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        document_type_id INT NOT NULL,
        purpose TEXT NOT NULL,
        preferred_release_date DATE,
        status ENUM('pending', 'processing', 'approved', 'denied', 'ready') DEFAULT 'pending',
        admin_notes TEXT,
        requirements_file VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (document_type_id) REFERENCES document_types(id)
    )";
    $pdo->exec($sql);

    // Notifications table
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Insert default document types
    $sql = "INSERT IGNORE INTO document_types (name, description, processing_days, fee) VALUES 
        ('Certificate of Enrollment', 'Official certificate confirming student enrollment', 2, 50.00),
        ('Good Moral Certificate', 'Certificate of good moral character', 3, 75.00),
        ('Transcript of Records', 'Complete academic record', 5, 150.00),
        ('Diploma', 'Graduation diploma', 7, 200.00),
        ('Certificate of Completion', 'Certificate for completed courses', 2, 50.00)";
    $pdo->exec($sql);

    // Insert default admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT IGNORE INTO users (first_name, last_name, email, password, role) VALUES 
        ('Admin', 'User', 'admin@lnhs.edu.ph', '$adminPassword', 'admin')";
    $pdo->exec($sql);
}

// Initialize tables
createTables($pdo);
?>