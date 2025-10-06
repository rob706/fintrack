<?php

## Database Details

$db_server = "localhost";
$db_name = "dailyexpense";

## Credentials for Update User

$db_update_user = "root";
$db_update_pass = "";

## Credentials for Read Only User

$db_read_user = $db_write_user;
$db_read_pass = $db_read_pass;

#### Do not edit below this line ###

$con = mysqli_connect($db_server,$db_read_user,$db_read_pass,$db_name);
$con_update = mysqli_connect($db_server,$db_update_user,$db_update_pass,$db_name);

if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error() ." | Seems like you haven't created the DATABASE with an exact name";
  }
?>