<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");
?>
<?php
$userd = session_details();

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

$starting_date = first_date_of_month($start_date);
$ending_date = last_date_of_month($starting_date);

// Create DateTime objects
$start = new DateTime($start_date);
$end = new DateTime($end_date);

// Calculate the difference in months
$diff = $start->diff($end);
$months = ($diff->y * 12) + $diff->m;  // Total months difference
if ($diff->d > 0) {
    $months++;  // Add one more month if there's a partial month
}

$loan_branches = branch_permissions($userd, 'o_loans');
$branches_id = branch_permissions($userd, 'o_branches');

$branch_disbursements = array();
$branch_targets = array();

$branch_disb_targets = table_to_obj('o_targets', "target_type='DISBURSEMENTS' AND target_group='BRANCH' AND status=1 AND starting_date='$starting_date' AND ending_date='$ending_date'", "1000", "group_id", "amount", "uid", "asc");
$branch_names = table_to_obj('o_branches', "uid > 0 $branches_id AND status=1", "10000", "uid", "name");

$loans = fetchtable('o_loans', "given_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1 AND status!=0 $loan_branches", "uid", "asc", 1000000000, "uid, loan_amount, current_branch");
while ($l = mysqli_fetch_array($loans)) {
    $loan_amount = $l['loan_amount'];
    $branch_id = $l['current_branch'];
    $branch_disbursements = obj_add($branch_disbursements, $branch_id, $loan_amount);
}
?>

<table class="tablex">
    <thead>
        <tr>
            <th>Branch</th>
            <th>Monthly Target</th>
            <th>MTD Target</th>
            <th>Progress</th>
            <th>Deficit</th>
            <th class="pull-right">Rate %</th>
        </tr>
    </thead>
    <tbody>
        <?php

       // echo mtd_target2(1000);
       // die();
        foreach ($branch_names as $bid => $bname) {
            // Calculate monthly target, considering the date range
            $monthly_target = $branch_disb_targets[$bid];

            // Calculate progress, MTD target, and deficit
            $progress = $branch_disbursements[$bid];
            $mtd_target = mtd_target2($monthly_target);
            if($mtd_target > $monthly_target){
                $mtd_target = $monthly_target;
            }
            $deficit = false_zero($mtd_target - $progress);
            $rate = round(((false_zero($progress) / false_zero($mtd_target)) * 100), 2);

            // Total calculations for footer
            $monthly_target_t += $monthly_target;
            $mtd_target_t += $mtd_target;
            $progress_t += $progress;
            $deficit_t += $deficit;

            echo "<tr>
                    <td>$bname</td>
                    <td>".money($monthly_target)."</td>
                    <td>".money($mtd_target)."</td>
                    <td>".money($progress)."</td>
                    <td>".money($deficit)."</td>
                    <td><span class=\"label bg-gray disabled pull-right text-black font-16 font-bold\">$rate%</span></td>
                  </tr>";
        }
        $rate_a = roundDown((($progress_t / $mtd_target_t) * 100), 2);
        ?>
    </tbody>
    <tfoot class="bg-gray font-bold">
        <tr>
            <td>Total</td>
            <td><?php echo money($monthly_target_t); ?></td>
            <td><?php echo money($mtd_target_t); ?></td>
            <td><?php echo money($progress_t); ?></td>
            <td><?php echo money($deficit_t); ?></td>
            <td><span class="label bg-purple-gradient pull-right font-16 font-bold"><?php echo $rate_a; ?>%</span></td>
        </tr>
    </tfoot>
</table>

<div class="container-fluid">
    <div style="position: relative; width: 100%; max-width: 600px; margin-top: 10px;">
        <div style="position: absolute; right: 100px; top: 0px; font-weight: bold; font-size: 14px; color: #1574ac; text-shadow: 1px 1px 2px rgb(255 255 255 / 30%);">
            <?php echo "$rate_a %"; ?>
        </div>
        <div style="background-color: #e0e0e0; height: 20px; border-radius: 10px; overflow: hidden;">
            <div style="width: <?php echo $rate_a; ?>%; background-color: #2cacff; height: 100%; border-radius: 10px 0 0 10px; transition: width 0.5s;"></div>
        </div>
    </div>
</div>
