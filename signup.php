<?php
session_start();

// Include database connection
require_once 'connection.php';

// Check if connection was successful
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . (isset($conn) ? $conn->connect_error : "Connection variable not set"));
}

// Initialize variables
$full_name = $email = $phone = $password = $confirm_password = "";
$error = "";
$success = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Generate username from email
    $username = strtolower(explode('@', $email)[0]);
    
    // Basic validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        // Check if email already exists
        $check_email = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email);
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "Email already exists! Please use a different email.";
            } else {
                // Use plain text password (no hashing) and set role to 'owner'
                $sql = "INSERT INTO users (username, full_name, email, phone, password, role) 
                        VALUES (?, ?, ?, ?, ?, 'owner')";
                
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sssss", $username, $full_name, $email, $phone, $password);
                    
                    if ($stmt->execute()) {
                        // Success - redirect to login page
                        $_SESSION['signup_success'] = "Account created successfully! Please login to continue.";
                        header("Location: login.php");
                        exit();
                    } else {
                        $error = "Error creating account: " . $conn->error;
                    }
                } else {
                    $error = "Database error: " . $conn->error;
                }
            }
            if ($stmt) {
                $stmt->close();
            }
        } else {
            $error = "Database error: " . $conn->error;
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
    <title>Sign Up - Pawprint Haven</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="40x40" href="assets/img/favicons/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicons/favicon.ico">
    <link rel="manifest" href="assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">

    <!-- Stylesheets -->
    <link href="assets/css/theme.css" rel="stylesheet" />
  </head>

  <body>

   <!-- Back Button with better visibility -->
<div class="container-fluid position-absolute top-0 start-0 mt-3 ms-3" style="z-index: 1000;">
  <a href="index.php" class="btn btn-light shadow-klean rounded-pill px-3 border">
    <svg class="bi bi-arrow-left" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
      <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
    </svg>
  </a>
</div>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
      <!-- Sign Up Section -->
      <section id="signup" class="py-8" style="margin-top: 50px;">
        <!-- Background with cover size -->
        <div class="bg-holder d-none d-md-block bg-size" style="background-image:url(assets/img/illustrations/background.png);background-position:center;background-size:cover;">
        </div>
        <!--/.bg-holder-->

        <div class="bg-holder" style="background-image:url(assets/img/illustrations/background.png);background-position:center;background-size:cover;">
        </div>
        <!--/.bg-holder-->

        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
              <div class="card card-bg p-4 shadow-klean">
                <div class="card-body">
                  <div class="text-center mb-5">
                    <h2 class="fw-bold text-gradient mb-3">Create Account</h2>
                    <p class="text-600">Join Pawprint Haven and create your account</p>
                  </div>
                  
                  <!-- Error Message -->
                  <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                      <?php echo $error; ?>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                  <?php endif; ?>
                  
                  <form action="signup.php" method="POST">
                    <!-- Full Name Input -->
                    <div class="mb-4 input-group-icon">
                      <label class="form-label visually-hidden" for="inputFullName">Full Name</label>
                      <div class="position-relative">
                        <input class="form-control rounded-pill border-300 input-box" id="inputFullName" name="full_name" type="text" placeholder="Full Name" value="<?php echo htmlspecialchars($full_name); ?>" required style="padding-left: 50px; padding-right: 20px;" />
                        <svg class="bi bi-person-fill input-box-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="left: 20px;">
                          <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
                        </svg>
                      </div>
                    </div>
                    
                    <!-- Email Input -->
                    <div class="mb-4 input-group-icon">
                      <label class="form-label visually-hidden" for="inputEmail">Email Address</label>
                      <div class="position-relative">
                        <input class="form-control rounded-pill border-300 input-box" id="inputEmail" name="email" type="email" placeholder="Email Address" value="<?php echo htmlspecialchars($email); ?>" required style="padding-left: 50px; padding-right: 20px;" />
                        <svg class="bi bi-envelope-fill input-box-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="left: 20px;">
                          <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555zM0 4.697v7.104l5.803-3.558L0 4.697zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757zm3.436-.586L16 11.801V4.697l-5.803 3.546z"></path>
                        </svg>
                      </div>
                    </div>
                    
                    <!-- Phone Number Input -->
                    <div class="mb-4 input-group-icon">
                      <label class="form-label visually-hidden" for="inputPhone">Phone Number</label>
                      <div class="position-relative">
                        <input class="form-control rounded-pill border-300 input-box" id="inputPhone" name="phone" type="tel" placeholder="Phone Number" value="<?php echo htmlspecialchars($phone); ?>" required style="padding-left: 50px; padding-right: 20px;" />
                        <svg class="bi bi-telephone-fill input-box-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="left: 20px;">
                          <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"></path>
                        </svg>
                      </div>
                    </div>
                    
                    <!-- Password Input -->
                    <div class="mb-4 input-group-icon">
                      <label class="form-label visually-hidden" for="inputPassword">Password</label>
                      <div class="position-relative">
                        <input class="form-control rounded-pill border-300 input-box" id="inputPassword" name="password" type="password" placeholder="Password" required style="padding-left: 50px; padding-right: 20px;" />
                        <svg class="bi bi-lock-fill input-box-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="left: 20px;">
                          <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path>
                        </svg>
                      </div>
                    </div>
                    
                    <!-- Confirm Password Input -->
                    <div class="mb-4 input-group-icon">
                      <label class="form-label visually-hidden" for="inputConfirmPassword">Confirm Password</label>
                      <div class="position-relative">
                        <input class="form-control rounded-pill border-300 input-box" id="inputConfirmPassword" name="confirm_password" type="password" placeholder="Confirm Password" required style="padding-left: 50px; padding-right: 20px;" />
                        <svg class="bi bi-shield-lock-fill input-box-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="left: 20px;">
                          <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.777 11.777 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7.159 7.159 0 0 0 1.048-.625 11.775 11.775 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.541 1.541 0 0 0-1.044-1.263 62.467 62.467 0 0 0-2.887-.87C9.843.266 8.69 0 8 0zm0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5z"></path>
                        </svg>
                      </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="form-check mb-4">
                      <input class="form-check-input" type="checkbox" id="termsCheck" name="terms" required>
                      <label class="form-check-label text-600" for="termsCheck">
                        I agree to the <a href="#" class="text-primary text-decoration-none">Terms and Conditions</a> and <a href="#" class="text-primary text-decoration-none">Privacy Policy</a>
                      </label>
                    </div>
                    
                    <!-- Sign Up Button -->
                    <div class="d-grid mb-4">
                      <button class="btn hover-top btn-glow btn-klean rounded-pill py-2" type="submit">
                        <span class="fw-bold">Create Account</span>
                      </button>
                    </div>
                    
                    <!-- Divider -->
                    <div class="position-relative text-center mb-4">
                      <hr class="text-300">
                      <div class="px-2 bg-white position-absolute top-50 start-50 translate-middle text-600">or</div>
                    </div>
                    
                    <!-- Login Section -->
                    <div class="text-center">
                      <p class="text-600 mb-3">Already have an account?</p>
                      <a href="login.php" class="btn btn-outline-klean rounded-pill px-4">
                        <span class="fw-bold text-gradient">Log In</span>
                      </a>
                    </div>
                  </form>
                </div>
              </div>
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
      
      // Client-side password validation
      document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('inputPassword').value;
        const confirmPassword = document.getElementById('inputConfirmPassword').value;
        
        if (password !== confirmPassword) {
          e.preventDefault();
          alert('Passwords do not match!');
          return false;
        }
        
        if (password.length < 6) {
          e.preventDefault();
          alert('Password must be at least 6 characters long!');
          return false;
        }
      });
    </script>
    <script src="assets/js/theme.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
  </body>
</html>