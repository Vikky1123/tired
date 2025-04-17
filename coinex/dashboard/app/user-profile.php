<?php
/**
 * User Profile Page
 * Shows user profile information
 */

// Suppress all PHP errors from being displayed to the user
error_reporting(0);
ini_set('display_errors', 0);

// Define the path to user_session.php
$projectRoot = $_SERVER['DOCUMENT_ROOT'] . '/PROJECT-BITRADER';
$dashboardDir = $projectRoot . '/coinex/dashboard';

// Include user session management with absolute path
require_once $dashboardDir . '/user_session.php';

// Anti-loop detection - if we're coming from login page, skip another redirect
$refererIsLoginPage = false;
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    if (strpos($referer, 'Signup-Signin') !== false) {
        $refererIsLoginPage = true;
    }
}

// DEBUG FIX: Skip all redirects to stop the infinite loop
$skipAllRedirects = true;

// Redirect to login if not authenticated, but avoid redirect loops
$userId = getCurrentUserId();
if (!$userId && !$skipAllRedirects && !$refererIsLoginPage) {
    // Add a URL parameter to prevent client-side redirect loops
    header('Location: /PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/index.html?no-redirect=true');
    exit;
}

// Get user data
$userData = getCurrentUser();
if (!$userData && !$skipAllRedirects && !$refererIsLoginPage) {
    // Add a URL parameter to prevent client-side redirect loops
    header('Location: /PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/index.html?no-redirect=true');
    exit;
}

// If we don't have valid data but came from login, show a helpful error instead of redirecting
if ((!$userId || !$userData) && ($refererIsLoginPage || $skipAllRedirects)) {
    // We'll continue to show the profile with an error message
    $authError = true;
} else {
    $authError = false;
}

// Get user financial data (only if authenticated)
$financialData = $authError ? [] : getUserFinancialData($userId);

// Safely get values with error checking to prevent PHP warnings
$total_balance_usd = isset($financialData['total_balance_usd']) ? $financialData['total_balance_usd'] : 0;
$total_profit = isset($financialData['total_profit']) ? $financialData['total_profit'] : 0;

// Format numbers for display
$formattedBalance = $authError ? '0.00' : number_format($total_balance_usd, 2);
$formattedProfit = $authError ? '0.00' : number_format($total_profit, 2);

// Get username for display (with fallbacks for auth errors)
$username = $authError ? 'Guest' : htmlspecialchars(isset($userData['username']) ? $userData['username'] : 'Unknown');
$fullName = $authError ? 'Not logged in' : htmlspecialchars(isset($userData['full_name']) && $userData['full_name'] ? $userData['full_name'] : 'Not set');
$email = $authError ? 'Please login' : htmlspecialchars(isset($userData['email']) ? $userData['email'] : 'Not available');
$phone = $authError ? 'Not available' : htmlspecialchars(isset($userData['phone']) && $userData['phone'] ? $userData['phone'] : 'Not set');
$country = $authError ? 'Not available' : htmlspecialchars(isset($userData['country']) && $userData['country'] ? $userData['country'] : 'Not set');
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
  
