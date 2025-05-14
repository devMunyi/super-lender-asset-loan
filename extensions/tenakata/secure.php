<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
date_default_timezone_set("Africa/Nairobi");

$hostname = 'localhost'; // Your MySQL hostname. Usualy named as 'localhost', so you're NOT necessary to change this even this script has already online on the internet.
$dbname  = $live_db = 'tenakata_db'; // Your database name.
$username = 'root';             // Your database username.
$password = '';

$con=mysqli_connect($hostname,$username,$password,$dbname);
if(mysqli_connect_errno())
{
    printf('Error Establishing a database connection');
    exit();
}




if(isset($_SESSION['archives']) || $inarchives == 1){
    $dbname = "tenova_local2";
}

$con=mysqli_connect($hostname,$username,$password,$dbname);
if(mysqli_connect_errno())
{
    printf('Error Establishing a database connection');
    echo $dbname;
    exit();
}
?>