<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$title = "Monthly Summaries";
//echo "<div class='alert alert-warning'>Report Under Maintenance, May be inaccurate</div>";



$end_date = $date;
$start_date = first_date_of_month(datesub($end_date,0,3,0));

echo "$start_date, $end_date";

//die();

$loans_totals_array = array();
$repaid_totals_array = array();
$payments_total_array = array();
$repayable_total_array = array();
$interest_array = array();
$defaulted_array = array();
$interest_addons = $membership_addons = $processing_addons = $penalty_addons = $daily_penalty_addons = array();


$loan_l  = table_to_array('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date'","10000000","uid","uid","asc");
$loan_list = implode(',', $loan_l);

//echo $loan_list;

$all_addons = fetchtable('o_loan_addons',"status=1 AND loan_id in ($loan_list)","uid","asc","1000000000","loan_id, addon_id, addon_amount");
while($aa = mysqli_fetch_array($all_addons)){
    $lid = $aa['loan_id'];
    $addon_id = $aa['addon_id'];
    $addon_amount = $aa['addon_amount'];

    if($addon_id == 1 || $addon_id == 4) {
        ////-----Interest
        $interest_addons[$lid] = $addon_amount;
    }
    elseif ($addon_id == 2){
        ////----Membership
        $membership_addons[$lid] = $addon_amount;
    }
    elseif ($addon_id == 5){
        ////----Processing
        $processing_addons[$lid] = $addon_amount;
    }
    elseif ($addon_id ==  12 || $addon_id == 3 || $addon_id == 7 || $addon_id == 11){
        ////---All penalties
        $penalty_addons = obj_add($penalty_addons, $lid,$addon_amount);
    }
    elseif ($addon_id == 9){
        $daily_penalty_addons[$lid] = $addon_amount;
    }

}

echo "$start_date, $end_date <br/>";

$interest_totals_array = $membership_totals_array = $processing_totals_array = $penalty_totals_array = $daily_penalty_totals_array = $addons_total_array = $loan_balances_array = array();
$loans_d = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date'","uid","asc","100000000000","uid, loan_amount, given_date, total_repaid, total_addons, status ,total_repayable_amount, loan_balance");
while($l = mysqli_fetch_array($loans_d)){

    $uid = $l['uid'];
    $lamount = $l['loan_amount'];
    $repaid = $l['total_repaid'];
    $given_date = $l['given_date'];
    $total_repayable = $l['total_repayable_amount'];
    $total_addons = $l['total_addons'];
    $loan_balance = $l['loan_balance'];
    $status = $l['status'];


    $loan_int = $interest_addons[$uid];
    $penalties = $penalty_addons[$uid];
    $daily_penalties = $daily_penalty_addons[$uid];
    $processing = $processing_addons[$uid];
    $membership = $membership_addons[$uid];

    $gd = explode('-',$given_date);
    $ym = $gd[0].'-'.$gd[1];
    $loans_totals_array = obj_add($loans_totals_array, $ym, $lamount);
    $repaid_totals_array = obj_add($repaid_totals_array, $ym, $repaid);
    $repayable_total_array = obj_add($repayable_total_array, $ym, $total_repayable);
    $loan_balances_array = obj_add($loan_balances_array, $ym, $loan_balance);

    $interest_totals_array = obj_add($interest_totals_array, $ym, $loan_int);
    $penalty_totals_array = obj_add($penalty_totals_array, $ym, $penalties);
    $daily_penalty_totals_array = obj_add($daily_penalty_totals_array, $ym, $daily_penalties);
    $processing_totals_array = obj_add($processing_totals_array, $ym, $processing);
    $membership_totals_array = obj_add($membership_totals_array, $ym, $membership);
    $addons_total_array = obj_add($addons_total_array, $ym, $total_addons);

    if($status == 7){
        $loan_balance = $l['loan_balance'];
        $defaulted_array = obj_add($defaulted_array, $ym, $loan_balance);
    }

}

//var_dump($loans_totals_array);

$payments_d = fetchtable('o_incoming_payments', "status=1 AND payment_date >= '$start_date' AND payment_date <= '$end_date'","uid","asc","1000000000","uid, amount, payment_date");
while($p = mysqli_fetch_array($payments_d)){
    $pid = $p['uid'];
    $amount = $p['amount'];
    $payment_date = $p['payment_date'];

    $gd = explode('-',$payment_date);
    $ym = $gd[0].'-'.$gd[1];
    $payments_total_array = obj_add($payments_total_array, $ym, $amount);

}


// echo "<h4>Branch Performance</h4>";


?>

<?php
$date1 = new DateTime($start_date);
$date2 = new DateTime($end_date);

while ($date1 <= $date2) {

    echo "kdkdd";
    $dt_ = $date1->format('Y-m');
    $y_ = $date1->format('Y');
    $m_ = $date1->format('m');

    $amt = (($loans_totals_array[$dt_]));
    $pamt = (($payments_total_array[$dt_]));
    //$total_rep = (($repayable_total_array[$dt_]));
    $repaid_amount = (($repaid_totals_array[$dt_]));
    $int_amount = (($interest_totals_array[$dt_]));
    $total_rep  = $amt + $int_amount;
    $penalty_amount = $penalty_totals_array[$dt_];
    $daily_penalty_amount = (($daily_penalty_totals_array[$dt_]));
    $processing_amount = (($processing_totals_array[$dt_]));
    $membership_totals = (($membership_totals_array[$dt_]));
    $defaulted = false_zero(($defaulted_array[$dt_]));
    $total_repayable = $repayable_total_array[$dt_];

    $principle_interest = $amt + $int_amount;

    $bal = $loan_balances_array[$dt_];

    $addons = $addons_total_array[$dt_];
    $other_addons = false_zero($addons - $int_amount);

    $repaid_pi = $repaid_amount - $other_addons;

    ////----Totals
    $repaid_pi_t+=$repaid_pi;
    $principle_interest_t+=$principle_interest;
    $int_amount_t+=$int_amount;
    $amt_t+=$amt;
    $membership_totals_t+=$membership_totals;
    $processing_amount_t+=$processing_amount;
    $penalty_amount_t+=$penalty_amount;
    $daily_penalty_amount_t+=$daily_penalty_amount;
    $total_repayable_t+=$total_repayable;
    $total_rep_t+=$total_rep;
    $repaid_amount_t+=$repaid_amount;
    $bal_t+=$bal;
    $defaulted_t+=$defaulted;
    ///-----End of totals

    $crate = round(($repaid_pi/($principle_interest))*100,2);
    $interest_rate = round( ($int_amount/$amt)*100, 2);

    //echo "<tr><td>$y_</td><td>$m_</td><td>".number_format($amt)."</td><td>".number_format($int_amount)."</td><td>".number_format($membership_totals)."</td><td>".number_format($processing_amount)."</td><td>".number_format($penalty_amount)."</td><td>".number_format($daily_penalty_amount)."</td><td>".number_format($total_repayable)."</td><td>".number_format($total_rep)."</td><td>".number_format($repaid_amount)."</td><td>".number_format($repaid_pi)."</td><td>".number_format($bal)."</td><td>".number_format($defaulted)."</td><td>$crate%</td><td>$interest_rate%</td></tr>";




///----Try to create
    $fds = array('title','tbl_','fld_','val_','foreign_key','start_date','end_date','last_update','amount_','status');
    $vals = array("Monthly Collection Rate","o_loans","business","$y_-$m_","0","$y_-$m_-01","$y_-$m_-27","$fulldate","$crate","1");
    $add = addtodb('o_report_summary_monthly',$fds, $vals);

    echo "ADD:".$add.",";
//if($add == 1){
    $update = updatedb('o_report_summary_monthly',"amount_='$crate', last_update='$fulldate', status=1", "tbl_='o_loans' AND fld_='business' AND val_='$y_-$m_' AND start_date='$y_-$m_-01' AND end_date='$y_-$m_-27'");
    echo "UPDATE:".$update.",";
//  }


    $date1->modify('first day of next month');
}

$crate_a = round(($repaid_pi_t/($principle_interest_t))*100,2);
$interest_rate_a = round( ($int_amount_t/$amt_t)*100, 2);


//echo "<tfoot><tr><th colspan='2'>Total</th><th>".number_format($amt_t)."</th><th>".number_format($int_amount_t)."</th><th>".number_format($membership_totals_t)."</th><th>".number_format($processing_amount_t)."</th><th>".number_format($penalty_amount_t)."</th><th>".number_format($daily_penalty_amount_t)."</th><th>".number_format($total_repayable_t)."</th><th>".number_format($total_rep_t)."</th><th>".number_format($repaid_amount_t)."</th><th>".number_format($repaid_pi_t)."</th><th>".number_format($bal_t)."</th><th>".number_format($defaulted_t)."</th><th>$crate_a%</th><th>$interest_rate_a%</th></tr></tfoot>";


?>




