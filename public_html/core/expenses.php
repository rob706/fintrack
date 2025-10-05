<?php

@include_once("../session.php");

if(!isset($_POST['act'])) header("location: /dashboard/expense.htm");

if(isset($_POST['expenseamount'])) $expenseamount = $con->real_escape_string($_POST['expenseamount']);
if(isset($_POST['expensedate'])) $expensedate = $con->real_escape_string($_POST['expensedate']);
if(isset($_POST['expensecategory'])) $expensecategory = $con->real_escape_string($_POST['expensecategory']);
if(isset($_POST['id'])) $id = $con->real_escape_string($_POST['id']);

$act = $_POST['act'];

switch($act){
    default:
        header("location: /dashboard/expense.htm");
        break;

    case 'add':
        $sql = "INSERT INTO expenses (user_id, expense,expensedate,expensecategory) VALUES ('$userid', '$expenseamount','$expensedate','$expensecategory')";
        break;
    
    case 'edit':
        $sql = "UPDATE expenses SET expense='$expenseamount', expensedate='$expensedate', expensecategory='$expensecategory' WHERE user_id='$userid' AND expense_id='$id'";
        break;

    case 'delete':
        $sql = "DELETE FROM expenses WHERE user_id='$userid' AND expense_id='$id'";
        break;

}

print_r($_POST);

if ($con->query($sql)) {
    echo "Records were updated successfully.";
} else {
    echo "ERROR: Could not able to execute $sql. " . $con->error();
}
header('location: /dashboard/expense.htm');

?>