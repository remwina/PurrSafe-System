<?php
require_once '../../2_User/UserBackend/userAuth.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

//Logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $login->logout();
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

require_once '../../2_User/UserBackend/db.php';

// Fetch user's profile image
$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'dashboard':
            header("Location: 1_user_dashboard.php");
            exit();

        case 'create':
            header("Location: 2.1_create_new_report.php");
            exit();

        case 'view':
            header("Location: 3.1_view_reports.php");
            exit();

        case 'myProfile':
            header("Location: 4.1_my_profile.php");
            exit();

        case 'help':
            header("Location: 5_help_and_support.php");
            exit();

        case 'others':
            header("Location: 6_others.php");
            exit();
            
        case 'search':
            $search_query = isset($_POST['search']) ? $_POST['search'] : '';
            header("Location: search.php?q=" . urlencode($search_query));
            exit();
            
        case 'profile':
            header("Location: 4.1_my_profile.php");
            exit();
            
        default:
            header("Location: 1_user_dashboard.php");
            exit();
    }
}    

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';

// Add this function to format image paths
function formatImagePath($image) {
    if (empty($image)) {
        return '../../3_Images/cat-user.png'; // Default image
    }
    
    // If it's a full path, return as is
    if (strpos($image, '../../5_Uploads/') === 0) {
        return $image;
    }
    
    // Otherwise, prepend the uploads directory path
    return '../../5_Uploads/' . basename($image);
}

class DashboardData extends Database {
    public function getRecentReports($limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT r.*, u.fullname as reporter_name, 
                       CASE 
                           WHEN r.edited_at IS NOT NULL THEN 1
                           ELSE 0
                       END as is_edited
                FROM lost_reports r 
                LEFT JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC 
                LIMIT ?
            ");
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            return false;
        }
    }

    public function getCatProfileCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM cat_profiles");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getReportCount() {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count 
                FROM lost_reports 
                WHERE status != 'found' OR status IS NULL
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getFoundCatCount() {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count 
                FROM lost_reports 
                WHERE status = 'found'
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

}

