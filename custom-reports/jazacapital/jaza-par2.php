<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
$customer_ids = table_to_array('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <='$end_date' $andbranch_loan","1000000","customer_id");
$customer_list = implode(',', $customer_ids);
$customer_names = table_to_obj('o_customers',"uid in ($customer_list)","100000000","uid","full_name");
$branches = table_to_obj('o_branches',"uid > 0 $andbranch1","1000","uid","name");
$staff_names = table_to_obj('o_users',"uid > 0 $andbranch_client","1000","uid","name");


$loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","1000","uid","name");


$interest_addons = $membership_addons = $processing_addons = $penalty_addons = $daily_penalty_addons = array();

$interests = array(3,4,6);
$registrations = array(1);
$processings = array(2);
$penalties = array(5);


$loan_l  = table_to_array('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan","10000000","uid","uid","asc");
$loan_list = implode(',', $loan_l);

$all_addons = fetchtable('o_loan_addons',"status=1 AND loan_id in ($loan_list)","uid","asc","1000000000","loan_id, addon_id, addon_amount");
while($aa = mysqli_fetch_array($all_addons)){
    $lid = $aa['loan_id'];
    $addon_id = $aa['addon_id'];
    $addon_amount = $aa['addon_amount'];

    if(in_array($addon_id, $interests)){
        ////-----Interest

        $interest_addons[$lid]+= $addon_amount;
    }
    elseif (in_array($addon_id, $registrations)){
        ////----Membership
        $membership_addons[$lid]+= $addon_amount;
    }
    elseif (in_array($addon_id, $processings)){
        ////----Processing
        $processing_addons[$lid]+= $addon_amount;
    }
    elseif (in_array($addon_id, $penalties)){
        ////---All penalties
        $penalty_addons[$lid]+= $addon_amount;
    }


}
$last_pay_date_array = array();
///----Payments, find last payment
$payments = fetchtable('o_incoming_payments',"loan_id in ($loan_list)","payment_date","asc","10000000000","loan_id, payment_date");
while($p = mysqli_fetch_array($payments)){
    $loan_id = $p['loan_id'];
    $payment_date = $p['payment_date'];
    $last_pay_date_array[$loan_id] = $payment_date;
}

$branch_repaid_array = array();
$branch_deficit_array = array();


echo "<table class='tablex' id='example2'>";
//echo "<thead><tr><th>UID</th><th>Name</th><th>Phone</th><th>Branch</th><th>Principal</th><th>Interest</th><th>Registration</th><th>Processing</th><th>Other Charges(Penalties, Court etc)</th><th>Total Repayable(P+I)</th><th>Total Repayable(All)</th><th>Total Repaid</th><th>Loan Balance</th><th>Disbursed Date</th><th>Due Date</th><th>Cleared Date</th><th>Last Pay Date</th><th>Status</th></tr></thead>";

echo "<thead><tr><th>UID</th><th>Phone</th><th>Branch</th><th>Principal Applied</th><th>Principal Paid</th><th>Principal Balance</th><th>Interest Applied</th><th>Interest Paid</th><th>Interest Balance</th><th>P. Applied + I. Applied (Repayable)</th><th>P. Paid + I. Paid (Repaid)</th><th>P+I Repayment Balance </th><th>Registration Fees</th><th>Processing Fees</th><th>Other Charges Applied(Penalties, Court etc)</th><th>Other Charges Paid</th><th>Other Charges Balance</th><th>Client Total Repaid (All)</th><th>Total Balance (All Outstanding)</th><th>Disbursed Date</th><th>Due Date</th><th>Cleared Date</th><th>Last Pay Date</th><th>Status</th></tr></thead>";


?>
<tbody>
<?php
$loans = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <='$end_date' $andbranch_loan","uid","asc","100000000","uid, customer_id, account_number, loan_amount, total_repayable_amount, total_repaid, loan_balance, total_addons, given_date, current_lo, current_co, total_addons,current_branch, status, final_due_date, cleared_date, status");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $customer_id = $l['customer_id'];
    $account_number = $l['account_number'];
    $loan_amount = $l['loan_amount'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $cleared_date = $l['cleared_date'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = $l['loan_balance'];
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    $total_addons = $l['total_addons'];
    $branch = $l['current_branch'];
    $status = $l['status'];



    $name = $customer_names[$customer_id];
    $branch_name = $branches[$branch];
    //$interest = $loan_amount * 0.24;
    $interest = $interest_addons[$uid];
    $penalties = $penalty_addons[$uid];
    $other_addons = $total_addons - $interest;
    $principle_interest_paid = $total_repaid - $other_addons;
    $interest_principal = $loan_amount + $interest;
    $days_ago = datediff3($given_date, $date);
    $membership_fees = $membership_addons[$uid];
    $processing_fees = $processing_addons[$uid];
    $full_length = datediff3($given_date, $final_due_date);
    if($days_ago > $full_length){
        $days_ago = $full_length;
    }
    $full_length = 30;
    $daily_expected = round(($days_ago/$full_length)*$interest_principal);
    $deficit = $daily_expected - $principle_interest_paid;
    if($current_lo > 0 || $current_co > 0) {
        $lo_co = $staff_names[$current_lo] . ' & ' . $staff_names[$current_co];
    }


    //////Maths
    $upfronts = $membership_fees + $processing_fees;
    /////----------
    $principle_paid = false_zero($total_repaid - $upfronts);  ////Deduct from amount paid first
    if($total_repaid > $loan_amount){
        $principle_paid = $loan_amount; ////If its greater, set it as already paid
    }
    $interest_paid = false_zero($total_repaid - $loan_amount - $upfronts); ////Deduct from total amount - principal
    if($interest_paid > $interest){
        $interest_paid = $interest;
    }

    $other_charges_applied = $total_addons - $interest - $upfronts;
    $other_charges_paid = false_zero($total_repaid - $principle_paid - $interest_paid - $upfronts);
    $other_charges_balance = false_zero($other_charges_applied - $other_charges_paid);

    $principle_balance = $loan_amount - $principle_paid;
    $interest_balance = $interest - $interest_paid;
    $pi_repayable = $loan_amount + $interest;
    $pi_repaid = false_zero($principle_paid + $interest_paid);
    $pi_balance = false_zero($pi_repayable - $pi_repaid);

    $branch_deficit_array = obj_add($branch_deficit_array, $branch, $deficit);
    $state = $loan_statuses[$status];
    $last_pay_date = $last_pay_date_array[$uid];


    echo "<tr><td>$uid</td><td>$account_number</td><td>$branch_name</td><td>".number_format($loan_amount)."</td><td>".number_format($principle_paid)."</td><td>".number_format($principle_balance)."</td><td>".number_format($interest)."</td><td>".number_format($interest_paid)."</td><td>".number_format($interest_balance)."</td><td>".number_format($pi_repayable)."</td><td>".number_format($pi_repaid)."</td><td>".number_format($pi_balance)." </td><td>".number_format($membership_fees)."</td><td>".number_format($processing_fees)."</td><td>".number_format($other_charges_applied)."</td><td>".number_format($other_charges_paid)."</td><td>".number_format($other_charges_balance)."</td><td>".number_format($total_repaid)."</td><td>".number_format($loan_balance)."</td><td>$given_date</td><td>$final_due_date</td><td>$cleared_date</td><td>$last_pay_date</td><td>$state</td></tr>";

}







?>
</tbody>
<?php
//echo "<tfoot><td>--</td><td>--</td><td>--</td><td>--</td><td>$loan_amount_total</td><td>$interest_total</td><td>$membership_total</td><td>$processing_fees</td><td>$interest_principal</td><td>$total_repayable_amount</td><td>$total_repaid</td><td>$loan_balance</td><td>$given_date</td><td>$final_due_date</td><td>$state</td></tfoot>";
?>
</table>


<?php


?>


