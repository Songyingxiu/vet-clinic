<?php
session_start();

// Include database connection
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data from database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Check and add missing columns to appointments table
try {
    // Check if new columns exist
    $columns_to_check = ['pet_name', 'species', 'breed', 'gender', 'service_type', 'pet_age', 'notes', 'vet_id'];
    $missing_columns = [];
    
    foreach ($columns_to_check as $column) {
        $check_stmt = $pdo->prepare("SHOW COLUMNS FROM appointments LIKE ?");
        $check_stmt->execute([$column]);
        if (!$check_stmt->fetch()) {
            $missing_columns[] = $column;
        }
    }
    
    // Add missing columns
    if (!empty($missing_columns)) {
        $alter_sql = "ALTER TABLE appointments ";
        $add_columns = [];
        
        foreach ($missing_columns as $column) {
            switch($column) {
                case 'pet_name':
                    $add_columns[] = "ADD COLUMN pet_name VARCHAR(100) AFTER user_id";
                    break;
                case 'species':
                    $add_columns[] = "ADD COLUMN species VARCHAR(50) AFTER pet_name";
                    break;
                case 'breed':
                    $add_columns[] = "ADD COLUMN breed VARCHAR(100) AFTER species";
                    break;
                case 'gender':
                    $add_columns[] = "ADD COLUMN gender ENUM('Male', 'Female') AFTER breed";
                    break;
                case 'service_type':
                    $add_columns[] = "ADD COLUMN service_type VARCHAR(100) AFTER gender";
                    break;
                case 'pet_age':
                    $add_columns[] = "ADD COLUMN pet_age VARCHAR(50) AFTER service_type";
                    break;
                case 'notes':
                    $add_columns[] = "ADD COLUMN notes TEXT AFTER pet_age";
                    break;
                case 'vet_id':
                    $add_columns[] = "ADD COLUMN vet_id INT AFTER user_id";
                    break;
            }
        }
        
        $alter_sql .= implode(", ", $add_columns);
        $pdo->exec($alter_sql);
        $success = "Database table updated successfully!";
    }
    
} catch (PDOException $e) {
    $error = "Table update error: " . $e->getMessage();
}

// Get available vets from veterinarians table with user information
try {
    $vets_stmt = $pdo->prepare("
        SELECT v.*, u.full_name, u.email, u.phone 
        FROM veterinarians v 
        JOIN users u ON v.user_id = u.user_id 
        ORDER BY u.full_name
    ");
    $vets_stmt->execute();
    $vets = $vets_stmt->fetchAll();
} catch (PDOException $e) {
    $vets = [];
    $error = "Error fetching veterinarians: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Book new appointment
    if (isset($_POST['book_appointment'])) {
        $vet_id = $_POST['vet_id'];
        $pet_name = trim($_POST['pet_name']);
        $species = trim($_POST['species']);
        $breed = trim($_POST['breed']);
        $gender = $_POST['gender'];
        $service_type = $_POST['service_type'];
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];
        $pet_age = trim($_POST['pet_age']);
        $notes = trim($_POST['notes']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (user_id, vet_id, pet_name, species, breed, gender, service_type, appointment_date, appointment_time, pet_age, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')");
            $stmt->execute([$_SESSION['user_id'], $vet_id, $pet_name, $species, $breed, $gender, $service_type, $appointment_date, $appointment_time, $pet_age, $notes]);
            $success = "Appointment booked successfully!";
            
            // Refresh the page to show the new appointment
            header("Location: patient.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error booking appointment: " . $e->getMessage();
        }
    }
    
    // Update appointment
    if (isset($_POST['update_appointment'])) {
        $appointment_id = $_POST['appointment_id'];
        $vet_id = $_POST['vet_id'];
        $pet_name = trim($_POST['pet_name']);
        $species = trim($_POST['species']);
        $breed = trim($_POST['breed']);
        $gender = $_POST['gender'];
        $service_type = $_POST['service_type'];
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];
        $pet_age = trim($_POST['pet_age']);
        $notes = trim($_POST['notes']);
        
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET vet_id = ?, pet_name = ?, species = ?, breed = ?, gender = ?, service_type = ?, appointment_date = ?, appointment_time = ?, pet_age = ?, notes = ? WHERE appointment_id = ? AND user_id = ?");
            $stmt->execute([$vet_id, $pet_name, $species, $breed, $gender, $service_type, $appointment_date, $appointment_time, $pet_age, $notes, $appointment_id, $_SESSION['user_id']]);
            $success = "Appointment updated successfully!";
            
            // Refresh the page
            header("Location: patient.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error updating appointment: " . $e->getMessage();
        }
    }
}

// Handle delete appointment
if (isset($_GET['delete_appointment'])) {
    $appointment_id = $_GET['delete_appointment'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $_SESSION['user_id']]);
        $success = "Appointment deleted successfully!";
        
        // Refresh the page
        header("Location: patient.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting appointment: " . $e->getMessage();
    }
}

