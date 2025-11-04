<?php
session_start();
require_once 'connection.php';

// Only add this if you need basic protection
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Optional: Get admin name for display
$admin_name = $_SESSION['fullname'] ?? 'Administrator';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new user
    if (isset($_POST['add_user'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $role = trim($_POST['role']);
        $password = trim($_POST['password']);
        $username = trim($_POST['username']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, phone, role, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $username, $email, $phone, $role, $password]);
            
            $_SESSION['success'] = "User created successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error creating user: " . $e->getMessage();
        }
    }
    
    // Delete user
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $_SESSION['success'] = "User deleted successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
        }
    }
    
    // Update user
    if (isset($_POST['update_user'])) {
        $user_id = $_POST['user_id'];
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $role = trim($_POST['role']);
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $email, $phone, $role, $user_id]);
            
            $_SESSION['success'] = "User updated successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating user: " . $e->getMessage();
        }
    }
    
    // Add medical report
    if (isset($_POST['add_report'])) {
        $patient_id = $_POST['patient_id'];
        $vet_id = $_POST['vet_id'];
        $report_date = $_POST['report_date'];
        $service_type = trim($_POST['service_type']);
        $status = trim($_POST['status']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO reports (patient_id, vet_id, report_date, service_type, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $vet_id, $report_date, $service_type, $status]);
            
            $_SESSION['success'] = "Medical report added successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding report: " . $e->getMessage();
        }
    }
    
    // Delete report
    if (isset($_POST['delete_report'])) {
        $report_id = $_POST['report_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM reports WHERE report_id = ?");
            $stmt->execute([$report_id]);
            
            $_SESSION['success'] = "Report deleted successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting report: " . $e->getMessage();
        }
    }
    
    // Update report
    if (isset($_POST['update_report'])) {
        $report_id = $_POST['report_id'];
        $patient_id = $_POST['patient_id'];
        $vet_id = $_POST['vet_id'];
        $report_date = $_POST['report_date'];
        $service_type = trim($_POST['service_type']);
        $status = trim($_POST['status']);
        
        try {
            $stmt = $pdo->prepare("UPDATE reports SET patient_id = ?, vet_id = ?, report_date = ?, service_type = ?, status = ? WHERE report_id = ?");
            $stmt->execute([$patient_id, $vet_id, $report_date, $service_type, $status, $report_id]);
            
            $_SESSION['success'] = "Report updated successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating report: " . $e->getMessage();
        }
    }
    
    // Add appointment
    if (isset($_POST['add_appointment'])) {
        $pet_name = trim($_POST['pet_name']);
        $species = trim($_POST['species']);
        $breed = trim($_POST['breed']);
        $gender = trim($_POST['gender']);
        $service_type = trim($_POST['service_type']);
        $pet_age = trim($_POST['pet_age']);
        $notes = trim($_POST['notes']);
        $patient_id = $_POST['patient_id'];
        $vet_id = $_POST['vet_id'];
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];
        $reason = trim($_POST['reason']);
        $status = trim($_POST['status']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (pet_name, species, breed, gender, service_type, pet_age, notes, patient_id, vet_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$pet_name, $species, $breed, $gender, $service_type, $pet_age, $notes, $patient_id, $vet_id, $appointment_date, $appointment_time, $reason, $status]);
            
            $_SESSION['success'] = "Appointment scheduled successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error scheduling appointment: " . $e->getMessage();
        }
    }
    
    // Delete appointment
    if (isset($_POST['delete_appointment'])) {
        $appointment_id = $_POST['appointment_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = ?");
            $stmt->execute([$appointment_id]);
            
            $_SESSION['success'] = "Appointment deleted successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting appointment: " . $e->getMessage();
        }
    }
    
    // Update appointment
    if (isset($_POST['update_appointment'])) {
        $appointment_id = $_POST['appointment_id'];
        $pet_name = trim($_POST['pet_name']);
        $species = trim($_POST['species']);
        $breed = trim($_POST['breed']);
        $gender = trim($_POST['gender']);
        $service_type = trim($_POST['service_type']);
        $pet_age = trim($_POST['pet_age']);
        $notes = trim($_POST['notes']);
        $patient_id = $_POST['patient_id'];
        $vet_id = $_POST['vet_id'];
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];
        $reason = trim($_POST['reason']);
        $status = trim($_POST['status']);
        
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET pet_name = ?, species = ?, breed = ?, gender = ?, service_type = ?, pet_age = ?, notes = ?, patient_id = ?, vet_id = ?, appointment_date = ?, appointment_time = ?, reason = ?, status = ? WHERE appointment_id = ?");
            $stmt->execute([$pet_name, $species, $breed, $gender, $service_type, $pet_age, $notes, $patient_id, $vet_id, $appointment_date, $appointment_time, $reason, $status, $appointment_id]);
            
            $_SESSION['success'] = "Appointment updated successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating appointment: " . $e->getMessage();
        }
    }
    
    // Update treatment record
    if (isset($_POST['update_treatment'])) {
        $record_id = $_POST['record_id'];
        $patient_name = trim($_POST['patient_name']);
        $treatment_date = $_POST['treatment_date'];
        $procedure = trim($_POST['procedure']);
        $medications = trim($_POST['medications']);
        $treatment_details = trim($_POST['treatment_details']);
        $followup_date = $_POST['followup_date'];
        $vet_id = $_POST['vet_id'];
        
        try {
            $stmt = $pdo->prepare("UPDATE treatment SET patient_name = ?, treatment_date = ?, procedure = ?, medications = ?, treatment_details = ?, followup_date = ?, vet_id = ? WHERE record_id = ?");
            $stmt->execute([$patient_name, $treatment_date, $procedure, $medications, $treatment_details, $followup_date, $vet_id, $record_id]);
            
            $_SESSION['success'] = "Treatment record updated successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating treatment record: " . $e->getMessage();
        }
    }
    
    // Delete treatment record
    if (isset($_POST['delete_treatment'])) {
        $record_id = $_POST['record_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM treatment WHERE record_id = ?");
            $stmt->execute([$record_id]);
            
            $_SESSION['success'] = "Treatment record deleted successfully!";
            header("Location: admin.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting treatment record: " . $e->getMessage();
        }
    }
}

