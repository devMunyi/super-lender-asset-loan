<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");



$loans = fetchtable('o_loans',"disbursed=1 AND given_date = '2022-10-24' AND paid=0 AND paid=0","uid","asc","100000","uid, loan_amount");
while($l = mysqli_fetch_array($loans)){
    $loan = $l['uid'];
    $loan_amount = $l['loan_amount'];
    $amount = $loan_amount * 0.01;
    echo $loan.'-'.$amount.'<br/>';
 echo  remove_addon(7, $loan, 0);
 echo  addon_with_amount(7, $loan, $amount, 0);
}

include_once("../configs/close_connection.inc");
?>