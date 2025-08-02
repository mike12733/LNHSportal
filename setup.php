<?php
/**
 * LNHS Documents Request Portal - Setup Script
 * Run this file once to set up the system
 */

require_once 'config/database.php';

$setup_messages = [];
$setup_errors = [];

// Function to create directory if it doesn't exist
function createDirectory($path) {
    if (!file_exists($path)) {
        if (mkdir($path, 0755, true)) {
            return "Created directory: $path";
        } else {
            return "Failed to create directory: $path";
        }
    }
    return "Directory already exists: $path";
}

// Check if setup has already been run
if (file_exists('setup_complete.txt')) {
    die('<h1>Setup Already Complete</h1><p>The setup has already been run. Delete setup_complete.txt to run setup again.</p>');
}

try {
    // Test database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        $setup_messages[] = "✓ Database connection successful";
        
        // Check if tables exist
        $tables_to_check = ['users', 'document_types', 'document_requests', 'request_status_history', 'notifications', 'admin_logs'];
        $existing_tables = [];
        
        foreach ($tables_to_check as $table) {
            $stmt = $conn->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() > 0) {
                $existing_tables[] = $table;
            }
        }
        
        if (count($existing_tables) === count($tables_to_check)) {
            $setup_messages[] = "✓ All required database tables exist";
        } else {
            $setup_errors[] = "✗ Missing database tables. Please import database/schema.sql";
        }
        
        // Update admin password to 'password' (hashed)
        $admin_password = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'admin@lnhs.edu.ph'");
        if ($stmt->execute([$admin_password])) {
            $setup_messages[] = "✓ Admin password updated";
        } else {
            $setup_errors[] = "✗ Failed to update admin password";
        }
        
    } else {
        $setup_errors[] = "✗ Database connection failed";
    }
} catch (Exception $e) {
    $setup_errors[] = "✗ Database error: " . $e->getMessage();
}

// Create required directories
$directories = [
    'uploads',
    'uploads/id',
    'uploads/requirements'
];

foreach ($directories as $dir) {
    $result = createDirectory($dir);
    if (strpos($result, 'Failed') !== false) {
        $setup_errors[] = "✗ $result";
    } else {
        $setup_messages[] = "✓ $result";
    }
}

// Check file permissions
$upload_dir = 'uploads';
if (is_writable($upload_dir)) {
    $setup_messages[] = "✓ Upload directory is writable";
} else {
    $setup_errors[] = "✗ Upload directory is not writable. Run: chmod 755 uploads/";
}

// Check PHP extensions
$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        $setup_messages[] = "✓ PHP extension '$ext' is loaded";
    } else {
        $setup_errors[] = "✗ PHP extension '$ext' is not loaded";
    }
}

// Check PHP settings
$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$setup_messages[] = "ℹ PHP upload_max_filesize: $upload_max";
$setup_messages[] = "ℹ PHP post_max_size: $post_max";

if (empty($setup_errors)) {
    // Create setup complete file
    file_put_contents('setup_complete.txt', 'Setup completed on ' . date('Y-m-d H:i:s'));
    $setup_complete = true;
} else {
    $setup_complete = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LNHS Portal - Setup</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(180deg, #4e73df 10%, #224abe 100%); min-height: 100vh; }
        .setup-container { max-width: 800px; margin: 50px auto; }
        .setup-card { border: none; border-radius: 15px; box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175); }
    </style>
</head>
<body>
    <div class="container setup-container">
        <div class="card setup-card">
            <div class="card-header bg-primary text-white text-center py-4">
                <h1><i class="fas fa-graduation-cap"></i> LNHS Documents Request Portal</h1>
                <h3>System Setup</h3>
            </div>
            <div class="card-body p-5">
                <?php if ($setup_complete): ?>
                    <div class="alert alert-success">
                        <h4><i class="fas fa-check-circle"></i> Setup Complete!</h4>
                        <p>The LNHS Documents Request Portal has been successfully set up.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <h4><i class="fas fa-exclamation-triangle"></i> Setup Issues Found</h4>
                        <p>Please resolve the issues below before using the system.</p>
                    </div>
                <?php endif; ?>

                <h5>Setup Results:</h5>
                
                <?php foreach ($setup_messages as $message): ?>
                    <div class="alert alert-info py-2">
                        <?php echo $message; ?>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($setup_errors as $error): ?>
                    <div class="alert alert-danger py-2">
                        <?php echo $error; ?>
                    </div>
                <?php endforeach; ?>

                <?php if ($setup_complete): ?>
                    <hr>
                    <h5>Default Login Credentials:</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Administrator</h6>
                                    <p class="card-text">
                                        <strong>Email:</strong> admin@lnhs.edu.ph<br>
                                        <strong>Password:</strong> password
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Students/Alumni</h6>
                                    <p class="card-text">
                                        Register new accounts using the<br>
                                        "Create an Account" link on the login page.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Next Steps:</h5>
                    <ol>
                        <li>Delete this setup.php file for security</li>
                        <li>Change the default admin password</li>
                        <li>Configure email settings in config/database.php</li>
                        <li>Test the system functionality</li>
                        <li>Set up SSL certificate for production</li>
                    </ol>

                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Go to Login Page
                        </a>
                    </div>
                <?php else: ?>
                    <hr>
                    <h5>Setup Instructions:</h5>
                    <ol>
                        <li>Import the database schema: <code>mysql -u username -p lnhs_portal &lt; database/schema.sql</code></li>
                        <li>Update database credentials in <code>config/database.php</code></li>
                        <li>Ensure upload directories have write permissions: <code>chmod 755 uploads/</code></li>
                        <li>Install required PHP extensions if missing</li>
                        <li>Refresh this page to re-run setup</li>
                    </ol>

                    <div class="text-center mt-4">
                        <a href="setup.php" class="btn btn-warning btn-lg">
                            <i class="fas fa-redo"></i> Re-run Setup
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center text-muted">
                <small>LNHS Documents Request Portal &copy; <?php echo date('Y'); ?></small>
            </div>
        </div>
    </div>
</body>
</html>