<?php
require_once '../config/database.php';
require_once '../classes/User.php';
require_once '../classes/DocumentRequest.php';

$user = new User();
$documentRequest = new DocumentRequest();

// Check if user is logged in and is admin
if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header('Location: ../index.php');
    exit();
}

// Get statistics
$stats = $documentRequest->getRequestStats();
$recent_requests = $documentRequest->getAllRequests(null, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="sidebar-brand-text mx-3">LNHS Admin</div>
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
                Request Management
            </div>

            <li class="nav-item">
                <a class="nav-link" href="requests.php">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>All Requests</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseRequests"
                aria-expanded="true" aria-controls="collapseRequests">
                <i class="fas fa-fw fa-filter"></i>
                <span>Filter Requests</span>
            </a>
            <div id="collapseRequests" class="collapse" aria-labelledby="headingRequests" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="requests.php?status=pending">Pending</a>
                    <a class="collapse-item" href="requests.php?status=processing">Processing</a>
                    <a class="collapse-item" href="requests.php?status=approved">Approved</a>
                    <a class="collapse-item" href="requests.php?status=ready_for_pickup">Ready for Pickup</a>
                </div>
            </div>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                User Management
            </div>

            <li class="nav-item">
                <a class="nav-link" href="users.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Manage Users</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="document-types.php">
                    <i class="fas fa-fw fa-file-medical"></i>
                    <span>Document Types</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Reports & Analytics
            </div>

            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Reports</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="analytics.php">
                    <i class="fas fa-fw fa-chart-pie"></i>
                    <span>Analytics</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                System
            </div>

            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
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
                        <!-- Notifications Dropdown -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <span class="badge badge-danger badge-counter"><?php echo $stats['pending']; ?></span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Pending Requests
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="requests.php?status=pending">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500"><?php echo date('F j, Y'); ?></div>
                                        <span class="font-weight-bold"><?php echo $stats['pending']; ?> pending requests need attention</span>
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="requests.php">Show All Requests</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- User Information -->
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
                                <a class="dropdown-item" href="settings.php">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../logout.php">
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
                        <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
                        <div>
                            <a href="reports.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                                <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
                            </a>
                            <a href="requests.php" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                                <i class="fas fa-eye fa-sm text-white-50"></i> View All Requests
                            </a>
                        </div>
                    </div>

                    <!-- Content Row - Statistics Cards -->
                    <div class="row">
                        <!-- Total Requests Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 dashboard-card">
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
                            <div class="card border-left-warning shadow h-100 py-2 dashboard-card warning">
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
                            <div class="card border-left-info shadow h-100 py-2 dashboard-card">
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

                        <!-- Completed Requests Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 dashboard-card success">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Completed</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['completed']; ?></div>
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
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Actions:</div>
                                            <a class="dropdown-item" href="requests.php">View All</a>
                                            <a class="dropdown-item" href="reports.php">Export Data</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_requests)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-file-alt fa-3x text-gray-300 mb-3"></i>
                                            <p class="text-gray-500">No requests found.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Request ID</th>
                                                        <th>Student</th>
                                                        <th>Document</th>
                                                        <th>Status</th>
                                                        <th>Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_requests as $request): ?>
                                                        <tr>
                                                            <td>#<?php echo str_pad($request['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="mr-3">
                                                                        <i class="fas fa-user-circle fa-2x text-gray-300"></i>
                                                                    </div>
                                                                    <div>
                                                                        <div class="font-weight-bold"><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></div>
                                                                        <div class="small text-gray-500"><?php echo htmlspecialchars($request['student_id']); ?></div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($request['document_name']); ?></td>
                                                            <td>
                                                                <span class="badge badge-<?php echo str_replace('_', '-', $request['status']); ?>">
                                                                    <?php echo ucwords(str_replace('_', ' ', $request['status'])); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                                            <td>
                                                                <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <?php if ($request['status'] == 'pending'): ?>
                                                                    <a href="process-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-success">
                                                                        <i class="fas fa-cog"></i>
                                                                    </a>
                                                                <?php endif; ?>
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

                        <!-- Quick Actions & Stats -->
                        <div class="col-lg-4 mb-4">
                            <!-- Quick Actions -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="requests.php?status=pending" class="list-group-item list-group-item-action">
                                            <i class="fas fa-clock text-warning"></i>
                                            <span class="ml-2">Process Pending Requests</span>
                                            <span class="badge badge-warning float-right"><?php echo $stats['pending']; ?></span>
                                        </a>
                                        <a href="users.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-users text-info"></i>
                                            <span class="ml-2">Manage Users</span>
                                        </a>
                                        <a href="document-types.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-file-medical text-success"></i>
                                            <span class="ml-2">Document Types</span>
                                        </a>
                                        <a href="reports.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-chart-area text-primary"></i>
                                            <span class="ml-2">Generate Reports</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Distribution -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Request Status Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="small mb-1">Pending <span class="float-right"><?php echo $stats['pending']; ?></span></div>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $stats['total'] > 0 ? ($stats['pending'] / $stats['total']) * 100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small mb-1">Processing <span class="float-right"><?php echo $stats['processing']; ?></span></div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $stats['total'] > 0 ? ($stats['processing'] / $stats['total']) * 100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small mb-1">Approved <span class="float-right"><?php echo $stats['approved']; ?></span></div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $stats['total'] > 0 ? ($stats['approved'] / $stats['total']) * 100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small mb-1">Ready for Pickup <span class="float-right"><?php echo $stats['ready_for_pickup']; ?></span></div>
                                        <div class="progress">
                                            <div class="progress-bar" style="background-color: #6f42c1; width: <?php echo $stats['total'] > 0 ? ($stats['ready_for_pickup'] / $stats['total']) * 100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <div class="small mb-1">Completed <span class="float-right"><?php echo $stats['completed']; ?></span></div>
                                        <div class="progress">
                                            <div class="progress-bar bg-secondary" role="progressbar" style="width: <?php echo $stats['total'] > 0 ? ($stats['completed'] / $stats['total']) * 100 : 0; ?>%"></div>
                                        </div>
                                    </div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle sidebar
            $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
                $("body").toggleClass("sidebar-toggled");
                $(".sidebar").toggleClass("toggled");
                if ($(".sidebar").hasClass("toggled")) {
                    $('.sidebar .collapse').collapse('hide');
                }
            });

            // Close any open menu accordions when window is resized below 768px
            $(window).resize(function() {
                if ($(window).width() < 768) {
                    $('.sidebar .collapse').collapse('hide');
                }
                
                // Toggle the side navigation when window is resized below 480px
                if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
                    $("body").addClass("sidebar-toggled");
                    $(".sidebar").addClass("toggled");
                    $('.sidebar .collapse').collapse('hide');
                }
            });
        });
    </script>
</body>
</html>