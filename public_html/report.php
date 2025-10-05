<?php
include("session.php");

// Set default time period
$period = isset($_GET['period']) ? $_GET['period'] : 'monthly';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

// Generate report data
function generateReport($con, $userid, $period, $year, $month) {
    $data = array();
    
    switch($period) {
        case 'yearly':
            // Yearly report - don't need month parameter
            $query = "SELECT YEAR(incomedate) as year, 
                      SUM(income) as total_income,
                      (SELECT SUM(expense) FROM expenses WHERE user_id='$userid' AND YEAR(expensedate) = YEAR(i.incomedate)) as total_expense
                      FROM income i 
                      WHERE user_id='$userid' 
                      GROUP BY YEAR(incomedate) 
                      ORDER BY year DESC";
            break;
            
        case 'monthly':
            // Monthly report for selected year
            $query = "SELECT MONTH(incomedate) as month, 
                      SUM(income) as total_income,
                      (SELECT SUM(expense) FROM expenses WHERE user_id='$userid' AND YEAR(expensedate) = '$year' AND MONTH(expensedate) = MONTH(i.incomedate)) as total_expense
                      FROM income i 
                      WHERE user_id='$userid' AND YEAR(incomedate) = '$year'
                      GROUP BY MONTH(incomedate) 
                      ORDER BY month DESC";
            break;
            
        case 'weekly':
            // Weekly report for selected month
            $query = "SELECT WEEK(incomedate, 1) as week, 
                      SUM(income) as total_income,
                      (SELECT SUM(expense) FROM expenses WHERE user_id='$userid' AND YEAR(expensedate) = '$year' AND MONTH(expensedate) = '$month' AND WEEK(expensedate, 1) = WEEK(i.incomedate, 1)) as total_expense
                      FROM income i 
                      WHERE user_id='$userid' AND YEAR(incomedate) = '$year' AND MONTH(incomedate) = '$month'
                      GROUP BY WEEK(incomedate, 1) 
                      ORDER BY week DESC";
            break;
    }
    
    $result = mysqli_query($con, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

$reportData = generateReport($con, $userid, $period, $year, $month);

/*

~~~~~~~~~~~~~~~~~~~~~~~~~~~
Not Required?
~~~~~~~~~~~~~~~~~~~~~~~~~~~

// Handle PDF generation
if(isset($_GET['export']) && $_GET['export'] == 'pdf') {
    require_once('tcpdf/tcpdf.php');
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Finance Tracker');
    $pdf->SetTitle('Financial Report');
    $pdf->SetSubject('Income and Expense Report');
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Financial Report - '.ucfirst($period).' View', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    
    // Add period information
    $periodInfo = "Year: $year";
    if($period != 'yearly') $periodInfo .= ", Month: ".date('F', mktime(0, 0, 0, $month, 10));
    $pdf->Cell(0, 10, $periodInfo, 0, 1);
    
    // Create table header
    $header = array('Period', 'Income', 'Expense', 'Balance');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(220, 220, 220);
    
    // Column widths
    $w = array(40, 40, 40, 40);
    
    // Header
    for($i = 0; $i < count($header); $i++) {
        $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
    }
    $pdf->Ln();
    
    // Data
    $pdf->SetFont('helvetica', '', 12);
    $fill = false;
    
    foreach($reportData as $row) {
        $periodLabel = '';
        switch($period) {
            case 'yearly': $periodLabel = $row['year']; break;
            case 'monthly': $periodLabel = date('F', mktime(0, 0, 0, $row['month'], 10)); break;
            case 'weekly': $periodLabel = 'Week '.$row['week']; break;
        }
        
        $income = $row['total_income'] ? $row['total_income'] : 0;
        $expense = $row['total_expense'] ? $row['total_expense'] : 0;
        $balance = $income - $expense;
        
        $pdf->Cell($w[0], 6, $periodLabel, 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 6, number_format($income, 2), 'LR', 0, 'R', $fill);
        $pdf->Cell($w[2], 6, number_format($expense, 2), 'LR', 0, 'R', $fill);
        $pdf->Cell($w[3], 6, number_format($balance, 2), 'LR', 0, 'R', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }
    
    // Closing line
    $pdf->Cell(array_sum($w), 0, '', 'T');
    
    // Output PDF
    $pdf->Output('financial_report_'.$period.'_'.date('Ymd').'.pdf', 'D');
    exit;
}

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

*/
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Financial Reports - Dashboard</title>
    
    <!-- Bootstrap core CSS -->
    <link href="core/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Feather JS for Icons -->
    <script src="core/js/feather.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
    /* Add your existing styles here */
    .report-period {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .table-responsive {
        margin-bottom: 20px;
    }
    .chart-container {
        height: 300px;
        margin-bottom: 30px;
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
        <a href="dashboard.php" class="list-group-item list-group-item-action">
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
        <a href="report.php" class="list-group-item list-group-item-action  sidebar-active">
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
        <div class="report-period">
            <form method="get" class="form-inline">
                <div class="form-group mr-3">
                    <label for="period" class="mr-2">Report Period:</label>
                    <select name="period" id="period" class="form-control" onchange="this.form.submit()">
                        <option value="yearly" <?php echo $period == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                        <option value="monthly" <?php echo $period == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        <option value="weekly" <?php echo $period == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                    </select>
                </div>
                
                <div class="form-group mr-3">
                    <label for="year" class="mr-2">Year:</label>
                    <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                        <?php 
                        $currentYear = date('Y');
                        for($y = $currentYear; $y >= $currentYear - 5; $y--) {
                            echo '<option value="'.$y.'" '.($year == $y ? 'selected' : '').'>'.$y.'</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <?php if($period != 'yearly'): ?>
                <div class="form-group mr-3">
                    <label for="month" class="mr-2">Month:</label>
                    <select name="month" id="month" class="form-control" onchange="this.form.submit()">
                        <?php 
                        for($m = 1; $m <= 12; $m++) {
                            $monthName = date('F', mktime(0, 0, 0, $m, 10));
                            echo '<option value="'.$m.'" '.($month == $m ? 'selected' : '').'>'.$monthName.'</option>';
                        }
                        ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <a href="pdf_preview.php?period=<?php echo $period ?>&year=<?php echo $year ?>&month=<?php echo $month ?>" class="btn btn-primary">
                      <span data-feather="download"></span> Export PDF
                </a>
            </form>
        </div>
        
        <div class="chart-container">
            <canvas id="financialChart"></canvas>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Period</th>
                        <th>Income</th>
                        <th>Expense</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalIncome = 0;
                    $totalExpense = 0;
                    
                    foreach($reportData as $row): 
                        $periodLabel = '';
                        switch($period) {
                            case 'yearly': $periodLabel = $row['year']; break;
                            case 'monthly': $periodLabel = date('F', mktime(0, 0, 0, $row['month'], 10)); break;
                            case 'weekly': $periodLabel = 'Week '.$row['week']; break;
                        }
                        
                        $income = $row['total_income'] ? $row['total_income'] : 0;
                        $expense = $row['total_expense'] ? $row['total_expense'] : 0;
                        $balance = $income - $expense;
                        
                        $totalIncome += $income;
                        $totalExpense += $expense;
                    ?>
                    <tr>
                        <td><?php echo $periodLabel ?></td>
                        <td class="text-success"><?php echo number_format($income, 2) ?></td>
                        <td class="text-danger"><?php echo number_format($expense, 2) ?></td>
                        <td class="<?php echo $balance >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?php echo number_format($balance, 2) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold">
                        <td>Total</td>
                        <td class="text-success"><?php echo number_format($totalIncome, 2) ?></td>
                        <td class="text-danger"><?php echo number_format($totalExpense, 2) ?></td>
                        <td class="<?php echo ($totalIncome - $totalExpense) >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?php echo number_format($totalIncome - $totalExpense, 2) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
      </div>
    </div>
</div>

<!-- Bootstrap core JavaScript -->
<script src="core/js/jquery.slim.min.js"></script>
<script src="core/js/bootstrap.min.js"></script>
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
    
    // Initialize Chart
    const ctx = document.getElementById('financialChart').getContext('2d');
    const chartData = {
        labels: [
            <?php 
            foreach($reportData as $row) {
                $label = '';
                switch($period) {
                    case 'yearly': $label = $row['year']; break;
                    case 'monthly': $label = date('M', mktime(0, 0, 0, $row['month'], 10)); break;
                    case 'weekly': $label = 'Week '.$row['week']; break;
                }
                echo "'".$label."',";
            }
            ?>
        ],
        datasets: [
            {
                label: 'Income',
                backgroundColor: 'rgba(40, 167, 69, 0.5)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1,
                data: [
                    <?php 
                    foreach($reportData as $row) {
                        echo ($row['total_income'] ? $row['total_income'] : 0).",";
                    }
                    ?>
                ]
            },
            {
                label: 'Expense',
                backgroundColor: 'rgba(220, 53, 69, 0.5)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1,
                data: [
                    <?php 
                    foreach($reportData as $row) {
                        echo ($row['total_expense'] ? $row['total_expense'] : 0).",";
                    }
                    ?>
                ]
            }
        ]
    };
    
    const financialChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Income vs Expense - <?php echo ucfirst($period) ?> View'
                }
            }
        }
    });
</script>
</body>
</html>