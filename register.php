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
    if (isset($_POST['register'])) {
        $data = [
            'student_id' => trim($_POST['student_id']),
            'email' => trim($_POST['email']),
            'password' => $_POST['password'],
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'middle_name' => trim($_POST['middle_name']),
            'user_type' => $_POST['user_type'],
            'phone_number' => trim($_POST['phone_number']),
            'address' => trim($_POST['address']),
            'course' => trim($_POST['course'])
        ];
        
        // Add year_graduated for alumni
        if ($_POST['user_type'] === 'alumni') {
            $data['year_graduated'] = $_POST['year_graduated'];
        }
        
        // Validate required fields
        if (empty($data['student_id']) || empty($data['email']) || empty($data['password']) || 
            empty($data['first_name']) || empty($data['last_name']) || empty($data['user_type'])) {
            $error = 'Please fill in all required fields.';
        } elseif (strlen($data['password']) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($_POST['password'] !== $_POST['confirm_password']) {
            $error = 'Passwords do not match.';
        } else {
            $result = $user->register($data);
            if ($result['success']) {
                $success = $result['message'] . ' You can now login.';
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
    <title><?php echo SITE_NAME; ?> - Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-lg-5 d-none d-lg-block bg-register-image">
                        <div class="login-image-content">
                            <div class="school-logo">
                                <i class="fas fa-user-plus fa-4x text-white mb-3"></i>
                                <h2 class="text-white font-weight-bold">Join LNHS</h2>
                                <p class="text-white-50">Documents Request Portal</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                            </div>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                    <br><a href="index.php" class="btn btn-success btn-sm mt-2">Login Now</a>
                                </div>
                            <?php endif; ?>
                            
                            <form class="user" method="POST" id="registerForm">
                                <div class="form-group row mb-3">
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-control-user" 
                                               name="first_name" placeholder="First Name" 
                                               value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-control-user" 
                                               name="last_name" placeholder="Last Name" 
                                               value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <input type="text" class="form-control form-control-user" 
                                           name="middle_name" placeholder="Middle Name (Optional)" 
                                           value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-control-user" 
                                               name="student_id" placeholder="Student ID" 
                                               value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <select class="form-control form-control-user" name="user_type" id="user_type" required>
                                            <option value="">Select Type</option>
                                            <option value="student" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'student') ? 'selected' : ''; ?>>Current Student</option>
                                            <option value="alumni" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'alumni') ? 'selected' : ''; ?>>Alumni</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <input type="email" class="form-control form-control-user" 
                                           name="email" placeholder="Email Address" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control form-control-user" 
                                               name="password" placeholder="Password" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control form-control-user" 
                                               name="confirm_password" placeholder="Repeat Password" required>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <input type="tel" class="form-control form-control-user" 
                                           name="phone_number" placeholder="Phone Number (Optional)" 
                                           value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <textarea class="form-control" name="address" placeholder="Address (Optional)" rows="2"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <input type="text" class="form-control form-control-user" 
                                           name="course" placeholder="Course/Program" 
                                           value="<?php echo isset($_POST['course']) ? htmlspecialchars($_POST['course']) : ''; ?>">
                                </div>
                                
                                <div class="form-group mb-3" id="year_graduated_group" style="display: none;">
                                    <input type="number" class="form-control form-control-user" 
                                           name="year_graduated" placeholder="Year Graduated" 
                                           min="1990" max="<?php echo date('Y'); ?>"
                                           value="<?php echo isset($_POST['year_graduated']) ? $_POST['year_graduated'] : ''; ?>">
                                </div>
                                
                                <button type="submit" name="register" class="btn btn-primary btn-user btn-block">
                                    <i class="fas fa-user-plus"></i> Register Account
                                </button>
                            </form>
                            
                            <hr>
                            <div class="text-center">
                                <a class="small" href="index.php">Already have an account? Login!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            const yearGraduatedGroup = document.getElementById('year_graduated_group');
            if (this.value === 'alumni') {
                yearGraduatedGroup.style.display = 'block';
                yearGraduatedGroup.querySelector('input').required = true;
            } else {
                yearGraduatedGroup.style.display = 'none';
                yearGraduatedGroup.querySelector('input').required = false;
            }
        });
        
        // Trigger change event on page load if alumni is selected
        if (document.getElementById('user_type').value === 'alumni') {
            document.getElementById('user_type').dispatchEvent(new Event('change'));
        }
    </script>
</body>
</html>