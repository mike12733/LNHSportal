<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$userRequests = getUserRequests($pdo, $_SESSION['user_id']);
$userData = getUserData($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - LNHS Documents Request Portal</title>
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
        .request-card {
            transition: transform 0.3s ease;
        }
        .request-card:hover {
            transform: translateY(-2px);
        }
        .status-timeline {
            position: relative;
            padding-left: 30px;
        }
        .status-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .status-step {
            position: relative;
            margin-bottom: 20px;
        }
        .status-step::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e9ecef;
        }
        .status-step.active::before {
            background: #667eea;
        }
        .status-step.completed::before {
            background: #28a745;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>LNHS Portal
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
                <a class="nav-link active" href="my_requests.php">
                    <i class="fas fa-list me-1"></i>My Requests
                </a>
                <a class="nav-link" href="request_document.php">
                    <i class="fas fa-plus me-1"></i>New Request
                </a>
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
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-1">
                                    <i class="fas fa-list me-2 text-primary"></i>My Document Requests
                                </h4>
                                <p class="card-text text-muted">
                                    Track the status of all your document requests
                                </p>
                            </div>
                            <a href="request_document.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>New Request
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
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
                        <i class="fas fa-cogs fa-2x text-info mb-2"></i>
                        <h5 class="card-title">
                            <?php echo count(array_filter($userRequests, function($req) { return $req['status'] === 'processing'; })); ?>
                        </h5>
                        <p class="card-text text-muted">Processing</p>
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
                        <p class="card-text text-muted">Ready</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2 text-info"></i>Request History
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($userRequests)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No requests yet</h5>
                                <p class="text-muted">You haven't submitted any document requests yet.</p>
                                <a href="request_document.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Submit Your First Request
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($userRequests as $request): ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card request-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="card-title mb-1"><?php echo $request['document_name']; ?></h6>
                                                        <small class="text-muted">Request #<?php echo $request['id']; ?></small>
                                                    </div>
                                                    <?php echo getStatusBadge($request['status']); ?>
                                                </div>
                                                
                                                <p class="card-text text-muted mb-3">
                                                    <strong>Purpose:</strong> <?php echo substr($request['purpose'], 0, 100) . (strlen($request['purpose']) > 100 ? '...' : ''); ?>
                                                </p>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <small class="text-muted">Fee:</small><br>
                                                        <strong>₱<?php echo number_format($request['fee'], 2); ?></strong>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Requested:</small><br>
                                                        <strong><?php echo formatDate($request['created_at']); ?></strong>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($request['preferred_release_date']): ?>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Preferred Release:</small><br>
                                                        <strong><?php echo formatDate($request['preferred_release_date']); ?></strong>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <a href="view_request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>View Details
                                                    </a>
                                                    <?php if ($request['status'] === 'ready'): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Ready for Pickup
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>