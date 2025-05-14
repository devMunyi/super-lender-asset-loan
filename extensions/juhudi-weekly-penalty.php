<?php
session_start();
include_once("../configs/auth.inc");
include_once '../configs/20200902.php';

include_once("../php_functions/functions.php");


$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");

$weekly_interest = fetchrow('o_addons',"uid=5","amount");

$overdues = fetchtable('o_loans',"final_due_date < '$date' AND disbursed=1 AND paid=0","uid","asc","100000","uid, final_due_date, status, loan_balance");
while($od = mysqli_fetch_array($overdues)){
    $uid = $od['uid'];
    $final_due_date = $od['final_due_date'];
    $status = $od['status'];
    $loan_balance = $od['loan_balance'];
    $due_ago = datediff3($final_due_date, $date);

    $weeks_passed =  intval($due_ago/7);

    $total_pen = $weekly_interest * $weeks_passed;

   // echo "$uid, Bal: $loan_balance, Final_d: $final_due_date, Due_ago: $due_ago, Weeks_passed: $weeks_passed, Total Pen: $total_pen <br/>";
    if ($due_ago % 7 == 0 && $loan_balance > 10) {
        $addon_amount = $loan_balance * ($total_pen/100);
        if($addon_amount >= 5) {
            //echo $addon_amount;
            echo addon_with_amount(5, $uid, $addon_amount);
        }
    }



}

include_once("../configs/close_connection.inc");