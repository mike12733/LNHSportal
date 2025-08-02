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

// Get user's request statistics
$stats = $documentRequest->getRequestStats($_SESSION['user_id']);
$recent_requests = $documentRequest->getUserRequests($_SESSION['user_id'], 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Dashboard</title>
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

            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Documents
            </div>

            <li class="nav-item">
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
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <a href="request-document.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> New Request
                        </a>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Total Requests Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Requests</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Processing Requests Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Processing</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['processing']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ready for Pickup Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Ready for Pickup</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['ready_for_pickup']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Recent Requests -->
                        <div class="col-lg-8 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Requests</h6>
                                    <a href="my-requests.php" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_requests)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-file-alt fa-3x text-gray-300 mb-3"></i>
                                            <p class="text-gray-500">No requests found.</p>
                                            <a href="request-document.php" class="btn btn-primary">Make Your First Request</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Document</th>
                                                        <th>Status</th>
                                                        <th>Date Requested</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_requests as $request): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($request['document_name']); ?></td>
                                                            <td>
                                                                <span class="badge badge-<?php echo str_replace('_', '-', $request['status']); ?>">
                                                                    <?php echo ucwords(str_replace('_', ' ', $request['status'])); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                                            <td>
                                                                <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="col-lg-4 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="request-document.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-plus text-primary"></i>
                                            <span class="ml-2">Request New Document</span>
                                        </a>
                                        <a href="track-request.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-search text-info"></i>
                                            <span class="ml-2">Track Request Status</span>
                                        </a>
                                        <a href="my-requests.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-list text-success"></i>
                                            <span class="ml-2">View All Requests</span>
                                        </a>
                                        <a href="profile.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-user text-warning"></i>
                                            <span class="ml-2">Update Profile</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Types Info -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Available Documents</h6>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">
                                        • Certificate of Enrollment<br>
                                        • Good Moral Certificate<br>
                                        • Transcript of Records<br>
                                        • Diploma Copy<br>
                                        • Certificate of Graduation<br><br>
                                        <strong>Processing Time:</strong> 3-10 business days<br>
                                        <strong>Requirements:</strong> Valid ID, Student ID
                                    </small>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle the side navigation
        (function($) {
            "use strict";
            
            // Toggle sidebar
            $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
                $("body").toggleClass("sidebar-toggled");
                $(".sidebar").toggleClass("toggled");
                if ($(".sidebar").hasClass("toggled")) {
                    $('.sidebar .collapse').collapse('hide');
                };
            });

            // Close any open menu accordions when window is resized below 768px
            $(window).resize(function() {
                if ($(window).width() < 768) {
                    $('.sidebar .collapse').collapse('hide');
                };
                
                // Toggle the side navigation when window is resized below 480px
                if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
                    $("body").addClass("sidebar-toggled");
                    $(".sidebar").addClass("toggled");
                    $('.sidebar .collapse').collapse('hide');
                };
            });
        })(jQuery);
    </script>
</body>
</html>