// Handle cancel appointment (change status to cancelled)
if (isset($_GET['cancel_appointment'])) {
    $appointment_id = $_GET['cancel_appointment'];
    
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $_SESSION['user_id']]);
        $success = "Appointment cancelled successfully!";
        
        // Refresh the page
        header("Location: patient.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error cancelling appointment: " . $e->getMessage();
    }
}

// Initialize appointment arrays
$appointments = [];
$upcoming_appointments = [];
$past_appointments = [];
$follow_up_appointments = [];

// Get user's appointments with vet information from veterinarians table
try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.full_name as vet_name, v.specialization, v.experience_years 
        FROM appointments a 
        LEFT JOIN veterinarians v ON a.vet_id = v.vet_id 
        LEFT JOIN users u ON v.user_id = u.user_id 
        WHERE a.user_id = ? 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $appointments = $stmt->fetchAll();
    
    // Separate appointments by status
    $upcoming_appointments = array_filter($appointments, function($appt) {
        return $appt['status'] == 'scheduled' && strtotime($appt['appointment_date']) >= strtotime(date('Y-m-d'));
    });
    
    $past_appointments = array_filter($appointments, function($appt) {
        return $appt['status'] == 'completed' || strtotime($appt['appointment_date']) < strtotime(date('Y-m-d'));
    });
    
    $follow_up_appointments = array_filter($appointments, function($appt) {
        return $appt['status'] == 'follow_up';
    });
    
} catch (PDOException $e) {
    $error = "Error fetching appointments: " . $e->getMessage();
}

