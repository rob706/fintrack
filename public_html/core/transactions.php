<?php

@include_once("../session.php");

if(!isset($_POST['act'])) header("location: /dashboard/transaction.htm");

if(isset($_POST['trans_amount'])) $trans_amount = $con->real_escape_string($_POST['trans_amount']);
if(isset($_POST['trans_date'])) $trans_date = $con->real_escape_string($_POST['trans_date']);
if(isset($_POST['trans_category'])) $trans_category = $con->real_escape_string($_POST['trans_category']);
if(isset($_POST['trans_type'])) $trans_type = $con->real_escape_string($_POST['trans_type']);
if(isset($_POST['id'])) $id = $con->real_escape_string($_POST['id']);

$act = $_POST['act'];

if($trans_type == "expense") $trans_amount = $trans_amount * -1;

switch($act){
    default:
        header("location: /dashboard/transaction.htm");
        break;

    case 'add':
        $sql = "INSERT INTO transactions (user_id, date, category, value) VALUES ('$userid','$trans_date','$trans_category', '$trans_amount')";
        break;
    
    case 'edit':
        $sql = "UPDATE transactions SET value='$trans_amount', date='$trans_date', category='$trans_category' WHERE user_id='$userid' AND transaction_id='$id'";
        break;

    case 'delete':
        $sql = "DELETE FROM transactions WHERE user_id='$userid' AND transaction_id='$id'";
        break;

}

if ($con_update->query($sql)) {
    echo "Records were updated successfully.";
} else {
    echo "ERROR: Could not able to execute $sql. " . $con->error();
}

#print_r($_POST);

header('location: /dashboard/transaction.htm');

?>