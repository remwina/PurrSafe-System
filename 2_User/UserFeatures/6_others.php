<?php
require_once '../../2_User/UserBackend/userAuth.php';

$db = new Database();
$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $login->logout();
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("POST received: " . print_r($_POST, true));
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

        case 'logout':
            $login->logout();
            header('Location: login.php');
            exit();
    }
}

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
$email = $_SESSION['email'] ?? '';

// Fetch user's profile image
$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Others</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
</head>
<body>
    <div class="side-menu">
        <div class="text-center">
            <img src="../../3_Images/logo.png" class="logo" style="width: 150px; height: 150px; margin: 20px auto; display: block;">
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="dashboard" class="btn btn-link nav-link text-dark">
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
                    <button type="submit" name="action" value="others" class="btn btn-link nav-link text-dark active">
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
            <h2 class="mb-0">Others</h2>
        </header>

        <main class="main-content">
            <div class="row g-4">
                <div class="col-12">
                    <div class="accordion" id="settingsAccordion">
                      
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#futureUpdates">
                                    <i class="fas fa-clock me-2"></i>Future Updates
                                </button>
                            </h2>
                            <div id="futureUpdates" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
                                <div class="accordion-body">
                                    <div class="py-4">
                                        <i class="fas fa-tools fa-3x mb-3 text-muted d-block text-center"></i>
                                        <h5 class="mb-4 text-center">Upcoming Features</h5>
                                        <div class="list-group">
                                            <div class="list-group-item">
                                                <i class="fas fa-user-edit me-2 text-primary"></i>
                                                <strong>Account Updates</strong>
                                                <p class="text-muted small mb-0">Modify your profile information and preferences</p>
                                            </div>
                                            <div class="list-group-item">
                                                <i class="fas fa-key me-2 text-warning"></i>
                                                <strong>Password Management</strong>
                                                <p class="text-muted small mb-0">Change your password and enhance account security</p>
                                            </div>
                                            <div class="list-group-item">
                                                <i class="fab fa-google me-2 text-danger"></i>
                                                <strong>Gmail Integration</strong>
                                                <p class="text-muted small mb-0">Link your Gmail account for seamless notifications</p>
                                            </div>
                                            <div class="list-group-item">
                                                <i class="fas fa-bell me-2 text-success"></i>
                                                <strong>Notification Settings</strong>
                                                <p class="text-muted small mb-0">Customize your notification preferences</p>
                                            </div>
                                            <div class="list-group-item">
                                                <i class="fas fa-user-times me-2 text-danger"></i>
                                                <strong>Delete Account</strong>
                                                <p class="text-muted small mb-0">Permanently remove your account and all associated data</p>
                                            </div>
                                        </div>
                                        <div class="text-center mt-3">
                                            <small class="text-muted">These features are currently under development</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#donation">
                                    <i class="fas fa-heart me-2"></i>Support PurrSafe
                                </button>
                            </h2>
                            <div id="donation" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
                                <div class="accordion-body">
                                    <div class="text-center py-4">
                                        <i class="fas fa-hand-holding-heart fa-3x mb-3 text-danger"></i>
                                        <h5 class="mb-3">Support Our Mission</h5>
                                        <p class="text-muted mb-4">
                                            Your donation helps us maintain and improve PurrSafe, ensuring we can continue helping reunite lost cats with their families.
                                        </p>
                                        <div class="row justify-content-center g-3">
                                            <div class="col-md-8">
                                                <div class="d-grid gap-2">
                                                    <button class="btn btn-outline-primary" disabled>
                                                        <i class="fab fa-paypal me-2"></i>Donate via PayPal
                                                    </button>
                                                    <button class="btn btn-outline-success" disabled>
                                                        <i class="fas fa-credit-card me-2"></i>Credit Card
                                                    </button>
                                                    <button class="btn btn-outline-info" disabled>
                                                        <i class="fas fa-mobile-alt me-2"></i>GCash
                                                    </button>
                                                    <small class="text-muted mt-2">
                                                        * Donation features coming soon
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#about">
                                    <i class="fas fa-info-circle me-2"></i>About
                                </button>
                            </h2>
                            <div id="about" class="accordion-collapse collapse show" data-bs-parent="#settingsAccordion">
                                <div class="accordion-body">
                                    
                                    <div class="mb-4">
                                        <h6 class="mb-3">About PurrSafe</h6>
                                        <p class="text-muted small">
                                        This project is a comprehensive platform designed to simplify the process of reuniting lost cats with their owners. 
                                        It provides users with essential tools to report missing cats, browse through listings of found cats, and manage profiles for the users and their cats.
                                        </p>
                                    </div>

                                    <h6 class="mb-3">Development Team - GROUP 3</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4 text-center">
                                            <div class="mb-2">
                                                <img src="../../3_Images/developers/katigbak.png" class="rounded-circle" alt="katigbak" 
                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <h6 class="mb-1">Justin Kyle E. Katigbak</h6>
                                            <small class="text-muted d-block">Member</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="mb-2">
                                                <img src="../../3_Images/developers/madrid.png" class="rounded-circle" alt="madrid" 
                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <h6 class="mb-1">Jaika Remina A. Madrid</h6>
                                            <small class="text-muted d-block">Member</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="mb-2">
                                                <img src="../../3_Images/developers/paraiso.png" class="rounded-circle" alt="paraiso" 
                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <h6 class="mb-1">John Lorcan E. Paraiso</h6>
                                            <small class="text-muted d-block">Leader</small>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <small class="text-muted">
                                            Version 1.0.0 | December 2024
                                            <br>
                                            © PurrSafe Lost and Found Cat System. All rights reserved.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </main>
    </div> 

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html> 