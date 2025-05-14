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

    if($addon_id == 3 || $addon_id == 4 || $addon_id == 6){
        ////-----Interest
        $interest_addons[$lid] = $addon_amount;
    }
    elseif ($addon_id == 1){
        ////----Membership
        $membership_addons[$lid] = $addon_amount;
    }
    elseif ($addon_id == 2){
        ////----Processing
        $processing_addons[$lid] = $addon_amount;
    }
    elseif ($addon_id ==  5){
        ////---All penalties
        $penalty_addons[$lid] = $addon_amount;
    }


}


$co_principal_array = array();
$co_interest_array = array();
$branches_array = table_to_obj('o_branches', "status=1", "1000", "uid", "name");
$branch_managers_array = table_to_obj('o_users', "status = 1 AND user_group = 5", "1000", "branch", "name");
$user_branch_array = table_to_obj('o_users', "status = 1 AND user_group != 5", "1000", "branch", "name");
$branch_users_array = table_to_obj('o_users', "status = 1", "10000", "uid", "name");
$co_expected_today_array = array();
$co_deficit_array = array();
$co_paid_today_array = array();
$co_other_charges_array = array();
$available_users_array = array();
$current_pair_array = array();


$loans = fetchtable('o_loans', "disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <='$end_date' $andbranch_loan", "uid", "asc", "100000000", "uid, customer_id, account_number, loan_amount, total_repayable_amount, total_repaid, loan_balance, total_addons, given_date, current_lo, current_co, total_addons,current_branch, status, final_due_date");
while ($l = mysqli_fetch_array($loans)) {
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

    if ($current_co > 0) {
        //---Add this user to an array to loop through them later
        $available_users_array[$current_co] = $branch_users_array[$current_co];
        $user_branch_array[$current_co] = $branch;

    } else {
        ///--Else, check if LO is present
        if ($current_lo > 0) {
            $available_users_array[$current_lo] = $branch_users_array[$current_lo];
            $user_branch_array[$current_lo] = $branch;
        }
    }

    ///----Get the PAIR
    if($current_lo > 0 && $current_co > 0){
        $current_pair_array[$current_lo] = $current_co;
    }



    $name = $customer_names[$customer_id];
    $branch_name = $branches[$branch];
   // $interest = $loan_amount * 0.24;
    $interest = $interest_addons[$uid];
    $other_addons = $total_addons - $interest_addons[$uid];
    $principle_interest_paid = $total_repaid - $other_addons;
    $interest_principal = $loan_amount + $interest;
    $days_ago = datediff3($given_date, $date);
    $membership_fees = $membership_addons[$uid];
    $full_length = datediff3($given_date, $final_due_date);
    if ($days_ago > $full_length) {
        $days_ago = $full_length;
    }
    $full_length = 30;
    $daily_expected = round(($days_ago / $full_length) * $interest_principal);
    $deficit = $daily_expected - $principle_interest_paid;


    $co_principal_array = obj_add($co_principal_array, $current_co, $loan_amount);
    $co_interest_array = obj_add($co_interest_array, $current_co, $interest);
    $co_other_charges_array = obj_add($co_other_charges_array, $current_co, $other_addons);
    $co_expected_today_array = obj_add($co_expected_today_array, $current_co, $daily_expected);
    $co_deficit_array = obj_add($co_deficit_array, $current_co, $deficit);
    $co_paid_today_array = obj_add($co_paid_today_array, $current_co, $principle_interest_paid);


}


echo "<table class='table table-bordered table-striped' id='example2'>";
echo "<thead><tr><th>Agent</th><th>Branch</th><th>Arrears</th></thead>";
echo "<tbody>";

foreach ($available_users_array as $bid => $bname) {
    $bprinncipal = $co_principal_array[$bid];
    $binterest = $co_interest_array[$bid];
    $bother_charges = $co_other_charges_array[$bid];
    $btotal_PI = $bprinncipal + $binterest;
    $barrears_expected = $co_expected_today_array[$bid];
    $barrears_paid = $co_paid_today_array[$bid];
    $barrears_balance = false_zero($barrears_expected - $barrears_paid);

    $pair = $current_pair_array[$bid];
    if($pair > 0){
        $pair_name = " <i class='fa fa-chain'/> ". $staff_names[$pair];
    }
    else{
        ///----Check from values
        $pair = array_search($bid, $current_pair_array);
        if($pair > 0){
            $pair_name = " <i class='fa fa-chain'/> ". $staff_names[$pair];
        }
    }

    $branchid = $user_branch_array[$bid];
    $branchname = $branches_array[$branchid];

    $crate = round(($barrears_paid / ($barrears_expected)) * 100, 2);
    $bm = $branch_managers_array[$branchid];


    echo "<tr><td class='font-italic'>$bname $pair_name <a href=\"reports?hreport=jc-daily-instalments-bdo.php&from=$start_date&to=$end_date&bdo=$bid\"><i class='fa fa-external-link-square'></i></a></td><td>$branchname</td><td class='text-bold'>" . number_format($barrears_balance) . "</td></tr>";

    $bprinncipal_ += $bprinncipal;
    $binterest_ += $binterest;
    $bother_charges_ += $bother_charges;
    $btotal_PI_ += $btotal_PI;
    $barrears_expected_ += $barrears_expected;
    $barrears_paid_ += $barrears_paid;
    $barrears_balance_ += $barrears_balance;


}
$crate_ = round(($barrears_paid_ / ($barrears_expected_)) * 100, 2);

echo "</tbody>";
echo "<tfoot><tr><th>--</th><th>Total</th><th>".money($barrears_balance_)."</th></tfoot>";

echo "</tfoot>";
echo "</table>";












