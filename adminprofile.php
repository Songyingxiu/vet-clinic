<?php
session_start();
require_once 'connection.php';

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$success = "";
$error = "";

// Get admin data from database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    $bio = trim($_POST['bio']);
    
    // Basic validation
    if (empty($full_name) || empty($email)) {
        $error = "Full name and email are required!";
    } else {
        try {
            // Check if email already exists (excluding current admin)
            $check_stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $check_stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($check_stmt->fetch()) {
                $error = "Email already exists! Please use a different email.";
            } else {
                // Update admin profile
                $update_stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
                $update_stmt->execute([$full_name, $email, $phone, $_SESSION['user_id']]);
                
                // Update session variables
                $_SESSION['fullname'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['phone'] = $phone;
                
                // Refresh admin data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $admin = $stmt->fetch();
                
                $success = "Profile updated successfully!";
            }
        } catch (PDOException $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long!";
    } elseif ($current_password !== $admin['password']) {
        $error = "Current password is incorrect!";
    } else {
        try {
            // Update password (plain text - as per your requirement)
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update_stmt->execute([$new_password, $_SESSION['user_id']]);
            
            $success = "Password changed successfully!";
        } catch (PDOException $e) {
            $error = "Error changing password: " . $e->getMessage();
        }
    }
}

// Get statistics for the dashboard
try {
    // Total users count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE role != 'admin'");
    $stmt->execute();
    $total_users = $stmt->fetchColumn();

    // Total reports count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_reports FROM reports");
    $stmt->execute();
    $total_reports = $stmt->fetchColumn();

    // Total appointments count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_appointments FROM appointments");
    $stmt->execute();
    $total_appointments = $stmt->fetchColumn();

} catch (PDOException $e) {
    $total_users = $total_reports = $total_appointments = 0;
}

// Get admin's initials for profile picture
$initials = "";
if (!empty($admin['full_name'])) {
    $name_parts = explode(' ', $admin['full_name']);
    $initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
} else {
    $initials = strtoupper(substr($admin['username'], 0, 2));
}
?>

<!DOCTYPE html>
<html lang="en-US" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Profile - Pawprint Haven</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="40x40" href="assets/img/favicons/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicons/favicon.ico">
    <link rel="manifest" href="assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">

    <!-- Stylesheets -->
    <link href="assets/css/theme.css" rel="stylesheet" />
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .card-bg {
            background-color: rgba(248, 249, 250, 0.85);
            border: 1px solid rgba(233, 236, 239, 0.7);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-klean {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-outline-klean {
            border: 1px solid #667eea;
            color: #667eea;
        }
        .hover-top:hover {
            transform: translateY(-2px);
            transition: transform 0.2s;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0 auto;
        }
        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
            border-left: 4px solid #667eea;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
        .admin-badge {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 0.25rem;
            display: inline-block;
        }
        .permission-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 0.25rem;
            display: inline-block;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        .modal-content {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.9);
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .alert {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
    </style>
</head>

<body>
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3 d-block navbar-klean" data-navbar-on-scroll="data-navbar-on-scroll">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand" href="admin.php"> 
                    <img class="me-3 d-inline-block" src="assets/img/gallery/logo.png" alt="" style="height: 65px;" />
                </a>
                <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse border-top border-lg-0 mt-4 mt-lg-0" id="navbarSupportedContent">
                    <!-- Admin Navigation Menu -->
                    <ul class="navbar-nav me-auto pt-2 pt-lg-0 font-base">
                        <li class="nav-item px-2">
                            <a class="nav-link" href="admin.php#dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="admin.php#manage-users">Manage Users</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="admin.php#manage-reports">Manage Reports</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="admin.php#manage-appointments">Manage Appointments</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="admin.php#treatment-records">Treatment Records</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link fw-medium active" aria-current="page" href="profileadmin.php">Profile</a>
                        </li>
                    </ul>
                    <!-- Logout Button -->
                    <div class="ps-lg-5">
                        <button class="btn btn-light shadow-klean order-0" type="button" data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <span class="text-gradient fw-bold">Log Out</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="container mt-4" style="margin-top: 100px !important;">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="container mt-4" style="margin-top: 100px !important;">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Profile Section -->
        <section id="profile" class="py-8" style="margin-top: 100px;">
            <div class="bg-holder" style="background-image:url(assets/img/illustrations/background.png);background-position:right bottom;">
            </div>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card card-bg p-4">
                            <div class="text-center mb-5">
                                <h2 class="fw-bold text-gradient mb-3">Administrator Profile</h2>
                                <p class="text-600">Manage your administrative account and system settings</p>
                            </div>
                            
                            <div class="row">
                                <!-- Profile Picture & Basic Info -->
                                <div class="col-md-4 text-center mb-4">
                                    <div class="profile-avatar mb-3">
                                        <span><?php echo $initials; ?></span>
                                    </div>
                                    <h5 class="fw-bold"><?php echo htmlspecialchars($admin['full_name'] ?? $admin['username']); ?></h5>
                                    <p class="text-600">System Administrator</p>
                                    
                                    <!-- Admin Stats -->
                                    <div class="mt-4">
                                        <div class="stats-card mb-3">
                                            <h4 class="text-gradient fw-bold"><?php echo $total_users; ?></h4>
                                            <p class="text-600 mb-0">Users Managed</p>
                                        </div>
                                        <div class="stats-card mb-3">
                                            <h4 class="text-gradient fw-bold"><?php echo $total_reports; ?></h4>
                                            <p class="text-600 mb-0">Reports Processed</p>
                                        </div>
                                        <div class="stats-card">
                                            <h4 class="text-gradient fw-bold"><?php echo $total_appointments; ?></h4>
                                            <p class="text-600 mb-0">Appointments</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button class="btn btn-outline-klean rounded-pill w-100 mb-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                            Change Password
                                        </button>
                                        <button class="btn btn-outline-klean rounded-pill w-100" data-bs-toggle="modal" data-bs-target="#systemSettingsModal">
                                            System Settings
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Profile Form -->
                                <div class="col-md-8">
                                    <form method="POST">
                                        <input type="hidden" name="update_profile" value="1">
                                        <h5 class="fw-bold text-gradient mb-4">Administrative Information</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Full Name</label>
                                                <input type="text" class="form-control rounded-pill" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Username</label>
                                                <input type="text" class="form-control rounded-pill" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly style="background-color: #f8f9fa;">
                                                <small class="text-muted">Username cannot be changed</small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Email</label>
                                                <input type="email" class="form-control rounded-pill" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Phone</label>
                                                <input type="tel" class="form-control rounded-pill" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Administrative Role</label>
                                            <div class="mt-2">
                                                <span class="admin-badge">System Administrator</span>
                                                <span class="admin-badge">User Management</span>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">System Permissions</label>
                                            <div class="mt-2">
                                                <span class="permission-badge">Full System Access</span>
                                                <span class="permission-badge">User Management</span>
                                                <span class="permission-badge">Report Management</span>
                                                <span class="permission-badge">Appointment Scheduling</span>
                                                <span class="permission-badge">Database Administration</span>
                                                <span class="permission-badge">System Configuration</span>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Bio</label>
                                            <textarea class="form-control rounded-3" name="bio" rows="3">System Administrator at Pawprint Haven Veterinary Clinic with full system access and management privileges. Responsible for user management, system configuration, and ensuring smooth clinic operations.</textarea>
                                        </div>
                                        
                                        <h5 class="fw-bold text-gradient mt-5 mb-4">Account Information</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">User ID</label>
                                                <input type="text" class="form-control rounded-pill" value="#<?php echo $admin['user_id']; ?>" readonly style="background-color: #f8f9fa;">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Account Role</label>
                                                <input type="text" class="form-control rounded-pill" value="<?php echo ucfirst($admin['role']); ?>" readonly style="background-color: #f8f9fa;">
                                            </div>
                                        </div>
                                        
                                        <h5 class="fw-bold text-gradient mt-5 mb-4">Clinic Information</h5>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Clinic</label>
                                            <input type="text" class="form-control rounded-pill" value="Pawprint Haven Veterinary Clinic" readonly style="background-color: #f8f9fa;">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Department</label>
                                                <input type="text" class="form-control rounded-pill" name="department" value="Administration">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Position</label>
                                                <input type="text" class="form-control rounded-pill" name="position" value="System Administrator">
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                                            <a href="admin.php" class="btn btn-outline-klean rounded-pill px-4">Back to Dashboard</a>
                                            <button type="submit" class="btn hover-top btn-glow btn-klean rounded-pill px-4">
                                                <span class="fw-bold">Save Changes</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Logout Confirmation Modal -->
        <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-gradient" id="logoutModalLabel">Confirm Logout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-sign-out-alt text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="fw-bold mb-3">Are you sure you want to log out?</h6>
                        <p class="text-600">You will need to log in again to access your account.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-klean rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <a href="logout.php" class="btn hover-top btn-glow btn-klean rounded-pill px-4">
                            <span class="fw-bold">Yes, Log Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Modal -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-gradient" id="changePasswordModalLabel">Change Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" id="changePasswordForm">
                        <input type="hidden" name="change_password" value="1">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Current Password</label>
                                <input type="password" class="form-control rounded-pill" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">New Password</label>
                                <input type="password" class="form-control rounded-pill" name="new_password" required minlength="6">
                                <div class="form-text">Password must be at least 6 characters long</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Confirm New Password</label>
                                <input type="password" class="form-control rounded-pill" name="confirm_password" required>
                                <div class="invalid-feedback" id="passwordError" style="display: none;">Passwords do not match</div>
                            </div>
                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fas fa-info-circle me-2"></i>
                                    Make sure your new password is strong and different from your previous passwords.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-klean rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn hover-top btn-glow btn-klean rounded-pill px-4">
                                <span class="fw-bold">Change Password</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Settings Modal -->
        <div class="modal fade" id="systemSettingsModal" tabindex="-1" aria-labelledby="systemSettingsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-gradient" id="systemSettingsModalLabel">System Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <h6 class="fw-bold text-gradient mb-3">General Settings</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Default Dashboard View</label>
                                    <select class="form-select rounded-pill">
                                        <option selected>Overview</option>
                                        <option>Statistics</option>
                                        <option>Activity Feed</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Items Per Page</label>
                                    <select class="form-select rounded-pill">
                                        <option>10</option>
                                        <option selected>25</option>
                                        <option>50</option>
                                        <option>100</option>
                                    </select>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold text-gradient mt-4 mb-3">Notification Preferences</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                        <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="systemAlerts" checked>
                                        <label class="form-check-label" for="systemAlerts">System Alerts</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="userActivityAlerts">
                                        <label class="form-check-label" for="userActivityAlerts">User Activity Alerts</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="reportAlerts" checked>
                                        <label class="form-check-label" for="reportAlerts">Report Generation Alerts</label>
                                    </div>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold text-gradient mt-4 mb-3">Backup & Security</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Auto Backup Frequency</label>
                                    <select class="form-select rounded-pill">
                                        <option>Daily</option>
                                        <option selected>Weekly</option>
                                        <option>Monthly</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Session Timeout</label>
                                    <select class="form-select rounded-pill">
                                        <option>15 minutes</option>
                                        <option selected>30 minutes</option>
                                        <option>60 minutes</option>
                                        <option>Never</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fas fa-info-circle me-2"></i>
                                    These settings affect your administrative dashboard and system preferences.
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn hover-top btn-glow btn-klean rounded-pill px-4">
                            <span class="fw-bold">Save Settings</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <section class="pb-0 pt-6">
            <div class="container">
                <div class="row justify-content-lg-between">
                    <!-- Contact Info -->
                    <div class="col-12 col-sm-6 col-lg-4 mb-5">
                        <h5 class="text-600 mb-3 fw-bold">Pawprint Haven</h5>
                        <p class="text-400">43/A Spooner Street<br>St Laurence, Virginia<br>Texas, 75001</p>
                        <p class="text-400">+62 0852-7767-0706</p>
                    </div>
                    
                    <!-- Emergency Info -->
                    <div class="col-12 col-sm-6 col-lg-4 mb-5">
                        <h5 class="text-600 mb-3 fw-bold">Emergency Contact</h5>
                        <p class="text-400 mb-1">24/7 Emergency Line</p>
                        <p class="text-400 fw-bold">+62 0852-7767-0707</p>
                    </div>
                    
                    <!-- Support Hours -->
                    <div class="col-12 col-sm-6 col-lg-4 mb-5">
                        <h5 class="text-600 mb-3 fw-bold">Support Hours</h5>
                        <p class="text-400 mb-1">Monday - Friday: 8:00 AM - 6:00 PM</p>
                        <p class="text-400 mb-1">Saturday: 9:00 AM - 4:00 PM</p>
                        <p class="text-400">Sunday: 10:00 AM - 2:00 PM</p>
                    </div>
                </div>
                
                <hr class="text-100 mb-0" />
                
                <div class="row justify-content-md-between justify-content-evenly py-3">
                    <div class="col-12 col-sm-8 col-md-6 col-lg-auto text-center text-md-start">
                        <p class="fs--1 my-2 fw-bold">All rights Reserved &copy; Pawprint Haven, <?php echo date('Y'); ?></p>
                    </div>
                    <div class="col-12 col-sm-8 col-md-6">
                        <p class="fs--1 my-2 text-center text-md-end"> 
                            Made with&nbsp;
                            <svg class="bi bi-suit-heart-fill" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="#EB6453" viewBox="0 0 16 16">
                                <path d="M4 1c2.21 0 4 1.755 4 3.92C8 2.755 9.79 1 12 1s4 1.755 4 3.92c0 3.263-3.234 4.414-7.608 9.608a.513.513 0 0 1-.784 0C3.234 9.334 0 8.183 0 4.92 0 2.755 1.79 1 4 1z"></path>
                            </svg>
                            &nbsp;for our furry friends
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- JavaScripts -->
    <script src="vendors/@popperjs/popper.min.js"></script>
    <script src="vendors/bootstrap/bootstrap.min.js"></script>
    <script src="vendors/is/is.min.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>
    <script src="vendors/feather-icons/feather.min.js"></script>
    <script>
        feather.replace();
        
        // Real-time password confirmation validation
        document.querySelectorAll('input[name="new_password"], input[name="confirm_password"]').forEach(input => {
            input.addEventListener('input', function() {
                const newPassword = document.querySelector('input[name="new_password"]').value;
                const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
                const passwordError = document.getElementById('passwordError');
                
                if (confirmPassword && newPassword !== confirmPassword) {
                    document.querySelector('input[name="confirm_password"]').classList.add('is-invalid');
                    passwordError.style.display = 'block';
                } else {
                    document.querySelector('input[name="confirm_password"]').classList.remove('is-invalid');
                    passwordError.style.display = 'none';
                }
            });
        });
        
        // Clear validation when modal is hidden
        document.getElementById('changePasswordModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('changePasswordForm').reset();
            document.querySelector('input[name="confirm_password"]').classList.remove('is-invalid');
            document.getElementById('passwordError').style.display = 'none';
        });
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
</body>
</html>