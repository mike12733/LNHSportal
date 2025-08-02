<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$stats = getRequestStats($pdo);
$recentRequests = getAllRequests($pdo);
$recentRequests = array_slice($recentRequests, 0, 10); // Get only 10 most recent
$notifications = getUserNotifications($pdo, $_SESSION['user_id'], 5);
$unreadCount = getUnreadNotificationCount($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
            color: #667eea !important;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
        }
        .stats-card {
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .sidebar .nav-link {
            border-radius: 10px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>LNHS Admin Portal
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown me-3">
                    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notification-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Notifications</h6>
                        <?php if (empty($notifications)): ?>
                            <div class="dropdown-item text-muted">No notifications</div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="dropdown-item">
                                    <div class="d-flex align-items-center">
                                        <i class="<?php echo getNotificationIcon($notification['type']); ?> me-2"></i>
                                        <div>
                                            <div class="fw-bold"><?php echo $notification['title']; ?></div>
                                            <small class="text-muted"><?php echo $notification['message']; ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-shield me-1"></i><?php echo $_SESSION['user_name']; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user-edit me-2"></i>Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <div class="sidebar p-3">
                    <h6 class="text-muted mb-3">Admin Menu</h6>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="requests.php">
                            <i class="fas fa-list me-2"></i>All Requests
                        </a>
                        <a class="nav-link" href="pending_requests.php">
                            <i class="fas fa-clock me-2"></i>Pending Requests
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                        <a class="nav-link" href="documents.php">
                            <i class="fas fa-file-alt me-2"></i>Document Types
                        </a>
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <!-- Welcome Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">
                                    <i class="fas fa-tachometer-alt me-2 text-primary"></i>Admin Dashboard
                                </h4>
                                <p class="card-text text-muted">
                                    Welcome back, <?php echo $_SESSION['user_name']; ?>! Here's an overview of the document request system.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h3 class="card-title"><?php echo $stats['pending']; ?></h3>
                                <p class="card-text text-muted">Pending Requests</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-cogs fa-2x text-info mb-2"></i>
                                <h3 class="card-title"><?php echo $stats['processing']; ?></h3>
                                <p class="card-text text-muted">Processing</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h3 class="card-title"><?php echo $stats['ready']; ?></h3>
                                <p class="card-text text-muted">Ready for Pickup</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                                <h3 class="card-title"><?php echo array_sum($stats); ?></h3>
                                <p class="card-text text-muted">Total Requests</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                                </h5>
                                <a href="pending_requests.php" class="btn btn-warning me-2">
                                    <i class="fas fa-clock me-2"></i>Review Pending
                                </a>
                                <a href="requests.php" class="btn btn-primary me-2">
                                    <i class="fas fa-list me-2"></i>View All Requests
                                </a>
                                <a href="reports.php" class="btn btn-info me-2">
                                    <i class="fas fa-chart-bar me-2"></i>Generate Report
                                </a>
                                <a href="users.php" class="btn btn-secondary">
                                    <i class="fas fa-users me-2"></i>Manage Users
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Requests -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2 text-info"></i>Recent Requests
                                </h5>
                                <a href="requests.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentRequests)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No requests yet</h5>
                                        <p class="text-muted">No document requests have been submitted.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Request ID</th>
                                                    <th>Student</th>
                                                    <th>Document</th>
                                                    <th>Status</th>
                                                    <th>Date Requested</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentRequests as $request): ?>
                                                    <tr>
                                                        <td>
                                                            <strong>#<?php echo $request['id']; ?></strong>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong><?php echo $request['first_name'] . ' ' . $request['last_name']; ?></strong>
                                                                <br><small class="text-muted"><?php echo $request['email']; ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <strong><?php echo $request['document_name']; ?></strong>
                                                            <br><small class="text-muted">₱<?php echo number_format($request['fee'], 2); ?></small>
                                                        </td>
                                                        <td><?php echo getStatusBadge($request['status']); ?></td>
                                                        <td><?php echo formatDate($request['created_at']); ?></td>
                                                        <td>
                                                            <a href="view_request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if ($request['status'] === 'pending'): ?>
                                                                <a href="update_status.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-success">
                                                                    <i class="fas fa-edit"></i>
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>