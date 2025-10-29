<?php
session_start();
require_once 'connection.php';

// Check if vet is logged in
if (!isset($_SESSION['vet_id'])) {
    header("Location: login.php");
    exit();
}

$vet_id = $_SESSION['vet_id'];
$username = $_SESSION['username'];

// Get user data for display
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$vet_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get dashboard statistics
$today = date('Y-m-d');
$this_month = date('Y-m');

// Today's appointments count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE vet_id = ? AND appointment_date = ?");
$stmt->execute([$vet_id, $today]);
$today_appointments = $stmt->fetchColumn();

// Follow-ups needed count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM treatment WHERE vet_id = ? AND followup_date IS NOT NULL AND followup_date >= CURDATE()");
$stmt->execute([$vet_id]);
$followups_needed = $stmt->fetchColumn();

// Get recent reports
$stmt = $pdo->prepare("
    SELECT r.*, a.pet_name, a.species as animal_type 
    FROM reports r 
    JOIN appointments a ON r.patient_id = a.patient_id AND r.vet_id = a.vet_id
    WHERE r.vet_id = ? 
    ORDER BY r.report_date DESC 
    LIMIT 10
");
$stmt->execute([$vet_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get appointments
$stmt = $pdo->prepare("
    SELECT * FROM appointments 
    WHERE vet_id = ? 
    ORDER BY appointment_date DESC 
    LIMIT 10
");
$stmt->execute([$vet_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get treatments
$stmt = $pdo->prepare("
    SELECT * FROM treatment 
    WHERE vet_id = ? 
    ORDER BY treatment_date DESC 
    LIMIT 10
");
$stmt->execute([$vet_id]);
$treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patients for dropdowns - UPDATED: Now fetching from patients table
$stmt = $pdo->prepare("
    SELECT patient_id, name as pet_name, species, breed, gender, age 
    FROM patients 
    WHERE user_id IN (SELECT user_id FROM appointments WHERE vet_id = ?)
    ORDER BY name
");
$stmt->execute([$vet_id]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_report'])) {
        // Handle add report form
        $patient_id = $_POST['patient_id']; // Changed to patient_id
        $report_date = $_POST['report_date'];
        $service_type = $_POST['service_type'];
        $diagnosis = $_POST['diagnosis'];
        $treatment_given = $_POST['treatment_given'];
        $medications = $_POST['medications'];
        $followup_date = $_POST['followup_date'] ?: null;
        $notes = $_POST['notes'];
        
        try {
            // First, let's check what columns actually exist in reports table
            $check_columns = $pdo->prepare("SHOW COLUMNS FROM reports");
            $check_columns->execute();
            $existing_columns = $check_columns->fetchAll(PDO::FETCH_COLUMN);
            
            // Build dynamic query based on actual columns
            $columns = ['vet_id', 'status'];
            $values = [$vet_id, 'Completed'];
            $placeholders = ['?', '?'];
            
            // Add patient info - UPDATED: Use patient_id instead of patient_name
            if (in_array('patient_id', $existing_columns)) {
                $columns[] = 'patient_id';
                $values[] = $patient_id;
                $placeholders[] = '?';
            } elseif (in_array('patient_name', $existing_columns)) {
                // If patient_name exists, get the patient name from patients table
                $stmt = $pdo->prepare("SELECT name FROM patients WHERE patient_id = ?");
                $stmt->execute([$patient_id]);
                $patient = $stmt->fetch();
                if ($patient) {
                    $columns[] = 'patient_name';
                    $values[] = $patient['name'];
                    $placeholders[] = '?';
                }
            }
            
            // Add other fields if they exist
            if (in_array('report_date', $existing_columns)) {
                $columns[] = 'report_date';
                $values[] = $report_date;
                $placeholders[] = '?';
            }
            
            if (in_array('service_type', $existing_columns)) {
                $columns[] = 'service_type';
                $values[] = $service_type;
                $placeholders[] = '?';
            }
            
            if (in_array('diagnosis', $existing_columns)) {
                $columns[] = 'diagnosis';
                $values[] = $diagnosis;
                $placeholders[] = '?';
            }
            
            // Try different names for treatment given
            if (in_array('treatment_given', $existing_columns)) {
                $columns[] = 'treatment_given';
                $values[] = $treatment_given;
                $placeholders[] = '?';
            } elseif (in_array('treatment_plan', $existing_columns)) {
                $columns[] = 'treatment_plan';
                $values[] = $treatment_given;
                $placeholders[] = '?';
            } elseif (in_array('treatment_details', $existing_columns)) {
                $columns[] = 'treatment_details';
                $values[] = $treatment_given;
                $placeholders[] = '?';
            }
            
            if (in_array('medications', $existing_columns)) {
                $columns[] = 'medications';
                $values[] = $medications;
                $placeholders[] = '?';
            }
            
            if (in_array('followup_date', $existing_columns)) {
                $columns[] = 'followup_date';
                $values[] = $followup_date;
                $placeholders[] = '?';
            }
            
            // Try different names for notes
            if (in_array('notes', $existing_columns)) {
                $columns[] = 'notes';
                $values[] = $notes;
                $placeholders[] = '?';
            } elseif (in_array('vet_notes', $existing_columns)) {
                $columns[] = 'vet_notes';
                $values[] = $notes;
                $placeholders[] = '?';
            } elseif (in_array('clinical_notes', $existing_columns)) {
                $columns[] = 'clinical_notes';
                $values[] = $notes;
                $placeholders[] = '?';
            }
            
            // Build and execute the query
            $query = "INSERT INTO reports (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($values);
            
            $_SESSION['success'] = "Medical report added successfully!";
            header("Location: vet.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding report. Please check if the reports table exists and has the correct structure.";
            header("Location: vet.php");
            exit();
        }
    }
    
    if (isset($_POST['add_treatment'])) {
        // Handle add treatment form
        $patient_id = $_POST['patient_id']; // Changed to patient_id
        $treatment_date = $_POST['treatment_date'];
        $procedure = $_POST['procedure'];
        $medications = $_POST['medications'];
        $treatment_details = $_POST['treatment_details'];
        $followup_date = $_POST['followup_date'] ?: null;
        
        try {
            // Get patient name for treatment record
            $stmt = $pdo->prepare("SELECT name FROM patients WHERE patient_id = ?");
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch();
            $patient_name = $patient ? $patient['name'] : 'Unknown';
            
            $stmt = $pdo->prepare("INSERT INTO treatment (patient_name, treatment_date, `procedure`, medications, treatment_details, followup_date, vet_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient_name, $treatment_date, $procedure, $medications, $treatment_details, $followup_date, $vet_id]);
            
            $_SESSION['success'] = "Treatment record added successfully!";
            header("Location: vet.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding treatment: " . $e->getMessage();
            header("Location: vet.php");
            exit();
        }
    }
    
    // Handle edit report
    if (isset($_POST['edit_report'])) {
        $report_id = $_POST['report_id'];
        $patient_id = $_POST['patient_id']; // Changed to patient_id
        $report_date = $_POST['report_date'];
        $service_type = $_POST['service_type'];
        $diagnosis = $_POST['diagnosis'];
        $treatment_given = $_POST['treatment_given'];
        $medications = $_POST['medications'];
        $followup_date = $_POST['followup_date'] ?: null;
        $notes = $_POST['notes'];
        
        try {
            // Get patient name for report
            $stmt = $pdo->prepare("SELECT name FROM patients WHERE patient_id = ?");
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch();
            $patient_name = $patient ? $patient['name'] : 'Unknown';
            
            $stmt = $pdo->prepare("UPDATE reports SET patient_name = ?, report_date = ?, service_type = ?, diagnosis = ?, treatment_given = ?, medications = ?, followup_date = ?, notes = ? WHERE report_id = ? AND vet_id = ?");
            $stmt->execute([$patient_name, $report_date, $service_type, $diagnosis, $treatment_given, $medications, $followup_date, $notes, $report_id, $vet_id]);
            
            $_SESSION['success'] = "Medical report updated successfully!";
            header("Location: vet.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating report: " . $e->getMessage();
            header("Location: vet.php");
            exit();
        }
    }
    
    // Handle edit treatment
    if (isset($_POST['edit_treatment'])) {
        $record_id = $_POST['record_id'];
        $patient_id = $_POST['patient_id']; // Changed to patient_id
        $treatment_date = $_POST['treatment_date'];
        $procedure = $_POST['procedure'];
        $medications = $_POST['medications'];
        $treatment_details = $_POST['treatment_details'];
        $followup_date = $_POST['followup_date'] ?: null;
        
        try {
            // Get patient name for treatment record
            $stmt = $pdo->prepare("SELECT name FROM patients WHERE patient_id = ?");
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch();
            $patient_name = $patient ? $patient['name'] : 'Unknown';
            
            $stmt = $pdo->prepare("UPDATE treatment SET patient_name = ?, treatment_date = ?, `procedure` = ?, medications = ?, treatment_details = ?, followup_date = ? WHERE record_id = ? AND vet_id = ?");
            $stmt->execute([$patient_name, $treatment_date, $procedure, $medications, $treatment_details, $followup_date, $record_id, $vet_id]);
            
            $_SESSION['success'] = "Treatment record updated successfully!";
            header("Location: vet.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating treatment: " . $e->getMessage();
            header("Location: vet.php");
            exit();
        }
    }
    
    // Handle delete report
    if (isset($_POST['delete_report'])) {
        $report_id = $_POST['report_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM reports WHERE report_id = ? AND vet_id = ?");
            $stmt->execute([$report_id, $vet_id]);
            
            $_SESSION['success'] = "Medical report deleted successfully!";
            header("Location: vet.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting report: " . $e->getMessage();
            header("Location: vet.php");
            exit();
        }
    }
}

// Handle GET requests for delete and edit
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Delete report via GET
    if (isset($_GET['delete_report'])) {
        $report_id = $_GET['delete_report'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM reports WHERE report_id = ? AND vet_id = ?");
            $stmt->execute([$report_id, $vet_id]);
            
            $_SESSION['success'] = "Medical report deleted successfully!";
            header("Location: vet.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting report: " . $e->getMessage();
            header("Location: vet.php");
            exit();
        }
    }
    
    // Get report data for editing
    if (isset($_GET['edit_report'])) {
        $report_id = $_GET['edit_report'];
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE report_id = ? AND vet_id = ?");
        $stmt->execute([$report_id, $vet_id]);
        $edit_report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get patient_id for the report
        if ($edit_report && isset($edit_report['patient_name'])) {
            $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE name = ? LIMIT 1");
            $stmt->execute([$edit_report['patient_name']]);
            $patient = $stmt->fetch();
            if ($patient) {
                $edit_report['patient_id'] = $patient['patient_id'];
            }
        }
    }
    
    // Get treatment data for editing
    if (isset($_GET['edit_treatment'])) {
        $record_id = $_GET['edit_treatment'];
        $stmt = $pdo->prepare("SELECT * FROM treatment WHERE record_id = ? AND vet_id = ?");
        $stmt->execute([$record_id, $vet_id]);
        $edit_treatment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get patient_id for the treatment
        if ($edit_treatment && isset($edit_treatment['patient_name'])) {
            $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE name = ? LIMIT 1");
            $stmt->execute([$edit_treatment['patient_name']]);
            $patient = $stmt->fetch();
            if ($patient) {
                $edit_treatment['patient_id'] = $patient['patient_id'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en-US" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veterinary Dashboard - Pawprint Haven</title>

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
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-confirmed {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .hover-top:hover {
            transform: translateY(-2px);
            transition: transform 0.2s;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        .btn-danger-klean {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3 d-block navbar-klean" data-navbar-on-scroll="data-navbar-on-scroll">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="vet.php"> 
                <img class="me-3 d-inline-block" src="assets/img/gallery/logo.png" alt="" style="height: 65px;" />
            </a>
            <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse border-top border-lg-0 mt-4 mt-lg-0" id="navbarSupportedContent">
                <!-- Vet Navigation Menu -->
                <ul class="navbar-nav me-auto pt-2 pt-lg-0 font-base">
                    <li class="nav-item px-2">
                        <a class="nav-link fw-medium active" aria-current="page" href="#dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="#manage-reports">Manage Reports</a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="#view-appointments">View Appointments</a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="#treatment-records">Treatment Records</a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="profilevet.php">Profile</a>
                    </li>
                </ul>
                <!-- Display user name -->
                <div class="navbar-text ms-3">
                    <span class="text-600">Welcome, Dr. <strong><?php echo htmlspecialchars($user['full_name'] ?? $username); ?></strong></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main" id="top">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Section -->
        <section id="dashboard" class="py-8" style="margin-top: 100px;">
            <div class="bg-holder" style="background-image:url(assets/img/illustrations/background.png);background-position:center;background-size:cover;">
            </div>

            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="fw-bold display-4 text-gradient mb-3">Welcome, Dr. <?php echo htmlspecialchars($user['full_name'] ?? $username); ?>!</h1>
                        <p class="text-600 mb-4">Manage your veterinary practice and patient care</p>
                        
                        <!-- Quick Stats -->
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="card card-bg p-3 text-center">
                                    <h4 class="text-gradient fw-bold"><?php echo $today_appointments; ?></h4>
                                    <p class="text-600 mb-0">Today's Appointments</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card card-bg p-3 text-center">
                                    <h4 class="text-gradient fw-bold"><?php echo $followups_needed; ?></h4>
                                    <p class="text-600 mb-0">Follow-ups Needed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Manage Reports Section -->
        <section id="manage-reports" class="py-7">
            <div class="container">
                <div class="row justify-content-center mb-6">
                    <div class="col-lg-10 text-center">
                        <h2 class="fw-bold mb-3">Manage Medical Reports</h2>
                        <p class="text-600">Create, update, and manage patient medical reports</p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="fw-bold">Patient Reports</h4>
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
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($reports)): ?>
                                                <?php foreach ($reports as $report): ?>
                                                <tr>
                                                    <td>#RPT-<?php echo str_pad($report['report_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                                    <td><?php echo htmlspecialchars($report['pet_name'] ?? $report['patient_name'] ?? 'Unknown'); ?> (<?php echo htmlspecialchars($report['animal_type'] ?? 'Unknown'); ?>)</td>
                                                    <td><?php echo date('M j, Y', strtotime($report['report_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($report['service_type'] ?? 'Unknown'); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo strtolower($report['status'] ?? 'pending'); ?>">
                                                            <?php echo ucfirst($report['status'] ?? 'Pending'); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-klean me-1" onclick="viewReport(<?php echo $report['report_id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-klean me-1" onclick="editReport(<?php echo $report['report_id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger-klean" onclick="confirmDelete(<?php echo $report['report_id']; ?>, 'report')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-file-medical fa-3x mb-3"></i>
                                                        <p>No medical reports found. Create your first report using the "Add New Report" button.</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- View Appointments Section -->
        <section id="view-appointments" class="py-7 bg-light">
            <div class="container">
                <div class="row justify-content-center mb-6">
                    <div class="col-lg-10 text-center">
                        <h2 class="fw-bold mb-3">View Appointments</h2>
                        <p class="text-600">Manage and view your scheduled appointments</p>
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
                                                <th>Patient</th>
                                                <th>Service</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($appointments as $appointment): ?>
                                            <tr>
                                                <td>#APT-<?php echo str_pad($appointment['appointment_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                                <td>
                                                    <?php echo date('M j, Y - g:i A', strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time'])); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($appointment['pet_name']); ?> (<?php echo htmlspecialchars($appointment['species']); ?>)</td>
                                                <td><?php echo htmlspecialchars($appointment['service_type']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo strtolower($appointment['status']); ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-klean me-1">
                                                        <i class="fas fa-info-circle"></i> Details
                                                    </button>
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
        <section id="treatment-records" class="py-7">
            <div class="container">
                <div class="row justify-content-center mb-6">
                    <div class="col-lg-10 text-center">
                        <h2 class="fw-bold mb-3">Treatment Records & Notes</h2>
                        <p class="text-600">Document treatments, procedures, and clinical notes</p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="fw-bold">Patient Treatments</h4>
                            <button class="btn hover-top btn-glow btn-klean rounded-pill" data-bs-toggle="modal" data-bs-target="#addTreatmentModal">
                                <i class="fas fa-plus me-2"></i>Add Treatment Record
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
                                                <th>Record ID</th>
                                                <th>Patient</th>
                                                <th>Treatment Date</th>
                                                <th>Procedure</th>
                                                <th>Medications</th>
                                                <th>Follow-up</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($treatments as $treatment): ?>
                                            <tr>
                                                <td>#TRT-<?php echo str_pad($treatment['record_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                                <td><?php echo htmlspecialchars($treatment['patient_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($treatment['treatment_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($treatment['procedure']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($treatment['medications'] ?? '', 0, 50)) . '...'; ?></td>
                                                <td>
                                                    <?php if ($treatment['followup_date']): ?>
                                                        <?php echo date('M j, Y', strtotime($treatment['followup_date'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-klean me-1">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-klean me-1" onclick="editTreatment(<?php echo $treatment['record_id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-klean">
                                                        <i class="fas fa-print"></i>
                                                    </button>
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
                                <label class="form-label fw-bold">Select Patient</label>
                                <select class="form-select rounded-pill" name="patient_id" required>
                                    <option value="">Choose patient...</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>">
                                            <?php echo htmlspecialchars($patient['pet_name'] . ' (' . $patient['species'] . ' - ' . $patient['breed'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Report Date</label>
                                <input type="date" class="form-control rounded-pill" name="report_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Service Type</label>
                            <select class="form-select rounded-pill" name="service_type" required>
                                <option value="">Choose service type...</option>
                                <option>Annual Checkup</option>
                                <option>Vaccination</option>
                                <option>Dental Procedure</option>
                                <option>Surgery</option>
                                <option>Lab Test</option>
                                <option>Emergency</option>
                                <option>Consultation</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Diagnosis</label>
                            <textarea class="form-control rounded-3" name="diagnosis" rows="2" placeholder="Enter diagnosis..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Treatment Given</label>
                            <textarea class="form-control rounded-3" name="treatment_given" rows="2" placeholder="Describe the treatment provided..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Medications Prescribed</label>
                            <textarea class="form-control rounded-3" name="medications" rows="2" placeholder="List medications with dosage..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Follow-up Date</label>
                                <input type="date" class="form-control rounded-pill" name="followup_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Veterinarian</label>
                                <input type="text" class="form-control rounded-pill" value="Dr. <?php echo htmlspecialchars($user['full_name'] ?? $username); ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Additional Notes</label>
                            <textarea class="form-control rounded-3" name="notes" rows="2" placeholder="Additional clinical notes and observations..."></textarea>
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

    <!-- Edit Report Modal -->
    <div class="modal fade" id="editReportModal" tabindex="-1" aria-labelledby="editReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient" id="editReportModalLabel">Edit Medical Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="report_id" id="edit_report_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Select Patient</label>
                                <select class="form-select rounded-pill" name="patient_id" id="edit_patient_id" required>
                                    <option value="">Choose patient...</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>">
                                            <?php echo htmlspecialchars($patient['pet_name'] . ' (' . $patient['species'] . ' - ' . $patient['breed'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Report Date</label>
                                <input type="date" class="form-control rounded-pill" name="report_date" id="edit_report_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Service Type</label>
                            <select class="form-select rounded-pill" name="service_type" id="edit_service_type" required>
                                <option value="">Choose service type...</option>
                                <option>Annual Checkup</option>
                                <option>Vaccination</option>
                                <option>Dental Procedure</option>
                                <option>Surgery</option>
                                <option>Lab Test</option>
                                <option>Emergency</option>
                                <option>Consultation</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Diagnosis</label>
                            <textarea class="form-control rounded-3" name="diagnosis" id="edit_diagnosis" rows="2" placeholder="Enter diagnosis..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Treatment Given</label>
                            <textarea class="form-control rounded-3" name="treatment_given" id="edit_treatment_given" rows="2" placeholder="Describe the treatment provided..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Medications Prescribed</label>
                            <textarea class="form-control rounded-3" name="medications" id="edit_medications" rows="2" placeholder="List medications with dosage..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Follow-up Date</label>
                                <input type="date" class="form-control rounded-pill" name="followup_date" id="edit_followup_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Veterinarian</label>
                                <input type="text" class="form-control rounded-pill" value="Dr. <?php echo htmlspecialchars($user['full_name'] ?? $username); ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Additional Notes</label>
                            <textarea class="form-control rounded-3" name="notes" id="edit_notes" rows="2" placeholder="Additional clinical notes and observations..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_report" class="btn hover-top btn-glow btn-klean rounded-pill">Update Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Treatment Record Modal -->
    <div class="modal fade" id="addTreatmentModal" tabindex="-1" aria-labelledby="addTreatmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient" id="addTreatmentModalLabel">Add Treatment Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Select Patient</label>
                                <select class="form-select rounded-pill" name="patient_id" required>
                                    <option value="">Choose patient...</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>">
                                            <?php echo htmlspecialchars($patient['pet_name'] . ' (' . $patient['species'] . ' - ' . $patient['breed'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Treatment Date</label>
                                <input type="date" class="form-control rounded-pill" name="treatment_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Procedure/Service</label>
                            <input type="text" class="form-control rounded-pill" name="procedure" placeholder="Enter procedure name..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Medications Prescribed</label>
                            <textarea class="form-control rounded-3" name="medications" rows="2" placeholder="List medications with dosage..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Treatment Details</label>
                            <textarea class="form-control rounded-3" name="treatment_details" rows="3" placeholder="Describe the treatment procedure..." required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Follow-up Date</label>
                                <input type="date" class="form-control rounded-pill" name="followup_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Veterinarian</label>
                                <input type="text" class="form-control rounded-pill" value="Dr. <?php echo htmlspecialchars($user['full_name'] ?? $username); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_treatment" class="btn hover-top btn-glow btn-klean rounded-pill">Save Treatment Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Treatment Record Modal -->
    <div class="modal fade" id="editTreatmentModal" tabindex="-1" aria-labelledby="editTreatmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-gradient" id="editTreatmentModalLabel">Edit Treatment Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="record_id" id="edit_record_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Select Patient</label>
                                <select class="form-select rounded-pill" name="patient_id" id="edit_treatment_patient_id" required>
                                    <option value="">Choose patient...</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>">
                                            <?php echo htmlspecialchars($patient['pet_name'] . ' (' . $patient['species'] . ' - ' . $patient['breed'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Treatment Date</label>
                                <input type="date" class="form-control rounded-pill" name="treatment_date" id="edit_treatment_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Procedure/Service</label>
                            <input type="text" class="form-control rounded-pill" name="procedure" id="edit_procedure" placeholder="Enter procedure name..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Medications Prescribed</label>
                            <textarea class="form-control rounded-3" name="medications" id="edit_treatment_medications" rows="2" placeholder="List medications with dosage..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Treatment Details</label>
                            <textarea class="form-control rounded-3" name="treatment_details" id="edit_treatment_details" rows="3" placeholder="Describe the treatment procedure..." required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Follow-up Date</label>
                                <input type="date" class="form-control rounded-pill" name="followup_date" id="edit_treatment_followup_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Veterinarian</label>
                                <input type="text" class="form-control rounded-pill" value="Dr. <?php echo htmlspecialchars($user['full_name'] ?? $username); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-klean rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_treatment" class="btn hover-top btn-glow btn-klean rounded-pill">Update Treatment Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Set today's date as default for date fields
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const reportDate = document.querySelector('input[name="report_date"]');
            const treatmentDate = document.querySelector('input[name="treatment_date"]');
            
            if (reportDate) reportDate.value = today;
            if (treatmentDate) treatmentDate.value = today;
        });

        // Edit Report Function
        function editReport(reportId) {
            // Redirect to edit mode with report ID
            window.location.href = 'vet.php?edit_report=' + reportId + '#manage-reports';
        }

        // Edit Treatment Function
        function editTreatment(recordId) {
            // Redirect to edit mode with treatment ID
            window.location.href = 'vet.php?edit_treatment=' + recordId + '#treatment-records';
        }

        // View Report Function (placeholder)
        function viewReport(reportId) {
            alert('View functionality for report #' + reportId + ' would be implemented here.');
            // You can implement a view modal or redirect to a detailed view page
        }

        // Confirm Delete Function
        function confirmDelete(id, type) {
            if (confirm('Are you sure you want to delete this ' + type + '? This action cannot be undone.')) {
                if (type === 'report') {
                    window.location.href = 'vet.php?delete_report=' + id;
                }
            }
        }

        // Initialize edit modals if we're in edit mode
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_GET['edit_report']) && isset($edit_report)): ?>
                // Populate edit report modal
                document.getElementById('edit_report_id').value = '<?php echo $edit_report['report_id']; ?>';
                document.getElementById('edit_patient_id').value = '<?php echo $edit_report['patient_id'] ?? ''; ?>';
                document.getElementById('edit_report_date').value = '<?php echo $edit_report['report_date']; ?>';
                document.getElementById('edit_service_type').value = '<?php echo $edit_report['service_type'] ?? ''; ?>';
                document.getElementById('edit_diagnosis').value = '<?php echo $edit_report['diagnosis'] ?? ''; ?>';
                document.getElementById('edit_treatment_given').value = '<?php echo $edit_report['treatment_given'] ?? $edit_report['treatment_plan'] ?? $edit_report['treatment_details'] ?? ''; ?>';
                document.getElementById('edit_medications').value = '<?php echo $edit_report['medications'] ?? ''; ?>';
                document.getElementById('edit_followup_date').value = '<?php echo $edit_report['followup_date'] ?? ''; ?>';
                document.getElementById('edit_notes').value = '<?php echo $edit_report['notes'] ?? $edit_report['vet_notes'] ?? $edit_report['clinical_notes'] ?? ''; ?>';
                
                // Show the edit modal
                var editModal = new bootstrap.Modal(document.getElementById('editReportModal'));
                editModal.show();
            <?php endif; ?>

            <?php if (isset($_GET['edit_treatment']) && isset($edit_treatment)): ?>
                // Populate edit treatment modal
                document.getElementById('edit_record_id').value = '<?php echo $edit_treatment['record_id']; ?>';
                document.getElementById('edit_treatment_patient_id').value = '<?php echo $edit_treatment['patient_id'] ?? ''; ?>';
                document.getElementById('edit_treatment_date').value = '<?php echo $edit_treatment['treatment_date']; ?>';
                document.getElementById('edit_procedure').value = '<?php echo $edit_treatment['procedure']; ?>';
                document.getElementById('edit_treatment_medications').value = '<?php echo $edit_treatment['medications']; ?>';
                document.getElementById('edit_treatment_details').value = '<?php echo $edit_treatment['treatment_details']; ?>';
                document.getElementById('edit_treatment_followup_date').value = '<?php echo $edit_treatment['followup_date'] ?? ''; ?>';
                
                // Show the edit modal
                var editModal = new bootstrap.Modal(document.getElementById('editTreatmentModal'));
                editModal.show();
            <?php endif; ?>
        });
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
</body>
</html>