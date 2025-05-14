<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$loans = fetchtable('o_loans',"disbursed=1 AND paid=1","uid","desc","100000","uid, given_date, final_due_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];

    $day_30 = dateadd($given_date, 0, 0, 30);
    if($day_30 != $final_due_date){
        echo "$uid, $given_date, $final_due_date, $day_30 <br/>";
       // $upd = updatedb('o_loans',"final_due_date='$day_30'","uid='$uid'");
    }
    echo "$uid, $given_date, $final_due_date, $day_30 <br/>";

   /* $loan_amount = $l['loan_amount'];
    $total_addons = $l['total_addons'];
    $total_repaid = $l['total_repaid'];
    $total_repayable_amount = $l['total_repayable_amount'];

    $interest = $loan_amount*0.15; */

   // echo addon_with_amount(1, $uid, $interest, 1, false);
    //echo addon_with_amount(2, $uid, 500, 1, false);

   // $repayable = $loan_amount + $total_addons;
   // $balance = $total_repayable_amount - $total_repaid;

   // echo "$loan_amount <br/>";
    //echo recalculate_loan($uid);

   // echo updatedb('o_loans',"loan_balance='$balance'","uid='$uid' AND loan_balance!='$balance' AND paid = 1");
}




