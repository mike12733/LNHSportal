<?php
// LNHS Documents Request Portal - Setup Script
// This script helps configure the system for first-time use

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>LNHS Portal Setup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .setup-container { background: rgba(255,255,255,0.95); border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class='container'>
        <div class='row justify-content-center align-items-center min-vh-100'>
            <div class='col-md-8 col-lg-6'>
                <div class='setup-container p-5'>
                    <div class='text-center mb-4'>
                        <h2 class='fw-bold text-primary'>LNHS Portal Setup</h2>
                        <p class='text-muted'>System Configuration Check</p>
                    </div>";

// Check PHP version
$phpVersion = phpversion();
$phpVersionOk = version_compare($phpVersion, '7.4.0', '>=');

echo "<div class='mb-3'>
        <h6>PHP Version Check</h6>
        <div class='alert alert-" . ($phpVersionOk ? 'success' : 'danger') . "'>
            <strong>PHP Version:</strong> $phpVersion " . ($phpVersionOk ? '✅' : '❌') . "
        </div>
    </div>";

// Check required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'session'];
$extensionsOk = true;

echo "<div class='mb-3'>
        <h6>Required Extensions</h6>";

foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $extensionsOk = $extensionsOk && $loaded;
    echo "<div class='alert alert-" . ($loaded ? 'success' : 'danger') . "'>
            <strong>$ext:</strong> " . ($loaded ? 'Loaded ✅' : 'Not Found ❌') . "
        </div>";
}

echo "</div>";

// Check directory permissions
$directories = ['uploads', 'uploads/requirements'];
$permissionsOk = true;

echo "<div class='mb-3'>
        <h6>Directory Permissions</h6>";

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<div class='alert alert-success'>
                    <strong>$dir:</strong> Created ✅
                </div>";
        } else {
            echo "<div class='alert alert-danger'>
                    <strong>$dir:</strong> Failed to create ❌
                </div>";
            $permissionsOk = false;
        }
    } else {
        $writable = is_writable($dir);
        $permissionsOk = $permissionsOk && $writable;
        echo "<div class='alert alert-" . ($writable ? 'success' : 'danger') . "'>
                <strong>$dir:</strong> " . ($writable ? 'Writable ✅' : 'Not Writable ❌') . "
            </div>";
    }
}

echo "</div>";

// Check if config file exists
$configExists = file_exists('config/database.php');

echo "<div class='mb-3'>
        <h6>Configuration</h6>
        <div class='alert alert-" . ($configExists ? 'success' : 'warning') . "'>
            <strong>Database Config:</strong> " . ($configExists ? 'Found ✅' : 'Not Found ⚠️') . "
        </div>
    </div>";

// Overall status
$allOk = $phpVersionOk && $extensionsOk && $permissionsOk && $configExists;

echo "<div class='mb-4'>
        <h6>Overall Status</h6>
        <div class='alert alert-" . ($allOk ? 'success' : 'warning') . "'>
            <strong>System Status:</strong> " . ($allOk ? 'Ready to Use ✅' : 'Needs Configuration ⚠️') . "
        </div>
    </div>";

if ($allOk) {
    echo "<div class='text-center'>
            <a href='index.php' class='btn btn-primary btn-lg'>
                <i class='fas fa-rocket me-2'></i>Launch System
            </a>
        </div>";
} else {
    echo "<div class='alert alert-info'>
            <h6>Setup Instructions:</h6>
            <ol>
                <li>Ensure PHP 7.4+ is installed</li>
                <li>Install required PHP extensions</li>
                <li>Set proper directory permissions</li>
                <li>Configure database in config/database.php</li>
                <li>Create MySQL database 'lnhs_portal'</li>
            </ol>
        </div>";
}

echo "<div class='text-center mt-4'>
        <small class='text-muted'>
            <strong>Default Admin Account:</strong><br>
            Email: admin@lnhs.edu.ph<br>
            Password: admin123
        </small>
    </div>
</div>
</div>
</div>
</body>
</html>";
?>