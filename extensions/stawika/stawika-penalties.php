<?php
session_start();
include_once ('../configs/20200902.php');
$product_id = $_GET['p'];
$offset = $_GET['offset'];
$rpp = $_GET['rpp'];
include_once("../php_functions/functions.php");
$_SESSION['db_name'] = $db_;
include_once("../configs/conn.inc");

$month_ago_2 = datesub($date, 0, 0, 60);

echo $month_ago_2;
$loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","100","uid","name");


$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status !=0 AND product_id='$product_id' AND final_due_date < '$date' and given_date >= '$month_ago_2'","uid","desc","$offset,$rpp","uid, given_date, final_due_date, loan_amount, loan_balance, status");
while($l = mysqli_fetch_array($loans)) {
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $loan_amount = $l['loan_amount'];
    $loan_balance = $l['loan_balance'];
    $loan_status = $l['status'];

    $amount = $loan_amount;
    if($loan_balance < $loan_amount){
        $amount = $loan_balance;
    }


    $days_passed = datediff3($date, $final_due_date);


   if($days_passed > 0 && $loan_status != 7){
       $overdue_mark = updatedb('o_loans',"status='7'","uid='$uid'");
     //  echo "$uid: Mark to overdue: $overdue_mark <br/>";
       $total_marked = $total_marked + 1;
   }

    if($amount < 5000){
       $days_passed_graced = $days_passed - 6;
    }
    else{
        $days_passed_graced = $days_passed - 10;
    }
    if($days_passed_graced > 10){
        $days_passed_graced = 10;
    }

    if($days_passed_graced > 0){
        $penalty = 0.01*$days_passed_graced*$amount;
        $addon = addon_with_amount(3, $uid, $penalty);
      //  echo "Loan:$uid, Addon addstatus: $addon <br/>";
    }
    else{
       // echo "Loan: $uid, Grace not passed <br/>";
    }





    $state = $loan_statuses[$loan_status];


  //  echo "$uid [$given_date] [$final_due_date] [$loan_amount] [$loan_balance] $state [$days_passed][$days_passed_graced]<br/>";

}


echo "Marked overdue $total_marked<br/>";
