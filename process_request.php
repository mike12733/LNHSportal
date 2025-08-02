<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document_type_id = (int)$_POST['document_type_id'];
    $purpose = sanitize($_POST['purpose']);
    $preferred_release_date = $_POST['preferred_release_date'];
    $requirements_file = null;
    
    // Validate input
    if (empty($document_type_id) || empty($purpose) || empty($preferred_release_date)) {
        $_SESSION['error'] = 'All required fields must be filled';
        header('Location: request_document.php');
        exit();
    }
    
    // Validate document type exists
    $sql = "SELECT * FROM document_types WHERE id = ? AND is_active = TRUE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$document_type_id]);
    $documentType = $stmt->fetch();
    
    if (!$documentType) {
        $_SESSION['error'] = 'Invalid document type selected';
        header('Location: request_document.php');
        exit();
    }
    
    // Validate release date
    $selectedDate = new DateTime($preferred_release_date);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($selectedDate <= $today) {
        $_SESSION['error'] = 'Preferred release date must be at least 1 day from today';
        header('Location: request_document.php');
        exit();
    }
    
    // Handle file upload if provided
    if (isset($_FILES['requirements_file']) && $_FILES['requirements_file']['error'] === UPLOAD_ERR_OK) {
        $fileValidation = validateFileUpload($_FILES['requirements_file']);
        
        if ($fileValidation === true) {
            $uploadedFile = uploadFile($_FILES['requirements_file'], 'requirements');
            if ($uploadedFile) {
                $requirements_file = $uploadedFile;
            } else {
                $_SESSION['error'] = 'Failed to upload file. Please try again.';
                header('Location: request_document.php');
                exit();
            }
        } else {
            $_SESSION['error'] = $fileValidation;
            header('Location: request_document.php');
            exit();
        }
    }
    
    try {
        // Insert the request
        $sql = "INSERT INTO document_requests (user_id, document_type_id, purpose, preferred_release_date, requirements_file, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_SESSION['user_id'],
            $document_type_id,
            $purpose,
            $preferred_release_date,
            $requirements_file
        ]);
        
        $requestId = $pdo->lastInsertId();
        
        // Create notification for user
        createNotification(
            $pdo, 
            $_SESSION['user_id'], 
            'Request Submitted Successfully', 
            "Your request for {$documentType['name']} has been submitted and is now pending review.", 
            'success'
        );
        
        // Create notification for admin (you can modify this to notify specific admin users)
        $adminUsers = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll();
        foreach ($adminUsers as $admin) {
            createNotification(
                $pdo,
                $admin['id'],
                'New Document Request',
                "New request for {$documentType['name']} from {$_SESSION['user_name']}",
                'info'
            );
        }
        
        // Send email notification (placeholder)
        $userData = getUserData($pdo, $_SESSION['user_id']);
        $emailSubject = "Document Request Submitted - LNHS Portal";
        $emailMessage = "
            Dear {$_SESSION['user_name']},
            
            Your document request has been successfully submitted.
            
            Request Details:
            - Document: {$documentType['name']}
            - Purpose: {$purpose}
            - Preferred Release Date: " . formatDate($preferred_release_date) . "
            - Fee: ₱" . number_format($documentType['fee'], 2) . "
            
            Your request is now pending review. You will be notified once the status changes.
            
            Thank you for using LNHS Documents Request Portal.
        ";
        
        sendEmail($userData['email'], $emailSubject, $emailMessage);
        
        $_SESSION['success'] = "Your request for {$documentType['name']} has been submitted successfully! Request ID: #{$requestId}";
        header('Location: my_requests.php');
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to submit request. Please try again.';
        header('Location: request_document.php');
        exit();
    }
} else {
    header('Location: request_document.php');
    exit();
}
?>