// Get treatment records with follow-up information - SIMPLIFIED QUERY
$treatment_records = [];
try {
    // First, let's check if the treatment table exists and has data
    $check_table = $pdo->query("SHOW TABLES LIKE 'treatment'");
    $table_exists = $check_table->fetch();
    
    if ($table_exists) {
        // Simple query to get all treatment records for this user's pets
        $treatment_stmt = $pdo->prepare("
            SELECT t.*, u.full_name as vet_name, a.pet_name, a.species, a.breed
            FROM treatment t 
            LEFT JOIN veterinarians v ON t.vet_id = v.vet_id 
            LEFT JOIN users u ON v.user_id = u.user_id 
            LEFT JOIN appointments a ON t.record_id = a.appointment_id 
            WHERE a.user_id = ?
            ORDER BY t.treatment_date DESC, t.created_at DESC
        ");
        $treatment_stmt->execute([$_SESSION['user_id']]);
        $treatment_records = $treatment_stmt->fetchAll();
        
        // If no records found with the complex query, try a simpler approach
        if (empty($treatment_records)) {
            // Alternative query - get all treatment records
            $alt_stmt = $pdo->query("
                SELECT t.*, u.full_name as vet_name
                FROM treatment t 
                LEFT JOIN veterinarians v ON t.vet_id = v.vet_id 
                LEFT JOIN users u ON v.user_id = u.user_id 
                ORDER BY t.treatment_date DESC
                LIMIT 10
            ");
            $treatment_records = $alt_stmt->fetchAll();
        }
    } else {
        $error = "Treatment table does not exist in the database.";
    }
} catch (PDOException $e) {
    $error = "Error fetching treatment records: " . $e->getMessage();
    // Debug: show the actual error
    $error .= " - " . $e->getMessage();
}

// Debug: Check what we're getting
if (empty($treatment_records)) {
    // Uncomment the line below for debugging
    // $error = "No treatment records found. User ID: " . $_SESSION['user_id'];
}

// Get appointment for editing
$edit_appointment = null;
if (isset($_GET['edit_appointment'])) {
    $appointment_id = $_GET['edit_appointment'];
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, u.full_name as vet_name, v.specialization, v.experience_years 
            FROM appointments a 
            LEFT JOIN veterinarians v ON a.vet_id = v.vet_id 
            LEFT JOIN users u ON v.user_id = u.user_id 
            WHERE a.appointment_id = ? AND a.user_id = ?
        ");
        $stmt->execute([$appointment_id, $_SESSION['user_id']]);
        $edit_appointment = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error fetching appointment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en-US" dir="ltr">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Owner Dashboard - Pawprint Haven</title>

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
  </head>

  <body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
      <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3 d-block navbar-klean" data-navbar-on-scroll="data-navbar-on-scroll">
        <div class="container">
          <!-- Logo -->
          <a class="navbar-brand" href="patient.php"> 
            <img class="me-3 d-inline-block" src="assets/img/gallery/logo.png" alt="" style="height: 65px;" />
          </a>
          <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse border-top border-lg-0 mt-4 mt-lg-0" id="navbarSupportedContent">
            <!-- Owner Navigation Menu -->
            <ul class="navbar-nav me-auto pt-2 pt-lg-0 font-base">
              <li class="nav-item px-2" data-anchor="data-anchor">
                <a class="nav-link fw-medium active" aria-current="page" href="#services">Services</a>
              </li>
              <li class="nav-item px-2" data-anchor="data-anchor">
                <a class="nav-link" href="#book-appointment">Book Appointment</a>
              </li>
              <li class="nav-item px-2" data-anchor="data-anchor">
                <a class="nav-link" href="#appointment-history">Appointment History</a>
              </li>
              <li class="nav-item px-2" data-anchor="data-anchor">
                <a class="nav-link" href="#upcoming-appointments">Upcoming</a>
              </li>
              <li class="nav-item px-2" data-anchor="data-anchor">
                <a class="nav-link" href="#medical-records">Medical Records</a>
              </li>
              <li class="nav-item px-2">
                <a class="nav-link" href="profile.php" target="_self">Profile</a>
              </li>
            </ul>
            <!-- Display user name -->
            <div class="navbar-text ms-3">
              <span class="text-600">Welcome, <strong><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></strong></span>
            </div>
          </div>
        </div>
      </nav>

      <!-- Success/Error Messages -->
      <?php if (isset($success)): ?>
        <div class="container mt-4" style="margin-top: 100px !important;">
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
      <?php endif; ?>
      
      <?php if (isset($error)): ?>
        <div class="container mt-4" style="margin-top: 100px !important;">
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
      <?php endif; ?>

      <!-- Welcome Section -->
      <section id="welcome">
        <!-- Background holders -->
        <div class="bg-holder d-none d-md-block bg-size" style="background-image:url(assets/img/illustrations/background.png);background-position:right bottom;"></div>
        <div class="bg-holder" style="background-image:url(assets/img/illustrations/background.png);background-position:center;background-size:contain;"></div>
        <div class="bg-holder d-none d-md-block bg-size" style="background-image:url(assets/img/illustrations/background.png);background-position:center;background-size:cover;"></div>
        <div class="bg-holder" style="background-image:url(assets/img/illustrations/background.png);background-position:center;background-size:cover;"></div>

        <div class="container">
          <div class="row align-items-center">
            <div class="col-md-7 col-lg-6 py-6 text-sm-start text-center">
              <h1 class="fw-bold display-4 fs-4 fs-lg-6 fs-xxl-7 text-gradient">Welcome Back, <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>!</h1>
              <h1 class="text-700">Manage your pet's health in <span class="fw-bold">one place</span></h1>
              <p class="mb-5 fs-0">Access all your pet's medical records, appointments, and follow-up care from your personalized dashboard.</p>
              
              <!-- Quick Stats -->
              <div class="row mt-5">
                <div class="col-md-3 mb-3">
                  <div class="card card-bg p-3 text-center">
                    <h4 class="text-gradient fw-bold" id="upcoming-count"><?php echo count($upcoming_appointments); ?></h4>
                    <p class="text-600 mb-0">Upcoming</p>
                  </div>
                </div>
                <div class="col-md-3 mb-3">
                  <div class="card card-bg p-3 text-center">
                    <h4 class="text-gradient fw-bold"><?php echo count($past_appointments); ?></h4>
                    <p class="text-600 mb-0">Past Visits</p>
                  </div>
                </div>
                <div class="col-md-3 mb-3">
                  <div class="card card-bg p-3 text-center">
                    <h4 class="text-gradient fw-bold"><?php echo count($treatment_records); ?></h4>
                    <p class="text-600 mb-0">Medical Records</p>
                  </div>
                </div>
                <div class="col-md-3 mb-3">
                  <div class="card card-bg p-3 text-center">
                    <h4 class="text-gradient fw-bold"><?php echo count($appointments); ?></h4>
                    <p class="text-600 mb-0">Total Appointments</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Services Section -->
      <section class="py-0 circle-blend circle-blend-right circle-warning" id="services">
        <div class="container">
          <div class="row">
            <div class="col-lg-7 col-xxl-5 mx-auto text-center py-7">
              <h5 class="fw-bold fs-3 fs-lg-5 lh-sm mb-3">Our Services</h5>
              <p class="mb-0">We offer a comprehensive range of veterinary services to keep your pets healthy and happy.</p>
            </div>
          </div>
          
          <div class="row flex-center">
            <div class="col-xl-9">
              <div class="row justify-content-center circle">
                <!-- Vaccination Service -->
                <div class="col-md-4 mb-4">
                  <div class="card card-bg h-100 px-4 px-md-2 px-lg-3 px-xxl-4 pt-4">
                    <div class="text-center">
                      <img class="my-5" src="assets/img/icons/vaccination.jpg" width="100" alt="Vaccination" />
                      <div class="card-body text-center text-md-start">
                        <h6 class="fw-bold fs-1">Vaccination</h6>
                        <p class="mt-3 mb-md-0 mb-lg-3">Essential vaccinations to protect your pets from common diseases and ensure their long-term health.</p>
                        <a class="btn btn-lg ps-0 pe-3 text-primary" href="#" role="button">Learn more
                          <svg class="bi bi-arrow-right-short" xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"></path>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Medical Checkup Service -->
                <div class="col-md-4 mb-4">
                  <div class="card card-bg h-100 px-4 px-md-2 px-lg-3 px-xxl-4 pt-4">
                    <div class="text-center">
                      <img class="my-5" src="assets/img/icons/medcheck.png" width="100" alt="Medical Checkup" />
                      <div class="card-body text-center text-md-start">
                        <h6 class="fw-bold fs-1">Medical Checkup</h6>
                        <p class="mt-3 mb-md-0 mb-lg-3">Comprehensive health examinations to detect issues early and maintain your pet's wellbeing.</p>
                        <a class="btn btn-lg ps-0 pe-3 text-primary" href="#" role="button">Learn more
                          <svg class="bi bi-arrow-right-short" xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"></path>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Microchip & Rapid Test Service -->
                <div class="col-md-4 mb-4">
                  <div class="card card-bg h-100 px-4 px-md-2 px-lg-3 px-xxl-4 pt-4">
                    <div class="text-center">
                      <img class="my-5" src="assets/img/icons/microchip.png" width="100" alt="Microchip & Rapid Test" />
                      <div class="card-body text-center text-md-start">
                        <h6 class="fw-bold fs-1">Microchip & Rapid Test</h6>
                        <p class="mt-3 mb-md-0 mb-lg-3">Identification microchipping and quick diagnostic tests for immediate health assessment.</p>
                        <a class="btn btn-lg ps-0 pe-3 text-primary" href="#" role="button">Learn more
                          <svg class="bi bi-arrow-right-short" xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"></path>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Second row of services -->
              <div class="row justify-content-center circle mt-4">
                <!-- Surgery Service -->
                <div class="col-md-4 mb-4">
                  <div class="card card-bg h-100 px-4 px-md-2 px-lg-3 px-xxl-4 pt-4">
                    <div class="text-center">
                      <img class="my-5" src="assets/img/icons/surgery.png" width="100" alt="Surgery" />
                      <div class="card-body text-center text-md-start">
                        <h6 class="fw-bold fs-1">Surgery</h6>
                        <p class="mt-3 mb-md-0 mb-lg-3">Expert surgical procedures performed with the latest technology and utmost care.</p>
                        <a class="btn btn-lg ps-0 pe-3 text-primary" href="#" role="button">Learn more
                          <svg class="bi bi-arrow-right-short" xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"></path>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Dentist Service -->
                <div class="col-md-4 mb-4">
                  <div class="card card-bg h-100 px-4 px-md-2 px-lg-3 px-xxl-4 pt-4">
                    <div class="text-center">
                      <img class="my-5" src="assets/img/icons/dentist.png" width="100" alt="Dentist" />
                      <div class="card-body text-center text-md-start">
                        <h6 class="fw-bold fs-1">Dentist</h6>
                        <p class="mt-3 mb-md-0 mb-lg-3">Complete dental care including cleaning, extractions, and oral health maintenance.</p>
                        <a class="btn btn-lg ps-0 pe-3 text-primary" href="#" role="button">Learn more
                          <svg class="bi bi-arrow-right-short" xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"></path>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Lab Test Service -->
                <div class="col-md-4 mb-4">
                  <div class="card card-bg h-100 px-4 px-md-2 px-lg-3 px-xxl-4 pt-4">
                    <div class="text-center">
                      <img class="my-5" src="assets/img/icons/labtest.png" width="100" alt="Lab Test" />
                      <div class="card-body text-center text-md-start">
                        <h6 class="fw-bold fs-1">Lab Test</h6>
                        <p class="mt-3 mb-md-0 mb-lg-3">Comprehensive laboratory testing for accurate diagnosis and treatment planning.</p>
                        <a class="btn btn-lg ps-0 pe-3 text-primary" href="#" role="button">Learn more
                          <svg class="bi bi-arrow-right-short" xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"></path>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Book Appointment Section -->
      <section id="book-appointment" class="py-7 bg-light">
        <div class="container">
          <div class="row justify-content-center mb-6">
            <div class="col-lg-8 text-center">
              <h2 class="fw-bold mb-3"><?php echo $edit_appointment ? 'Edit Appointment' : 'Book an Appointment'; ?></h2>
              <p class="text-600">Schedule a visit for your pet with our expert veterinarians</p>
            </div>
          </div>
          
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="card card-bg p-4">
                <form method="POST">
                  <?php if ($edit_appointment): ?>
                    <input type="hidden" name="appointment_id" value="<?php echo $edit_appointment['appointment_id']; ?>">
                  <?php endif; ?>
                  
                  <div class="row">
                    <!-- Vet Selection -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Preferred Veterinarian</label>
                      <select class="form-select rounded-pill" name="vet_id" required>
                        <option value="" disabled <?php echo !$edit_appointment ? 'selected' : ''; ?>>Select veterinarian...</option>
                        <?php foreach ($vets as $vet): ?>
                          <option value="<?php echo $vet['vet_id']; ?>" 
                            <?php echo ($edit_appointment && $edit_appointment['vet_id'] == $vet['vet_id']) ? 'selected' : ''; ?>>
                            Dr. <?php echo htmlspecialchars($vet['full_name']); ?> - <?php echo htmlspecialchars($vet['specialization']); ?> (<?php echo $vet['experience_years']; ?> years exp.)
                          </option>
                        <?php endforeach; ?>
                        <?php if (empty($vets)): ?>
                          <option value="" disabled>No veterinarians available</option>
                        <?php endif; ?>
                      </select>
                    </div>
                    <!-- Pet Name -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Pet Name</label>
                      <input type="text" class="form-control rounded-pill" name="pet_name" 
                             value="<?php echo $edit_appointment ? htmlspecialchars($edit_appointment['pet_name']) : ''; ?>" 
                             placeholder="Enter pet name" required>
                    </div>
                  </div>
                  <div class="row">
                    <!-- Species -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Species</label>
                      <input type="text" class="form-control rounded-pill" name="species" 
                             value="<?php echo $edit_appointment ? htmlspecialchars($edit_appointment['species']) : ''; ?>" 
                             placeholder="e.g., Dog, Cat, Rabbit" required>
                    </div>
                    <!-- Breed -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Breed</label>
                      <input type="text" class="form-control rounded-pill" name="breed" 
                             value="<?php echo $edit_appointment ? htmlspecialchars($edit_appointment['breed']) : ''; ?>" 
                             placeholder="e.g., Golden Retriever, Persian" required>
                    </div>
                  </div>
                  <div class="row">
                    <!-- Gender -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Gender</label>
                      <select class="form-select rounded-pill" name="gender" required>
                        <option value="" disabled <?php echo !$edit_appointment ? 'selected' : ''; ?>>Select gender...</option>
                        <option value="Male" <?php echo ($edit_appointment && $edit_appointment['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($edit_appointment && $edit_appointment['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                      </select>
                    </div>
                    <!-- Service Type -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Service Type</label>
                      <select class="form-select rounded-pill" name="service_type" required>
                        <option value="" disabled <?php echo !$edit_appointment ? 'selected' : ''; ?>>Choose service...</option>
                        <option value="Vaccination" <?php echo ($edit_appointment && $edit_appointment['service_type'] == 'Vaccination') ? 'selected' : ''; ?>>Vaccination</option>
                        <option value="Medical Checkup" <?php echo ($edit_appointment && $edit_appointment['service_type'] == 'Medical Checkup') ? 'selected' : ''; ?>>Medical Checkup</option>
                        <option value="Dental Care" <?php echo ($edit_appointment && $edit_appointment['service_type'] == 'Dental Care') ? 'selected' : ''; ?>>Dental Care</option>
                        <option value="Surgery" <?php echo ($edit_appointment && $edit_appointment['service_type'] == 'Surgery') ? 'selected' : ''; ?>>Surgery</option>
                        <option value="Lab Test" <?php echo ($edit_appointment && $edit_appointment['service_type'] == 'Lab Test') ? 'selected' : ''; ?>>Lab Test</option>
                        <option value="Microchip" <?php echo ($edit_appointment && $edit_appointment['service_type'] == 'Microchip') ? 'selected' : ''; ?>>Microchip</option>
                        <option value="Rapid Test" <?php echo ($edit_appointment && $edit_appointment['service_type'] == 'Rapid Test') ? 'selected' : ''; ?>>Rapid Test</option>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <!-- Preferred Date -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Preferred Date</label>
                      <input type="date" class="form-control rounded-pill" name="appointment_date" 
                             value="<?php echo $edit_appointment ? $edit_appointment['appointment_date'] : ''; ?>" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <!-- Preferred Time -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Preferred Time</label>
                      <select class="form-select rounded-pill" name="appointment_time" required>
                        <option value="" disabled <?php echo !$edit_appointment ? 'selected' : ''; ?>>Choose time...</option>
                        <option value="09:00" <?php echo ($edit_appointment && $edit_appointment['appointment_time'] == '09:00') ? 'selected' : ''; ?>>9:00 AM</option>
                        <option value="10:00" <?php echo ($edit_appointment && $edit_appointment['appointment_time'] == '10:00') ? 'selected' : ''; ?>>10:00 AM</option>
                        <option value="11:00" <?php echo ($edit_appointment && $edit_appointment['appointment_time'] == '11:00') ? 'selected' : ''; ?>>11:00 AM</option>
                        <option value="14:00" <?php echo ($edit_appointment && $edit_appointment['appointment_time'] == '14:00') ? 'selected' : ''; ?>>2:00 PM</option>
                        <option value="15:00" <?php echo ($edit_appointment && $edit_appointment['appointment_time'] == '15:00') ? 'selected' : ''; ?>>3:00 PM</option>
                        <option value="16:00" <?php echo ($edit_appointment && $edit_appointment['appointment_time'] == '16:00') ? 'selected' : ''; ?>>4:00 PM</option>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <!-- Age -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Pet Age</label>
                      <input type="text" class="form-control rounded-pill" name="pet_age" 
                             value="<?php echo $edit_appointment ? htmlspecialchars($edit_appointment['pet_age']) : ''; ?>" 
                             placeholder="e.g., 2 years, 6 months" required>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Additional Notes</label>
                    <textarea class="form-control rounded-3" name="notes" rows="3" 
                              placeholder="Any special requirements or concerns..."><?php echo $edit_appointment ? htmlspecialchars($edit_appointment['notes']) : ''; ?></textarea>
                  </div>
                  <div class="d-grid">
                    <?php if ($edit_appointment): ?>
                      <button type="submit" name="update_appointment" class="btn hover-top btn-glow btn-klean rounded-pill py-2">
                        <span class="fw-bold">Update Appointment</span>
                      </button>
                      <a href="patient.php" class="btn btn-outline-klean rounded-pill mt-2">Cancel Edit</a>
                    <?php else: ?>
                      <button type="submit" name="book_appointment" class="btn hover-top btn-glow btn-klean rounded-pill py-2">
                        <span class="fw-bold">Book Appointment</span>
                      </button>
                    <?php endif; ?>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Upcoming Appointments Section -->
      <section id="upcoming-appointments" class="py-7">
        <div class="container">
          <div class="row justify-content-center mb-6">
            <div class="col-lg-10 text-center">
              <h2 class="fw-bold mb-3">Upcoming Appointments</h2>
              <p class="text-600">Your scheduled appointments</p>
            </div>
          </div>
          
          <div class="row" id="upcoming-appointments-content">
            <?php if (empty($upcoming_appointments)): ?>
              <div class="col-12 text-center">
                <div class="card card-bg">
                  <div class="card-body py-5">
                    <h5 class="text-600">No upcoming appointments</h5>
                    <p class="text-500">Book an appointment to see it here!</p>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <?php foreach ($upcoming_appointments as $appointment): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                  <div class="card card-bg h-100 appointment-card">
                    <div class="card-body">
                      <h5 class="fw-bold text-gradient"><?php echo htmlspecialchars($appointment['pet_name']); ?></h5>
                      <?php if (!empty($appointment['vet_name'])): ?>
                        <p class="mb-1"><strong>Veterinarian:</strong> Dr. <?php echo htmlspecialchars($appointment['vet_name']); ?></p>
                        <p class="mb-1"><strong>Specialization:</strong> <?php echo htmlspecialchars($appointment['specialization']); ?> (<?php echo $appointment['experience_years']; ?> years exp.)</p>
                      <?php else: ?>
                        <p class="mb-1"><strong>Veterinarian:</strong> Not assigned</p>
                      <?php endif; ?>
                      <div class="mt-3">
                        <p class="mb-1"><strong>Date:</strong> <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></p>
                        <p class="mb-1"><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                        <p class="mb-1"><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_type']); ?></p>
                        <p class="mb-1"><strong>Species:</strong> <?php echo htmlspecialchars($appointment['species']); ?></p>
                        <p class="mb-1"><strong>Breed:</strong> <?php echo htmlspecialchars($appointment['breed']); ?></p>
                        <?php if (!empty($appointment['notes'])): ?>
                          <p class="mb-1"><strong>Notes:</strong> <?php echo htmlspecialchars($appointment['notes']); ?></p>
                        <?php endif; ?>
                      </div>
                      <div class="mt-3">
                        <a href="patient.php?edit_appointment=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-klean me-1">Edit</a>
                        <a href="patient.php?cancel_appointment=<?php echo $appointment['appointment_id']; ?>" 
                           class="btn btn-sm btn-outline-danger" 
                           onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- Appointment History Section -->
      <section id="appointment-history" class="py-7 bg-light">
        <div class="container">
          <div class="row justify-content-center mb-6">
            <div class="col-lg-10 text-center">
              <h2 class="fw-bold mb-3">Appointment History</h2>
              <p class="text-600">Review and manage all your appointments</p>
            </div>
          </div>
          
          <div class="row">
            <div class="col-12">
              <div class="card card-bg">
                <div class="card-body">
                  <?php if (empty($appointments)): ?>
                    <div class="text-center py-5">
                      <h5 class="text-600">No appointments found</h5>
                      <p class="text-500">Book your first appointment to get started!</p>
                    </div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-hover">
                        <thead>
                          <tr>
                            <th>Date & Time</th>
                            <th>Pet Name</th>
                            <th>Veterinarian</th>
                            <th>Species</th>
                            <th>Breed</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($appointments as $appointment): ?>
                            <tr>
                              <td>
                                <strong><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></strong><br>
                                <small class="text-600"><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></small>
                              </td>
                              <td><?php echo htmlspecialchars($appointment['pet_name']); ?></td>
                              <td>
                                <?php if (!empty($appointment['vet_name'])): ?>
                                  Dr. <?php echo htmlspecialchars($appointment['vet_name']); ?>
                                <?php else: ?>
                                  Not assigned
                                <?php endif; ?>
                              </td>
                              <td><?php echo htmlspecialchars($appointment['species']); ?></td>
                              <td><?php echo htmlspecialchars($appointment['breed']); ?></td>
                              <td><?php echo htmlspecialchars($appointment['service_type']); ?></td>
                              <td>
                                <?php
                                $status_class = '';
                                switch($appointment['status']) {
                                  case 'scheduled':
                                    $status_class = 'bg-warning';
                                    break;
                                  case 'completed':
                                    $status_class = 'bg-success';
                                    break;
                                  case 'follow_up':
                                    $status_class = 'bg-info';
                                    break;
                                  case 'cancelled':
                                    $status_class = 'bg-danger';
                                    break;
                                  default:
                                    $status_class = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($appointment['status']); ?></span>
                              </td>
                              <td>
                                <a href="patient.php?edit_appointment=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-klean me-1">Edit</a>
                                <a href="patient.php?delete_appointment=<?php echo $appointment['appointment_id']; ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this appointment?')">Delete</a>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Medical Records Section -->
      <section id="medical-records" class="py-7">
        <div class="container">
          <div class="row justify-content-center mb-6">
            <div class="col-lg-10 text-center">
              <h2 class="fw-bold mb-3">Medical Records & Follow-up</h2>
              <p class="text-600">Your pet's complete medical treatment history and follow-up schedule</p>
            </div>
          </div>
          
          <div class="row">
            <?php if (empty($treatment_records)): ?>
              <div class="col-12 text-center">
                <div class="card card-bg">
                  <div class="card-body py-5">
                    <h5 class="text-600">No medical records found</h5>
                    <p class="text-500">Your pet's medical records will appear here after veterinary visits</p>
                    <?php if (isset($error)): ?>
                      <div class="alert alert-info mt-3">
                        <small>Debug: <?php echo htmlspecialchars($error); ?></small>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <?php foreach ($treatment_records as $record): ?>
                <div class="col-lg-6 mb-4">
                  <div class="card card-bg h-100">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="fw-bold text-gradient"><?php echo htmlspecialchars($record['pet_name'] ?? 'Unknown Pet'); ?></h5>
                        <span class="badge bg-info">Record #<?php echo $record['record_id']; ?></span>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6">
                          <p class="mb-2"><strong>🩺 Procedure:</strong> <?php echo htmlspecialchars($record['procedure']); ?></p>
                          <p class="mb-2"><strong>👨‍⚕️ Veterinarian:</strong> <?php echo !empty($record['vet_name']) ? 'Dr. ' . htmlspecialchars($record['vet_name']) : 'Not specified'; ?></p>
                          <p class="mb-2"><strong>📅 Treatment Date:</strong> <?php echo date('M d, Y', strtotime($record['treatment_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                          <?php if (!empty($record['followup_date'])): ?>
                            <div class="alert alert-info p-2 mb-2" style="background-color: #e3f2fd; border-color: #90caf9; color: #1565c0;">
                              <strong>📋 Follow-up Required:</strong> 
                              <span class="fw-bold"><?php echo date('M d, Y', strtotime($record['followup_date'])); ?></span>
                            </div>
                          <?php else: ?>
                            <p class="mb-2"><strong>✅ Status:</strong> No follow-up required</p>
                          <?php endif; ?>
                          <p class="mb-2"><strong>🐕 Species:</strong> <?php echo htmlspecialchars($record['species'] ?? 'Unknown'); ?></p>
                          <p class="mb-2"><strong>🎯 Breed:</strong> <?php echo htmlspecialchars($record['breed'] ?? 'Unknown'); ?></p>
                        </div>
                      </div>
                      
                      <?php if (!empty($record['medications'])): ?>
                        <div class="mt-3">
                          <h6 class="fw-bold">💊 Medications:</h6>
                          <p class="mb-0"><?php echo nl2br(htmlspecialchars($record['medications'])); ?></p>
                        </div>
                      <?php endif; ?>
                      
                      <?php if (!empty($record['treatment_details'])): ?>
                        <div class="mt-3">
                          <h6 class="fw-bold">📝 Treatment Details:</h6>
                          <p class="mb-0"><?php echo nl2br(htmlspecialchars($record['treatment_details'])); ?></p>
                        </div>
                      <?php endif; ?>
                      
                      <?php if (!empty($record['followup_date'])): ?>
                        <div class="mt-3">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <small class="text-info fw-bold">
                                <i class="fas fa-calendar-check me-1"></i>
                                Next follow-up: <?php echo date('M d, Y', strtotime($record['followup_date'])); ?>
                              </small>
                            </div>
                            <a href="patient.php#book-appointment" class="btn btn-sm btn-outline-info">
                              Schedule Follow-up
                            </a>
                          </div>
                        </div>
                      <?php endif; ?>
                      
                      <div class="mt-3 text-end">
                        <small class="text-500">Record created: <?php echo date('M d, Y H:i', strtotime($record['created_at'])); ?></small>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          
          <!-- Upcoming Follow-ups Summary -->
          <?php 
          // Get treatment records that have upcoming follow-up dates
          $upcoming_followups = array_filter($treatment_records, function($record) {
              return !empty($record['followup_date']) && strtotime($record['followup_date']) >= strtotime(date('Y-m-d'));
          });
          ?>
          
          <?php if (!empty($upcoming_followups)): ?>
            <div class="row mt-6">
              <div class="col-12">
                <div class="card card-bg border-info">
                  <div class="card-header bg-info text-white">
                    <h5 class="fw-bold mb-0">📅 Upcoming Follow-ups</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <?php foreach ($upcoming_followups as $record): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                          <div class="card card-bg h-100">
                            <div class="card-body">
                              <h6 class="fw-bold text-gradient"><?php echo htmlspecialchars($record['pet_name'] ?? 'Unknown Pet'); ?></h6>
                              <p class="mb-1"><strong>Procedure:</strong> <?php echo htmlspecialchars($record['procedure']); ?></p>
                              <p class="mb-1"><strong>Follow-up Date:</strong> 
                                <span class="badge bg-info text-white"><?php echo date('M d, Y', strtotime($record['followup_date'])); ?></span>
                              </p>
                              <?php if (!empty($record['vet_name'])): ?>
                                <p class="mb-1"><strong>Veterinarian:</strong> Dr. <?php echo htmlspecialchars($record['vet_name']); ?></p>
                              <?php endif; ?>
                              <a href="patient.php#book-appointment" class="btn btn-sm btn-outline-info mt-2">Schedule Appointment</a>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Footer Section -->
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

    <!-- JavaScripts -->
    <script src="vendors/@popperjs/popper.min.js"></script>
    <script src="vendors/bootstrap/bootstrap.min.js"></script>
    <script src="vendors/is/is.min.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>
    <script src="vendors/feather-icons/feather.min.js"></script>
    <script>
      feather.replace();
    </script>
    <script src="assets/js/theme.js"></script>

    <!-- Manual Refresh Script for Upcoming Appointments -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to update upcoming appointments
        function updateUpcomingAppointments() {
            const refreshButton = document.querySelector('.refresh-upcoming-btn');
            const upcomingSection = document.getElementById('upcoming-appointments-content');
            
            if (!upcomingSection) return;
            
            // Show loading state
            const originalHTML = refreshButton.innerHTML;
            refreshButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Refreshing...';
            refreshButton.disabled = true;
            
            fetch('get_upcoming_appointments.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    upcomingSection.innerHTML = data;
                    
                    // Update the statistics counter
                    updateStatisticsCounters();
                })
                .catch(error => {
                    console.error('Error updating appointments:', error);
                    // Show error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger alert-dismissible fade show mt-3';
                    errorMsg.innerHTML = `
                        Error refreshing appointments. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    upcomingSection.parentNode.insertBefore(errorMsg, upcomingSection);
                })
                .finally(() => {
                    // Restore button state
                    refreshButton.innerHTML = originalHTML;
                    refreshButton.disabled = false;
                });
        }
        
        // Function to update statistics counters
        function updateStatisticsCounters() {
            const upcomingCards = document.querySelectorAll('#upcoming-appointments-content .col-md-6.col-lg-4.mb-4');
            const upcomingCount = upcomingCards.length;
            
            // Update the upcoming counter
            const upcomingCounter = document.getElementById('upcoming-count');
            if (upcomingCounter && upcomingCounter.textContent !== upcomingCount.toString()) {
                upcomingCounter.textContent = upcomingCount;
                
                // Add animation effect
                upcomingCounter.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    upcomingCounter.style.transform = 'scale(1)';
                }, 300);
            }
        }
        
        // Create and add refresh button to the upcoming appointments header
        const refreshButton = document.createElement('button');
        refreshButton.className = 'btn btn-outline-klean btn-sm ms-2 refresh-upcoming-btn';
        refreshButton.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh';
        refreshButton.addEventListener('click', updateUpcomingAppointments);
        
        // Add refresh button to the upcoming appointments header
        const upcomingHeader = document.querySelector('#upcoming-appointments h2');
        if (upcomingHeader) {
            upcomingHeader.parentNode.appendChild(refreshButton);
        }
    });
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
  </body>
</html>