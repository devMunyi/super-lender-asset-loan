<?php

session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$branches_array = array();
$branch_disb_targets = array();
$this_year = date('Y');
$this_month = date('m');


$userd = session_details();
$userbranch = $userd['branch'];
$view_summary = permission($userd['uid'],'o_summaries',"0","read_");
$inarchive_ = $_SESSION['archives'] ?? 0;
if($view_summary == 1 || $allow_bdos == 1 || $inarchive_ == 1) {
    $andbranch_loans = "";
    $andbranch = "";
}
else{
    $andbranch_loans = "AND current_branch = $userbranch";
    $andbranch = "AND uid = $userbranch";

     //////-----Check users who view multiple branches
     $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
     if (sizeof($staff_branches) > 0) {
         ///------Staff has been set to view multiple branches
         array_push($staff_branches, $userd['branch']);
         $staff_branches_list = implode(",", $staff_branches);

         $andbranch_loans = "AND current_branch IN ($staff_branches_list)";
         $andbranch = "AND uid IN ($staff_branches_list)";
     }
}

$branch_disb_targets = table_to_obj('o_targets', "target_type='DISBURSEMENTS' AND target_group='BRANCH' AND status=1", "1000", "group_id", "amount", "uid", "asc");
//  $branch_disb_days = table_to_obj('o_targets',"target_type='DISBURSEMENTS' AND target_group='BRANCH' AND status=1","1000","group_id","working_days");

$branches_list = fetchtable('o_branches', "status=1 $andbranch", "name", "asc", "1000", "uid, name");
while ($br = mysqli_fetch_array($branches_list)) {
    $bid = $br['uid'];
    $name = $br['name'];
    $branches_array[$bid] = $name;
}

$loans = fetchtable('o_loans', "disbursed=1 AND given_date >= '$this_year-$this_month-01' $andbranch_loans AND status!=0", "uid", "asc", "5000000", "uid, customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, final_due_date, current_agent, current_branch, status");
$branch_totals_array = array();
while ($dp = mysqli_fetch_array($loans)) {
    $current_branch = $dp['current_branch'];
    $given_date = $dp['given_date'];
    $given_date_array = explode('-', $given_date);

    if ($this_year == $given_date_array[0] && $this_month == $given_date_array[1]) {
        $loan_amount = $dp['loan_amount'];
        if ($branch_totals_array[$current_branch] > 0) {
            $branch_totals_array[$current_branch] = $loan_amount + $branch_totals_array[$current_branch];
        } else {
            $branch_totals_array[$current_branch] = $loan_amount;
        }
    }
}

?>
<table class="table table-striped table-condensed">
    <tr>
        <th>Branch</th>
        <th>Monthly Target</th>
        <th>MTD Target</th>
        <th>Actual</th>
        <th>Deficit</th>
        <th>Rate</th>
    </tr>
    <?php

    // $branch_totals_arra is empty creaing an equivalent setting totals to 0
    
   /// check if there is a branch with no disbursed amount if so set it to 0
    foreach ($branches_array as $bid => $branch_name) {
        if (!isset($branch_totals_array[$bid])) {
            $branch_totals_array[$bid] = 0;
        }
    }

    foreach ($branch_totals_array as $bra => $branch_total) {
            $monthly_target = $branch_disb_targets[$bra];
            $mtd_target = mtd_target($monthly_target);
            $deficit = false_zero($mtd_target - $branch_total);
            $branch_name = $branches_array[$bra];
            if (input_available($branch_name) == 0) {
                $branch_name = 'Unspecified';
            }
            $total_target = $total_target + $monthly_target;
            $total_mtd_target = $total_mtd_target + $mtd_target;
            $total_disbursed = $total_disbursed + $branch_total;
            $rate = false_zero(ceil((($branch_total / $mtd_target) * 100)));
            echo "<tr><td>" . $branch_name . "</td><td>" . money($monthly_target) . "</td><td>" . money($mtd_target) . "</td><td>" . money($branch_total) . "</td><td>" . money($deficit) . "</td><td><span class=\"font-16 font-bold text-black\">$rate%</span></td></tr>";
    }
    $total_deficit =  $total_target - $total_disbursed;
    $average_rate = false_zero(ceil((($total_disbursed / $total_mtd_target) * 100)));
    ?>

    <tr class="font-16 text-blue">
        <th>Total.</th>
        <th><?php echo money($total_target) ?></th>
        <th><?php echo money($total_mtd_target); ?></th>
        <th><?php echo money($total_disbursed) ?></th>
        <th><?php echo money($total_deficit); ?></th>
        <th><span class="font-18 font-bold text-blue">~<?php echo $average_rate; ?>%</span></th>
    </tr>
</table>