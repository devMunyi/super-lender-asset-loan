<?php
session_start();
include_once("../configs/auth.inc");
include_once '../configs/20200902.php';

include_once("../php_functions/functions.php");


$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");


$loans = fetchtable('o_loans',"disbursed=0 AND status=1","uid","desc","10","uid, disbursed_amount, account_number");
while($l = mysqli_fetch_array($loans)){
    $lid = $l['uid'];
    $disbursed_amount = $l['disbursed_amount'];
    $number = $l['account_number'];
    $customer_id = $l['customer_id'];
    echo queue_money($number, $customer_id, $disbursed_amount, $lid,  0);
    store_event('o_loans', $lid,"Mobile Money Initiated via queue");


   // product_notify($cust['primary_product'], 0, 'DISBURSEMENT',3, $result, $number);
   // $message = "Your request has been submitted successfully, please wait while we review it";
}