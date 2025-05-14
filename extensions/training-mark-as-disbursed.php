<?php
//session_start();
include_once('../configs/20200902.php');
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");



$product_id = $_GET['p'];
$offset = $_GET['offset'];
$rpp = $_GET['rpp'] ?? 5;


$upd = updatedb('o_loans',"status=3, disbursed=1","status=2");
echo $upd;

// mysqli_commit($con);
include_once("../configs/close_connection.inc");