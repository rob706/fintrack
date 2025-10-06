<?php
require("./core/config.php");

if(isset($_POST['firstname'])) $firstname = $con->real_escape_string(stripslashes($_POST['firstname']));
if(isset($_POST['lastname'])) $lastname = $con->real_escape_string(stripslashes($_POST['lastname']));
if(isset($_POST['email'])) $email = $con->real_escape_string(stripslashes($_POST['email']));
if(isset($_POST['password'])) $password = $con->real_escape_string(stripslashes($_POST['password']));

# Check for Errors

$e = 0;

## Check if email is already registered
if(isset($email)){
  $q = $con->query("Select email from users where email='".$email."'");
  if($q->num_rows > 0){
    echo "ERROR: Email already exists";
    $e++;
  }
}

## Check the passwords are the same

if(isset($password)){
  if ($_POST['password'] != $_POST['confirm_password']) {
    echo ("ERROR: Please Check Your Password & Confirmation password");
    $e++;
  }
}

# Process the user addition if all the fields exists and also if there are no errors

if (isset($firstname) && isset($lastname) && isset($email) && isset($password) && $e==0) {
  $trn_date = date("Y-m-d H:i:s");

  $query = "INSERT into `users` (firstname, lastname, password, email, trn_date) VALUES ('".$firstname."','".$lastname."', '" . md5($password) . "', '".$email."', '".$trn_date."')";
  $result = $con_update->query($query);
  if ($result) {
    header("Location: /login.htm");
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Register</title>

  <!-- Bootstrap core CSS -->
  <link href="/core/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      color: #000;
      background: #fff;
      font-family: 'Roboto', sans-serif;
    }

    .form-control {
      height: 40px;
      box-shadow: none;
      color: #969fa4;
    }

    .form-control:focus {
      border-color: #5cb85c;
    }

    .form-control,
    .btn {
      border-radius: 3px;
    }

    .signup-form {
      width: 450px;
      margin: 0 auto;
      padding: 30px 0;
      font-size: 15px;
    }

    .signup-form h2 {
      color: #636363;
      margin: 0 0 15px;
      position: relative;
      text-align: center;
    }

    .signup-form h2:before,
    .signup-form h2:after {
      content: "";
      height: 2px;
      width: 30%;
      background: #d4d4d4;
      position: absolute;
      top: 50%;
      z-index: 2;
    }

    .signup-form h2:before {
      left: 0;
    }

    .signup-form h2:after {
      right: 0;
    }

    .signup-form .hint-text {
      color: #999;
      margin-bottom: 30px;
      text-align: center;
    }

    .signup-form form {
      color: #999;
      border-radius: 3px;
      margin-bottom: 15px;
      background: #fff;
      box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
      padding: 30px;
      border: 1px solid #ddd;
    }

    .signup-form .form-group {
      margin-bottom: 20px;
    }

    .signup-form input[type="checkbox"] {
      margin-top: 3px;
    }

    .signup-form .btn {
      font-size: 16px;
      font-weight: bold;
      min-width: 140px;
      outline: none !important;
    }

    .signup-form .row div:first-child {
      padding-right: 10px;
    }

    .signup-form .row div:last-child {
      padding-left: 10px;
    }

    .signup-form a:hover {
      text-decoration: none;
    }

    .signup-form form a {
      color: #5cb85c;
      text-decoration: none;
    }

    .signup-form form a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="signup-form">
    <form action="" method="POST" autocomplete="off">
      <h2>Register</h2>
      <div class="form-group">
        <div class="row">
          <div class="col"><input type="text" class="form-control" name="firstname" placeholder="First Name" required="required"></div>
          <div class="col"><input type="text" class="form-control" name="lastname" placeholder="Last Name" required="required"></div>
        </div>
      </div>
      <div class="form-group">
        <input type="email" class="form-control" name="email" placeholder="Email" required="required">
      </div>
      <div class="form-group">
        <input type="password" class="form-control" name="password" placeholder="Password" required="required">
      </div>
      <div class="form-group">
        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required="required">
      </div>
      <div class="form-group">
        <label class="form-check-label"><input type="checkbox" required="required"> I accept the <a href="#">Terms of Use</a> &amp; <a href="#">Privacy Policy</a></label>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-danger btn-lg btn-block" style="border-radius:0%;background-color:#008080;">Register</button>
      </div>
    </form>
    <div class="text-center">Already have an account? <a class="text-success" href="/login.htm">Login Here</a></div>
  </div>
</body>
<!-- Bootstrap core JavaScript -->
<script src="/core/js/jquery.slim.min.js"></script>
<script src="/core/js/bootstrap.min.js"></script>
<!-- Menu Toggle Script -->
<script>
  $("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
  });
</script>
<script>
  feather.replace()
</script>

</html>