<?php

@include_once("../session.php");

if(!isset($_POST['act'])) header("location: /dashboard/income.htm");

if(isset($_POST['incomeamount'])) $incomeamount = $con->real_escape_string($_POST['incomeamount']);
if(isset($_POST['incomedate'])) $incomedate = $con->real_escape_string($_POST['incomedate']);
if(isset($_POST['incomecategory'])) $incomecategory = $con->real_escape_string($_POST['incomecategory']);
if(isset($_POST['id'])) $id = $con->real_escape_string($_POST['id']);

$act = $_POST['act'];

switch($act){
    default:
        header("location: /dashboard/income.htm");
        break;

    case 'add':
        $sql = "INSERT INTO income (user_id, income,incomedate,incomecategory) VALUES ('$userid', '$incomeamount','$incomedate','$incomecategory')";
        break;
    
    case 'edit':
        $sql = "UPDATE income SET income='$incomeamount', incomedate='$incomedate', incomecategory='$incomecategory' WHERE user_id='$userid' AND income_id='$id'";
        break;

    case 'delete':
        $sql = "DELETE FROM income WHERE user_id='$userid' AND income_id='$id'";
        break;

}

print_r($_POST);

if ($con->query($sql)) {
    echo "Records were updated successfully.";
} else {
    echo "ERROR: Could not able to execute $sql. " . $con->error();
}
header('location: /dashboard/income.htm');

?>