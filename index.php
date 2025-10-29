<!DOCTYPE html>
<html lang="en-US" dir="ltr">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pawprint Haven Landing Page</title>

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

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
      <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3 d-block navbar-klean" data-navbar-on-scroll="data-navbar-on-scroll">
        <div class="container">
          <!-- Larger logo -->
          <a class="navbar-brand" href="index.php"> 
            <img class="me-3 d-inline-block" src="assets/img/gallery/logo.png" alt="" style="height: 65px;" />
          </a>
          <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse border-top border-lg-0 mt-4 mt-lg-0" id="navbarSupportedContent">
            <!-- Updated navigation menu -->
            <ul class="navbar-nav me-auto pt-2 pt-lg-0 font-base">
              <li class="nav-item px-2" data-anchor="data-anchor">
                <a class="nav-link fw-medium active" aria-current="page" href="#home">Home</a>
              </li>
              <li class="nav-item px-2" data-anchor="data-anchor">
                <a class="nav-link" href="#about">About Us</a>
              </li>
              <li class="nav-item px-2" data-anchor="data-anchor">
                <a class="nav-link" href="#service">Services</a>
              </li>
              <li class="nav-item px-2" data-anchor="data-anchor">
                <a class="nav-link" href="#team">Our Team</a>
              </li>
            </ul>
            <!-- Updated buttons - removed Sign In, changed Sign Up to Log In -->
            <form class="ps-lg-5" action="login.php" method="GET">
              <button class="btn btn-light shadow-klean order-0" type="submit">
                <span class="text-gradient fw-bold">Log In</span>
              </button>
            </form>
          </div>
        </div>
      </nav>

      <!-- Home/Hero Section -->
      <section id="home">
        <!-- Background with cover size -->
        <div class="bg-holder d-none d-md-block bg-size" style="background-image:url(assets/img/illustrations/background.png);background-position:center;background-size:cover;">
        </div>
        <!--/.bg-holder-->

        <div class="bg-holder" style="background-image:url(assets/img/illustrations/background.png);background-position:center;background-size:cover;">
        </div>
        <!--/.bg-holder-->

        <div class="container">
          <div class="row align-items-center">
            <div class="col-md-7 col-lg-6 py-6 text-sm-start text-center">
              <h1 class="fw-bold display-4 fs-4 fs-lg-6 fs-xxl-7 text-gradient">Welcome to Pawprint Haven</h1>
              <h1 class="text-700">Your trusted partner in <span class="fw-bold">pet care</span></h1>
              <p class="mb-5 fs-0">We offer comprehensive pet care services to ensure your furry friends are happy, healthy, and well-cared for.</p>
              <a class="btn hover-top btn-glow btn-klean" href="#service">Our Services</a>
            </div>
          </div>
        </div>
      </section>

      <!-- About Us Section -->
      <section id="about" class="py-7">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
              <h2 class="fw-bold mb-4">About Pawprint Haven</h2>
              <p class="mb-5">At Pawprint Haven, we believe every pet deserves the best care possible. Our team of experienced professionals is dedicated to providing exceptional services for your beloved companions.</p>
              <div class="row">
                <div class="col-md-4 mb-4">
                  <h4>Our Mission</h4>
                  <p>To provide outstanding care that pets love and owners trust.</p>
                </div>
                <div class="col-md-4 mb-4">
                  <h4>Our Vision</h4>
                  <p>A world where every pet receives the care and attention they deserve.</p>
                </div>
                <div class="col-md-4 mb-4">
                  <h4>Our Values</h4>
                  <p>Compassion, excellence, and dedication in everything we do.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Services Section -->
      <section class="py-0 circle-blend circle-blend-right circle-warning" id="service">
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

      <!-- Our Team Section -->
      <section id="team" class="py-7">
        <div class="container">
          <div class="row justify-content-center mb-6">
            <div class="col-lg-6 text-center mx-auto mb-7">
              <h5 class="fw-bold fs-3 fs-lg-5 lh-sm mb-3">Our Team</h5>
              <p class="mb-0">Meet our dedicated team of veterinary professionals who are passionate about animal welfare.</p>
            </div>
          </div>
          <div class="row flex-center g-0">
            <!-- Vet 1: Xia Ekavira -->
            <div class="col-sm-6 col-lg-4 text-center">
              <div class="wrapper shadow-square-right">
                <img class="team-card-1" src="assets/img/gallery/xia.jpg" width="200" alt="Xia Ekavira" />
              </div>
              <h5 class="text-800 fw-bold mt-4 mb-1">Xia Ekavira</h5>
              <p>Veterinarian</p>
            </div>
            
            <!-- Vet 2: Rosemi Lovelock -->
            <div class="col-sm-6 col-lg-4 text-center">
              <div class="wrapper shadow-square-left">
                <img class="team-card-2" src="assets/img/gallery/rosemi1.jpg" width="200" alt="Rosemi Lovelock" />
              </div>
              <h5 class="text-800 fw-bold mt-4 mb-1">Rosemi Lovelock</h5>
              <p>Veterinarian</p>
            </div>
            
            <!-- Admin: Anya Melfisa -->
            <div class="col-sm-6 col-lg-4 text-center">
              <div class="wrapper shadow-square-right">
                <img class="team-card-3" src="assets/img/gallery/anya.jpg" width="200" alt="Anya Melfisa" />
              </div>
              <h5 class="text-800 fw-bold mt-4 mb-1">Anya Melfisa</h5>
              <p>Administrator</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <section class="pb-0 pt-6">
        <div class="container">
          <div class="row justify-content-lg-between">
            <!-- Location -->
            <div class="col-12 col-sm-6 col-lg-4 mb-5">
              <h5 class="text-600 mb-3 fw-bold">Location</h5>
              <p class="text-400">43/A Spooner Street<br>St Laurence, Virginia<br>Texas, 75001</p>
            </div>
            
            <!-- Phone Number -->
            <div class="col-12 col-sm-6 col-lg-4 mb-5">
              <h5 class="text-600 mb-3 fw-bold">Phone Number</h5>
              <p class="text-400">+62 0852-7767-0706</p>
            </div>
            
            <!-- Opening Hours -->
            <div class="col-12 col-sm-6 col-lg-4 mb-5">
              <h5 class="text-600 mb-3 fw-bold">Opening Hours</h5>
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

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
  </body>
</html>