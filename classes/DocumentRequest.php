<?php
require_once 'config/database.php';

class DocumentRequest {
    private $conn;
    private $table = 'document_requests';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createRequest($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                     (user_id, document_type_id, purpose, preferred_release_date, quantity, 
                      uploaded_id_path, uploaded_requirements_path) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['user_id'],
                $data['document_type_id'],
                $data['purpose'],
                $data['preferred_release_date'],
                $data['quantity'] ?? 1,
                $data['uploaded_id_path'] ?? null,
                $data['uploaded_requirements_path'] ?? null
            ]);
            
            $request_id = $this->conn->lastInsertId();
            
            // Add to status history
            $this->addStatusHistory($request_id, null, 'pending', $data['user_id'], 'Request submitted');
            
            return ['success' => true, 'message' => 'Document request submitted successfully!', 'request_id' => $request_id];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Request submission failed: ' . $e->getMessage()];
        }
    }

    public function getUserRequests($user_id, $limit = null) {
        try {
            $query = "SELECT dr.*, dt.document_name, dt.description, dt.fee, dt.processing_days 
                     FROM " . $this->table . " dr 
                     JOIN document_types dt ON dr.document_type_id = dt.id 
                     WHERE dr.user_id = ? 
                     ORDER BY dr.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT " . intval($limit);
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllRequests($status = null, $limit = null) {
        try {
            $query = "SELECT dr.*, dt.document_name, dt.description, dt.fee, dt.processing_days,
                             u.first_name, u.last_name, u.student_id, u.email, u.user_type
                     FROM " . $this->table . " dr 
                     JOIN document_types dt ON dr.document_type_id = dt.id 
                     JOIN users u ON dr.user_id = u.id";
            
            if ($status) {
                $query .= " WHERE dr.status = ?";
            }
            
            $query .= " ORDER BY dr.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT " . intval($limit);
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($status) {
                $stmt->execute([$status]);
            } else {
                $stmt->execute();
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getRequestById($id) {
        try {
            $query = "SELECT dr.*, dt.document_name, dt.description, dt.fee, dt.processing_days,
                             u.first_name, u.last_name, u.student_id, u.email, u.user_type, u.phone_number
                     FROM " . $this->table . " dr 
                     JOIN document_types dt ON dr.document_type_id = dt.id 
                     JOIN users u ON dr.user_id = u.id 
                     WHERE dr.id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateRequestStatus($request_id, $new_status, $admin_id, $notes = null) {
        try {
            // Get current status
            $current_request = $this->getRequestById($request_id);
            if (!$current_request) {
                return ['success' => false, 'message' => 'Request not found'];
            }
            
            $old_status = $current_request['status'];
            
            // Update request
            $query = "UPDATE " . $this->table . " 
                     SET status = ?, admin_notes = ?, processed_by = ?, processed_at = NOW() 
                     WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$new_status, $notes, $admin_id, $request_id]);
            
            // Add to status history
            $this->addStatusHistory($request_id, $old_status, $new_status, $admin_id, $notes);
            
            return ['success' => true, 'message' => 'Request status updated successfully!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Status update failed: ' . $e->getMessage()];
        }
    }

    public function addStatusHistory($request_id, $old_status, $new_status, $changed_by, $notes = null) {
        try {
            $query = "INSERT INTO request_status_history 
                     (request_id, old_status, new_status, changed_by, notes) 
                     VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$request_id, $old_status, $new_status, $changed_by, $notes]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getRequestHistory($request_id) {
        try {
            $query = "SELECT rsh.*, u.first_name, u.last_name 
                     FROM request_status_history rsh 
                     JOIN users u ON rsh.changed_by = u.id 
                     WHERE rsh.request_id = ? 
                     ORDER BY rsh.created_at ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$request_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getDocumentTypes() {
        try {
            $query = "SELECT * FROM document_types WHERE status = 'active' ORDER BY document_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function uploadFile($file, $type = 'id') {
        $upload_dir = UPLOAD_PATH . $type . '/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF files are allowed.'];
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File size too large. Maximum size is 5MB.'];
        }
        
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filepath' => $filepath];
        } else {
            return ['success' => false, 'message' => 'File upload failed.'];
        }
    }

    public function getRequestStats($user_id = null) {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'denied' THEN 1 ELSE 0 END) as denied,
                        SUM(CASE WHEN status = 'ready_for_pickup' THEN 1 ELSE 0 END) as ready_for_pickup,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                     FROM " . $this->table;
            
            if ($user_id) {
                $query .= " WHERE user_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$user_id]);
            } else {
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
            }
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return [
                'total' => 0, 'pending' => 0, 'processing' => 0,
                'approved' => 0, 'denied' => 0, 'ready_for_pickup' => 0, 'completed' => 0
            ];
        }
    }

    public function deleteRequest($request_id, $user_id) {
        try {
            // Check if request belongs to user and is still pending
            $query = "SELECT status FROM " . $this->table . " WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$request_id, $user_id]);
            $request = $stmt->fetch();
            
            if (!$request) {
                return ['success' => false, 'message' => 'Request not found'];
            }
            
            if ($request['status'] !== 'pending') {
                return ['success' => false, 'message' => 'Cannot delete request that is already being processed'];
            }
            
            // Delete request
            $query = "DELETE FROM " . $this->table . " WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$request_id, $user_id]);
            
            return ['success' => true, 'message' => 'Request deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()];
        }
    }
}
?>