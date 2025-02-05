<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../2_User/UserBackend/db.php';

$ADMIN_USERNAME = "admin";
$ADMIN_PASSWORD = "admin123";

class AdminProcess extends Database {
    public function getReport($id) {
        try {
            $query = "
                SELECT 
                    lr.*,
                    u.fullname as reporter_name,
                    ri.image_path,
                    CASE 
                        WHEN fr.id IS NOT NULL THEN 'found'
                        ELSE 'missing'
                    END as status
                FROM lost_reports lr
                LEFT JOIN users u ON lr.user_id = u.id
                LEFT JOIN report_images ri ON lr.id = ri.report_id
                LEFT JOIN found_reports fr ON lr.id = fr.report_id
                WHERE lr.id = ?
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['error' => 'Report not found'];
            }
            
            return $result->fetch_assoc();
        } catch (Exception $e) {
            return ['error' => 'Database error occurred'];
        }
    }

    public function getAllUsers() {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    u.id,
                    u.fullname,
                    u.username,
                    u.email,
                    u.created_at,
                    COUNT(lr.id) as report_count,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM lost_reports WHERE user_id = u.id) THEN 'Active'
                        ELSE 'Inactive'
                    END as status
                FROM users u
                LEFT JOIN lost_reports lr ON u.id = lr.user_id
                GROUP BY u.id
                ORDER BY u.id ASC
            ");
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getAllReports() {
        try {
            $query = "
                SELECT 
                    lr.*,
                    u.fullname as reporter_name,
                    ri.image_path,
                    CASE 
                        WHEN fr.id IS NOT NULL THEN 'found'
                        ELSE 'missing'
                    END as status
                FROM lost_reports lr
                LEFT JOIN users u ON lr.user_id = u.id
                LEFT JOIN report_images ri ON lr.id = ri.report_id
                LEFT JOIN found_reports fr ON lr.id = fr.report_id
                GROUP BY lr.id
                ORDER BY lr.created_at DESC
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getReportById($id) {
        try {
            $query = "
                SELECT 
                    lr.*,
                    u.fullname as reporter_name
                FROM lost_reports lr
                LEFT JOIN users u ON lr.user_id = u.id
                WHERE lr.id = ?
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            return null;
        }
    }

    public function deleteReport($id) {
        try {
            $this->conn->begin_transaction();

            $stmt = $this->conn->prepare("DELETE FROM report_images WHERE report_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $stmt = $this->conn->prepare("DELETE FROM lost_reports WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['error' => 'Failed to delete report'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please enter both username and password";
        header('Location: admin_login.php');
        exit();
    }
    
    if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = 'admin';
        
        $_SESSION['success'] = "Welcome back, Administrator!";
        header('Location: admin_features/1_dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = "Invalid admin credentials";
        header('Location: admin_login.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
        http_response_code(403);
        exit(json_encode(['error' => 'Unauthorized']));
    }

    switch ($_GET['action']) {
        case 'get_report':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                exit(json_encode(['error' => 'Report ID not provided']));
            }

            $reportId = intval($_GET['id']);
            $adminProcess = new AdminProcess();
            $report = $adminProcess->getReport($reportId);

            if ($report) {
                header('Content-Type: application/json');
                echo json_encode($report);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Report not found']);
            }
            exit();

        case 'delete_feedback':
            if (isset($_GET['id'])) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id = ?");
                    if ($stmt->execute([$_GET['id']])) {
                        echo json_encode(['success' => true]);
                    } else {
                        throw new Exception("Failed to delete feedback");
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            }
            break;

        case 'delete_announcement':
            if (isset($_GET['id'])) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
                    if ($stmt->execute([$_GET['id']])) {
                        echo json_encode(['success' => true]);
                    } else {
                        throw new Exception("Failed to delete announcement");
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
            }
            break;

        default:
            http_response_code(400);
            exit(json_encode(['error' => 'Invalid action']));
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
} 
?>