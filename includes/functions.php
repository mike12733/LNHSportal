<?php
// Utility functions for LNHS Documents Request Portal

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

// Get status badge
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'processing' => '<span class="badge bg-info">Processing</span>',
        'approved' => '<span class="badge bg-success">Approved</span>',
        'denied' => '<span class="badge bg-danger">Denied</span>',
        'ready' => '<span class="badge bg-primary">Ready for Pickup</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
}

// Get notification icon
function getNotificationIcon($type) {
    $icons = [
        'info' => 'fas fa-info-circle text-info',
        'success' => 'fas fa-check-circle text-success',
        'warning' => 'fas fa-exclamation-triangle text-warning',
        'error' => 'fas fa-times-circle text-danger'
    ];
    return $icons[$type] ?? 'fas fa-bell text-secondary';
}

// Send email notification (placeholder function)
function sendEmail($to, $subject, $message) {
    // In a real application, you would use PHPMailer or similar
    // For now, we'll just log the email
    error_log("Email to: $to, Subject: $subject, Message: $message");
    return true;
}

// Create notification
function createNotification($pdo, $userId, $title, $message, $type = 'info') {
    $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$userId, $title, $message, $type]);
}

// Get user notifications
function getUserNotifications($pdo, $userId, $limit = 10) {
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

// Mark notification as read
function markNotificationAsRead($pdo, $notificationId) {
    $sql = "UPDATE notifications SET is_read = TRUE WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$notificationId]);
}

// Get unread notification count
function getUnreadNotificationCount($pdo, $userId) {
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result['count'];
}

// Upload file
function uploadFile($file, $destination) {
    $uploadDir = 'uploads/' . $destination . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    return false;
}

// Validate file upload
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'], $maxSize = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'File upload error';
    }
    
    if ($file['size'] > $maxSize) {
        return 'File size too large (max 5MB)';
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedTypes)) {
        return 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes);
    }
    
    return true;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit();
    }
}

// Require admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../dashboard.php');
        exit();
    }
}

// Get user data
function getUserData($pdo, $userId) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

// Get document types
function getDocumentTypes($pdo) {
    $sql = "SELECT * FROM document_types WHERE is_active = TRUE ORDER BY name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get user requests
function getUserRequests($pdo, $userId) {
    $sql = "SELECT dr.*, dt.name as document_name, dt.fee 
            FROM document_requests dr 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            WHERE dr.user_id = ? 
            ORDER BY dr.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Get all requests (for admin)
function getAllRequests($pdo, $status = null) {
    $sql = "SELECT dr.*, dt.name as document_name, dt.fee, 
            u.first_name, u.last_name, u.email, u.student_id 
            FROM document_requests dr 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            JOIN users u ON dr.user_id = u.id";
    
    if ($status) {
        $sql .= " WHERE dr.status = ?";
        $sql .= " ORDER BY dr.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status]);
    } else {
        $sql .= " ORDER BY dr.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    
    return $stmt->fetchAll();
}

// Update request status
function updateRequestStatus($pdo, $requestId, $status, $notes = '') {
    $sql = "UPDATE document_requests SET status = ?, admin_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$status, $notes, $requestId]);
}

// Get request details
function getRequestDetails($pdo, $requestId) {
    $sql = "SELECT dr.*, dt.name as document_name, dt.fee, dt.processing_days,
            u.first_name, u.last_name, u.email, u.student_id, u.contact_number 
            FROM document_requests dr 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            JOIN users u ON dr.user_id = u.id 
            WHERE dr.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$requestId]);
    return $stmt->fetch();
}

// Calculate total requests by status
function getRequestStats($pdo) {
    $sql = "SELECT status, COUNT(*) as count FROM document_requests GROUP BY status";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    $stats = [
        'pending' => 0,
        'processing' => 0,
        'approved' => 0,
        'denied' => 0,
        'ready' => 0
    ];
    
    foreach ($results as $row) {
        $stats[$row['status']] = $row['count'];
    }
    
    return $stats;
}

// Export data to CSV
function exportToCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Write headers
        fputcsv($output, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
}
?>