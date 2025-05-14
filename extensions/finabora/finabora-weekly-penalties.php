<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$company = $_GET['c'];
$product_id = $_GET['p'];
include_once("../configs/auth.inc");
if($company > 0) {
echo $company;
    include_once("../php_functions/functions.php");

    $company_d = company_details($company);
    if ($company_d['uid'] > 0) {
        $db = $company_d['db_name'];
        $_SESSION['db_name'] = $db;
        include_once("../configs/conn.inc");

        $prod = fetchonerow('o_loan_products',"uid='$product_id'","period, period_units, pay_frequency");
        $pay_frequency = $prod['pay_frequency'];
        $period = $prod['period'];
        $period_units = $prod['period_units'];
        $total_period = $period * $period_units;
        $instalments  = ceil(($total_period/$period_units));




///////-------------------------End of get company details
        ///-------Lets start here
       $loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status !=0 AND product_id='$product_id' AND final_due_date > '$date'","given_date","asc","5000","uid, given_date, final_due_date, total_repayable_amount, total_instalments, total_repaid");
       while($l = mysqli_fetch_array($loans)){
           $uid = $l['uid'];
           $given_date = $l['given_date'];
           $final_due_date = $l['final_due_date'];
           $total_repayable_amount = $l['total_repayable_amount'];
           $total_repaid = $l['total_repaid'];
           $total_instalments = $l['total_instalments'];
           $days_passed = datediff($given_date, $date);

           echo "$uid $given_date [$days_passed] <br/>";
          // echo remove_addon(3, $uid);

            ///----penalize balance if > 100
           /// //---Penalize at 12:
           /// Send messages at 7.00

           if($pay_frequency > 0) {


              /////---------------Set next instalment date


               /////--------------Set penalties
               if($days_passed == 8){
                   $required = $total_repayable_amount*0.25;
                   $instalment_balance = $required - $total_repaid;
                   if($instalment_balance > 200){
                       $penalty = ceil((0.05*$instalment_balance));
                       addon_with_amount(3, $uid, $penalty, 1 );
                       $event = "A day 8 penalty of $penalty applied  by system on [$fulldate]";
                       store_event('o_loans', $uid,"$event");
                       echo $event;
                   }
               }
              else if($days_passed == 15){
                  $required = $total_repayable_amount*0.5;
                  $instalment_balance = $required - $total_repaid;
                  if($instalment_balance > 200){
                      $penalty = ceil((0.05*$instalment_balance));
                      addon_with_amount(3, $uid, $penalty, 1 );
                      $event = "A day 15 penalty of $penalty applied  by system on [$fulldate]";
                      store_event('o_loans', $uid,"$event");
                      echo $event;
                  }
               }
               else if($days_passed == 22){
                   $required = $total_repayable_amount*0.75;
                   $instalment_balance = $required - $total_repaid;
                   if($instalment_balance > 200){
                       $penalty = ceil((0.05*$instalment_balance));
                       addon_with_amount(3, $uid, $penalty, 1 );
                       $event = "A day 22 penalty of $penalty applied  by system on [$fulldate]";
                       store_event('o_loans', $uid,"$event");
                       echo $event;
                   }
               }
              else if($days_passed == 29){
                  $required = $total_repayable_amount;
                  $instalment_balance = $required - $total_repaid;
                  if($instalment_balance > 200){
                      $penalty = ceil((0.05*$instalment_balance));
                      addon_with_amount(3, $uid, $penalty, 1 );
                      $event = "A day 29 penalty of $penalty applied  by system on [$fulldate]";
                      store_event('o_loans', $uid,"$event");
                      echo $event;
                  }
               }



               if($days_passed <= 7){
                   $next_due_date = dateadd($given_date,0,0, 7);
                   $current_inst_amount = $total_repayable_amount*0.25;
                   $update_ = updatedb('o_loans', "current_instalment=1, next_due_date='$next_due_date', current_instalment_amount='$current_inst_amount' ","uid='$uid'");
               }
               else if($days_passed > 7 && $days_passed <= 14){
                   $next_due_date = dateadd($given_date,0,0, 14);
                   $current_inst_amount = $total_repayable_amount*0.5;
                   $update_ = updatedb('o_loans', "current_instalment=2, next_due_date='$next_due_date', current_instalment_amount='$current_inst_amount' ","uid='$uid'");
               }
               else if($days_passed > 14 && $days_passed <= 21){
                   $next_due_date = dateadd($given_date,0,0, 21);
                   $current_inst_amount = $total_repayable_amount*0.75;
                   $update_ = updatedb('o_loans', "current_instalment=3, next_due_date='$next_due_date', current_instalment_amount='$current_inst_amount' ","uid='$uid'");
               }
               else if($days_passed > 21){
                   $current_inst_amount = $total_repayable_amount*1;
                   $update_ = updatedb('o_loans', "current_instalment=4, next_due_date='$next_due_date', current_instalment_amount='$current_inst_amount' ","uid='$uid'");
                   $next_due_date = dateadd($given_date,0,0, 28);
               }

               recalculate_loan($uid);

               }
           else{
               echo "$uid has 0 instalments frequency <br/>";
           }



       }
    }

}
else{
    ///------Probably a suspense payment
    echo "Add a company parameter";
}



