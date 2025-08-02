<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']);
    
    // Validate input
    if (empty($email) || empty($password) || empty($role)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: ../index.php');
        exit();
    }
    
    try {
        // Check if user exists
        $sql = "SELECT * FROM users WHERE email = ? AND role = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Create login notification
            createNotification($pdo, $user['id'], 'Login Successful', 'Welcome back to LNHS Documents Request Portal!', 'success');
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../dashboard.php');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Invalid email, password, or role';
            header('Location: ../index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Login failed. Please try again.';
        header('Location: ../index.php');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>