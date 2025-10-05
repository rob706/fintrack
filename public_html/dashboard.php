<?php
include("session.php");

// Get time period filter (default to monthly)
$time_period = isset($_GET['period']) ? $_GET['period'] : 'monthly';

// Expense Queries based on time period
if ($time_period == 'yearly') {
    // Yearly trend data
    $exp_date_line = mysqli_query($con, "SELECT YEAR(expensedate) as period FROM expenses WHERE user_id = '$userid' GROUP BY YEAR(expensedate)");
    $exp_amt_line = mysqli_query($con, "SELECT SUM(expense) FROM expenses WHERE user_id = '$userid' GROUP BY YEAR(expensedate)");
    $inc_date_line = mysqli_query($con, "SELECT YEAR(incomedate) as period FROM income WHERE user_id = '$userid' GROUP BY YEAR(incomedate)");
    $inc_amt_line = mysqli_query($con, "SELECT SUM(income) FROM income WHERE user_id = '$userid' GROUP BY YEAR(incomedate)");
    
    // Yearly category data
    $exp_category_dc = mysqli_query($con, "SELECT expensecategory FROM expenses WHERE user_id = '$userid' GROUP BY expensecategory");
    $exp_amt_dc = mysqli_query($con, "SELECT SUM(expense) FROM expenses WHERE user_id = '$userid' GROUP BY expensecategory");
    $inc_category_dc = mysqli_query($con, "SELECT incomecategory FROM income WHERE user_id = '$userid' GROUP BY incomecategory");
    $inc_amt_dc = mysqli_query($con, "SELECT SUM(income) FROM income WHERE user_id = '$userid' GROUP BY incomecategory");
} elseif ($time_period == 'weekly') {
    // Weekly trend data
    $exp_date_line = mysqli_query($con, "SELECT CONCAT(YEAR(expensedate), '-W', WEEK(expensedate)) as period FROM expenses WHERE user_id = '$userid' GROUP BY YEAR(expensedate), WEEK(expensedate)");
    $exp_amt_line = mysqli_query($con, "SELECT SUM(expense) FROM expenses WHERE user_id = '$userid' GROUP BY YEAR(expensedate), WEEK(expensedate)");
    $inc_date_line = mysqli_query($con, "SELECT CONCAT(YEAR(incomedate), '-W', WEEK(incomedate)) as period FROM income WHERE user_id = '$userid' GROUP BY YEAR(incomedate), WEEK(incomedate)");
    $inc_amt_line = mysqli_query($con, "SELECT SUM(income) FROM income WHERE user_id = '$userid' GROUP BY YEAR(incomedate), WEEK(incomedate)");
    
    // Weekly category data (current week)
    $exp_category_dc = mysqli_query($con, "SELECT expensecategory FROM expenses WHERE user_id = '$userid' AND YEAR(expensedate) = YEAR(CURDATE()) AND WEEK(expensedate) = WEEK(CURDATE()) GROUP BY expensecategory");
    $exp_amt_dc = mysqli_query($con, "SELECT SUM(expense) FROM expenses WHERE user_id = '$userid' AND YEAR(expensedate) = YEAR(CURDATE()) AND WEEK(expensedate) = WEEK(CURDATE()) GROUP BY expensecategory");
    $inc_category_dc = mysqli_query($con, "SELECT incomecategory FROM income WHERE user_id = '$userid' AND YEAR(incomedate) = YEAR(CURDATE()) AND WEEK(incomedate) = WEEK(CURDATE()) GROUP BY incomecategory");
    $inc_amt_dc = mysqli_query($con, "SELECT SUM(income) FROM income WHERE user_id = '$userid' AND YEAR(incomedate) = YEAR(CURDATE()) AND WEEK(incomedate) = WEEK(CURDATE()) GROUP BY incomecategory");
} else {
    // Default monthly trend data
    $exp_date_line = mysqli_query($con, "SELECT DATE_FORMAT(expensedate, '%Y-%m') as period FROM expenses WHERE user_id = '$userid' GROUP BY DATE_FORMAT(expensedate, '%Y-%m')");
    $exp_amt_line = mysqli_query($con, "SELECT SUM(expense) FROM expenses WHERE user_id = '$userid' GROUP BY DATE_FORMAT(expensedate, '%Y-%m')");
    $inc_date_line = mysqli_query($con, "SELECT DATE_FORMAT(incomedate, '%Y-%m') as period FROM income WHERE user_id = '$userid' GROUP BY DATE_FORMAT(incomedate, '%Y-%m')");
    $inc_amt_line = mysqli_query($con, "SELECT SUM(income) FROM income WHERE user_id = '$userid' GROUP BY DATE_FORMAT(incomedate, '%Y-%m')");
    
    // Monthly category data (current month)
    $exp_category_dc = mysqli_query($con, "SELECT expensecategory FROM expenses WHERE user_id = '$userid' AND DATE_FORMAT(expensedate, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') GROUP BY expensecategory");
    $exp_amt_dc = mysqli_query($con, "SELECT SUM(expense) FROM expenses WHERE user_id = '$userid' AND DATE_FORMAT(expensedate, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') GROUP BY expensecategory");
    $inc_category_dc = mysqli_query($con, "SELECT incomecategory FROM income WHERE user_id = '$userid' AND DATE_FORMAT(incomedate, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') GROUP BY incomecategory");
    $inc_amt_dc = mysqli_query($con, "SELECT SUM(income) FROM income WHERE user_id = '$userid' AND DATE_FORMAT(incomedate, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') GROUP BY incomecategory");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Personal Finance Tracker - Dashboard</title>
  
  <!-- Bootstrap CSS -->
  <link href="core/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Feather Icons -->
  <script src="core/js/feather.min.js"></script>
  
  <!-- Chart.js -->
  <script src="core/js/Chart.min.js"></script>
  
  <style>
    :root {
      --sidebar-width: 250px;
      --header-height: 60px;
      --primary-color: #008080;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
    }
    
    /* Sidebar Styles */
    #sidebar-wrapper {
      position: fixed;
      top: 0;
      left: 0;
      width: var(--sidebar-width);
      height: 100vh;
      background: #fff;
      border-right: 1px solid #dee2e6;
      z-index: 1030;
      overflow-y: auto;
      transition: all 0.3s;
    }
    
    .sidebar-heading {
      padding: 0.75rem 1.25rem;
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #6c757d;
    }
    
    .list-group-item {
      border: none;
      padding: 0.6rem 1.25rem;
      font-weight: 500;
      font-size: 0.9rem;
      color: #495057;
    }
    
    .list-group-item:hover {
      color: var(--primary-color);
      background: #f8f9fa;
    }
    
    .list-group-item .feather {
      width: 16px;
      height: 16px;
      margin-right: 5px;
      vertical-align: text-top;
    }
    
    .sidebar-active {
      color: #008080;
      background: rgba(40, 167, 69, 0.1);
    }
    
    .user {
      padding: 1rem;
      text-align: center;
      border-bottom: 1px solid #dee2e6;
    }
    
    .user img {
      width: 75px;
      height: 75px;
      margin-bottom: 0.5rem;
    }
    
    .user h5 {
      font-size: 0.9rem;
      margin-bottom: 0.25rem;
    }
    
    .user p {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0;
    }
    
    /* Header Styles */
    .app-header {
      position: fixed;
      top: 0;
      left: var(--sidebar-width);
      right: 0;
      height: var(--header-height);
      background: #fff;
      z-index: 1020;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1rem;
    }
    
    .app-title {
      font-size: 1rem;
      font-weight: 600;
      margin: 0;
      color: #2c3e50;
    }
    
    .app-title span {
      color: #008080;
    }
    
    /* Main Content Styles */
    #page-content-wrapper {
      margin-left: var(--sidebar-width);
      margin-top: var(--header-height);
      padding: 1rem;
      width: calc(100% - var(--sidebar-width));
      min-height: calc(100vh - var(--header-height));
    }
    
    /* Card Styles */
    .card {
      border: none;
      border-radius: 0.5rem;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      transition: all 0.3s ease;
      margin-bottom: 1rem;
    }
    
    .card-header {
      padding: 0.75rem 1.25rem;
      background: #fff;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      font-weight: 600;
      font-size: 0.9rem;
    }
    
    /* Quick Actions */
    .quick-action {
      text-align: center;
      padding: 0.75rem;
      transition: all 0.2s;
      border-radius: 0.5rem;
    }
    
    .quick-action:hover {
      background: #f8f9fa;
    }
    
    .quick-action .feather {
      width: 20px;
      height: 20px;
      margin-bottom: 0.25rem;
    }
    
    .quick-action p {
      font-size: 0.75rem;
      margin-bottom: 0;
    }
    
    /* Avatar Menu */
    .avatar-menu {
      position: relative;
      display: inline-block;
    }
    
    .avatar-menu-content {
      display: none;
      position: absolute;
      right: 0;
      background: white;
      min-width: 160px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      z-index: 1;
      border-radius: 8px;
      font-size: 0.85rem;
    }
    
    .avatar-menu:hover .avatar-menu-content {
      display: block;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 992px) {
      #sidebar-wrapper {
        margin-left: -250px;
      }
      
      #sidebar-wrapper.active {
        margin-left: 0;
      }
      
      #page-content-wrapper {
        margin-left: 0;
        width: 100%;
      }
      
      .app-header {
        left: 0;
      }
      
      .app-title {
        font-size: 0.9rem;
      }
      
      /* Mobile menu toggle button */
      .mobile-menu-toggle {
        display: block;
        margin-right: 0.5rem;
      }
    }
    
    @media (min-width: 992px) {
      .mobile-menu-toggle {
        display: none;
      }
    }
    
    /* Mobile menu toggle button */
    .mobile-menu-toggle {
      background: none;
      border: none;
      color: #495057;
      font-size: 1.25rem;
    }
    
    /* Chart container adjustments */
    .chart-container {
      position: relative;
      height: 250px;
    }
    
    /* Small devices (landscape phones, 576px and up) */
    @media (max-width: 576px) {
      .quick-action {
        padding: 0.5rem;
      }
      
      .card-header {
        padding: 0.5rem;
      }
      
      #page-content-wrapper {
        padding: 0.5rem;
      }
    }
      /* In your existing CSS, find the #sidebar-wrapper section and modify it like this: */