// Fetch data from database
try {
    // Fetch users
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY user_id DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch medical reports with patient and vet information
    $stmt = $pdo->prepare("
        SELECT r.*, u1.full_name as vet_name, u2.full_name as patient_name 
        FROM reports r 
        LEFT JOIN users u1 ON r.vet_id = u1.user_id 
        LEFT JOIN users u2 ON r.patient_id = u2.user_id 
        ORDER BY r.report_id DESC
    ");
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch appointments with patient and vet information
    $stmt = $pdo->prepare("
        SELECT a.*, u1.full_name as vet_name, u2.full_name as patient_name 
        FROM appointments a 
        LEFT JOIN users u1 ON a.vet_id = u1.user_id 
        LEFT JOIN users u2 ON a.patient_id = u2.user_id 
        ORDER BY a.appointment_id DESC
    ");
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch treatment records with vet information
    $stmt = $pdo->prepare("
        SELECT t.*, u.full_name as vet_name 
        FROM treatment t 
        LEFT JOIN users u ON t.vet_id = u.user_id 
        ORDER BY t.record_id DESC
    ");
    $stmt->execute();
    $treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch patients and vets for dropdowns
    $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE role = 'owner'");
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE role = 'vet'");
    $stmt->execute();
    $vets = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Initialize empty arrays if tables don't exist yet
    $users = $reports = $appointments = $treatments = $patients = $vets = [];
    error_log("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en-US" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Pawprint Haven</title>

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
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 12px;
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
    
    /* Status Badge Base Styles */
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
        border: 1px solid;
    }
    
    /* Individual status colors - Only completed remains for reports */
    .status-badge-completed {
        background-color: #d4edda !important;
        color: #155724 !important;
        border-color: #c3e6cb !important;
    }
    .status-badge-cancelled {
        background-color: #f8d7da !important;
        color: #721c24 !important;
        border-color: #f5c6cb !important;
    }
    
    .hover-top:hover {
        transform: translateY(-2px);
        transition: transform 0.2s;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
    .user-role-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: bold;
        display: inline-block;
    }
    .role-admin { background-color: #dc3545; color: white; }
    .role-vet { background-color: #17a2b8; color: white; }
    .role-owner { background-color: #28a745; color: white; }
</style>
</head>

<body>
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
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
                            <a class="nav-link fw-medium active" aria-current="page" href="#dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="#manage-users">Manage Users</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="#manage-reports">Manage Reports</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="#manage-appointments">Manage Appointments</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="#treatment-records">Treatment Records</a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link" href="adminprofile.php">Profile</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container mt-4" style="margin-top: 100px !important;">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="container mt-4" style="margin-top: 100px !important;">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Dashboard Section -->
        <section id="dashboard" class="py-8" style="margin-top: 100px;">
            <div class="bg-holder" style="background-image:url(assets/img/illustrations/background.png);background-position:center;background-size:cover;">
            </div>

            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="fw-bold display-4 text-gradient mb-3">Admin Dashboard</h1>
                        <p class="text-600 mb-4">Hello, <?php echo htmlspecialchars($admin_name); ?>! Manage all aspects of Pawprint Haven Veterinary Clinic</p>
                        
                        <!-- Quick Stats -->
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="card card-bg p-3 text-center">
                                    <h4 class="text-gradient fw-bold"><?php echo count($users); ?></h4>
                                    <p class="text-600 mb-0">Total Users</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card card-bg p-3 text-center">
                                    <h4 class="text-gradient fw-bold"><?php echo count($appointments); ?></h4>
                                    <p class="text-600 mb-0">Total Appointments</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card card-bg p-3 text-center">
                                    <h4 class="text-gradient fw-bold"><?php echo count($reports); ?></h4>
                                    <p class="text-600 mb-0">Total Reports</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Manage Users Section -->
        <section id="manage-users" class="py-7">
            <div class="container">
                <div class="row justify-content-center mb-6">
                    <div class="col-lg-10 text-center">
                        <h2 class="fw-bold mb-3">Manage Users</h2>
                        <p class="text-600">Add, edit, and manage all user accounts</p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="fw-bold">User Accounts</h4>
                            <button class="btn hover-top btn-glow btn-klean rounded-pill" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-plus me-2"></i>Add New User
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card card-bg">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User ID</th>
                                                <th>Username</th>
                                                <th>Full Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Phone</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>#<?php echo $user['user_id']; ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="user-role-badge role-<?php echo $user['role']; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-klean me-1" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['user_id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-sm btn-outline-klean">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Manage Reports Section -->
        <section id="manage-reports" class="py-7 bg-light">
            <div class="container">
                <div class="row justify-content-center mb-6">
                    <div class="col-lg-10 text-center">
                        <h2 class="fw-bold mb-3">Manage Medical Reports</h2>
                        <p class="text-600">View, edit, and manage all medical reports</p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="fw-bold">Medical Reports</h4>
                            <button class="btn hover-top btn-glow btn-klean rounded-pill" data-bs-toggle="modal" data-bs-target="#addReportModal">
                                <i class="fas fa-plus me-2"></i>Add New Report
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card card-bg">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Report ID</th>
                                                <th>Patient</th>
                                                <th>Veterinarian</th>
                                                <th>Date</th>
                                                <th>Service Type</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reports as $report): ?>
                                            <tr>
                                                <td>#<?php echo $report['report_id']; ?></td>
                                                <td><?php echo htmlspecialchars($report['patient_name'] ?? 'Patient #' . $report['patient_id']); ?></td>
                                                <td><?php echo htmlspecialchars($report['vet_name'] ?? 'Vet #' . $report['vet_id']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($report['report_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($report['service_type']); ?></td>
                                                <td>
                                                    <span class="status-badge status-badge-<?php echo $report['status']; ?>">
                                                        <?php echo ucfirst($report['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-klean me-1" data-bs-toggle="modal" data-bs-target="#editReportModal<?php echo $report['report_id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this report?')">
                                                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                                        <button type="submit" name="delete_report" class="btn btn-sm btn-outline-klean">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Manage Appointments Section -->
        <section id="manage-appointments" class="py-7">
            <div class="container">
                <div class="row justify-content-center mb-6">
                    <div class="col-lg-10 text-center">
                        <h2 class="fw-bold mb-3">Manage Appointments</h2>
                        <p class="text-600">Schedule, edit, and manage all appointments</p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="fw-bold">Appointment Schedule</h4>
                            <button class="btn hover-top btn-glow btn-klean rounded-pill" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
                                <i class="fas fa-plus me-2"></i>Add New Appointment
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card card-bg">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Appointment ID</th>
                                                <th>Date & Time</th>
                                                <th>Pet Name</th>
                                                <th>Species/Breed</th>
                                                <th>Service Type</th>
                                                <th>Veterinarian</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($appointments as $appointment): ?>
                                            <tr>
                                                <td>#<?php echo $appointment['appointment_id']; ?></td>
                                                <td><?php echo date('M d, Y - g:i A', strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['pet_name']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['species'] . ' / ' . $appointment['breed']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['service_type']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['vet_name'] ?? 'Vet #' . $appointment['vet_id']); ?></td>
                                                <td>
                                                    <span class="status-badge status-badge-<?php echo $appointment['status']; ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-klean me-1" data-bs-toggle="modal" data-bs-target="#editAppointmentModal<?php echo $appointment['appointment_id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this appointment?')">
                                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                        <button type="submit" name="delete_appointment" class="btn btn-sm btn-outline-klean">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Treatment Records Section -->
        <section id="treatment-records" class="py-7 bg-light">
            <div class="container">
                <div class="row justify-content-center mb-6">
                    <div class="col-lg-10 text-center">
                        <h2 class="fw-bold mb-3">Treatment Records</h2>
                        <p class="text-600">View and manage all treatment records</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card card-bg">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Record ID</th>
                                                <th>Patient Name</th>
                                                <th>Treatment Date</th>
                                                <th>Procedure</th>
                                                <th>Medications</th>
                                                <th>Veterinarian</th>
                                                <th>Follow-up</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($treatments as $treatment): ?>
                                            <tr>
                                                <td>#<?php echo $treatment['record_id']; ?></td>
                                                <td><?php echo htmlspecialchars($treatment['patient_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($treatment['treatment_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($treatment['procedure']); ?></td>
                                                <td><?php echo htmlspecialchars($treatment['medications'] ?? 'None'); ?></td>
                                                <td><?php echo htmlspecialchars($treatment['vet_name'] ?? 'Vet #' . $treatment['vet_id']); ?></td>
                                                <td><?php echo $treatment['followup_date'] ? date('M d, Y', strtotime($treatment['followup_date'])) : 'None'; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-klean me-1" data-bs-toggle="modal" data-bs-target="#editTreatmentModal<?php echo $treatment['record_id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this treatment record?')">
                                                        <input type="hidden" name="record_id" value="<?php echo $treatment['record_id']; ?>">
                                                        <button type="submit" name="delete_treatment" class="btn btn-sm btn-outline-klean">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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
                        <p class="fs--1 my-2 fw-bold">All rights Reserved &copy; Pawprint Haven, 202x</p>
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

    <!-- Modals -->

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control rounded-pill" name="full_name" placeholder="Enter full name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control rounded-pill" name="username" placeholder="Enter username" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control rounded-pill" name="email" placeholder="Enter email address" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="tel" class="form-control rounded-pill" name="phone" placeholder="Enter phone number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">User Role</label>
                                <select class="form-select rounded-pill" name="role" required>
                                    <option value="">Select role...</option>
                                    <option value="admin">Administrator</option>
                                    <option value="vet">Veterinarian</option>
                                    <option value="owner">Pet Owner</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" class="form-control rounded-pill" name="password" placeholder="Set password" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn hover-top btn-glow btn-klean rounded-pill">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Report Modal -->
    <div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient" id="addReportModalLabel">Add New Medical Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Patient</label>
                                <select class="form-select rounded-pill" name="patient_id" required>
                                    <option value="">Select patient...</option>
                                    <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['user_id']; ?>"><?php echo htmlspecialchars($patient['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Veterinarian</label>
                                <select class="form-select rounded-pill" name="vet_id" required>
                                    <option value="">Select veterinarian...</option>
                                    <?php foreach ($vets as $vet): ?>
                                    <option value="<?php echo $vet['user_id']; ?>"><?php echo htmlspecialchars($vet['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Report Date</label>
                                <input type="date" class="form-control rounded-pill" name="report_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Service Type</label>
                                <input type="text" class="form-control rounded-pill" name="service_type" placeholder="Enter service type" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select rounded-pill" name="status" required>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_report" class="btn hover-top btn-glow btn-klean rounded-pill">Save Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Appointment Modal -->
    <div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient" id="addAppointmentModalLabel">Add New Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Pet Name</label>
                                <input type="text" class="form-control rounded-pill" name="pet_name" placeholder="Enter pet name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Species</label>
                                <input type="text" class="form-control rounded-pill" name="species" placeholder="Enter species" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Breed</label>
                                <input type="text" class="form-control rounded-pill" name="breed" placeholder="Enter breed" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Gender</label>
                                <select class="form-select rounded-pill" name="gender" required>
                                    <option value="">Select gender...</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Service Type</label>
                                <input type="text" class="form-control rounded-pill" name="service_type" placeholder="Enter service type" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Pet Age</label>
                                <input type="text" class="form-control rounded-pill" name="pet_age" placeholder="Enter pet age">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Patient (Owner)</label>
                                <select class="form-select rounded-pill" name="patient_id" required>
                                    <option value="">Select patient...</option>
                                    <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['user_id']; ?>"><?php echo htmlspecialchars($patient['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Veterinarian</label>
                                <select class="form-select rounded-pill" name="vet_id" required>
                                    <option value="">Select veterinarian...</option>
                                    <?php foreach ($vets as $vet): ?>
                                    <option value="<?php echo $vet['user_id']; ?>"><?php echo htmlspecialchars($vet['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Appointment Date</label>
                                <input type="date" class="form-control rounded-pill" name="appointment_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Appointment Time</label>
                                <input type="time" class="form-control rounded-pill" name="appointment_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason</label>
                            <textarea class="form-control rounded-3" name="reason" rows="2" placeholder="Enter reason for appointment"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea class="form-control rounded-3" name="notes" rows="2" placeholder="Additional notes"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select rounded-pill" name="status" required>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_appointment" class="btn hover-top btn-glow btn-klean rounded-pill">Schedule Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modals -->
    <?php foreach ($users as $user): ?>
    <div class="modal fade" id="editUserModal<?php echo $user['user_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control rounded-pill" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control rounded-pill" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="tel" class="form-control rounded-pill" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">User Role</label>
                                <select class="form-select rounded-pill" name="role" required>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                    <option value="vet" <?php echo $user['role'] == 'vet' ? 'selected' : ''; ?>>Veterinarian</option>
                                    <option value="owner" <?php echo $user['role'] == 'owner' ? 'selected' : ''; ?>>Pet Owner</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_user" class="btn hover-top btn-glow btn-klean rounded-pill">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Edit Report Modals -->
    <?php foreach ($reports as $report): ?>
    <div class="modal fade" id="editReportModal<?php echo $report['report_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient">Edit Medical Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Patient</label>
                                <select class="form-select rounded-pill" name="patient_id" required>
                                    <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['user_id']; ?>" <?php echo $report['patient_id'] == $patient['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($patient['full_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Veterinarian</label>
                                <select class="form-select rounded-pill" name="vet_id" required>
                                    <?php foreach ($vets as $vet): ?>
                                    <option value="<?php echo $vet['user_id']; ?>" <?php echo $report['vet_id'] == $vet['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($vet['full_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Report Date</label>
                                <input type="date" class="form-control rounded-pill" name="report_date" value="<?php echo $report['report_date']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Service Type</label>
                                <input type="text" class="form-control rounded-pill" name="service_type" value="<?php echo htmlspecialchars($report['service_type']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select rounded-pill" name="status" required>
                                <option value="completed" <?php echo $report['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_report" class="btn hover-top btn-glow btn-klean rounded-pill">Update Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Edit Appointment Modals -->
    <?php foreach ($appointments as $appointment): ?>
    <div class="modal fade" id="editAppointmentModal<?php echo $appointment['appointment_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient">Edit Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Pet Name</label>
                                <input type="text" class="form-control rounded-pill" name="pet_name" value="<?php echo htmlspecialchars($appointment['pet_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Species</label>
                                <input type="text" class="form-control rounded-pill" name="species" value="<?php echo htmlspecialchars($appointment['species']); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Breed</label>
                                <input type="text" class="form-control rounded-pill" name="breed" value="<?php echo htmlspecialchars($appointment['breed']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Gender</label>
                                <select class="form-select rounded-pill" name="gender" required>
                                    <option value="Male" <?php echo $appointment['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $appointment['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Service Type</label>
                                <input type="text" class="form-control rounded-pill" name="service_type" value="<?php echo htmlspecialchars($appointment['service_type']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Pet Age</label>
                                <input type="text" class="form-control rounded-pill" name="pet_age" value="<?php echo htmlspecialchars($appointment['pet_age']); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Patient (Owner)</label>
                                <select class="form-select rounded-pill" name="patient_id" required>
                                    <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['user_id']; ?>" <?php echo $appointment['patient_id'] == $patient['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($patient['full_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Veterinarian</label>
                                <select class="form-select rounded-pill" name="vet_id" required>
                                    <?php foreach ($vets as $vet): ?>
                                    <option value="<?php echo $vet['user_id']; ?>" <?php echo $appointment['vet_id'] == $vet['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($vet['full_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Appointment Date</label>
                                <input type="date" class="form-control rounded-pill" name="appointment_date" value="<?php echo $appointment['appointment_date']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Appointment Time</label>
                                <input type="time" class="form-control rounded-pill" name="appointment_time" value="<?php echo $appointment['appointment_time']; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason</label>
                            <textarea class="form-control rounded-3" name="reason" rows="2"><?php echo htmlspecialchars($appointment['reason'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea class="form-control rounded-3" name="notes" rows="2"><?php echo htmlspecialchars($appointment['notes'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select rounded-pill" name="status" required>
                                <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_appointment" class="btn hover-top btn-glow btn-klean rounded-pill">Update Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Edit Treatment Modals -->
    <?php foreach ($treatments as $treatment): ?>
    <div class="modal fade" id="editTreatmentModal<?php echo $treatment['record_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient">Edit Treatment Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="record_id" value="<?php echo $treatment['record_id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Patient Name</label>
                                <input type="text" class="form-control rounded-pill" name="patient_name" value="<?php echo htmlspecialchars($treatment['patient_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Treatment Date</label>
                                <input type="date" class="form-control rounded-pill" name="treatment_date" value="<?php echo $treatment['treatment_date']; ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Procedure</label>
                                <input type="text" class="form-control rounded-pill" name="procedure" value="<?php echo htmlspecialchars($treatment['procedure']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Medications</label>
                                <input type="text" class="form-control rounded-pill" name="medications" value="<?php echo htmlspecialchars($treatment['medications'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Veterinarian</label>
                                <select class="form-select rounded-pill" name="vet_id" required>
                                    <?php foreach ($vets as $vet): ?>
                                    <option value="<?php echo $vet['user_id']; ?>" <?php echo $treatment['vet_id'] == $vet['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($vet['full_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Follow-up Date</label>
                                <input type="date" class="form-control rounded-pill" name="followup_date" value="<?php echo $treatment['followup_date'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Treatment Details</label>
                            <textarea class="form-control rounded-3" name="treatment_details" rows="3"><?php echo htmlspecialchars($treatment['treatment_details'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_treatment" class="btn hover-top btn-glow btn-klean rounded-pill">Update Treatment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- JavaScripts -->
    <script src="vendors/@popperjs/popper.min.js"></script>
    <script src="vendors/bootstrap/bootstrap.min.js"></script>
    <script src="vendors/is/is.min.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>
    <script src="vendors/feather-icons/feather.min.js"></script>
    <script>
        feather.replace();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
</body>
</html>