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

$branch_repaid_array = array();
$branch_deficit_array = array();


echo "<table class='tablex'>";
//echo "<thead><tr><th>UID</th><th>Name</th><th>Phone</th><th>Branch</th><th>Principal</th><th>Interest</th><th>Total Repayable</th><th>Given Date</th><th>Days Passed</th><th>Daily Expected</th><th>Paid</th><th class='font-bold text-purple'>Deficit</th><th>Agent</th></tr></thead>";
?>
<tbody>
<?php
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
    //$interest = $loan_amount * 0.24;
    $interest = $interest_addons[$uid];
    $other_addons = $total_addons - $interest_addons[$uid];
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
    $loan_amount_total+=$loan_amount;
    $interest_total+=$interest;
    $interest_principal_total+=$interest_principal;
    $daily_expected_total+=$daily_expected;
    $paid_total+=$principle_interest_paid;
    $deficit_total+=$deficit;
    $membership_total+=$membership_fees;

    $branch_deficit_array = obj_add($branch_deficit_array, $branch, $deficit);


   // echo "<tr><td>$uid</td><td>$name</td><td>$account_number</td><td>$branch_name</td><td>".money($loan_amount)."</td><td>".money($interest)."</td><td>".money($interest_principal)."</td><td>$given_date</td><td>$days_ago</td><td>".money($daily_expected)."</td><td>".money($principle_interest_paid)."</td><td class='font-bold text-purple'>".money($deficit)."</td><td>$lo_co</td></tr>";

}
echo "<thead><tr><th>UID</th><th>Branch</th><th>Arrears</th></thead>";


foreach ($branches as $bid => $bname){
    $barrears = $branch_deficit_array[$bid];
    $barrears_total+=false_zero($barrears);
    $bid_enc = encurl($bid);
    $link = "<a href=\"reports?hreport=jc-daily-instalments.php&from=$start_date&to=$end_date&branch=$bid_enc\"><i class='fa fa-external-link-square'></i></a>";
    echo "<thead><tr><td>$bid</td><td>$bname $link</td><td>".money(false_zero($barrears))."</td></thead>";


}

echo "<tfoot><tr><th>--</th><th>Total</th><th>".money($barrears_total)."</th></tfoot>";

?>
</tbody>
<?php
//echo "<tfoot><tr><th>--</th><th>--</th><th>--</th><th>--</th><th>".money($loan_amount_total)."</th><th>".money($interest_total)."</th><th>".money($interest_principal_total)."</th><th>--</th><th>--</th><th>".money($daily_expected_total)."</th><th>".money($paid_total)."</th><th>".money($deficit_total)."</th><th>--</th></tr></tfoot>";
?>
</table>


<?php


?>


