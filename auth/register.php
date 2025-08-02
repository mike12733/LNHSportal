<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    $student_id = sanitize($_POST['student_id']);
    $contact_number = sanitize($_POST['contact_number']);
    $year_graduated = !empty($_POST['year_graduated']) ? (int)$_POST['year_graduated'] : null;
    $address = sanitize($_POST['address']);
    
    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['error'] = 'Required fields cannot be empty';
        header('Location: ../register.php');
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: ../register.php');
        exit();
    }
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long';
        header('Location: ../register.php');
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format';
        header('Location: ../register.php');
        exit();
    }
    
    if (!in_array($role, ['student', 'alumni'])) {
        $_SESSION['error'] = 'Invalid role selected';
        header('Location: ../register.php');
        exit();
    }
    
    try {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email address already registered';
            header('Location: ../register.php');
            exit();
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (first_name, last_name, email, password, role, student_id, contact_number, year_graduated, address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $first_name, 
            $last_name, 
            $email, 
            $hashed_password, 
            $role, 
            $student_id, 
            $contact_number, 
            $year_graduated, 
            $address
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // Create welcome notification
        createNotification($pdo, $userId, 'Welcome to LNHS!', 'Your account has been created successfully. You can now request documents online.', 'success');
        
        $_SESSION['success'] = 'Account created successfully! You can now login.';
        header('Location: ../index.php');
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        header('Location: ../register.php');
        exit();
    }
} else {
    header('Location: ../register.php');
    exit();
}
?>