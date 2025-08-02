<?php
require_once 'config/database.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                     (student_id, email, password, first_name, last_name, middle_name, 
                      user_type, phone_number, address, year_graduated, course) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt->execute([
                $data['student_id'],
                $data['email'],
                $hashed_password,
                $data['first_name'],
                $data['last_name'],
                $data['middle_name'] ?? null,
                $data['user_type'],
                $data['phone_number'] ?? null,
                $data['address'] ?? null,
                $data['year_graduated'] ?? null,
                $data['course'] ?? null
            ]);
            
            return ['success' => true, 'message' => 'Registration successful!'];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Email or Student ID already exists!'];
            }
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    public function login($email, $password) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email = ? AND status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['student_id'] = $user['student_id'];
                    
                    return ['success' => true, 'user' => $user];
                }
            }
            return ['success' => false, 'message' => 'Invalid email or password!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }

    public function getUserById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateProfile($user_id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET first_name = ?, last_name = ?, middle_name = ?, 
                         phone_number = ?, address = ?, course = ? 
                     WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['middle_name'] ?? null,
                $data['phone_number'] ?? null,
                $data['address'] ?? null,
                $data['course'] ?? null,
                $user_id
            ]);
            
            return ['success' => true, 'message' => 'Profile updated successfully!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }

    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Verify current password
            $query = "SELECT password FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!password_verify($current_password, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect!'];
            }
            
            // Update password
            $query = "UPDATE " . $this->table . " SET password = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt->execute([$hashed_password, $user_id]);
            
            return ['success' => true, 'message' => 'Password changed successfully!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Password change failed: ' . $e->getMessage()];
        }
    }

    public function getAllUsers($user_type = null) {
        try {
            $query = "SELECT id, student_id, email, first_name, last_name, middle_name, 
                             user_type, phone_number, course, year_graduated, status, created_at 
                      FROM " . $this->table;
            
            if ($user_type) {
                $query .= " WHERE user_type = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$user_type]);
            } else {
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>