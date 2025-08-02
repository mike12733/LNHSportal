<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$documentTypes = getDocumentTypes($pdo);
$userData = getUserData($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Document - LNHS Documents Request Portal</title>
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
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .document-type-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .document-type-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .document-type-card.selected {
            border-color: #667eea;
            background-color: #f8f9ff;
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
                <a class="nav-link" href="my_requests.php">
                    <i class="fas fa-list me-1"></i>My Requests
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-file-plus me-2 text-primary"></i>Request Document
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="process_request.php" method="POST" enctype="multipart/form-data" id="requestForm">
                            <!-- Document Type Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">1. Select Document Type</label>
                                <div class="row">
                                    <?php foreach ($documentTypes as $docType): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card document-type-card h-100" onclick="selectDocumentType(<?php echo $docType['id']; ?>, '<?php echo $docType['name']; ?>', <?php echo $docType['fee']; ?>)">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                                                    <h6 class="card-title"><?php echo $docType['name']; ?></h6>
                                                    <p class="card-text text-muted small"><?php echo $docType['description']; ?></p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-info"><?php echo $docType['processing_days']; ?> days</span>
                                                        <span class="text-primary fw-bold">₱<?php echo number_format($docType['fee'], 2); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="document_type_id" id="document_type_id" required>
                                <div class="text-danger" id="documentTypeError"></div>
                            </div>

                            <!-- Purpose of Request -->
                            <div class="mb-4">
                                <label for="purpose" class="form-label fw-bold">2. Purpose of Request</label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="4" placeholder="Please specify the purpose for requesting this document..." required></textarea>
                            </div>

                            <!-- Preferred Release Date -->
                            <div class="mb-4">
                                <label for="preferred_release_date" class="form-label fw-bold">3. Preferred Release Date</label>
                                <input type="date" class="form-control" id="preferred_release_date" name="preferred_release_date" 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                <small class="text-muted">Please select a date at least 1 day from today</small>
                            </div>

                            <!-- Upload Requirements -->
                            <div class="mb-4">
                                <label for="requirements_file" class="form-label fw-bold">4. Upload Requirements (Optional)</label>
                                <input type="file" class="form-control" id="requirements_file" name="requirements_file" 
                                       accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted">Accepted formats: JPG, JPEG, PNG, PDF (Max 5MB)</small>
                            </div>

                            <!-- Summary -->
                            <div class="mb-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2 text-info"></i>Request Summary
                                        </h6>
                                        <div id="requestSummary" class="text-muted">
                                            Please select a document type to see the summary.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedDocumentType = null;

        function selectDocumentType(id, name, fee) {
            // Remove previous selection
            document.querySelectorAll('.document-type-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selection to clicked card
            event.currentTarget.classList.add('selected');

            // Update hidden input
            document.getElementById('document_type_id').value = id;
            selectedDocumentType = { id, name, fee };

            // Update summary
            updateSummary();

            // Clear error
            document.getElementById('documentTypeError').textContent = '';
        }

        function updateSummary() {
            const summaryDiv = document.getElementById('requestSummary');
            if (selectedDocumentType) {
                summaryDiv.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Document:</strong> ${selectedDocumentType.name}<br>
                            <strong>Fee:</strong> ₱${selectedDocumentType.fee.toFixed(2)}
                        </div>
                        <div class="col-md-6">
                            <strong>Processing Time:</strong> 2-5 business days<br>
                            <strong>Payment:</strong> Pay upon pickup
                        </div>
                    </div>
                `;
            } else {
                summaryDiv.textContent = 'Please select a document type to see the summary.';
            }
        }

        // Form validation
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            const documentTypeId = document.getElementById('document_type_id').value;
            const purpose = document.getElementById('purpose').value.trim();
            const releaseDate = document.getElementById('preferred_release_date').value;

            if (!documentTypeId) {
                e.preventDefault();
                document.getElementById('documentTypeError').textContent = 'Please select a document type.';
                return false;
            }

            if (!purpose) {
                e.preventDefault();
                alert('Please specify the purpose of your request.');
                return false;
            }

            if (!releaseDate) {
                e.preventDefault();
                alert('Please select a preferred release date.');
                return false;
            }

            // Check if release date is in the future
            const selectedDate = new Date(releaseDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate <= today) {
                e.preventDefault();
                alert('Please select a date at least 1 day from today.');
                return false;
            }
        });

        // File size validation
        document.getElementById('requirements_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    alert('File size must be less than 5MB.');
                    this.value = '';
                }
            }
        });
    </script>
</body>
</html>