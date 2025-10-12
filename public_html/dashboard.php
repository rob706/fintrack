<?php
@require_once("session.php");


$pg = "dashboard";
$dpage = "../pages/".$pg.".pg";
if(!empty($_GET['pg'])) $pg = $_GET['pg'];

$page = "../pages/".$pg.".pg";

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Personal Finance Tracker - Dashboard</title>
  
  <!-- Bootstrap CSS -->
  <link href="/core/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Feather Icons -->
  <script src="/core/js/feather.min.js"></script>
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <link rel="stylesheet" href="/core/css/style.css">
</head>
<body>
  <div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="border-right" id="sidebar-wrapper">
      <div class="user">
        <img class="img img-fluid rounded-circle" src="<?php echo $userprofile ?>">
        <h5><?php echo $username ?></h5>
        <p><?php echo $useremail ?></p>
      </div>
      <div class="sidebar-heading">Management</div>
      <div class="list-group list-group-flush">
        <a href="/dashboard" class="list-group-item list-group-item-action <?php if($pg=="dashboard") echo "sidebar-active"; ?>">
          <span data-feather="home"></span> Dashboard
        </a>
        <a href="/dashboard/assets.htm" class="list-group-item list-group-item-action <?php if($pg=="assets" || $pg=="manage_assets") echo "sidebar-active"; ?>">
          <span data-feather="home"></span> Assets
        </a>
        <a href="/dashboard/loans.htm" class="list-group-item list-group-item-action <?php if($pg=="loans" || $pg=="manage_loans") echo "sidebar-active"; ?>">
          <span data-feather="dollar-sign"></span> Loans
        </a>
        <a href="/dashboard/investments.htm" class="list-group-item list-group-item-action <?php if($pg=="investments" || $pg=="manage_investments") echo "sidebar-active"; ?>">
          <span data-feather="dollar-sign"></span> Investments
        </a>
        <a href="/dashboard/transaction/new/add.htm" class="list-group-item list-group-item-action <?php if($pg=="manage_transaction" && !isset($_GET['act'])) echo "sidebar-active"; ?>">
          <span data-feather="plus-circle"></span> Add Transaction
        </a>
        <a href="/dashboard/transaction.htm" class="list-group-item list-group-item-action <?php if($pg=="transaction" || ($pg=="manage_transaction" && isset($_GET['act']))) echo "sidebar-active"; ?>">
          <span data-feather="edit"></span> Manage Transactions
        </a>
        <a href="/dashboard/report.htm" class="list-group-item list-group-item-action <?php if($pg=="report") echo "sidebar-active"; ?>">
          <span data-feather="bar-chart-2"></span>Report
        </a>
      </div>
      <div class="sidebar-heading">Settings</div>
      <div class="list-group list-group-flush">
        <a href="/dashboard/profile.htm" class="list-group-item list-group-item-action">
          <span data-feather="user"></span> Profile
        </a>
        <a href="/logout.htm" class="list-group-item list-group-item-action">
          <span data-feather="power"></span> Logout
        </a>
      </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
      <!-- Sticky Header -->
      <header class="app-header">
        <button class="mobile-menu-toggle" id="menu-toggle">
          <span data-feather="menu"></span>
        </button>
        <h1 class="app-title">Personal <span>Finance Tracker</span></h1>
        
        <div class="avatar-menu ml-auto">
          <img src="<?php echo $userprofile; ?>" class="rounded-circle cursor-pointer" width="40" height="40">
          <div class="avatar-menu-content p-2">
            <div class="px-3 py-2 text-muted small"><?php echo $useremail; ?></div>
            <a href="/dashboard/profile.htm" class="d-block px-3 py-2 hover-bg-gray-100">Profile</a>
            <a href="/logout.htm" class="d-block px-3 py-2 text-danger hover-bg-gray-100">Logout</a>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <div class="container-fluid">
        <?php

        if(file_exists($page)){
        include($page);
        } else {
        include($dpage);
        }
      ?>
      </div>
    </div>
  </div>

  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="/core/js/jquery.slim.min.js"></script>
  <script src="/core/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Initialize Feather Icons
    feather.replace();
    
    // Toggle sidebar on mobile
    $('#menu-toggle').click(function(e) {
      e.preventDefault();
      $('#sidebar-wrapper').toggleClass('active');
    });
    
    // Close sidebar when clicking outside on mobile
    $(document).click(function(e) {
      if ($(window).width() <= 992) {
        if (!$(e.target).closest('#sidebar-wrapper').length && !$(e.target).closest('#menu-toggle').length) {
          $('#sidebar-wrapper').removeClass('active');
        }
      }
    });
    
    
  </script>
  
</body>
</html>
