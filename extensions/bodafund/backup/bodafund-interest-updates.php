<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$customers_list = array();

$customer_limits = table_to_array('o_customers',"status=1 AND loan_limit>0","1000000","uid");

$addon_amount = fetchrow('o_addons',"uid=2","amount");
$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status !=0 ","uid","desc","100000","uid, given_date, final_due_date, customer_id, loan_amount, total_addons");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $customer_id = $l['customer_id'];
    $loan_amount = $l['loan_amount'];
    $total_addons = $l['total_addons'];

    $interest = $loan_amount * ($addon_amount/100);

    $ago = intval(datediff($given_date, $date));

   // $week = (floor(($ago/7)));
    $week = countXInY(8, $ago);



    $new_interest = ($week+1) * $interest;

    echo " New Interest:$new_interest, Interest:$interest, Week:$week <br/>";
    if($new_interest != $total_addons ){
        echo "Update Loan:$uid, Old: $total_addons, New: $new_interest, Date: $given_date, Ago: $week <br/>";
        $update = updatedb('o_loan_addons',"addon_amount='$new_interest'","loan_id='$uid' AND status=1 AND addon_id=2");
        if($update == 1){
            echo "Success Updating $uid <br/>";
            $event = "Loan interest updated by system cron process";
            store_event('o_loans', $uid,"$event");
            echo recalculate_loan($uid);
        }
        else{
            echo "Error updating $uid <br/>";
        }
    }
    else{
        echo "Skipped $uid because NI: $new_interest, Ad:$total_addons <br/>";
    }

    if($week > 2){
        ///---Customer has a limit and has overstayed with loan
        $upd = updatedb('o_customers',"loan_limit=0","uid='$customer_id' AND loan_limit > 0");
        if($upd == 1){
            store_event('o_customers', $customer_id,"Limit removed by system because loan is Week:$week overdue");
           echo "Limit for $customer_id: Removed";
        }
    }


    //echo "Update Loan:$uid, Interest: $interest, Old: $total_addons, New: $new_interest, Date: $given_date, Ago: $ago ($week * $interest) <br/>";


}