#sidebar-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  width: var(--sidebar-width);
  height: 100vh;
  background: #2c3e50; /* Changed to dark blue-gray */
  border-right: 1px solid #1a252f;
  z-index: 1030;
  overflow-y: auto;
  transition: all 0.3s;
}

/* Update the sidebar heading color */
.sidebar-heading {
  padding: 0.75rem 1.25rem;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #ecf0f1; /* Light gray text */
}

/* Update list items */
.list-group-item {
  border: none;
  padding: 0.6rem 1.25rem;
  font-weight: 500;
  font-size: 0.9rem;
  color: #bdc3c7; /* Light gray text */
  background-color: transparent; /* Make background transparent */
}

.list-group-item:hover {
  color: #ffffff; /* White text on hover */
  background: rgba(255, 255, 255, 0.1); /* Slight white overlay */
}

/* Active item */
.sidebar-active {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.2); /* More visible active state */
}

/* User section */
.user {
  padding: 1rem;
  text-align: center;
  border-bottom: 1px solid #34495e; /* Darker border */
  background: rgba(0, 0, 0, 0.1); /* Slightly darker background */
}

.user h5 {
  color: #ffffff; /* White text for username */
}

.user p {
  color: #bdc3c7; /* Light gray for email */
}
  
  </style>
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
        <a href="dashboard.php" class="list-group-item list-group-item-action sidebar-active">
          <span data-feather="home"></span> Dashboard
        </a>
        <a href="add_expense.php" class="list-group-item list-group-item-action">
          <span data-feather="plus-circle"></span> Add Expenses
        </a>
        <a href="manage_expense.php" class="list-group-item list-group-item-action">
          <span data-feather="edit"></span> Manage Expenses
        </a>
        <a href="add_income.php" class="list-group-item list-group-item-action">
          <span data-feather="plus-circle"></span> Add Income
        </a>
        <a href="manage_income.php" class="list-group-item list-group-item-action">
          <span data-feather="edit"></span> Manage Income
        </a>
        <a href="report.php" class="list-group-item list-group-item-action">
          <span data-feather="bar-chart-2"></span>Report
        </a>
      </div>
      <div class="sidebar-heading">Settings</div>
      <div class="list-group list-group-flush">
        <a href="profile.php" class="list-group-item list-group-item-action">
          <span data-feather="user"></span> Profile
        </a>
        <a href="logout.php" class="list-group-item list-group-item-action">
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
            <a href="profile.php" class="d-block px-3 py-2 hover-bg-gray-100">Profile</a>
            <a href="logout.php" class="d-block px-3 py-2 text-danger hover-bg-gray-100">Logout</a>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <div class="container-fluid">
        <!-- Quick Actions -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="card">
              <div class="card-body p-2">
                <div class="row text-center">
                  <div class="col">
                    <a href="add_expense.php" class="text-decoration-none">
                      <div class="quick-action">
                        <span data-feather="plus-circle" class="text-primary"></span>
                        <p class="mt-1 mb-0">Add Expense</p>
                      </div>
                    </a>
                  </div>
                  <div class="col">
                    <a href="manage_expense.php" class="text-decoration-none">
                      <div class="quick-action">
                        <span data-feather="edit" class="text-success"></span>
                        <p class="mt-1 mb-0">Manage Expenses</p>
                      </div>
                    </a>
                  </div>
                  <div class="col">
                    <a href="add_income.php" class="text-decoration-none">
                      <div class="quick-action">
                        <span data-feather="plus-circle" class="text-primary"></span>
                        <p class="mt-1 mb-0">Add Income</p>
                      </div>
                    </a>
                  </div>
                  <div class="col">
                    <a href="manage_income.php" class="text-decoration-none">
                      <div class="quick-action">
                        <span data-feather="edit" class="text-success"></span>
                        <p class="mt-1 mb-0">Manage Income</p>
                      </div>
                    </a>
                  </div>
                  <div class="col">
                    <a href="report.php" class="text-decoration-none">
                      <div class="quick-action">
                        <span data-feather="bar-chart-2" class="text-info"></span>
                        <p class="mt-1 mb-0">Report</p>
                      </div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Financial Reports -->
        <div class="row mb-4">
           <div class="col-lg-12">
             <div class="btn-group btn-group-lg d-flex justify-content-center" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
               <a href="?period=weekly" 
                 class="btn flex-fill text-uppercase fw-<?php echo $time_period == 'weekly' ? 'bold' : 'normal'; ?>" 
                 style="font-size: 1.1rem; padding: 0.5rem 1rem; 
                 <?php echo $time_period == 'weekly' ? 'background-color: #0d6efd; color: white;' : 'background-color: #f8f9fa; color: #495057;' ?>">
                 <i class="bi bi-calendar-week me-2" style="font-size: 1.2rem;"></i> Weekly
               </a>
               <a href="?period=monthly" 
                  class="btn flex-fill text-uppercase fw-<?php echo $time_period == 'monthly' ? 'bold' : 'normal'; ?>" 
                  style="font-size: 1.1rem; padding: 0.5rem 1rem; 
                 <?php echo $time_period == 'monthly' ? 'background-color: #0d6efd; color: white;' : 'background-color: #f8f9fa; color: #495057;' ?>">
                 <i class="bi bi-calendar-month me-2" style="font-size: 1.2rem;"></i> Monthly
               </a>
               <a href="?period=yearly" 
                  class="btn flex-fill text-uppercase fw-<?php echo $time_period == 'yearly' ? 'bold' : 'normal'; ?>" 
                  style="font-size: 1.1rem; padding: 0.5rem 1rem; 
                  <?php echo $time_period == 'yearly' ? 'background-color: #0d6efd; color: white;' : 'background-color: #f8f9fa; color: #495057;' ?>">
                  <i class="bi bi-calendar me-2" style="font-size: 1.2rem;"></i> Yearly
               </a>
             </div>
           </div>
         </div>

        <div class="row">
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <span>Expense Trends</span>
                
              </div>
              <div class="card-body p-2">
                <div class="chart-container">
                  <canvas id="expense_line"></canvas>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <span>Expense Categories</span>
                
              </div>
              <div class="card-body p-2">
                <div class="chart-container">
                  <canvas id="expense_category_pie"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="row mt-3">
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <span>Income Trends</span>
                
              </div>
              <div class="card-body p-2">
                <div class="chart-container">
                  <canvas id="income_line"></canvas>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <span>Income Categories</span>
                
              </div>
              <div class="card-body p-2">
                <div class="chart-container">
                  <canvas id="income_category_pie"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="core/js/jquery.slim.min.js"></script>
  <script src="core/js/bootstrap.bundle.min.js"></script>
  
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
    
    // Charts
    document.addEventListener('DOMContentLoaded', function() {
      // Expense Line Chart
      var expenseLine = new Chart(
        document.getElementById('expense_line').getContext('2d'),
        {
          type: 'line',
          data: {
            labels: [<?php while ($c = mysqli_fetch_array($exp_date_line)) { echo '"' . $c['period'] . '",'; } ?>],
            datasets: [{
              label: 'Expenses',
              data: [<?php while ($d = mysqli_fetch_array($exp_amt_line)) { echo '"' . $d['SUM(expense)'] . '",'; } ?>],
              borderColor: '#dc3545',
              backgroundColor: 'rgba(220, 53, 69, 0.1)',
              borderWidth: 2,
              tension: 0.1,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'top',
              }
            }
          }
        }
      );
      
      // Expense Category Chart
      var expenseCategory = new Chart(
        document.getElementById('expense_category_pie').getContext('2d'),
        {
          type: 'doughnut',
          data: {
            labels: [<?php while ($a = mysqli_fetch_array($exp_category_dc)) { echo '"' . $a['expensecategory'] . '",'; } ?>],
            datasets: [{
              data: [<?php while ($b = mysqli_fetch_array($exp_amt_dc)) { echo '"' . $b['SUM(expense)'] . '",'; } ?>],
              backgroundColor: [
                '#6f42c1', '#dc3545', '#28a745', '#007bff', '#ffc107',
                '#20c997', '#17a2b8', '#fd7e14', '#e83e8c', '#6610f2'
              ],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right',
              }
            }
          }
        }
      );
      
      // Income Line Chart
      var incomeLine = new Chart(
        document.getElementById('income_line').getContext('2d'),
        {
          type: 'line',
          data: {
            labels: [<?php while ($g = mysqli_fetch_array($inc_date_line)) { echo '"' . $g['period'] . '",'; } ?>],
            datasets: [{
              label: 'Income',
              data: [<?php while ($h = mysqli_fetch_array($inc_amt_line)) { echo '"' . $h['SUM(income)'] . '",'; } ?>],
              borderColor: '#28a745',
              backgroundColor: 'rgba(40, 167, 69, 0.1)',
              borderWidth: 2,
              tension: 0.1,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'top',
              }
            }
          }
        }
      );
      
      // Income Category Chart
      var incomeCategory = new Chart(
        document.getElementById('income_category_pie').getContext('2d'),
        {
          type: 'doughnut',
          data: {
            labels: [<?php while ($e = mysqli_fetch_array($inc_category_dc)) { echo '"' . $e['incomecategory'] . '",'; } ?>],
            datasets: [{
              data: [<?php while ($f = mysqli_fetch_array($inc_amt_dc)) { echo '"' . $f['SUM(income)'] . '",'; } ?>],
              backgroundColor: [
                '#007bff', '#17a2b8', '#28a745', '#20c997', '#ffc107',
                '#fd7e14', '#e83e8c', '#6f42c1', '#dc3545', '#6610f2'
              ],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right',
              }
            }
          }
        }
      );
    });
  </script>
  
</body>
</html>
