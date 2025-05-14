<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

//echo $db_;
$yesterday = datesub($date, 0, 0, 1);
$sixty_ago = datesub($date, 0, 0, 60);

echo $yesterday.$sixty_ago;
$total = 0;
$added_ = 0;
$skipped = 0;
$loans = fetchtable('o_loans',"disbursed=1 AND paid=0  AND status!=0 AND final_due_date BETWEEN '$sixty_ago' AND '$yesterday'","uid","asc","100000","uid, final_due_date, loan_amount, total_repaid");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $final_due_date = $l['final_due_date'];
    $loan_amount = $l['loan_amount'];
    $total_repaid = $l['total_repaid'];
   // remove_addon(10, $uid, 1);

    //---------------This is an inefficient way, fix it in the future by removing the function outside
    $total_interest = totaltable('o_loan_addons',"loan_id='$uid' AND status=1 AND addon_id in (1,2,4,6,7)","addon_amount");
    $total_after_due = totaltable('o_loan_addons',"loan_id='$uid' AND status=1 AND addon_id = 10","addon_amount");
    $original_loan = $total_interest + $loan_amount;
    if($total_repaid >= $total_interest+$loan_amount){
        ///----No further penalties
        echo "Skipped $uid, Repaid: $total_repaid, P+Interest: $original_loan <br/>";
        $skipped = $skipped + 1;
    }
    else {


        $lapse = datediff3($final_due_date, $date);

        $interest = ($lapse / 100) * $loan_amount;
        if($interest < $total_after_due){  ////-----If we calculate a less after due, keep current afterdue
            //$interest = $total_after_due;
            echo "$uid: New afterdue is less than current afterdue: $interest, $total_after_due <br/> ";
        }
        else {

            $exists = checkrowexists('o_loan_addons', "loan_id='$uid' AND addon_id = 10 AND status=1");
            //echo
            if ($exists == 1) {
                $added = addon_with_amount_update(10, $uid, $interest, 1, true);
            } else {
                $added = addon_with_amount(10, $uid, $interest, 0, true);
            }

            if ($added == 1) {
                store_event('o_loans', $uid, "Afterdue Interest Addon Added/Updated ($exists) with amount $interest by system cron service");
            }

            $added_ = $added_ + $added;
            $total = $total + 1;
            echo "Applied $uid, Repaid: $total_repaid, Interest: $total_interest, Addon amount: $interest, Lapsed: $lapse <br/>";
        }
    }
   // echo "$uid, $final_due_date, $lapse, $loan_amount,$interest<br/>";

}

echo "Run $total ($added_ Added) Skipped ($skipped)";

?>