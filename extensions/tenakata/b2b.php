<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once '../configs/20200902.php';
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
include_once("../extensions/tenakata_sms.php");

$_SESSION['db_name'] = $db_;


$from = $_POST['from'];
$to = $_POST['to'];
$amount = $_POST['amount'];

if ($from == "" || $to == "" || $amount == "") {
  //  exit(errormes("Please parse all required fields"));
}

//echo b2b(3033631, 9856611, 40000);
echo b2b(3033631, 9856611, 20000);
die("jjj");




include_once("../../configs/close_connection.inc");
?>