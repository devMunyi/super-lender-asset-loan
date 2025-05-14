<?php
session_start();
include_once ("../configs/20200902.php");
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$customers_list = array();

$customer_limits = table_to_array('o_customers',"status=1 AND loan_limit>0","1000000","uid");

$addon_amount = fetchrow('o_addons',"uid=2","amount");
$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status !=0 ","uid","desc","100000","uid, given_date, final_due_date, customer_id, loan_amount, total_addons, loan_balance, product_id");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $new_due = $l['final_due_date'];
    $customer_id = $l['customer_id'];
    $loan_amount = $l['loan_amount'];
    $total_addons = $l['total_addons'];
    $loan_balance = $l['loan_balance'];
    $product_id = $l['product_id'];

    $interest = $loan_amount * ($addon_amount/100);

    $ago = intval(datediff($given_date, $date));
    $to_final = intval(datediff($date, $final_due_date));

    //////----------------Reminders
    ///

    if($to_final == 1){
        //----Day to due
        $message = "Dear Customer! Your BODAFUND loan repayment of $loan_balance is due on Tomorrow. For more information and to repay your loan, dial *789*600#";
    }
    elseif ($to_final == 0){
        //---Due date
        $message = "Dear Customer! Your BODAFUND loan repayment of $loan_balance is due on Today. For more information and to repay your loan, dial *789*600#";
    }
    elseif ($to_final == -1 && $ago > 20){
        //--After due date
         $message = "Dear Customer! Your BODAFUND loan repayment of $loan_balance is overdue. For more information and to repay your loan, dial *789*600#";
    }

    ///
    ////////--------------End of reminders




    ////-----One time fix

   // $week = (floor(($ago/7)));
    $week = countXInY(8, $ago);
    $weekx = countXInY(7, $ago);

    if($ago >= 5){
        if($weekx > 3){
            $weekx = 3;
        }
        if($loan_amount >= 2500){
            $weekx = 1;
        }
        $new_due = dateadd($final_due_date, 0, 0, $weekx*7);
    }


    $new_interest = ($week+1) * $interest;
    if($loan_amount >= 2500 && $ago <= 21){
        $weekly_penalty = 100 * $weekx;
        $new_interest = $interest + $weekly_penalty;
        $productDaysDetails = fetchonerow('o_loan_products',"uid=$product_id","period, period_units");
        $productDays = $productDaysDetails['period'] * $productDaysDetails['period_units'];
        $new_due = dateadd($given_date, 0, 0, $productDays);
        if($new_due != $final_due_date){
            $update_due_date = updatedb('o_loans',"final_due_date='$new_due'","uid='$uid'");
            echo "$uid Final due date pushed $given_date - $final_due_date <br/>";
        }
    }

    echo " New Interest:$new_interest, Interest:$interest, Week:$week <br/>";
    if($new_interest != $total_addons && $loan_amount < 2500 && $ago <= 21){
        echo "Update Loan:$uid, Old: $total_addons, New: $new_interest, Date: $given_date, Ago: $week <br/>";
        $update = updatedb('o_loan_addons',"addon_amount='$new_interest'","loan_id='$uid' AND status=1 AND addon_id=2");
        if($update == 1){
            echo "Success Updating $uid <br/>";
            $event = "Loan interest updated to $new_interest AND due date extended to $new_due by system cron process";
            store_event('o_loans', $uid,"$event");

            ////------Increase loan due date too
              $update_due_date = updatedb('o_loans',"final_due_date='$new_due'","uid='$uid'");

            echo recalculate_loan($uid);
        }
        else{
            echo "Error updating $uid <br/>";
        }
    }
    else{
        echo "Skipped $uid because NI: $new_interest, Ad:$total_addons <br/>";
    }

    if($ago > 21){
        ///---Customer has a limit and has overstayed with loan
        $upd = updatedb('o_customers',"loan_limit=0","uid='$customer_id' AND loan_limit > 0");
        $update_due_date = updatedb('o_loans',"status=7","uid='$uid' AND status=3 AND disbursed=1 AND paid=0");
        ///---Mark Loan as overdue
        if($upd == 1){
            store_event('o_customers', $customer_id,"Limit removed by system because loan is Week:$week overdue");
           echo "Limit for $customer_id: Removed";
        }
    }


    //echo "Update Loan:$uid, Interest: $interest, Old: $total_addons, New: $new_interest, Date: $given_date, Ago: $ago ($week * $interest) <br/>";


}








