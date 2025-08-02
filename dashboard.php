<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$userData = getUserData($pdo, $_SESSION['user_id']);
$userRequests = getUserRequests($pdo, $_SESSION['user_id']);
$documentTypes = getDocumentTypes($pdo);
$notifications = getUserNotifications($pdo, $_SESSION['user_id'], 5);
$unreadCount = getUnreadNotificationCount($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LNHS Documents Request Portal</title>
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
        .status-badge {
            font-size: 0.8rem;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>LNHS Portal
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
                        <i class="fas fa-user me-1"></i><?php echo $_SESSION['user_name']; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user-edit me-2"></i>Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-home me-2 text-primary"></i>Welcome, <?php echo $userData['first_name']; ?>!
                        </h4>
                        <p class="card-text text-muted">
                            You are logged in as a <strong><?php echo ucfirst($userData['role']); ?></strong>. 
                            You can request documents and track their status here.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                        <h5 class="card-title"><?php echo count($userRequests); ?></h5>
                        <p class="card-text text-muted">Total Requests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h5 class="card-title">
                            <?php echo count(array_filter($userRequests, function($req) { return $req['status'] === 'pending'; })); ?>
                        </h5>
                        <p class="card-text text-muted">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h5 class="card-title">
                            <?php echo count(array_filter($userRequests, function($req) { return $req['status'] === 'ready'; })); ?>
                        </h5>
                        <p class="card-text text-muted">Ready for Pickup</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h5 class="card-title">
                            <?php echo count(array_filter($userRequests, function($req) { return $req['status'] === 'denied'; })); ?>
                        </h5>
                        <p class="card-text text-muted">Denied</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-plus-circle me-2 text-success"></i>Quick Actions
                        </h5>
                        <a href="request_document.php" class="btn btn-primary me-2">
                            <i class="fas fa-file-plus me-2"></i>Request New Document
                        </a>
                        <a href="my_requests.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-list me-2"></i>View All Requests
                        </a>
                        <a href="profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user-edit me-2"></i>Update Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Requests -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2 text-info"></i>Recent Requests
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($userRequests)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No requests yet</h5>
                                <p class="text-muted">Start by requesting your first document</p>
                                <a href="request_document.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Request Document
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Document</th>
                                            <th>Purpose</th>
                                            <th>Status</th>
                                            <th>Date Requested</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($userRequests, 0, 5) as $request): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo $request['document_name']; ?></strong>
                                                    <br><small class="text-muted">Fee: ₱<?php echo number_format($request['fee'], 2); ?></small>
                                                </td>
                                                <td><?php echo substr($request['purpose'], 0, 50) . (strlen($request['purpose']) > 50 ? '...' : ''); ?></td>
                                                <td><?php echo getStatusBadge($request['status']); ?></td>
                                                <td><?php echo formatDate($request['created_at']); ?></td>
                                                <td>
                                                    <a href="view_request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($userRequests) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="my_requests.php" class="btn btn-outline-primary">
                                        View All Requests
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>