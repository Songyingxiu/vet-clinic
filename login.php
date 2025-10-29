<?php
// Start output buffering to prevent header issues
ob_start();
session_start();

// Include database connection
require_once 'connection.php';

// Check for signup success message
if (isset($_SESSION['signup_success'])) {
    $success = $_SESSION['signup_success'];
    unset($_SESSION['signup_success']);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        try {
            // Find user with this email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Use plain text password comparison
                if ($password === $user['password']) {
                    // Login successful - Set session variables that match your vet dashboard
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['fullname'] = $user['full_name'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['phone'] = $user['phone'];
                    
                    // IMPORTANT: Your vet dashboard expects 'user_type' but your login sets 'role'
                    // Let's set both to be safe
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_type'] = $user['role']; // Set user_type for vet dashboard
                    
                    $_SESSION['logged_in'] = true;
                    
                    // Set specific session variables based on role
                    $role = strtolower(trim($user['role']));
                    
                    if ($role == 'vet') {
                        $_SESSION['vet_id'] = $user['user_id'];
                        $_SESSION['vet_name'] = $user['full_name'];
                    } elseif ($role == 'owner') {
                        $_SESSION['owner_id'] = $user['user_id'];
                        $_SESSION['owner_name'] = $user['full_name'];
                    } elseif ($role == 'admin') {
                        $_SESSION['admin_id'] = $user['user_id'];
                        $_SESSION['admin_name'] = $user['full_name'];
                    }
                    
                    // Debug: Check role before redirect
                    error_log("User role: " . $user['role'] . " - Redirecting...");
                    
                    // Redirect based on role with proper case handling
                    if ($role == 'admin') {
                        header("Location: admin.php");
                        exit();
                    } else if ($role == 'vet') {
                        header("Location: vet.php");
                        exit();
                    } else if ($role == 'owner') {
                        header("Location: patient.php");
                        exit();
                    } else {
                        header("Location: patient.php");
                        exit();
                    }
                } else {
                    $error = "Invalid email or password";
                }
            } else {
                $error = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
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
    <title>Login - Pawprint Haven</title>

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
      <!-- Login Section -->
      <section id="login" class="py-8" style="margin-top: 50px;">
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
                    <h2 class="fw-bold text-gradient mb-3">Welcome Back</h2>
                    <p class="text-600">Sign in to your Pawprint Haven account</p>
                  </div>
                  
                  <!-- Success Message (for registration) -->
                  <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                      <?php echo htmlspecialchars($success); ?>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                  <?php endif; ?>
                  
                  <!-- Error Message -->
                  <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                      <?php echo htmlspecialchars($error); ?>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                  <?php endif; ?>
                  
                  <form action="login.php" method="POST">
                    <!-- Email Input -->
                    <div class="mb-4 input-group-icon">
                      <label class="form-label visually-hidden" for="inputEmail">Email Address</label>
                      <div class="position-relative">
                        <input class="form-control rounded-pill border-300 input-box" id="inputEmail" name="email" type="email" placeholder="Email Address" required style="padding-left: 50px; padding-right: 20px;" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                        <svg class="bi bi-envelope-fill input-box-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="left: 20px;">
                          <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555zM0 4.697v7.104l5.803-3.558L0 4.697zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757zm3.436-.586L16 11.801V4.697l-5.803 3.546z"></path>
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
                    
                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                        <label class="form-check-label text-600" for="rememberMe">
                          Remember me
                        </label>
                      </div>
                      <a href="forgot-password.php" class="text-primary text-decoration-none">Forgot password?</a>
                    </div>
                    
                    <!-- Login Button -->
                    <div class="d-grid mb-4">
                      <button class="btn hover-top btn-glow btn-klean rounded-pill py-2" type="submit">
                        <span class="fw-bold">Log In</span>
                      </button>
                    </div>
                    
                    <!-- Divider -->
                    <div class="position-relative text-center mb-4">
                      <hr class="text-300">
                      <div class="px-2 bg-white position-absolute top-50 start-50 translate-middle text-600">or</div>
                    </div>
                    
                    <!-- Sign Up Section -->
                    <div class="text-center">
                      <p class="text-600 mb-3">Don't have an account?</p>
                      <a href="signup.php" class="btn btn-outline-klean rounded-pill px-4">
                        <span class="fw-bold text-gradient">Sign Up</span>
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
    </script>
    <script src="assets/js/theme.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
  </body>
</html>
<?php ob_end_flush(); ?>