<?php
$customer_ids = table_to_array('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <='$end_date' $andbranch_loan","1000000","customer_id");
$customer_list = implode(',', $customer_ids);
$customer_names = table_to_obj('o_customers',"uid in ($customer_list)","100000000","uid","full_name");
$branches = table_to_obj('o_branches',"uid > 0 $andbranch1","1000","uid","name");
$staff_names = table_to_obj('o_users',"uid > 0 $andbranch_client","1000","uid","name");


$interest_addons = $membership_addons = $processing_addons = $penalty_addons = $daily_penalty_addons = array();


$loan_l  = table_to_array('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan","10000000","uid","uid","asc");
$loan_list = implode(',', $loan_l);

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
        $penalty_addons[$lid] = $addon_amount;
    }
    elseif ($addon_id == 9){
        $daily_penalty_addons[$lid] = $addon_amount;
    }

}


?>

<?php
$branch_principal_array = array();
$branch_interest_array = array();
$branches_array = table_to_obj('o_branches',"status=1","1000","uid","name");
$branch_managers_array = table_to_obj('o_users',"status = 1 AND user_group = 5","1000","branch","name");
$branch_expected_today_array = array();
$branch_deficit_array = array();
$branch_paid_today_array = array();
$branch_other_charges_array = array();


$loans = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <='$end_date' $andbranch_loan","uid","asc","100000000","uid, customer_id, account_number, loan_amount, total_repayable_amount, total_repaid, loan_balance, total_addons, given_date, current_lo, current_co, total_addons,current_branch, status, final_due_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $customer_id = $l['customer_id'];
    $account_number = $l['account_number'];
    $loan_amount = $l['loan_amount'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = $l['loan_balance'];
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    $total_addons = $l['total_addons'];
    $branch = $l['current_branch'];



    $name = $customer_names[$customer_id];
    $branch_name = $branches[$branch];
    $interest = $loan_amount * 0.24;
    $other_addons = $total_addons - $interest_addons[$uid];
    $principle_interest_paid = $total_repaid - $other_addons;
    $interest_principal = $loan_amount + $interest;
    $days_ago = datediff3($given_date, $date);
    $membership_fees = $membership_addons[$uid];
    $full_length = datediff3($given_date, $final_due_date);
    if($days_ago > $full_length){
        $days_ago = $full_length;
    }
    $full_length = 25;
    $daily_expected = round(($days_ago/$full_length)*$interest_principal);
    $deficit = $daily_expected - $principle_interest_paid;



    $branch_principal_array = obj_add($branch_principal_array, $branch, $loan_amount);
    $branch_interest_array = obj_add($branch_interest_array, $branch, $interest);
    $branch_other_charges_array = obj_add($branch_other_charges_array, $branch, $other_addons);
    $branch_expected_today_array = obj_add($branch_expected_today_array, $branch, $daily_expected);
    $branch_deficit_array = obj_add($branch_deficit_array, $branch, $deficit);
    $branch_paid_today_array = obj_add($branch_paid_today_array, $branch, $principle_interest_paid);



}



echo "<table class='table table-bordered table-striped' id='example2'>";
echo "<thead><tr><th>BID</th><th>Branch</th><th>Principal</th><th>Interest</th><th>Other Charges</th><th>Total(P+I)</th><th>Arrears Expected</th><th class='font-bold text-purple'>Arrears Paid</th><th>Arrears Balance</th><th>Arrears C.Rate</th><th>BM</th></tr></thead>";
echo "<tbody>";

foreach ($branches_array as $bid => $bname) {
         $bprinncipal = $branch_principal_array[$bid];
         $binterest = $branch_interest_array[$bid];
         $bother_charges = $branch_other_charges_array[$bid];
         $btotal_PI = $bprinncipal + $binterest;
         $barrears_expected = $branch_expected_today_array[$bid];
         $barrears_paid = $branch_paid_today_array[$bid];
         $barrears_balance = false_zero($barrears_expected - $barrears_paid);

         $crate = round(($barrears_paid/($barrears_expected))*100,2);
         $bm = $branch_managers_array[$bid];

        echo "<tr><td>$bid</td><td>$bname</td><td>".number_format($bprinncipal)."</td><td>".number_format($binterest)."</td><td>".number_format($bother_charges)."</td><td>".number_format($btotal_PI)."</td><td>".number_format($barrears_expected)."</td><td class='font-bold text-purple'>".number_format($barrears_paid)."</td><td>".number_format($barrears_balance)."</td><td>$crate%</td><td>$bm</td></tr>";

        $bprinncipal_+=$bprinncipal;
        $binterest_+=$binterest;
        $bother_charges_+=$bother_charges;
        $btotal_PI_+=$btotal_PI;
        $barrears_expected_+=$barrears_expected;
        $barrears_paid_+=$barrears_paid;
        $barrears_balance_+=$barrears_balance;



}
$crate_ = round(($barrears_paid_/($barrears_expected_))*100,2);

echo "</tbody>";
echo "<tfoot><tr><th>--</th><th>Total</th><th>".number_format($bprinncipal_)."</th><th>".number_format($binterest_)."</th><th>".number_format($bother_charges_)."</th><th>".number_format($btotal_PI_)."</th><th>".number_format($barrears_expected_)."</th><th class='font-bold text-purple'>".number_format($barrears_paid_)."</th><th>".number_format($barrears_balance_)."</th><th>$crate_%</th><th>--</th></tr>";

echo "</tfoot>";
echo "</table>";








?>



