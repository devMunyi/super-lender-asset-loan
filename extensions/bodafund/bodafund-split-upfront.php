<?php
session_start();
include_once("../configs/auth.inc");
include_once '../configs/20200902.php';

include_once("../php_functions/functions.php");


$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");


$payments = fetchtable('o_incoming_payments',"payment_category=2 AND amount >= 150 AND split_from=0 AND status=1","uid","asc","10","uid, customer_id, amount, payment_method, mobile_number, transaction_code, payment_date, record_method");
while($p = mysqli_fetch_array($payments)){
    $uid = $p['uid'];
    $customer_id = $p['customer_id'];
    $amount = $p['amount'];
    $payment_method = $p['payment_method'];
    $mobile_number = $p['mobile_number'];
    $transaction_code = $p['transaction_code'];
    $payment_date = $p['payment_date'];
    $record_method = $p['record_method'];

    $saving = $amount - 100;

    ////-----------Save registration
    $rflds = array('customer_id','split_from','payment_method','payment_category','mobile_number','amount','transaction_code','payment_date','recorded_date','record_method','comments','status');
    $rvals = array("$customer_id","$uid","$payment_method","2","$mobile_number","100","R1-$transaction_code","$payment_date","$fulldate","SYSTEM","Registration split from payment by Cron service","1");
    $save = addtodb('o_incoming_payments', $rflds, $rvals);
    ///------------Save saving
    echo $save;
    $sflds = array('customer_id','split_from','payment_method','payment_category','mobile_number','amount','transaction_code','payment_date','recorded_date','record_method','comments','status');
    $svals = array("$customer_id","$uid","$payment_method","2","$mobile_number","$saving","S1-$transaction_code","$payment_date","$fulldate","SYSTEM","Saving split from payment by Cron service","1");
    $save = addtodb('o_incoming_payments', $sflds, $svals);
    echo $save;

    ///------------Mark original payment
    $upd = updatedb('o_incoming_payments',"status=2, comments='Payments split from this payment by Cron service'","uid='$uid'");
    echo $upd;
    ///--------This looks like a registration fee, split to registration and saving
    ///
}
