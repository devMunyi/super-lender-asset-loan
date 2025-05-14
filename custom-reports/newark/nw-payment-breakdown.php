<?php

// get all loans that have been paid in the specified period
$payment_loans = table_to_array('o_incoming_payments',"status=1 AND payment_date >= '$start_date' AND payment_date <= '$end_date'","10000000","loan_id");

// filter out duplicates
$payment_loans = array_unique($payment_loans);

// convert to string separated by commas
$loans_list = implode(',',$payment_loans);
$interest_addons = $membership_addons = $processing_addons = $penalty_addons = $daily_penalty_addons = 0;


$all_addons = fetchtable('o_loan_addons',"status=1 AND loan_id in ($loans_list)","uid","asc","10000,000","loan_id, addon_id, addon_amount");
while($aa = mysqli_fetch_array($all_addons)){
    $lid = $aa['loan_id'];
    $addon_id = $aa['addon_id'];
    $addon_amount = $aa['addon_amount'];

    if($addon_id == 1 || $addon_id == 4) {
        ////-----Interest
        $interest_addons+= $addon_amount;
    }
    elseif ($addon_id == 2){
        ////----Membership
        $membership_addons+= $addon_amount;
    }
    elseif ($addon_id == 5){
        ////----Processing
        $processing_addons+= $addon_amount;
    }
    elseif ($addon_id ==  12 || $addon_id == 3 || $addon_id == 7 || $addon_id == 11){
        ////---All penalties
        $penalty_addons+= $addon_amount;
    }
    elseif ($addon_id == 9){
        $daily_penalty_addons+= $addon_amount;
    }

}

$loan_amount_array = array();
$loans = fetchtable('o_loans',"uid in ($loans_list)","uid","asc","10000000","uid, loan_amount, given_date, total_repaid, total_addons, status ,total_repayable_amount, loan_balance, current_branch");
while($l = mysqli_fetch_array($loans)){
    $lid = $l['uid'];
    $lamount = $l['loan_amount'];
    $loan_amount_array[$lid] = $lamount;
}


$payments = fetchtable('o_incoming_payments',"status=1 AND payment_date >= '$start_date' AND payment_date <= '$end_date'","uid","asc","1000000","uid, amount, loan_id, payment_date, branch_id");
while($p = mysqli_fetch_array($payments)){
    $uid = $p['uid'];
    $amount = $p['amount'];
    $loan_id = $p['loan_id'];
    $pdate = $p['payment_date'];
    $branch_id = $p['branch_id'];

    ////---------Loan Amount
    $l_amount = $loan_amount_array[$loan_id];
    ///----------Loan Interest
    $l_interest = $interest_addons[$loan_id];
    ///----------Loan Penalties
    $l_penalties = $penalty_addons[$loan_id];
    ///----------Processing
    $l_processing = $processing_addons[$loan_id];
    ///----------Membership
    $l_membership = $membership_addons[$loan_id];
    ////---------Daily Penalty
    $l_daily_penalty = $daily_penalty_addons[$loan_id];
}


echo "<table class='table table-bordered table-striped' id='example2'>";
echo "<thead><tr><th>Date</th><th>Principal</th><th>Interest</th><th>Membership</th><th>Processing</th><th>Penalties</th><th>DailyInterest (DD+31-45)</th><th>Total Repayable</th><th>Loan Payments</th><th>Balances</th><th>Default</th><th>Collection Rates</th><th>~Interest Rate</th></tr></thead>";
?>
<tbody>
<?php
$range = generateDateRange($start_date, $end_date);

for($b=0; $b < sizeof($range); ++$b)
{
    $dat = $range[$b];

    $amt = (($loans_totals_array[$dat]));
    $pamt = (($payments_total_array[$dat]));
    //$total_rep = (($repayable_total_array[$dt_]));
    $repaid_amount = (($repaid_totals_array[$dat]));
    $int_amount = (($interest_totals_array[$dat]));
    $total_rep  = $amt + $int_amount;
    $penalty_amount = (($penalty_totals_array[$dat]));
    $daily_penalty_amount = (($daily_penalty_totals_array[$dat]));
    $processing_amount = (($processing_totals_array[$dat]));
    $membership_totals = (($membership_totals_array[$dat]));
    $defaulted = false_zero(($defaulted_array[$dat]));

    $principle_interest = $amt + $int_amount;

    $bal = ($total_rep - $repaid_amount);

    $addons = $addons_total_array[$dat];
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
    $total_rep_t+=$total_rep;
    $bal_t+=$bal;
    $defaulted_t+=$defaulted;

    ///-----End of totals


    $crate = round(($repaid_pi/($principle_interest))*100,2);
    $interest_rate = round( ($int_amount/$amt)*100, 2);

    echo "<tr><td>$dat</td><td>".number_format($amt)."</td><td>".number_format($int_amount)."</td><td>".number_format($membership_totals)."</td><td>".number_format($processing_amount)."</td><td>".number_format($penalty_amount)."</td><td>".number_format($daily_penalty_amount)."</td><td>".number_format($total_rep)."</td><td>".number_format($repaid_pi)."</td><td>".number_format($bal)."</td><td>".number_format($defaulted)."</td><td>$crate%</td><td>$interest_rate%</td></tr>";



    // $date1->modify('first day of next month');
}


$crate_a = round(($repaid_pi_t/($principle_interest_t))*100,2);
$interest_rate_a = round( ($int_amount_t/$amt_t)*100, 2);


echo "<tfoot><tr><th>Total</th><th>".number_format($amt_t)."</th><th>".number_format($int_amount_t)."</th><th>".number_format($membership_totals_t)."</th><th>".number_format($processing_amount_t)."</th><th>".number_format($penalty_amount_t)."</th><th>".number_format($daily_penalty_amount_t)."</th><th>".number_format($total_rep_t)."</th><th>".number_format($repaid_pi_t)."</th><th>".number_format($bal_t)."</th><th>".number_format($defaulted_t)."</th><th>$crate_a%</th><th>$interest_rate_a%</th></tr></tfoot>";




?>
</tbody>


