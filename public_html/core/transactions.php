<?php

@include_once("../session.php");

if(!isset($_POST['act'])) header("location: /dashboard/transaction.htm");

if(isset($_POST['id'])) $id = $con->real_escape_string($_POST['id']);
if(isset($_POST['trans_account'])) $trans_account = $con->real_escape_string($_POST['trans_account']);
if(isset($_POST['trans_amount'])) $trans_amount = $con->real_escape_string($_POST['trans_amount']);
if(isset($_POST['trans_category'])) $trans_category = $con->real_escape_string($_POST['trans_category']);
if(isset($_POST['customCategory'])) $trans_customcategory = $con->real_escape_string($_POST['customCategory']);
if(isset($_POST['trans_date'])) $trans_date = $con->real_escape_string($_POST['trans_date']);
if(isset($_POST['trans_desc'])) $trans_desc = $con->real_escape_string($_POST['trans_desc']);
if(isset($_POST['trans_type'])) $trans_type = $con->real_escape_string($_POST['trans_type']);

## Flip the Signs on Expenses so they are processed as Negatives

if($trans_type == "expense") $trans_amount = $trans_amount * -1;

## Check if the Custom Category already exists and if not create the category

if(!empty($trans_customcategory)){
    $q_cat = $con->query("select category_id from category where category_name = '".$trans_customcategory."' and user_id in (0,".$userid.") limit 1");

    if($q_cat->num_rows == 0){
        $con_update->query("insert into category (`category_name`,`user_id`,`".$trans_type."`) values ('".$trans_customcategory."','".$userid."',1);");

        $q_cat = $con->query("select category_id from category where category_name = '".$trans_customcategory."' and user_id in (0,".$userid.") limit 1");
    }
    $cat = $q_cat->fetch_assoc();
    $trans_category = $cat['category_id'];
    echo "I've fired";
} 

$act = $_POST['act'];

switch($act){
    default:
        header("location: /dashboard/transaction.htm");
        break;

    case 'add':
        $sql = "INSERT INTO transactions (user_id, account_id, date, description, category_id, value) VALUES ('".$userid."','".$trans_account."','".$trans_date."',".$trans_desc.",'".$trans_category."', '".$trans_amount."')";
        break;
    
    case 'edit':
        $sql = "UPDATE transactions SET value='".$trans_amount."', date='".$trans_date."', description='".$trans_desc."', category_id='".$trans_category."', account_id='".$trans_account."' WHERE user_id='".$userid."' AND transaction_id='".$id."'";
        break;

    case 'delete':
        $sql = "DELETE FROM transactions WHERE user_id='".$userid."' AND transaction_id='".$id."'";
        break;

}

if ($con_update->query($sql)) {
    echo "Records were updated successfully.";
} else {
    echo "ERROR: Could not able to execute ".$sql."<br />" . $con->error();
}

/*print_r($_POST);
echo $sql;*/

header("location: /dashboard/".$trans_account."/transaction.htm");

?>