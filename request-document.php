<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/DocumentRequest.php';

$user = new User();
$documentRequest = new DocumentRequest();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Redirect admin to admin dashboard
if ($user->isAdmin()) {
    header('Location: admin/dashboard.php');
    exit();
}

$error = '';
$success = '';
$document_types = $documentRequest->getDocumentTypes();

if ($_POST) {
    if (isset($_POST['submit_request'])) {
        $data = [
            'user_id' => $_SESSION['user_id'],
            'document_type_id' => $_POST['document_type_id'],
            'purpose' => trim($_POST['purpose']),
            'preferred_release_date' => $_POST['preferred_release_date'],
            'quantity' => $_POST['quantity'] ?? 1
        ];
        
        // Validate required fields
        if (empty($data['document_type_id']) || empty($data['purpose'])) {
            $error = 'Please fill in all required fields.';
        } else {
            // Handle file uploads
            $upload_errors = [];
            
            // Upload ID file
            if (isset($_FILES['id_file']) && $_FILES['id_file']['error'] == 0) {
                $id_upload = $documentRequest->uploadFile($_FILES['id_file'], 'id');
                if ($id_upload['success']) {
                    $data['uploaded_id_path'] = $id_upload['filepath'];
                } else {
                    $upload_errors[] = 'ID File: ' . $id_upload['message'];
                }
            }
            
            // Upload requirements file
            if (isset($_FILES['requirements_file']) && $_FILES['requirements_file']['error'] == 0) {
                $req_upload = $documentRequest->uploadFile($_FILES['requirements_file'], 'requirements');
                if ($req_upload['success']) {
                    $data['uploaded_requirements_path'] = $req_upload['filepath'];
                } else {
                    $upload_errors[] = 'Requirements File: ' . $req_upload['message'];
                }
            }
            
            if (!empty($upload_errors)) {
                $error = implode('<br>', $upload_errors);
            } else {
                $result = $documentRequest->createRequest($data);
                if ($result['success']) {
                    $success = $result['message'];
                    // Clear form data
                    $_POST = [];
                } else {
                    $error = $result['message'];
                }
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
    <title><?php echo SITE_NAME; ?> - Request Document</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="sidebar-brand-text mx-3">LNHS Portal</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Documents
            </div>

            <li class="nav-item active">
                <a class="nav-link" href="request-document.php">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Request Document</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="my-requests.php">
                    <i class="fas fa-fw fa-list"></i>
                    <span>My Requests</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="track-request.php">
                    <i class="fas fa-fw fa-search"></i>
                    <span>Track Request</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Account
            </div>

            <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['full_name']; ?></span>
                                <i class="fas fa-user-circle fa-2x text-gray-600"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Request Document</h1>
                        <a href="dashboard.php" class="btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Dashboard
                        </a>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Document Request Form</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($success): ?>
                                        <div class="alert alert-success" role="alert">
                                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                            <br><br>
                                            <a href="my-requests.php" class="btn btn-success btn-sm">View My Requests</a>
                                            <a href="request-document.php" class="btn btn-primary btn-sm">Make Another Request</a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="form-group mb-3">
                                            <label for="document_type_id" class="form-label">Document Type <span class="text-danger">*</span></label>
                                            <select class="form-control" name="document_type_id" id="document_type_id" required>
                                                <option value="">Select Document Type</option>
                                                <?php foreach ($document_types as $type): ?>
                                                    <option value="<?php echo $type['id']; ?>" 
                                                            data-fee="<?php echo $type['fee']; ?>"
                                                            data-days="<?php echo $type['processing_days']; ?>"
                                                            data-requirements="<?php echo htmlspecialchars($type['requirements']); ?>"
                                                            <?php echo (isset($_POST['document_type_id']) && $_POST['document_type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($type['document_name']); ?> - ₱<?php echo number_format($type['fee'], 2); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div id="document-info" class="alert alert-info" style="display: none;">
                                            <h6><i class="fas fa-info-circle"></i> Document Information</h6>
                                            <div id="document-details"></div>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label for="purpose" class="form-label">Purpose of Request <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="purpose" id="purpose" rows="3" 
                                                      placeholder="Please specify the purpose for requesting this document..." required><?php echo isset($_POST['purpose']) ? htmlspecialchars($_POST['purpose']) : ''; ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="quantity" class="form-label">Quantity</label>
                                                    <input type="number" class="form-control" name="quantity" id="quantity" 
                                                           min="1" max="10" value="<?php echo isset($_POST['quantity']) ? $_POST['quantity'] : '1'; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="preferred_release_date" class="form-label">Preferred Release Date</label>
                                                    <input type="date" class="form-control" name="preferred_release_date" 
                                                           id="preferred_release_date" min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>"
                                                           value="<?php echo isset($_POST['preferred_release_date']) ? $_POST['preferred_release_date'] : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <h6 class="text-primary mb-3"><i class="fas fa-upload"></i> File Uploads</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="id_file" class="form-label">Valid ID <span class="text-muted">(Optional)</span></label>
                                                    <input type="file" class="form-control" name="id_file" id="id_file" 
                                                           accept=".jpg,.jpeg,.png,.pdf">
                                                    <small class="form-text text-muted">Upload a clear photo of your valid ID (JPG, PNG, PDF - Max 5MB)</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="requirements_file" class="form-label">Additional Requirements <span class="text-muted">(Optional)</span></label>
                                                    <input type="file" class="form-control" name="requirements_file" id="requirements_file" 
                                                           accept=".jpg,.jpeg,.png,.pdf">
                                                    <small class="form-text text-muted">Upload additional required documents (JPG, PNG, PDF - Max 5MB)</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="form-group mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="terms_agreement" required>
                                                <label class="form-check-label" for="terms_agreement">
                                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> 
                                                    and certify that all information provided is accurate and complete. <span class="text-danger">*</span>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <button type="submit" name="submit_request" class="btn btn-primary btn-lg">
                                                <i class="fas fa-paper-plane"></i> Submit Request
                                            </button>
                                            <a href="dashboard.php" class="btn btn-secondary btn-lg ml-2">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Important Notes</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle"></i> Processing Time</h6>
                                        <p class="mb-0">Documents typically take 3-10 business days to process. Rush processing may be available for additional fees.</p>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-file-upload"></i> File Requirements</h6>
                                        <ul class="mb-0">
                                            <li>Accepted formats: JPG, PNG, PDF</li>
                                            <li>Maximum file size: 5MB</li>
                                            <li>Ensure files are clear and readable</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="alert alert-success">
                                        <h6><i class="fas fa-bell"></i> Notifications</h6>
                                        <p class="mb-0">You will receive email notifications about your request status updates.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Need Help?</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Contact Information:</strong></p>
                                    <p class="mb-1"><i class="fas fa-phone"></i> (123) 456-7890</p>
                                    <p class="mb-1"><i class="fas fa-envelope"></i> registrar@lnhs.edu.ph</p>
                                    <p class="mb-1"><i class="fas fa-clock"></i> Mon-Fri: 8:00 AM - 5:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; LNHS Documents Request Portal <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Document Request Terms and Conditions</h6>
                    <ol>
                        <li>All information provided must be accurate and complete.</li>
                        <li>Processing fees are non-refundable once processing begins.</li>
                        <li>Documents must be claimed within 30 days of completion notification.</li>
                        <li>Valid identification is required for document pickup.</li>
                        <li>LNHS reserves the right to verify all submitted information.</li>
                        <li>False information may result in request denial and account suspension.</li>
                        <li>Processing times are estimates and may vary based on workload.</li>
                        <li>Rush processing requests are subject to availability and additional fees.</li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show document information when document type is selected
            $('#document_type_id').change(function() {
                const selectedOption = $(this).find('option:selected');
                if (selectedOption.val()) {
                    const fee = selectedOption.data('fee');
                    const days = selectedOption.data('days');
                    const requirements = selectedOption.data('requirements');
                    
                    const infoHtml = `
                        <p><strong>Processing Fee:</strong> ₱${parseFloat(fee).toFixed(2)}</p>
                        <p><strong>Processing Time:</strong> ${days} business days</p>
                        <p><strong>Requirements:</strong> ${requirements}</p>
                    `;
                    
                    $('#document-details').html(infoHtml);
                    $('#document-info').show();
                } else {
                    $('#document-info').hide();
                }
            });
            
            // Trigger change event if option is already selected
            $('#document_type_id').trigger('change');
            
            // Toggle sidebar
            $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
                $("body").toggleClass("sidebar-toggled");
                $(".sidebar").toggleClass("toggled");
                if ($(".sidebar").hasClass("toggled")) {
                    $('.sidebar .collapse').collapse('hide');
                }
            });
        });
    </script>
</body>
</html>