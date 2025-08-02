<?php
require_once 'config/database.php';
require_once 'classes/User.php';

$user = new User();

// Redirect if already logged in
if ($user->isLoggedIn()) {
    if ($user->isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $result = $user->login($email, $password);
            if ($result['success']) {
                if ($_SESSION['user_type'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image">
                                <div class="login-image-content">
                                    <div class="school-logo">
                                        <i class="fas fa-graduation-cap fa-4x text-white mb-3"></i>
                                        <h2 class="text-white font-weight-bold">LNHS</h2>
                                        <p class="text-white-50">Documents Request Portal</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($success): ?>
                                        <div class="alert alert-success" role="alert">
                                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form class="user" method="POST">
                                        <div class="form-group mb-3">
                                            <input type="email" class="form-control form-control-user" 
                                                   name="email" placeholder="Enter Email Address..." 
                                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <input type="password" class="form-control form-control-user" 
                                                   name="password" placeholder="Password" required>
                                        </div>
                                        <button type="submit" name="login" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-sign-in-alt"></i> Login
                                        </button>
                                    </form>
                                    
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.php">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register.php">Create an Account!</a>
                                    </div>
                                    
                                    <hr>
                                    <div class="text-center">
                                        <small class="text-muted">
                                            <strong>Demo Accounts:</strong><br>
                                            Admin: admin@lnhs.edu.ph / password<br>
                                            Student: Register as new student
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>