$dashboard = new DashboardData();
$result = $dashboard->getRecentReports();
$cat_profile_count = $dashboard->getCatProfileCount();
$report_count = $dashboard->getReportCount();
$found_cat_count = $dashboard->getFoundCatCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body>
    <div class="side-menu">
        <div class="text-center">
            <img src="../../3_Images/logo.png" class="logo" style="width: 150px; height: 150px; margin: 20px auto; display: block;">
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="dashboard" class="btn btn-link nav-link text-dark active">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="create" class="btn btn-link nav-link text-dark">
                        <i class="fas fa-plus-circle me-2"></i> Create New Report
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="view" class="btn btn-link nav-link text-dark">
                        <i class="fas fa-eye me-2"></i> View Reports
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="myProfile" class="btn btn-link nav-link text-dark">
                        <i class="fas fa-user me-2"></i> My Profile
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="help" class="btn btn-link nav-link text-dark">
                        <i class="fas fa-question-circle me-2"></i> Help and Support
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="others" class="btn btn-link nav-link text-dark">
                        <i class="fas fa-ellipsis-h me-2"></i> Others
                    </button>
                </form>
            </li>
            <li class="nav-item mt-auto">
                <form method="POST">
                    <button type="submit" name="action" value="logout" class="btn btn-link nav-link text-dark">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
    <div class="container-custom">
        <header class="header-container mb-4">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <form method="POST" class="d-flex flex-grow-1">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search..">
                        <button type="submit" name="action" value="search" class="btn btn-outline-secondary">
                            <img src="../../3_Images/search.png" alt="search" style="width: 20px;">
                        </button>
                    </div>
                </form>
                <div class="d-flex align-items-center gap-3">
                    <?php include '7_notifications.php'; ?>
                    <form method="POST" class="m-0">
                        <button type="submit" name="action" value="profile" class="btn rounded-circle p-0" style="width: 50px; height: 50px; overflow: hidden; border: none;">
                            <img src="<?= !empty($_SESSION['profile_image']) ? '../../6_Profile_Pictures/' . htmlspecialchars($_SESSION['profile_image']) : '../../3_Images/cat-user.png' ?>" 
                                 alt="user profile" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="main-content">
            <!-- Welcome Section with Animation -->
            <div class="welcome-section text-center mb-4 p-4 bg-white rounded shadow-sm animate__animated animate__fadeIn">
                <div class="user-greeting">
                    <h2 class="h3 fw-bold mb-2">Welcome back, <?php echo htmlspecialchars($fullname); ?>!</h2>
                    <p class="text-muted">Track and manage your cat reports all in one place</p>
                    <div class="border-bottom w-25 mx-auto my-3"></div>
                </div>
            </div>

            <!-- Statistics Cards with Hover Effects -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card card shadow-sm h-100 hover-lift">
                        <div class="card-body text-center p-4">
                            <div class="stat-icon mb-3">
                                <i class="fas fa-check-circle fa-3x text-success animate__animated animate__pulse animate__infinite"></i>
                            </div>
                            <h1 class="display-4 fw-bold mb-1 counter"><?php echo $found_cat_count; ?></h1>
                            <h3 class="text-muted h6 mb-0">Found Cats</h3>
                            <div class="progress mt-3" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card card shadow-sm h-100 hover-lift">
                        <div class="card-body text-center p-4">
                            <div class="stat-icon mb-3">
                                <i class="fas fa-search fa-3x text-warning animate__animated animate__heartBeat animate__infinite"></i>
                            </div>
                            <h1 class="display-4 fw-bold mb-1 counter"><?php echo $report_count; ?></h1>
                            <h3 class="text-muted h6 mb-0">Missing Cats</h3>
                            <div class="progress mt-3" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card card shadow-sm h-100 hover-lift">
                        <div class="card-body text-center p-4">
                            <div class="stat-icon mb-3">
                                <i class="fas fa-paw fa-3x text-primary animate__animated animate__bounce animate__infinite"></i>
                            </div>
                            <h1 class="display-4 fw-bold mb-1 counter"><?php echo $cat_profile_count; ?></h1>
                            <h3 class="text-muted h6 mb-0">Total Cat Profiles</h3>
                            <div class="progress mt-3" style="height: 4px;">
                                <div class="progress-bar bg-primary" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reports Table with Enhanced Styling -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2 text-primary"></i>
                            Recent Reports
                        </h5>
                        <form method="POST" class="m-0">
                            <button type="submit" name="action" value="view" class="btn btn-primary btn-sm rounded-pill px-3">
                                View All Reports
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4">Cat Details</th>
                                    <th>Last Seen</th>
                                    <th>Status</th>
                                    <th class="text-end px-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr class="hover-bg-light">
                                            <td class="px-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="cat-avatar me-3">
                                                        <?php if (!empty($row['image_path'])): ?>
                                                            <img src="<?= formatImagePath($row['image_path']) ?>" 
                                                                 class="rounded-circle" 
                                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="fas fa-cat text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center">
                                                            <span class="fw-bold"><?= htmlspecialchars($row['cat_name']) ?></span>
                                                            <?php if ($row['is_edited']): ?>
                                                                <span class="badge bg-purple-subtle text-purple ms-2" 
                                                                      data-bs-toggle="tooltip" 
                                                                      title="This report has been edited">
                                                                    <i class="fas fa-pen-fancy"></i>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <small class="text-muted"><?= htmlspecialchars($row['breed']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?= date('M j, Y', strtotime($row['last_seen_date'])) ?></div>
                                                <small class="text-muted"><?= date('g:i A', strtotime($row['last_seen_date'])) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] === 'found'): ?>
                                                    <span class="badge bg-success-subtle text-success px-3 py-2">
                                                        <i class="fas fa-check-circle me-1"></i>Found
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                                        <i class="fas fa-search me-1"></i>Missing
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end px-4">
                                                <a href="3.2_view_more.php?id=<?= $row['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary rounded-pill">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <img src="../../3_Images/no-data.png" alt="No Data" style="width: 120px; opacity: 0.5;">
                                            <p class="text-muted mt-2">No reports found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/countup.js@2.0.7/dist/countUp.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize counters
        document.querySelectorAll('.counter').forEach(counter => {
            const value = parseInt(counter.innerText);
            const countUp = new CountUp(counter, value, {
                duration: 2,
                separator: ',',
            });
            countUp.start();
        });
    });
    </script>
    <!-- Add canvas for confetti -->
    <canvas id="confetti-canvas" style="position: fixed; top: 0; left: 0; pointer-events: none; z-index: 9999;"></canvas>
    
    <!-- Add confetti.js library -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <script>
        // Trigger confetti when page loads
        window.onload = function() {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
        };
    </script>
</body>
</html>