<head>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>COINEX | User Profile</title>
      <!-- Favicon -->
      <link rel="shortcut icon" href="../assets/images/favicon.ico" />
      <link rel="stylesheet" href="../assets/css/core/libs.min.css">
      <link rel="stylesheet" href="../assets/css/coinex.min862f.css?v=4.1.0">
      <link rel="stylesheet" href="../assets/css/custom.min862f.css?v=4.1.0">
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&amp;display=swap" rel="stylesheet">
      
      <script>
      // Simple theme and error handling script
      document.addEventListener('DOMContentLoaded', function() {
          // Fix broken images
          document.querySelectorAll('img').forEach(function(img) {
              img.onerror = function() {
                  this.style.display = 'none';
              };
          });
          
          // Fix COINEX logo spelling in the sidebar
          const logoTitles = document.querySelectorAll('.logo-title');
          if (logoTitles.length) {
              logoTitles.forEach(element => {
                  if (element.textContent === 'OINEX') {
                      element.textContent = 'COINEX';
                  }
              });
          }
          
          // Add logout event handler
          document.querySelectorAll('.logout-btn, a[href*="logout"]').forEach(function(btn) {
              btn.addEventListener('click', function(e) {
                  e.preventDefault();
                  localStorage.removeItem('authToken');
                  localStorage.removeItem('userData');
                  localStorage.removeItem('authRedirectCount');
                  localStorage.removeItem('preventRedirectLoop');
                  window.location.href = '/PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/index.html';
              });
          });
          
          <?php if ($authError): ?>
          // Create error message div for auth errors
          const errorDiv = document.createElement('div');
          errorDiv.className = 'alert alert-danger mb-0';
          errorDiv.style.position = 'fixed';
          errorDiv.style.top = '0';
          errorDiv.style.left = '0';
          errorDiv.style.right = '0';
          errorDiv.style.zIndex = '9999';
          errorDiv.style.borderRadius = '0';
          errorDiv.style.textAlign = 'center';
          errorDiv.style.padding = '15px';
          
          errorDiv.innerHTML = `
              <strong>Authentication Error:</strong> You are not properly logged in. 
              <br>Please <a href="/PROJECT-BITRADER/bitrader.thetork.com/Signup-Signin/index.html" class="alert-link">go to the login page</a> and try again.
          `;
          
          document.body.prepend(errorDiv);
          
          // Hide loader if it exists
          const loader = document.getElementById('loading');
          if (loader) {
              loader.style.display = 'none';
          }
          <?php endif; ?>
      });
      </script>
  </head>
  <body class=" ">
    <!-- loader Start -->
    <div id="loading">
      <div class="loader simple-loader">
          <div class="loader-body"></div>
      </div>    </div>
    <!-- loader END -->
    <aside class="sidebar sidebar-default navs-rounded ">
        <div class="sidebar-header d-flex align-items-center justify-content-start">
            <a href="../index.php" class="navbar-brand dis-none align-items-center">
                <img src="../assets/images/logo.svg" class="img-fluid "  alt="logo">            <h4 class="logo-title m-0">OINEX</h4>
            </a>
            <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                <i class="icon">
                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5"></path>
                        <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5"></path>
                    </svg>
                </i>
            </div>
        </div>
        <div class="sidebar-body p-0 data-scrollbar">
            <div class="navbar-collapse pe-3" id="sidebar">
                <ul class="navbar-nav iq-main-menu">
                    <li class="nav-item ">
                        <a class="nav-link " aria-current="page" href="../index.php">
                            <i class="icon">
                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.14373 20.7821V17.7152C9.14372 16.9381 9.77567 16.3067 10.5584 16.3018H13.4326C14.2189 16.3018 14.8563 16.9346 14.8563 17.7152V20.7732C14.8562 21.4473 15.404 21.9951 16.0829 22H18.0438C18.9596 22.0023 19.8388 21.6428 20.4872 21.0007C21.1356 20.3586 21.5 19.4868 21.5 18.5775V9.86585C21.5 9.13139 21.1721 8.43471 20.6046 7.9635L13.943 2.67427C12.7785 1.74912 11.1154 1.77901 9.98539 2.74538L3.46701 7.9635C2.87274 8.42082 2.51755 9.11956 2.5 9.86585V18.5686C2.5 20.4637 4.04738 22 5.95617 22H7.87229C8.19917 22.0023 8.51349 21.8751 8.74547 21.6464C8.97746 21.4178 9.10793 21.1067 9.10792 20.7821H9.14373Z"
                                        fill="currentColor"></path>
                                </svg>
                            </i>
                            <span class="item-name">Dashboard</span>
                        </a>
                    </li>
                    
                        </ul>
                    </li>
                    
                    <li><hr class="hr-horizontal"></li>
                    
                    
                    
                    
                    
                    
                </ul>        </div>        
            <div id="sidebar-footer" class="position-relative sidebar-footer">
                <div class="card mx-4">
                    <div class="card-body">
                        <div class="sidebarbottom-content">
                            <div class="image">
                                <img src="../assets/images/coins/13.png" alt="User-Profile" class="img-fluid">
                            </div>
                            <p class="mb-0">Be more secure with Pro Feature</p>
                            <button type="button" class="btn btn-primary mt-3">Upgrade Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>    <main class="main-content">
      <div class="position-relative">
        <!--Nav Start-->
        <nav class="nav navbar navbar-expand-lg navbar-light iq-navbar border-bottom pb-lg-3  pt-lg-3 ">
          <div class="container-fluid navbar-inner">
            <a href="../index.html" class="navbar-brand">
            </a>
            <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                <i class="icon">
                 <svg width="20px" height="20px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
                </svg>
                </i>
            </div>
              <h4 class="title">
                Dashboard
              </h4>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon">
                  <span class="navbar-toggler-bar bar1"></span>
                  <span class="navbar-toggler-bar bar2"></span>
                  <span class="navbar-toggler-bar bar3"></span>
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav ms-auto navbar-list mb-2 mb-lg-0 align-items-center">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link" id="search-drop" data-bs-toggle="dropdown" aria-expanded="false">
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>
                            <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"></path>
                          </svg>
                    </a>
                    <ul class="p-0 sub-drop dropdown-menu dropdown-menu-end" aria-labelledby="search-drop">
                      <li class="">
                          <div class="p-3 card-header justify-content-between border-bottom rounded-top">
                            <div class="header-title">
                                <div class="iq-search-bar device-search  position-relative">
                                      <form action="#" class="searchbox">
                                        <input type="text" class="text search-input form-control" placeholder="Search here...">
                                        <a class="d-lg-none d-flex" href="javascript:void(0);">
                                            <span class="material-symbols-outlined">search</span>
                                        </a>
                                      </form>
                                </div>
                            </div>
                          </div>
                          <div class="p-0 card-body all-notification">
                            <div class="d-flex align-items-center border-bottom search-hover py-2 px-3">
                                  <div class="flex-shrink-0">
                                      <img src="../assets/images/avatars/01.png" class="align-self-center img-fluid avatar-50 rounded-pill" alt="#">
                                  </div>
                                  <div class="d-flex flex-column ms-3 w-100">
                                      <a href="javascript:void(0);" class="h6 mb-1">User Name</a>
                                      <small>Username</small>
                                  </div>
                            </div>
                            <div class="d-flex align-items-center border-bottom search-hover py-2 px-3">
                                <div class="flex-shrink-0">
                                  <img src="../assets/images/avatars/02.png" class="align-self-center img-fluid avatar-50 rounded-pill" alt="#">
                                </div>
                                <div class="d-flex flex-column ms-3 w-100">
                                  <a href="javascript:void(0);" class="h6 mb-1">User Name</a>
                                  <small>Username</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center search-hover py-2 px-3 border-bottom">
                                <div class="flex-shrink-0">
                                  <img src="../assets/images/avatars/03.png" class="align-self-center img-fluid avatar-50 rounded-pill" alt="#">
                                </div>
                                <div class="d-flex flex-column ms-3 w-100">
                                  <a href="javascript:void(0);" class="h6 mb-1">User Name</a>
                                  <small>Username</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center border-bottom search-hover py-2 px-3">
                                <div class="flex-shrink-0">
                                  <img src="../assets/images/avatars/04.png" class="align-self-center img-fluid avatar-50 rounded-pill" alt="#">
                                </div>
                                <div class="d-flex flex-column ms-3 w-100">
                                  <a href="javascript:void(0);" class="h6 mb-1">User Name</a>
                                  <small>Username</small>
                                </div>
                            </div>
                          </div> 
                      </li>  
                    </ul>  
                </li>
                <li class="nav-item dropdown">
                  <a href="#"  class="nav-link" id="notification-drop" data-bs-toggle="dropdown" >
                    <svg width="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 17.8476C17.6392 17.8476 20.2481 17.1242 20.5 14.2205C20.5 11.3188 18.6812 11.5054 18.6812 7.94511C18.6812 5.16414 16.0452 2 12 2C7.95477 2 5.31885 5.16414 5.31885 7.94511C5.31885 11.5054 3.5 11.3188 3.5 14.2205C3.75295 17.1352 6.36177 17.8476 12 17.8476Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14.3889 20.8572C13.0247 22.3719 10.8967 22.3899 9.51953 20.8572" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>                            
                    <span class="bg-danger dots"></span>
                  </a>
                  <div class="sub-drop dropdown-menu iq-noti dropdown-menu-end p-0" aria-labelledby="notification-drop">
                      <div class="card shadow-none m-0 bg-transparent">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                              <p class="fs-6 ">New notifications.</p>
                            </div>
                            <div class="header-title">
                              <p class="fs-6">Mark all</p>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <a href="#" class="iq-sub-card">
                              <div class="d-flex">
                                  <img src="../assets/images/utilities/05.png" class="img-fluid avatar avatar-30 avatar-rounded" alt="img51"><div class="ms-3 w-100">
                                    <h6 class="mb-0 ">Bitcoin</h6>
                                    <small class="float-left font-size-12">Cryptocurrency market information</small>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0">15 mins ago</p>
                                        </div>
                                  </div>
                              </div>
                            </a>
                            <a href="#" class="iq-sub-card">
                              <div class="d-flex align-items-center">
                                  <div class="">
                                    <img src="../assets/images/utilities/03.png" class="img-fluid avatar avatar-30 avatar-rounded" alt="img52">
                                  </div>
                                  <div class="ms-3 w-100">
                                    <h6 class="mb-0 ">Ethereum</h6>
                                    <small class="float-left font-size-12">Cryptocurrency market information</small>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0">30 mins ago</p>
                                        
                                    </div>
                                  </div>
                              </div>
                            </a>
                            <a href="#" class="iq-sub-card">
                              <div class="d-flex align-items-center">
                                  <img src="../assets/images/utilities/04.png" class="img-fluid avatar avatar-30 avatar-rounded" alt="img53"><div class="ms-3 w-100">
                                    <h6 class="mb-0 ">Litecoin</h6>
                                    <small class="float-left font-size-12">Cryptocurrency market information</small>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0">55 mins ago</p>
                                        
                                    </div>
                                  </div>
                              </div>
                            </a>
                            <a href="#" class="iq-sub-card">
                              <div class="d-flex align-items-center">
                                  <img src="../assets/images/utilities/05.png" class="img-fluid avatar avatar-30 avatar-rounded" alt="img54"><div class="w-100 ms-3">
                                    <h6 class="mb-0 ">Great speed notify of 1.34 LTC</h6>
                                    <small class="float-left font-size-12">Cryptocurrency market information</small>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0">14 Mar</p>
                                        
                                    </div>
                                  </div>
                              </div>
                            </a>
                        </div>
                      </div>
                  </div>
                </li>
                <li class="nav-item dropdown" >
                  <a href="#" class="nav-link" id="mail-drop" data-bs-toggle="dropdown"  aria-haspopup="true" aria-expanded="false">
                    <svg width="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.9028 8.85107L13.4596 12.4641C12.6201 13.1301 11.4389 13.1301 10.5994 12.4641L6.11865 8.85107" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M16.9089 21C19.9502 21.0084 22 18.5095 22 15.4384V8.57001C22 5.49883 19.9502 3 16.9089 3H7.09114C4.04979 3 2 5.49883 2 8.57001V15.4384C2 18.5095 4.04979 21.0084 7.09114 21H16.9089Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>                            
                    <span class="bg-primary count-mail"></span>
                  </a>
                  <div class="sub-drop dropdown-menu dropdown-menu-end p-0" aria-labelledby="mail-drop">
                      <div class="card shadow-none m-0 bg-transparent">
                        <div class="card-header d-flex justify-content-between py-3">
                            <div class="header-title">
                              <p class="mb-0 text-white">Our Latest News</p>
                            </div>
                        </div>
                        <div class="card-body p-0 ">
                            <a href="#" class="iq-sub-card">
                              <div class="d-flex ">
                                  <div class="">
                                    <img src="../assets/images/coins/02.png" class="img-fluid avatar avatar-50 avatar-rounded" alt="img55">
                                  </div>
                                  <div class=" w-100 ms-3">
                                    <h6 class="mb-0 ">Bitcoin</h6>
                                    <small class="float-left font-size-12">Cryptocurrency market information</small>
                                  </div>
                              </div>
                            </a>
                            <a href="#" class="iq-sub-card">
                              <div class="d-flex">
                                  <div class="">
                                    <img src="../assets/images/coins/03.png" class="img-fluid avatar avatar-50 avatar-rounded" alt="img56">
                                  </div>
                                  <div class="ms-3">
                                    <h6 class="mb-0 ">Ethereum</h6>
                                    <small class="float-left font-size-12">Cryptocurrency market information</small>
                                  </div>
                              </div>
                            </a>
                            <a href="#" class="iq-sub-card">
                              <div class="d-flex">
                                  <div class="">
                                    <img src="../assets/images/coins/06.png" class="img-fluid avatar avatar-50 avatar-rounded" alt="img57">
                                  </div>
                                  <div class="ms-3">
                                    <h6 class="mb-0 ">Litecoin</h6>
                                    <small class="float-left font-size-12">Cryptocurrency market information</small>
                                  </div>
                              </div>
                            </a>
                        </div>
                      </div>
                  </div>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link py-0 d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../assets/images/avatars/01.png" alt="User-Profile" class="img-fluid avatar avatar-50 avatar-rounded">
                    <div class="caption ms-3 d-none d-md-block ">
                      <h6 class="mb-0 caption-title"><?php echo $fullName; ?></h6>
                      <p class="mb-0 caption-sub-title"><?php echo $email; ?></p>
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li class="border-0"><a class="dropdown-item" href="user-profile.php">Profile</a></li>
                    <li class="border-0"><a class="dropdown-item" href="../auth/sign-out.php">Logout</a></li>
                  </ul>
                </li>
              </ul>
            </div>
          </div>
        </nav>        <!--Nav End-->
      </div>
      <div class="container-fluid content-inner pb-0">
      <div class="row">
          <div class="col-lg-12">
             <div class="card">
               <div class="iq-header-img bg-secondary-subtle iq-user-profile-bg">
                  <img src="../assets/images/pages/02-page.png" alt="header" class="img-fluid w-100 h-100 object-fit-cover">
               </div>
                  <div class="card-body mt-n6">
                     <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex flex-wrap align-items-center">
                           <div class="profile-img position-relative me-3 mb-3 mb-lg-0">
                              <img src="../assets/images/avatars/01.png" class="img-fluid avatar avatar-100 avatar-rounded" alt="profile-image">
                           </div>
                           <div class="d-flex align-items-center mb-3 mb-sm-0">
                              <div>
                                 <h6 class="me-2 text-warning"><?php echo $fullName; ?></h6>
                                 <p><?php echo $username; ?></p>
                              </div>
                              <div class="ms-3">
                                 <h6 class="me-2"><?php echo $email; ?></h6>
                                 <p><?php echo $phone; ?></p>
                              </div> 
                           </div>
                        </div>
                     </div>
                  </div>
             </div>
          </div>
      </div>
      <div class="row">
         <div class="col-md-12">
            <div class="row">
               <div class="col-lg-3">
             <div class="card">
               <div class="card-header">
                  <div class="header-title">
                     <h4 class="card-title text-primary">News</h4>
                  </div>
               </div>
               <div class="card-body">
                  <ul class="list-inline m-0 p-0">
                     <li class="d-flex mb-2">
                        <div class="news-icon me-3">
                           <svg width="20" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M20,2H4A2,2 0 0,0 2,4V22L6,18H20A2,2 0 0,0 22,16V4C22,2.89 21.1,2 20,2Z" />
                           </svg>
                        </div>
                        <p class="news-detail mb-0">Latest cryptocurrency market updates <a href="#">see details</a></p>
                     </li>
                     <li class="d-flex">
                        <div class="news-icon me-3">
                           <svg width="20" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M20,2H4A2,2 0 0,0 2,4V22L6,18H20A2,2 0 0,0 22,16V4C22,2.89 21.1,2 20,2Z" />
                           </svg>
                        </div>
                        <p class="news-detail mb-0">Cryptocurrency price movement updates</p>
                     </li>
                  </ul>
               </div>
             </div>
             <div class="card">
               <div class="card-header d-flex align-items-center justify-content-between">
                  <div class="header-title">
                     <h4 class="card-title text-primary">Interset</h4>
                  </div>
               </div>
               <div class="card-body">
                  <div class="d-grid gap-card grid-cols-3">
                     <a data-fslightbox="Interset" href="../assets/images/coins/04.png">
                        <img src="../assets/images/coins/04.png" class="img-fluid  rounded" alt="profile-image">
                     </a>
                     <a data-fslightbox="Interset" href="../assets/images/coins/01.png">
                        <img src="../assets/images/coins/01.png" class="img-fluid rounded" alt="profile-image">
                     </a>
                     <a data-fslightbox="Interset" href="../assets/images/coins/12.png">
                        <img src="../assets/images/coins/12.png" class="img-fluid rounded" alt="profile-image">
                     </a>
                     <a data-fslightbox="Interset" href="../assets/images/coins/09.png">
                        <img src="../assets/images/coins/09.png" class="img-fluid rounded" alt="profile-image">
                     </a>
                     <a data-fslightbox="Interset" href="../assets/images/coins/10.png">
                        <img src="../assets/images/coins/10.png" class="img-fluid rounded" alt="profile-image">
                     </a>
                     <a data-fslightbox="Interset" href="../assets/images/coins/13.png">
                        <img src="../assets/images/coins/13.png" class="img-fluid rounded" alt="profile-image">
                     </a>
                  </div>
               </div>
             </div>
             <div class="card">
               <div class="card-header">
                  <div class="header-title">
                     <h4 class="card-title text-primary">Our Letest News</h4>
                  </div>
               </div>
               <div class="card-body">
                  <div class="twit-feed">
                     <div class="d-flex align-items-center mb-2">
                        <img class="rounded-pill img-fluid avatar-50 me-3   ps-2" src="../assets/images/coins/01.png" alt="">
                        <div class="media-support-info">
                           <h6 class="mb-0">Bitcoin</h6>
                           <p class="mb-0">Bitcoin Price 
                              <span class="text-primary">
                                 <svg width="15" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                 </svg>
                              </span>
                           </p>
                        </div>
                     </div>
                     <div class="media-support-body">
                        <p class="mb-0">Market updates and cryptocurrency analysis will appear here</p>
                        <div class="twit-date">Date</div>
                     </div>
                  </div>
                  <hr class="my-3">
                  <div class="twit-feed">
                     <div class="d-flex align-items-center mb-2">
                        <img class="rounded-pill img-fluid avatar-50 me-3" src="../assets/images/coins/04.png" alt="">
                        <div class="media-support-info">
                           <h6 class="mb-0">LiteCoin</h6>
                           <p class="mb-0">Litecoin Price
                              <span class="text-primary">
                                 <svg width="15" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M10,17L5,12L6.41,10.58L10,14.17L17.59,6.58L19,8M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                 </svg>
                              </span>
                           </p>
                        </div>
                     </div>
                     <div class="media-support-body">
                        <p class="mb-0">Cryptocurrency market analysis and price information will appear here</p>
                        <div class="twit-date mt-2">Date</div>
                     </div>
                  </div>
               </div>
            </div>
          </div>
               <div class="col-lg-6">
                  <div class="profile-content tab-content">
               <div id="profile-feed" class="tab-pane fade active show">
                  <div class="card">
                     <div class="card-header d-flex align-items-center justify-content-between pb-4">
                        <div class="header-title">
                           <div class="d-flex flex-wrap">
                              <div class="media-support-user-img me-3">
                                 <img class="rounded-pill img-fluid avatar-60 bg-danger-subtle " src="../assets/images/avatars/02.png" alt="">
                              </div>
                              <div class="media-support-info mt-2">
                                 <h5 class="mb-0">User Name</h5>
                                 <p class="mb-0 text-primary">Position</p>
                              </div>
                           </div>
                        </div>                        
                        <div class="dropdown">
                              <a href="#" class="text-white dropdown-toggle" id="dropdownMenuButton7" data-bs-toggle="dropdown" aria-expanded="false">
                           Time 
                              </a>
                              <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton7">
                                 <li> <a class="dropdown-item " href="javascript:void(0);">Action</a></li>
                                 <li><a class="dropdown-item " href="javascript:void(0);">Another action</a></li>
                                 <li><a class="dropdown-item " href="javascript:void(0);">Something else here</a></li>
                              </ul>
                           </div>
                     </div>
                     <div class="card-body p-0">
                        <div class="user-post">
                           <a href="javascript:void(0);"><img src="../assets/images/pages/01-page.png" alt="post-image" class="img-fluid"></a>
                        </div>
                        <div class="comment-area p-3">
                           <div class="d-flex flex-wrap justify-content-between align-items-center">
                              <div class="d-flex align-items-center">
                                 <div class="d-flex align-items-center message-icon me-3">                                          
                                    <svg width="20" height="20" viewBox="0 0 24 24">
                                       <path fill="currentColor" d="M12.1,18.55L12,18.65L11.89,18.55C7.14,14.24 4,11.39 4,8.5C4,6.5 5.5,5 7.5,5C9.04,5 10.54,6 11.07,7.36H12.93C13.46,6 14.96,5 16.5,5C18.5,5 20,6.5 20,8.5C20,11.39 16.86,14.24 12.1,18.55M16.5,3C14.76,3 13.09,3.81 12,5.08C10.91,3.81 9.24,3 7.5,3C4.42,3 2,5.41 2,8.5C2,12.27 5.4,15.36 10.55,20.03L12,21.35L13.45,20.03C18.6,15.36 22,12.27 22,8.5C22,5.41 19.58,3 16.5,3Z" />
                                    </svg>
                                    <span class="ms-1">140</span>
                                 </div>
                                 <div class="d-flex align-items-center feather-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24">
                                       <path fill="currentColor" d="M9,22A1,1 0 0,1 8,21V18H4A2,2 0 0,1 2,16V4C2,2.89 2.9,2 4,2H20A2,2 0 0,1 22,4V16A2,2 0 0,1 20,18H13.9L10.2,21.71C10,21.9 9.75,22 9.5,22V22H9M10,16V19.08L13.08,16H20V4H4V16H10Z" />
                                    </svg>
                                    <span class="ms-1">140</span>
                                 </div>
                              </div>
                              <div class="share-block d-flex align-items-center feather-icon">
                                 <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#share-btn" aria-controls="share-btn">
                                 <span class="ms-1">
                                       <svg width="18" class="me-1" viewBox="0 0 24 24">
                                          <path fill="currentColor" d="M18 16.08C17.24 16.08 16.56 16.38 16.04 16.85L8.91 12.7C8.96 12.47 9 12.24 9 12S8.96 11.53 8.91 11.3L15.96 7.19C16.5 7.69 17.21 8 18 8C19.66 8 21 6.66 21 5S19.66 2 18 2 15 3.34 15 5C15 5.24 15.04 5.47 15.09 5.7L8.04 9.81C7.5 9.31 6.79 9 6 9C4.34 9 3 10.34 3 12S4.34 15 6 15C6.79 15 7.5 14.69 8.04 14.19L15.16 18.34C15.11 18.55 15.08 18.77 15.08 19C15.08 20.61 16.39 21.91 18 21.91S20.92 20.61 20.92 19C20.92 17.39 19.61 16.08 18 16.08M18 4C18.55 4 19 4.45 19 5S18.55 6 18 6 17 5.55 17 5 17.45 4 18 4M6 13C5.45 13 5 12.55 5 12S5.45 11 6 11 7 11.45 7 12 6.55 13 6 13M18 20C17.45 20 17 19.55 17 19S17.45 18 18 18 19 18.45 19 19 18.55 20 18 20Z"></path>
                                       </svg>
                                       99 Share</span></a>
                              </div>
                           </div>
                           <hr>
                           <p>Cryptocurrency market analysis and updates. Track market trends, follow price movements, and stay informed about the latest developments in the crypto space.</p>
                           <hr>
                           <ul class="list-inline p-0 m-0">
                              <li class="mb-2">
                                 <div class="d-flex">
                                    <img src="../assets/images/avatars/03.png" alt="userimg" class="avatar-50  rounded-pill img-fluid">
                                    <div class="ms-3">
                                       <h6 class="mb-1">User Name</h6>
                                       <p class="mb-1">Great insights on Bitcoin trends!</p>
                                       <div class="d-flex flex-wrap align-items-center mb-1">
                                          <a href="javascript:void(0);" class="me-3">
                                             <svg width="20" height="20" class="text-body me-1" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M12.1,18.55L12,18.65L11.89,18.55C7.14,14.24 4,11.39 4,8.5C4,6.5 5.5,5 7.5,5C9.04,5 10.54,6 11.07,7.36H12.93C13.46,6 14.96,5 16.5,5C18.5,5 20,6.5 20,8.5C20,11.39 16.86,14.24 12.1,18.55M16.5,3C14.76,3 13.09,3.81 12,5.08C10.91,3.81 9.24,3 7.5,3C4.42,3 2,5.41 2,8.5C2,12.27 5.4,15.36 10.55,20.03L12,21.35L13.45,20.03C18.6,15.36 22,12.27 22,8.5C22,5.41 19.58,3 16.5,3Z" />
                                             </svg>
                                             like
                                          </a>
                                          <a href="javascript:void(0);" class="me-3">
                                             <svg width="20" height="20" class="me-1" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M8,9.8V10.7L9.7,11C12.3,11.4 14.2,12.4 15.6,13.7C13.9,13.2 12.1,12.9 10,12.9H8V14.2L5.8,12L8,9.8M10,5L3,12L10,19V14.9C15,14.9 18.5,16.5 21,20C20,15 17,10 10,9" />
                                             </svg>
                                             reply
                                          </a>
                                          <a href="javascript:void(0);" class="me-3">translate</a>
                                          <span> 5 min </span>
                                       </div>
                                    </div>
                                 </div>
                              </li>
                              <li>
                                 <div class="d-flex">
                                    <img src="../assets/images/avatars/04.png" alt="userimg" class="avatar-50  rounded-pill img-fluid">
                                    <div class="ms-3">
                                       <h6 class="mb-1">User Name</h6>
                                       <p class="mb-1">Thanks for sharing the market analysis.</p>
                                       <div class="d-flex flex-wrap align-items-center">
                                          <a href="javascript:void(0);" class="me-3">
                                             <svg width="20" height="20" class="text-body me-1" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M12.1,18.55L12,18.65L11.89,18.55C7.14,14.24 4,11.39 4,8.5C4,6.5 5.5,5 7.5,5C9.04,5 10.54,6 11.07,7.36H12.93C13.46,6 14.96,5 16.5,5C18.5,5 20,6.5 20,8.5C20,11.39 16.86,14.24 12.1,18.55M16.5,3C14.76,3 13.09,3.81 12,5.08C10.91,3.81 9.24,3 7.5,3C4.42,3 2,5.41 2,8.5C2,12.27 5.4,15.36 10.55,20.03L12,21.35L13.45,20.03C18.6,15.36 22,12.27 22,8.5C22,5.41 19.58,3 16.5,3Z" />
                                             </svg>
                                             like
                                          </a>
                                          <a href="javascript:void(0);" class="me-3">
                                             <svg width="20" height="20" class="me-1" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M8,9.8V10.7L9.7,11C12.3,11.4 14.2,12.4 15.6,13.7C13.9,13.2 12.1,12.9 10,12.9H8V14.2L5.8,12L8,9.8M10,5L3,12L10,19V14.9C15,14.9 18.5,16.5 21,20C20,15 17,10 10,9" />
                                             </svg>
                                             reply
                                          </a>
                                          <a href="javascript:void(0);" class="me-3">translate</a>
                                          <span> 5 min </span>
                                       </div>
                                    </div>
                                 </div>
                              </li>
                           </ul>
                           <form class="comment-text d-flex align-items-center mt-3" action="javascript:void(0);">
                              <input type="text" class="form-control rounded" placeholder="Lovely!">
                              <div class="comment-attagement d-flex">
                                    <a href="javascript:void(0);" class="me-2 text-body">
                                       <svg width="20" height="20" viewBox="0 0 24 24">
                                          <path fill="currentColor" d="M20,12A8,8 0 0,0 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12M22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2A10,10 0 0,1 22,12M10,9.5C10,10.3 9.3,11 8.5,11C7.7,11 7,10.3 7,9.5C7,8.7 7.7,8 8.5,8C9.3,8 10,8.7 10,9.5M17,9.5C17,10.3 16.3,11 15.5,11C14.7,11 14,10.3 14,9.5C14,8.7 14.7,8 15.5,8C16.3,8 17,8.7 17,9.5M12,17.23C10.25,17.23 8.71,16.5 7.81,15.42L9.23,14C9.68,14.72 10.75,15.23 12,15.23C13.25,15.23 14.32,14.72 14.77,14L16.19,15.42C15.29,16.5 13.75,17.23 12,17.23Z" />
                                       </svg>
                                    </a>
                                    <a href="javascript:void(0);" class="text-body">
                                       <svg width="20" height="20" viewBox="0 0 24 24">
                                          <path fill="currentColor" d="M20,4H16.83L15,2H9L7.17,4H4A2,2 0 0,0 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6A2,2 0 0,0 20,4M20,18H4V6H8.05L9.88,4H14.12L15.95,6H20V18M12,7A5,5 0 0,0 7,12A5,5 0 0,0 12,17A5,5 0 0,0 17,12A5,5 0 0,0 12,7M12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15Z" />
                                       </svg>
                                    </a>
                              </div>
                           </form>
                        </div>                              
                     </div>
                  </div>
                  <div class="card">
                     <div class="card-header d-flex align-items-center justify-content-between pb-4">
                        <div class="header-title">
                           <div class="d-flex flex-wrap">
                              <div class="media-support-user-img me-3">
                                 <img class="rounded-pill img-fluid avatar-60 " src="../assets/images/avatars/05.png" alt="">
                              </div>
                              <div class="media-support-info mt-2">
                                 <h5 class="mb-0">Wade Warren</h5>
                                 <p class="mb-0 text-primary">colleages</p>
                              </div>
                           </div>
                        </div>                        
                         <div class="dropdown">
                              <a href="#" class="text-white dropdown-toggle" id="dropdownMenuButton08" data-bs-toggle="dropdown" aria-expanded="false">
                           1 Hr 
                              </a>
                              <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton07">
                                 <li> <a class="dropdown-item " href="javascript:void(0);">Action</a></li>
                                 <li><a class="dropdown-item " href="javascript:void(0);">Another action</a></li>
                                 <li><a class="dropdown-item " href="javascript:void(0);">Something else here</a></li>
                              </ul>
                           </div>
                     </div>
                     <div class="card-body p-0">
                           <p class="p-3 mb-0 pt-0">The cryptocurrency market is showing signs of recovery as Bitcoin and Ethereum lead the rally. Market analysts predict continued growth in the coming weeks as institutional adoption increases.</p>
                           <div class="comment-area p-3"><hr class="mt-0">
                           <div class="d-flex flex-wrap justify-content-between align-items-center">
                              <div class="d-flex align-items-center">
                                 <div class="d-flex align-items-center message-icon me-3">                                          
                                    <svg width="20" height="20" viewBox="0 0 24 24">
                                       <path fill="currentColor" d="M12.1,18.55L12,18.65L11.89,18.55C7.14,14.24 4,11.39 4,8.5C4,6.5 5.5,5 7.5,5C9.04,5 10.54,6 11.07,7.36H12.93C13.46,6 14.96,5 16.5,5C18.5,5 20,6.5 20,8.5C20,11.39 16.86,14.24 12.1,18.55M16.5,3C14.76,3 13.09,3.81 12,5.08C10.91,3.81 9.24,3 7.5,3C4.42,3 2,5.41 2,8.5C2,12.27 5.4,15.36 10.55,20.03L12,21.35L13.45,20.03C18.6,15.36 22,12.27 22,8.5C22,5.41 19.58,3 16.5,3Z" />
                                    </svg>
                                    <span class="ms-1">140</span>
                                 </div>
                                 <div class="d-flex align-items-center feather-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24">
                                       <path fill="currentColor" d="M9,22A1,1 0 0,1 8,21V18H4A2,2 0 0,1 2,16V4C2,2.89 2.9,2 4,2H20A2,2 0 0,1 22,4V16A2,2 0 0,1 20,18H13.9L10.2,21.71C10,21.9 9.75,22 9.5,22V22H9M10,16V19.08L13.08,16H20V4H4V16H10Z" />
                                    </svg>
                                    <span class="ms-1">140</span>
                                 </div>
                              </div>
                              <div class="share-block d-flex align-items-center feather-icon">
                                 <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#share-btn" aria-controls="share-btn">
                                    <span class="ms-1">
                                       <svg width="18" class="me-1" viewBox="0 0 24 24">
                                          <path fill="currentColor" d="M18 16.08C17.24 16.08 16.56 16.38 16.04 16.85L8.91 12.7C8.96 12.47 9 12.24 9 12S8.96 11.53 8.91 11.3L15.96 7.19C16.5 7.69 17.21 8 18 8C19.66 8 21 6.66 21 5S19.66 2 18 2 15 3.34 15 5C15 5.24 15.04 5.47 15.09 5.7L8.04 9.81C7.5 9.31 6.79 9 6 9C4.34 9 3 10.34 3 12S4.34 15 6 15C6.79 15 7.5 14.69 8.04 14.19L15.16 18.34C15.11 18.55 15.08 18.77 15.08 19C15.08 20.61 16.39 21.91 18 21.91S20.92 20.61 20.92 19C20.92 17.39 19.61 16.08 18 16.08M18 4C18.55 4 19 4.45 19 5S18.55 6 18 6 17 5.55 17 5 17.45 4 18 4M6 13C5.45 13 5 12.55 5 12S5.45 11 6 11 7 11.45 7 12 6.55 13 6 13M18 20C17.45 20 17 19.55 17 19S17.45 18 18 18 19 18.45 19 19 18.55 20 18 20Z"></path>
                                       </svg>
                                       99 Share
                                    </span>
                                 </a>
                              </div>
                           </div>
                           <form class="comment-text d-flex align-items-center mt-3" action="javascript:void(0);">
                              <input type="text" class="form-control rounded" placeholder="Lovely!">
                              <div class="comment-attagement d-flex">
                                 <a href="javascript:void(0);" class="me-2 text-body">
                                    <svg width="20" height="20" viewBox="0 0 24 24">
                                       <path fill="currentColor" d="M20,12A8,8 0 0,0 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12M22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2A10,10 0 0,1 22,12M10,9.5C10,10.3 9.3,11 8.5,11C7.7,11 7,10.3 7,9.5C7,8.7 7.7,8 8.5,8C9.3,8 10,8.7 10,9.5M17,9.5C17,10.3 16.3,11 15.5,11C14.7,11 14,10.3 14,9.5C14,8.7 14.7,8 15.5,8C16.3,8 17,8.7 17,9.5M12,17.23C10.25,17.23 8.71,16.5 7.81,15.42L9.23,14C9.68,14.72 10.75,15.23 12,15.23C13.25,15.23 14.32,14.72 14.77,14L16.19,15.42C15.29,16.5 13.75,17.23 12,17.23Z" />
                                    </svg>
                                 </a>
                                 <a href="javascript:void(0);" class="text-body">
                                    <svg width="20" height="20" viewBox="0 0 24 24">
                                       <path fill="currentColor" d="M20,4H16.83L15,2H9L7.17,4H4A2,2 0 0,0 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6A2,2 0 0,0 20,4M20,18H4V6H8.05L9.88,4H14.12L15.95,6H20V18M12,7A5,5 0 0,0 7,12A5,5 0 0,0 12,17A5,5 0 0,0 17,12A5,5 0 0,0 12,7M12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15Z" />
                                    </svg>
                                 </a>
                              </div>
                           </form>
                        </div>                              
                           </div>
                        </div>                              
                     </div>
                  </div>
               </div>
                <div class="col-lg-3">
                  <div class="card">
                     <div class="card-header">
                        <div class="header-title">
                           <h4 class="card-title">About</h4>
                        </div>
                     </div>
                     <div class="card-body">
                        <p>User biography will appear here.</p>
                        <div class="mb-1">Email: <a href="#" class="ms-3"><?php echo $email; ?></a></div>
                        <div class="mb-1">Phone: <a href="#" class="ms-3"><?php echo $phone; ?></a></div>
                        <div>Location: <span class="ms-3"><?php echo $country; ?></span></div>
                     </div>
                  </div>
                  <div class="card">
                     <div class="card-header">
                        <div class="header-title">
                           <h4 class="card-title">Market Cap</h4>
                        </div>
                     </div>
                     <div class="card-body">
                        <ul class="list-inline m-0 p-0">
                           <?php
                           // Display wallet data if available
                           if (!empty($financialData['wallets'])) {
                              foreach ($financialData['wallets'] as $index => $wallet) {
                                 $coinImage = $wallet['currency'] == 'USD' ? '01.png' : 
                                             ($wallet['currency'] == 'BTC' ? '01.png' : 
                                             ($wallet['currency'] == 'ETH' ? '04.png' : '10.png'));
                                 $activeClass = $index === 0 ? 'active' : '';
                                 echo '<li class="d-flex mb-4 align-items-center ' . $activeClass . '">
                                    <img src="../assets/images/coins/' . $coinImage . '" alt="coin-img" class="rounded-pill avatar-50 img-fluid">
                                    <div class="ms-3">
                                       <h5>' . htmlspecialchars($wallet['currency']) . '</h5>
                                       <p class="mb-0">' . htmlspecialchars(number_format($wallet['balance'], 2)) . '</p>
                                    </div>
                                 </li>';
                              }
                           } else {
                              // Show default if no wallet data
                              echo '<li class="d-flex mb-4 align-items-center active">
                              <img src="../assets/images/coins/01.png" alt="story-img" class="rounded-pill avatar-50  img-fluid">
                              <div class="ms-3">
                                 <h5>Bitcoin</h5>
                                    <p class="mb-0">No wallet data found</p>
                              </div>
                              </li>';
                           }
                           ?>
                        </ul>
                     </div>
                  </div>
                  <div class="card">
                     <div class="card-header">
                        <div class="header-title">
                           <h4 class="card-title">Suggestions</h4>
                        </div>
                     </div>
                     <div class="card-body">
                        <ul class="list-inline m-0 p-0">
                           <li class="d-flex mb-4 align-items-center">
                              <div class="img-fluid rounded-pill"><img src="../assets/images/coins/05.png" alt="story-img" class="rounded-pill avatar-40"></div>
                              <div class="ms-3 flex-grow-1">
                                 <h6>Bitcoin</h6>
                                 <p class="mb-0">Current Price</p>
                              </div>
                           </li>
                           <li class="d-flex mb-4 align-items-center">
                              <div class="img-fluid  rounded-pill"><img src="../assets/images/coins/03.png" alt="story-img" class="rounded-pill avatar-40"></div>
                              <div class="ms-3 flex-grow-1">
                                 <h6>USD Coin</h6>
                                 <p class="mb-0">Current Price</p>
                              </div>
                           </li>
                           <li class="d-flex mb-4 align-items-center">
                              <div class="img-fluid rounded-pill"><img src="../assets/images/coins/06.png" alt="story-img" class="rounded-pill avatar-40"></div>
                              <div class="ms-3 flex-grow-1">
                                 <h6>Ethereum Classic</h6>
                                 <p class="mb-0">Current Price</p>
                              </div>
                           </li>
                           <li class="d-flex mb-4 align-items-center">
                              <div class="img-fluid rounded-pill"><img src="../assets/images/coins/07.png" alt="story-img" class="rounded-pill avatar-40"></div>
                              <div class="ms-3 flex-grow-1">
                                 <h6>Wrapped Bitcoin</h6>
                                 <p class="mb-0">Current Price</p>
                              </div>
                           </li>
                           <li class="d-flex mb-4 align-items-center">
                              <div class="img-fluid rounded-pill"><img src="../assets/images/coins/04.png" alt="story-img" class="rounded-pill avatar-40"></div>
                              <div class="ms-3 flex-grow-1">
                                 <h6>Filecoin</h6>
                                 <p class="mb-0">Current Price</p>
                              </div>
                           </li>
                           <li class="d-flex mb-4 align-items-center">
                              <div class="img-fluid rounded-pill"><img src="../assets/images/coins/02.png" alt="story-img" class="rounded-pill avatar-40"></div>
                              <div class="ms-3 flex-grow-1">
                                 <h6>Litecoin</h6>
                                 <p class="mb-0">Current Price</p>
                              </div>
                           </li>
                           <li class="d-flex mb-4 align-items-center">
                              <div class="img-fluid rounded-pill"><img src="../assets/images/coins/01.png" alt="story-img" class="rounded-pill avatar-40"></div>
                              <div class="ms-3 flex-grow-1">
                                 <h6>Ethereumq</h6>
                                 <p class="mb-0">Current Price</p>
                              </div>                        
                           </li>
                           <li class="d-flex align-items-center">
                              <div class="img-fluid rounded-pill"><img src="../assets/images/coins/08.png" alt="story-img" class="rounded-pill avatar-40"></div>
                              <div class="ms-3 flex-grow-1">
                                 <h6>Bitcoin</h6>
                                 <p class="mb-0">Current Price</p>
                              </div>
                           </li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
          
      <div class="offcanvas offcanvas-bottom share-offcanvas" tabindex="-1" id="share-btn" aria-labelledby="shareBottomLabel">
         <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="shareBottomLabel">Share</h5>
            <button type="button" class="btn-close text-reset text-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
         </div>
         <div class="offcanvas-body small">
            <div class="d-flex flex-wrap align-items-center">
               <div class="text-center me-3 mb-3">
                  <img src="../assets/images/brands/02.png" class="img-fluid rounded mb-2" alt="">
                  <h6>Facebook</h6>
               </div>
               <div class="text-center me-3 mb-3">
                  <img src="../assets/images/brands/03.png" class="img-fluid rounded mb-2" alt="">
                  <h6>Instagram</h6>
               </div>
               <div class="text-center me-3 mb-3">
                  <img src="../assets/images/brands/06.png" class="img-fluid rounded mb-2" alt="">
                  <h6>Google Plus</h6>
               </div>
               <div class="text-center me-3 mb-3">
                  <img src="../assets/images/brands/04.png" class="img-fluid rounded mb-2" alt="">
                  <h6>linkedin</h6>
               </div>
                <div class="text-center me-3 mb-3">
                  <img src="../assets/images/brands/05.png" class="img-fluid rounded mb-2" alt="">
                  <h6>twitter</h6>
               </div>
            </div>
         </div>
      </div>      </div>
      <footer class="footer">
          <div class="footer-body gap-2">
              <ul class="left-panel list-inline mb-0 p-0">
                  <li class="list-inline-item me-2"><a href="../extra/privacy-policy.html" class="text-white">Privacy Policy</a></li>
                  <li class="list-inline-item"><a href="../extra/terms-of-service.html" class="text-white">Terms of Use</a></li>
              </ul>
              <div class="right-panel text-center">
                  ©<script>document.write(new Date().getFullYear())</script> COINEX, Made with
                  <span class="text-gray">
                      <svg width="15" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M15.85 2.50065C16.481 2.50065 17.111 2.58965 17.71 2.79065C21.401 3.99065 22.731 8.04065 21.62 11.5806C20.99 13.3896 19.96 15.0406 18.611 16.3896C16.68 18.2596 14.561 19.9196 12.28 21.3496L12.03 21.5006L11.77 21.3396C9.48102 19.9196 7.35002 18.2596 5.40102 16.3796C4.06102 15.0306 3.03002 13.3896 2.39002 11.5806C1.26002 8.04065 2.59002 3.99065 6.32102 2.76965C6.61102 2.66965 6.91002 2.59965 7.21002 2.56065H7.33002C7.61102 2.51965 7.89002 2.50065 8.17002 2.50065H8.28002C8.91002 2.51965 9.52002 2.62965 10.111 2.83065H10.17C10.21 2.84965 10.24 2.87065 10.26 2.88965C10.481 2.96065 10.69 3.04065 10.89 3.15065L11.27 3.32065C11.3618 3.36962 11.4649 3.44445 11.554 3.50912C11.6104 3.55009 11.6612 3.58699 11.7 3.61065C11.7163 3.62028 11.7329 3.62996 11.7496 3.63972C11.8354 3.68977 11.9247 3.74191 12 3.79965C13.111 2.95065 14.46 2.49065 15.85 2.50065ZM18.51 9.70065C18.92 9.68965 19.27 9.36065 19.3 8.93965V8.82065C19.33 7.41965 18.481 6.15065 17.19 5.66065C16.78 5.51965 16.33 5.74065 16.18 6.16065C16.04 6.58065 16.26 7.04065 16.68 7.18965C17.321 7.42965 17.75 8.06065 17.75 8.75965V8.79065C17.731 9.01965 17.8 9.24065 17.94 9.41065C18.08 9.58065 18.29 9.67965 18.51 9.70065Z" fill="currentColor"></path>
                      </svg>
                  </span> by <a href="https://iqonic.design/" target="_blank">IQONIC Design</a>.
              </div>
          </div>
      </footer>    </main>
     
    <!-- Wrapper End-->
    <!-- offcanvas start -->

    <!-- Backend Bundle JavaScript -->
    <script src="../assets/js/core/libs.min.js"></script>
    <script src="../assets/js/core/external.min.js"></script>
    
    <!-- widgetchart JavaScript -->
    <script src="../assets/js/charts/widgetcharts.js"></script>
    
    <!-- GSAP Animation JS-->
    <script src="../assets/vendor/gsap/gsap.min.js"></script>
    <script src="../assets/vendor/gsap/ScrollTrigger.min.js"></script>
    
    <!-- fslightbox JavaScript -->
    <script src="../assets/js/fslightbox.js"></script>
    
    <!-- Mapchart JavaScript -->
    <script src="../assets/js/charts/vector-chart.js"></script>
    <script src="../assets/js/charts/dashboard.js"></script>
    
    <!-- app JavaScript -->
    <script src="../assets/js/coinex.js"></script>
    
    <!-- apexchart JavaScript -->
    <script src="../assets/js/charts/apexcharts.js"></script>
    
    <!-- Gsap Animation Init -->
    <script src="../assets/js/gsap.js"></script>  </body>

</html>




