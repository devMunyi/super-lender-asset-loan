<?php
$title = "Monthly Summaries";
$oldest_loan = fetchmin('o_loans',"uid > 0 AND given_date != '0000-00-00'","given_date","given_date");
$oldest_loan_date = $oldest_loan['given_date'];

$ago = datediff3($oldest_loan_date, $date);
if($ago > 5475){
    // $oldest_loan_date = datesub($date, 15, 0, 0);
    $oldest_loan_date = $start_date;
}

$loans_totals_array = array();
$repaid_totals_array = array();
$payments_total_array = array();
$repayable_total_array = array();
$balance_total_array = array();
$interest_array = array();
$defaulted_array = array();
$interest_addons = $membership_addons = $processing_addons = $penalty_addons = $daily_penalty_addons = array();
$branches = table_to_obj('o_branches',"uid > 0 $andbranch1","1000","uid","name");


$loan_l  = table_to_array('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan","10000000","uid","uid","asc");
$loan_list = implode(',', $loan_l);

$all_addons = fetchtable('o_loan_addons',"status=1 AND loan_id in ($loan_list)","uid","asc","1000000000","loan_id, addon_id, addon_amount");
while($aa = mysqli_fetch_array($all_addons)){
    $lid = $aa['loan_id'];
    $addon_id = $aa['addon_id'];
    $addon_amount = $aa['addon_amount'];


    if($addon_id == 3 || $addon_id == 4 || $addon_id == 6){
        ////-----Interest
        $interest_addons[$lid]+= $addon_amount;
    }
    elseif ($addon_id == 1){
        ////----Membership
        $membership_addons[$lid]+= $addon_amount;
    }
    elseif ($addon_id == 2){
        ////----Processing
        $processing_addons[$lid]+= $addon_amount;
    }
    elseif ($addon_id ==  5){
        ////---All penalties
        $penalty_addons[$lid]+= $addon_amount;
    }

}

$interest_totals_array = $membership_totals_array = $processing_totals_array = $penalty_totals_array = $daily_penalty_totals_array = $addons_total_array = $deductions_total_array =  array();
$loans_d = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan","uid","asc","100000000000","uid, loan_amount, given_date, total_repaid, total_addons, status ,total_repayable_amount, total_deductions,loan_balance, current_branch");
while($l = mysqli_fetch_array($loans_d)){

    $uid = $l['uid'];
    $lamount = $l['loan_amount'];
    $repaid = $l['total_repaid'];
    $given_date = $l['given_date'];
    $total_repayable = $l['total_repayable_amount'];
    $total_addons = $l['total_addons'];
    $total_deductions = $l['total_deductions'];
     $loan_balance = $l['loan_balance'];


    $current_branch = $l['current_branch'];
    $status = $l['status'];


    $loan_int = $interest_addons[$uid];
    $penalties = $penalty_addons[$uid];
    $daily_penalties = $daily_penalty_addons[$uid];
    $processing = $processing_addons[$uid];
    $membership = $membership_addons[$uid];


    $loans_totals_array = obj_add($loans_totals_array, $current_branch, $lamount);
    $repaid_totals_array = obj_add($repaid_totals_array, $current_branch, $repaid);
    $repayable_total_array = obj_add($repayable_total_array, $current_branch, $total_repayable);

    $interest_totals_array = obj_add($interest_totals_array, $current_branch, $loan_int);
    $penalty_totals_array = obj_add($penalty_totals_array, $current_branch, $penalties);
    $daily_penalty_totals_array = obj_add($daily_penalty_totals_array, $current_branch, $daily_penalties);
    $processing_totals_array = obj_add($processing_totals_array, $current_branch, $processing);
    $membership_totals_array = obj_add($membership_totals_array, $current_branch, $membership);
    $addons_total_array = obj_add($addons_total_array, $current_branch, $total_addons);
    $deductions_total_array = obj_add($deductions_total_array, $current_branch, $total_deductions);
    $balance_total_array = obj_add($balance_total_array, $current_branch, $loan_balance);

    if($status == 7){

        $defaulted_array = obj_add($defaulted_array, $current_branch, $loan_balance);
    }

}


//var_dump($loans_totals_array);
/*
$payments_d = fetchtable('o_incoming_payments', "status=1 AND payment_date >= '$start_date' AND payment_date <= '$end_date' $andbranch_payments","uid","asc","1000000000","uid, amount, payment_date");
while($p = mysqli_fetch_array($payments_d)){
    $pid = $p['uid'];
    $amount = $p['amount'];
    $payment_date = $p['payment_date'];

    $gd = explode('-',$payment_date);
    $ym = $gd[0].'-'.$gd[1];
    $payments_total_array = obj_add($payments_total_array, $ym, $amount);

}
*/

// echo "<h4>Branch Performance</h4>";

echo "<table class='table table-bordered table-striped' id='example2'>";
echo "<thead><tr><th>Branch</th><th>Principal</th><th>Interest</th><th>Membership</th><th>Processing</th><th>Penalties</th><th>Total Repayable(P+I)</th><th>Total Repayable(P+I+Pen)</th><th>Loan Payments(P+I)</th><th>Default (Total Bal)</th><th>Collection Rates</th><th>~Interest Rate</th></tr></thead>";
?>
<tbody>
<?php
$date1 = new DateTime($start_date);
$date2 = new DateTime($end_date);

foreach ($branches as $bid => $bname) {


    $amt = (($loans_totals_array[$bid]));
    $pamt = (($payments_total_array[$bid]));
    $deductions_totals = (($deductions_total_array[$bid]));
    $total_rep_all = (($repayable_total_array[$bid]));
    $repaid_amount = (($repaid_totals_array[$bid]));
    $real_balance = (($balance_total_array[$bid]));
    $int_amount = (($interest_totals_array[$bid]));
    $total_rep  = $amt + $int_amount;
    $penalty_amount = (($penalty_totals_array[$bid])); ///---FIX
    $daily_penalty_amount = (($daily_penalty_totals_array[$bid]));
    $processing_amount = (($processing_totals_array[$bid]));
    $membership_totals = (($membership_totals_array[$bid]));
    $defaulted = false_zero(($defaulted_array[$bid]));



    $principle_interest = $amt + $int_amount;

    $bal = ($total_rep - $repaid_amount);

    $addons = $addons_total_array[$bid];
    $other_addons = false_zero($addons - $int_amount);

    $repaid_pi = $repaid_amount - ($other_addons-$penalty_amount); ///---FIX Other addons Pen+Reg+Processing

    ////----Totals
    $repaid_pi_t+=$repaid_pi;
    $principle_interest_t+=$principle_interest;
    $int_amount_t+=$int_amount;
    $amt_t+=$amt;
    $membership_totals_t+=$membership_totals;
    $processing_amount_t+=$processing_amount;
    $penalty_amount_t+=$penalty_amount;
    $daily_penalty_amount_t+=$daily_penalty_amount;
    $total_rep_all_t+=$total_rep_all;
    $total_rep_t+=$total_rep;
    $bal_t+=$bal;
    $defaulted_t+=$defaulted;
    $repaid_all+=$repaid_amount;
    $real_balance_t+=$real_balance;

    ///-----End of totals


    $crate = round((($repaid_pi)/($principle_interest))*100,2);
    $interest_rate = round( ($int_amount/$amt)*100, 2);

    echo "<tr><td>$bname</td><td>".number_format($amt)."</td><td>".number_format($int_amount)."</td><td>".number_format($membership_totals)."</td><td>".number_format($processing_amount)."</td><td>".number_format($penalty_amount)."</td><td>".number_format($total_rep)."</td><td>".number_format($total_rep_all)."</td><td>".number_format($repaid_pi)."</td><td>".number_format($defaulted)."</td><td>$crate%</td><td>$interest_rate%</td></tr>";



   // $date1->modify('first day of next month');
}


$crate_a = round(($repaid_pi_t/($principle_interest_t))*100,2);
$interest_rate_a = round( ($int_amount_t/$amt_t)*100, 2);


echo "<tfoot><tr><th>Total</th><th>".number_format($amt_t)."</th><th>".number_format($int_amount_t)."</th><th>".number_format($membership_totals_t)."</th><th>".number_format($processing_amount_t)."</th><th>".number_format($penalty_amount_t)."</th><th>".number_format($total_rep_t)."</th><th>".number_format($total_rep_all_t)."</th><th>".number_format($repaid_pi_t)."</th><th>".number_format($defaulted_t)."</th><th>$crate_a%</th><th>$interest_rate_a%</th></tr></tfoot>";




?>
</tbody>


<?php


echo "</table>";


echo "<div class=\"attachment-block clearfix font-italic\">";
echo "*Total Repayable: Principal+Interest+Processing+Membership+Penalties<br/>";
echo "*Collections: All monies that came in during the period, regardless of the loans<br/>";
echo "*Loan Payments: All payments towards the loans in that period. The payments are for Principal, Interest<br/>";
echo "*Balances: Full loan balance. Total Repaid amount - (Principal+Interest+Penalties+Processing+Admin+Registration)<br/>";
echo "*Default: Amount that has gone overdue (Includes Penalties, Processing , Admin, Registration)<br/>";
echo "*Collection Rate: The rate of collection for Principal+Interest Only <br/>";
echo "</div>";

?>
