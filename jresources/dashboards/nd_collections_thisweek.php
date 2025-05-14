<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$start_date = getFirstDayOfMonth($date);
$end_date = "$date";
$this_week_start = getFirstDayOfWeek($date);
$last_week = datesub($date, 0,0,7);
$last_week_start = getFirstDayOfWeek($last_week);

$total_repayable_today = 0;
$total_repayable_today_number = 0;
$principal_repayable_today = 0;
$interest_repayable_today = 0;
$other_charges_repayable_today = 0;
$overdue_repayable_today = 0;
$overdue_repayable_today_number = 0;
$total_repaid_today = 0;
$total_balance_today = 0;

$loan_q = "final_due_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1 AND status!=0";
$loans_array = table_to_array('o_loans',"$loan_q","1000000000","uid");

$addons = loan_addons_array($loans_array);
$interest_addons = $addons[0];
$other_charges_addons = $addons[1];

$payments = fetchtable('o_incoming_payments',"payment_date BETWEEN '$start_date' AND '$end_date' AND status=","uid","asc","10000000","uid, loan_id, loan_balance, payment_date, amount,branch_id, group_id");
while($p = mysqli_fetch_array($payments)){
    $uid = $p['uid'];
    $loan_id = $p['loan_id'];
    $loan_balance = $p['loan_balance'];
    $amount = $p['amount'];
    $branch_id = $p['branch_id'];
    $group_id = $p['group_id'];

}


$loans_list = implode(',', $loans_array);
$loans = fetchtable('o_loans',"uid in ($loans_list)","uid","asc","1000000000","uid, total_repayable_amount, given_date, final_due_date, loan_amount, status, total_repaid, loan_balance");
while($l = mysqli_fetch_array($loans)){
    $luid = $l['uid'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $total_repaid = $l['total_repaid'];
    $total_balance = $l['loan_balance'];
    $loan_amount = $l['loan_amount'];
    $loan_status = $l['status'];


    if((isDateInRange($final_due_date, $this_week_start, $date))){
        $total_repayable_today+=$total_repayable_amount;
        $total_repayable_today_number+=1;
        $principal_repayable_today+=$loan_amount;
        $interest_repayable_today+=$interest_addons[$luid];
        $total_repaid_today+=$total_repaid;
        $total_balance_today+=$total_balance;
        $other_charges_repayable_today+=$other_charges_addons[$luid];

    }

    elseif ((isDateInRange($final_due_date, $last_week_start, $last_week)) || ($loan_status == 7)){
        ///----Overdue yesterday
        $overdue_repayable_today+=$total_repayable_amount;
        $overdue_repayable_today_number+=1;
        $total_repayable_yest+=$total_repayable_amount;
        $total_repaid_yest+=$total_repaid;
    }

}

//---Today
$pi_repayable_today = $principal_repayable_today + $interest_repayable_today;
$total_repaid_today_rate = round(false_zero(($total_repaid_today/$total_repayable_today)*100), 2);
$total_repaid_yest_rate = round(false_zero(($total_repaid_yest/$total_repayable_yest)*100), 2);

if($total_repaid_today_rate >= 90){
    $today_analysis = "<span class='text-green'><i class=\"fa fa-thumbs-up\"></i> Collection rate is Okey</span>";
}
elseif ($total_repaid_today_rate < 90){
    $today_analysis = "<span class='text-red'><i class=\"fa fa-warning\"></i> Collection rate is low</span>";
}

$pi_repaid_today = false_zero($total_repaid_today - $other_charges_repayable_today);
$principle_repaid_today = false_zero($pi_repaid_today - $interest_repayable_today);
$interest_repaid_today = false_zero($pi_repaid_today - $principle_repaid_today);
$charges_repaid_today = false_zero($total_repaid_today - $pi_repaid_today);

$principle_balance_today = $principal_repayable_today - $principle_repaid_today;
$interest_balance_today = $interest_repayable_today - $interest_repaid_today;
$pi_balance_today = $principle_balance_today + $interest_balance_today;
$charges_balance_today = $other_charges_repayable_today - $charges_repaid_today;

$principle_rate_today = round(false_zero(($principle_repaid_today/$principal_repayable_today)*100), 2);
$interest_rate_today = round(false_zero(($interest_repaid_today/$principal_repayable_today)*100), 2);
$pi_rate_today = round(false_zero(($pi_repaid_today/$pi_repayable_today)*100), 2);
$charges_rate_today = round(false_zero(($charges_repaid_today/$other_charges_repayable_today)*100), 2);


?>
<table class="table font-16 table-hover table-bordered">
    <thead>
    <tr><th>Item</th><th>Total</th><th>Paid</th><th>Balance</th><th>Rate</th></tr>
    </thead>
    <tbody>
    <tr><td>Total Repayable</td><td><?php echo number_format($total_repayable_today); ?> <span class="label bg-info text-black"><?php echo $total_repayable_today_number; ?> Loans</span></td><td><?php echo number_format($total_repaid_today); ?></td><td><?php echo number_format($total_balance_today); ?></td><td><?php echo $total_repaid_today_rate; ?>%</td></tr>
    <tr><td>Principle</td><td><?php echo number_format($principal_repayable_today); ?></td><td><?php echo number_format($principle_repaid_today); ?> </td><td><?php echo number_format($principle_balance_today); ?></td><td><?php echo $principle_rate_today; ?>%</td></tr>

    <tr><td>Interest</td><td><?php echo number_format($interest_repayable_today); ?> </td><td><?php echo number_format($interest_repaid_today); ?></td><td><?php echo number_format($interest_balance_today); ?></td><td><?php echo number_format($interest_rate_today); ?>%</td></tr>
    <tr><td>Principal+Interest</td><td><?php echo number_format($pi_repayable_today); ?> </td><td><?php echo number_format($pi_repaid_today); ?></td><td><?php echo number_format($pi_balance_today); ?></td><td><?php echo number_format($pi_rate_today); ?>%</td></tr>
    <tr><td>Other Charges</td><td><?php echo number_format($other_charges_repayable_today); ?></td><td><?php echo number_format($charges_repaid_today); ?></td><td><?php echo number_format($charges_balance_today); ?></td><td><?php echo number_format($charges_rate_today); ?>%</td></tr>
    <tr class="text-red font-bold"><td>Overdue</td><td colspan="3"> <?php echo number_format($overdue_repayable_today); ?> <span class="label bg-danger
                            text-black"><?php echo $overdue_repayable_today_number;  ?> Loans</span> <i class="font-400">(Due Last Week)</i></td></tr>



    </tbody>
</table>
<hr/>
<div class="font-bold font-16"><?php echo $today_analysis; ?></div>