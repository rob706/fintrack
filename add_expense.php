<?php
include("session.php");
$update = false;
$del = false;
$expenseamount = "";
$expensedate = date("Y-m-d");
$expensecategory = "Entertainment";
if (isset($_POST['add'])) {
    $expenseamount = $_POST['expenseamount'];
    $expensedate = $_POST['expensedate'];
    $expensecategory = $_POST['expensecategory'];

    $expenses = "INSERT INTO expenses (user_id, expense,expensedate,expensecategory) VALUES ('$userid', '$expenseamount','$expensedate','$expensecategory')";
    $result = mysqli_query($con, $expenses) or die("Something Went Wrong!");
    header('location: add_expense.php');
}

if (isset($_POST['update'])) {
    $id = $_GET['edit'];
    $expenseamount = $_POST['expenseamount'];
    $expensedate = $_POST['expensedate'];
    $expensecategory = $_POST['expensecategory'];

    $sql = "UPDATE expenses SET expense='$expenseamount', expensedate='$expensedate', expensecategory='$expensecategory' WHERE user_id='$userid' AND expense_id='$id'";
    if (mysqli_query($con, $sql)) {
        echo "Records were updated successfully.";
    } else {
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
    }
    header('location: manage_expense.php');
}

if (isset($_POST['delete'])) {
    $id = $_GET['delete'];
    $expenseamount = $_POST['expenseamount'];
    $expensedate = $_POST['expensedate'];
    $expensecategory = $_POST['expensecategory'];

    $sql = "DELETE FROM expenses WHERE user_id='$userid' AND expense_id='$id'";
    if (mysqli_query($con, $sql)) {
        echo "Records were updated successfully.";
    } else {
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
    }
    header('location: manage_expense.php');
}

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $update = true;
    $record = mysqli_query($con, "SELECT * FROM expenses WHERE user_id='$userid' AND expense_id=$id");
    if (mysqli_num_rows($record) == 1) {
        $n = mysqli_fetch_array($record);
        $expenseamount = $n['expense'];
        $expensedate = $n['expensedate'];
        $expensecategory = $n['expensecategory'];
    } else {
        echo ("WARNING: AUTHORIZATION ERROR: Trying to Access Unauthorized data");
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $del = true;
    $record = mysqli_query($con, "SELECT * FROM expenses WHERE user_id='$userid' AND expense_id=$id");

    if (mysqli_num_rows($record) == 1) {
        $n = mysqli_fetch_array($record);
        $expenseamount = $n['expense'];
        $expensedate = $n['expensedate'];
        $expensecategory = $n['expensecategory'];
    } else {
        echo ("WARNING: AUTHORIZATION ERROR: Trying to Access Unauthorized data");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Expense Manager - Dashboard</title>
    
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Feather JS for Icons -->
    <script src="js/feather.min.js"></script>
    
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
        <a href="dashboard.php" class="list-group-item list-group-item-action">
          <span data-feather="home"></span> Dashboard
        </a>
        <a href="add_expense.php" class="list-group-item list-group-item-action sidebar-active">
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

      <!-- MAIN CONTENT REMAINS EXACTLY THE SAME -->
      <div class="container">
                <h3 class="mt-4 text-center">Add Your Daily Expenses</h3>
                <hr>
                <div class="row ">

                    <div class="col-md-3"></div>

                    <div class="col-md" style="margin:0 auto;">
                        <form action="" method="POST" onsubmit="return validateExpenseAmount()">
                            <div class="form-group row">
                                <label for="expensedate" class="col-sm-6 col-form-label"><b>Date</b></label>
                                <div class="col-md-6">
                                    <input type="date" class="form-control col-sm-12" value="<?php echo $expensedate; ?>" name="expensedate" id="expensedate" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="expenseamount" class="col-sm-6 col-form-label"><b>Enter Amount</b></label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control col-sm-12" value="<?php echo $expenseamount; ?>" id="expenseamount" name="expenseamount" required>
                                </div>
                            </div>
                            
                            
                            <fieldset class="form-group">
        <div class="row">
            <legend class="col-form-label col-sm-6 pt-0"><b>Category</b></legend>
            <div class="col-md">
                <!-- Dropdown Menu -->
                <select id="categoryDropdown" name="expensecategory" class="form-control" onchange="toggleOtherInput(this)">
                    <option value="Electricity">Electricity</option>
                    <option value="Rent">Rent</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Loan">Loan</option>
                    <option value="Gas">Gas</option>
                    <option value="Other">Other</option>
                </select>
                <!-- Text Box (Hidden by Default) -->
                <div id="otherInputContainer" class="d-none mt-2">
                    <input type="text" id="otherInput" name="customExpenseCategory" class="form-control" placeholder="Enter custom category">
                </div>
            </div>
        </div>
    </fieldset>

                            <div class="form-group row">
                                <div class="col-md-12 text-right">
                                    <?php if ($update == true) : ?>
                                        <button class="btn btn-lg btn-block btn-warning" style="border-radius: 0%;" type="submit" name="update">Update</button>
                                    <?php elseif ($del == true) : ?>
                                        <button class="btn btn-lg btn-block btn-danger" style="border-radius: 0%;" type="submit" name="delete">Delete</button>
                                    <?php else : ?>
                                        <button type="submit" name="add" class="btn btn-lg btn-block btn-success" style="border-radius: 0%; background-color:#008080;">Add Expense</button>
                                    <?php endif ?>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-3"></div>
                    
                </div>
            </div>
    </div>
</div>

<!-- Bootstrap core JavaScript -->
<script src="js/jquery.slim.min.js"></script>
<script src="js/bootstrap.min.js"></script>
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

    // Expense amount validation
    function validateExpenseAmount() {
        const amountInput = document.getElementById('expenseamount');
        const amountValue = amountInput.value.trim();
        
        // Check if the value is empty
        if (amountValue === '') {
            alert('Please enter an amount');
            return false;
        }
        
        // Check if the value is a valid number
        if (isNaN(amountValue)) {
            alert('Please enter a valid number for the amount');
            return false;
        }
        
        // Check if the value is negative
        if (parseFloat(amountValue) < 0) {
            alert('Expense amount cannot be negative');
            return false;
        }
        
        // If all validations pass
        return true;
    }

    // Prevent typing non-numeric characters (except decimal point)
    document.getElementById('expenseamount').addEventListener('keypress', function(e) {
        // Allow: backspace, delete, tab, escape, enter
        if ([46, 8, 9, 27, 13].includes(e.keyCode) || 
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (e.keyCode === 65 && e.ctrlKey === true) || 
            (e.keyCode === 67 && e.ctrlKey === true) || 
            (e.keyCode === 86 && e.ctrlKey === true) || 
            (e.keyCode === 88 && e.ctrlKey === true) ||
            // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        
        // Ensure it's a number or decimal point
        if ((e.keyCode < 48 || e.keyCode > 57) && e.keyCode !== 46) {
            e.preventDefault();
        }
        
        // Ensure only one decimal point
        if (e.keyCode === 46 && this.value.indexOf('.') !== -1) {
            e.preventDefault();
        }
    });

    function toggleOtherInput(select) {
        const otherInputContainer = document.getElementById('otherInputContainer');
        const otherInput = document.getElementById('otherInput');

        if (select.value === 'Other') {
            otherInputContainer.classList.remove('d-none'); // Show the text box
            otherInput.setAttribute("required", "true"); // Make it required
        } else {
            otherInputContainer.classList.add('d-none'); // Hide the text box
            otherInput.removeAttribute("required"); // Remove required if another category is chosen
            otherInput.value = ""; // Clear text input
        }
    }

    function handleSubmit() {
        const dropdown = document.getElementById('categoryDropdown');
        const otherInput = document.getElementById('otherInput');

        // If "Other" is selected and the user has entered a value
        if (dropdown.value === "Other" && otherInput.value.trim() !== "") {
            const newCategory = otherInput.value.trim();
            // Add the custom category to the dropdown options
            const newOption = document.createElement("option");
            newOption.value = newCategory;
            newOption.textContent = newCategory;
            newOption.selected = true; // Select the newly added option
            dropdown.appendChild(newOption);
        }

        console.log("Selected Category:", dropdown.value); // Debugging
        return true; // Allow form submission
    }
</script>
</body>
</html>