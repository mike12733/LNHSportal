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

// Handle request deletion
if (isset($_POST['delete_request'])) {
    $request_id = $_POST['request_id'];
    $result = $documentRequest->deleteRequest($request_id, $_SESSION['user_id']);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get user's requests
$user_requests = $documentRequest->getUserRequests($_SESSION['user_id']);

// Filter requests if needed
if ($status_filter || $search) {
    $user_requests = array_filter($user_requests, function($request) use ($status_filter, $search) {
        $status_match = empty($status_filter) || $request['status'] === $status_filter;
        $search_match = empty($search) || 
                       stripos($request['document_name'], $search) !== false ||
                       stripos($request['purpose'], $search) !== false;
        return $status_match && $search_match;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - My Requests</title>
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

            <li class="nav-item">
                <a class="nav-link" href="request-document.php">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Request Document</span>
                </a>
            </li>

            <li class="nav-item active">
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
                        <h1 class="h3 mb-0 text-gray-800">My Requests</h1>
                        <a href="request-document.php" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> New Request
                        </a>
                    </div>

                    <!-- Messages -->
                    <?php if (isset($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filters -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Filter Requests</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" name="status" id="status">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="denied" <?php echo $status_filter === 'denied' ? 'selected' : ''; ?>>Denied</option>
                                        <option value="ready_for_pickup" <?php echo $status_filter === 'ready_for_pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" name="search" id="search" 
                                           placeholder="Search by document name or purpose..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Requests Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Document Requests</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($user_requests)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-file-alt fa-3x text-gray-300 mb-3"></i>
                                    <h5 class="text-gray-500">No requests found</h5>
                                    <p class="text-gray-400">You haven't made any document requests yet.</p>
                                    <a href="request-document.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Make Your First Request
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Request ID</th>
                                                <th>Document</th>
                                                <th>Purpose</th>
                                                <th>Status</th>
                                                <th>Quantity</th>
                                                <th>Fee</th>
                                                <th>Date Requested</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($user_requests as $request): ?>
                                                <tr>
                                                    <td>
                                                        <strong>#<?php echo str_pad($request['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                                    </td>
                                                    <td>
                                                        <div class="font-weight-bold"><?php echo htmlspecialchars($request['document_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($request['description']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($request['purpose']); ?>">
                                                            <?php echo htmlspecialchars($request['purpose']); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?php echo str_replace('_', '-', $request['status']); ?>">
                                                            <?php echo ucwords(str_replace('_', ' ', $request['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $request['quantity']; ?></td>
                                                    <td>₱<?php echo number_format($request['fee'] * $request['quantity'], 2); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="view-request.php?id=<?php echo $request['id']; ?>" 
                                                               class="btn btn-sm btn-info" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if ($request['status'] === 'pending'): ?>
                                                                <button type="button" class="btn btn-sm btn-danger" 
                                                                        onclick="confirmDelete(<?php echo $request['id']; ?>)" title="Delete Request">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this request? This action cannot be undone.</p>
                    <p class="text-muted"><small>Note: Only pending requests can be deleted.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="request_id" id="deleteRequestId">
                        <button type="submit" name="delete_request" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(requestId) {
            document.getElementById('deleteRequestId').value = requestId;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        $(document).ready(function() {
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