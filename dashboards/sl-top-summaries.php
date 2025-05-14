<?php

$loan_branches = branch_permissions($userd, 'o_loans');
$payment_branches = branch_permissions($userd, 'o_incoming_payments');


$balances = table_to_obj('o_summaries',"uid > 0 AND status=1","100","name","value_");
$summaries = table_to_obj('o_mpesa_configs',"uid > 0 AND status=1","100","property_name","property_value");
$disb_ = totaltable('o_loans',"disbursed=1 AND status!=0 AND given_date = '$date' $loan_branches","loan_amount");
$coll_ = totaltable('o_incoming_payments',"status=1 AND payment_date='$date' $payment_branches","amount");

$view_summary = permission($userd['uid'], 'o_summaries', "0", "read_");

$sms_settings = table_to_obj('o_sms_settings',"uid > 0 AND status=1","100","property_name","property_value");

//******NPL Calculator
$months_ago_3 = datesub($date, 0, 3, 0);
$months_ago_4 = datesub($date, 0, 12, 0);
$date_ago_30 = datesub($date, 0, 1, 0);
$first_month_day = first_date_of_month($date);
////---Overdue for more than 3/4 months
//$overdue_3 = totaltable('o_loans',"uid > 0 AND final_due_date <= '$months_ago_3' AND disbursed=1 AND paid=0 AND status!=0 $loan_branches","loan_balance");
$dq = "uid > 0 AND final_due_date = '$date'  AND disbursed=1 AND status!=0 $loan_branches";
$due_today = totaltable('o_loans',"$dq","loan_balance");
$due_mtd = totaltable('o_loans',"uid > 0 AND final_due_date BETWEEN '$first_month_day' AND '$date'  AND disbursed=1 AND status!=0 $loan_branches","loan_balance");

$loans = fetchtable('o_loans',"uid > 0 AND final_due_date BETWEEN '$first_month_day' AND '$date'  AND disbursed=1 AND status!=0 $loan_branches","uid","asc",1000000000,"uid, loan_amount, total_repayable_amount,total_repaid, loan_balance, other_info, total_addons");
while($l = mysqli_fetch_array($loans)){

    //$total_repayable_amount = $l['total_repayable_amount'];  ////We are making the total repayable interest and principal only
    $sec = $l['other_info'];
    $sec_obj = (json_decode($sec, true));
    $interest = $sec_obj['INTEREST_AMOUNT'];

    $loan_amount = $l['loan_amount'];
    // $total_repaid = $l['total_repaid']; /////------We are deducting other charges from total repaid
    $loan_balance = $l['loan_balance'];
    $total_addons = $l['total_addons'];

    $total_repaid_ = $l['total_repaid'];
    $other_charges = $total_addons - $interest;
    $tt_adds+=$total_addons;
    $tt_repaid+=$l['total_repaid'];
    $tt_interest+=$interest;
    $tt_other_charges+=$other_charges;

    $total_repayable_amount = $loan_amount + $interest;  ////---Making this the total repayable amount
    $total_repaid = false_zero($total_repaid_ - $other_charges); ////----Total repaid, we remove other charges paid
    $tt_repaid_pi+=$total_repaid;
    $tt_repayable_pi+=$total_repayable_amount;

}
//echo "$tt_repaid_pi, $tt_repayable_pi";

$cr_ = roundDown((($tt_repaid_pi/$tt_repayable_pi)*100), 2);
//$overdue_count = countotal('o_loans',"$dq","uid");
//$overdue_4 = totaltable('o_loans',"uid> 0 AND final_due_date <= '$months_ago_4' AND disbursed=1 AND paid=0 AND status!=0 $loan_branches","loan_balance");
//$all_time = totaltable('o_loans',"uid> 0 AND disbursed=1 AND paid=0 AND status!=0 $loan_branches","loan_balance");
/*
$npl_3 = false_zero(round(($overdue_3/$all_time)*100, 2));
$npl_4 = false_zero(round(($overdue_4/$all_time)*100, 2));
if($npl_3 > $npl_4){
    $npl_diff = round((($npl_3 - $npl_4)/$npl_3)*100,2);
   $npl_trend = "<span class='text-red font-bold'><i class='fa fa-sort-up'></i> $npl_diff</span>";
}
else if($npl_3 < $npl_4){
    $npl = 0;
    $npl_diff = round((($npl_4 - $npl_3)/$npl_4)*100,2);
    $npl_trend = "<span class='text-green font-bold'><i class='fa fa-sort-down'></i> $npl_diff</span>";
} */
//******End of NPL Calculator

?>


<div class="row">
    <div class="col-sm-3">
        <span class="font-18 text text-purple"><i class="fa fa-money"></i> MAIN KPIs</span>
        <table class="table table-bordered font-18 box bg-gray-light bg-blue-gradient">
            <tr><td class="font-16"> Loans Today</td><td class="font-bold font-18"><?php echo number_format($disb_, 2); ?> <a class="text-gray" href="loans?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>">  <i class="fa fa-external-link-square"></i> </a></td></tr>
            <tr><td class="font-16"> Payments Today </td><td class="font-bold font-18"><?php echo number_format($coll_, 2); ?> <a class="text-gray" href="incoming-payments?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>"> <i class="fa fa-external-link-square"></i></a></td></tr>
        </table>
    </div>
    <div class="col-sm-3">
        <span class="font-18 text text-red"><i class="fa fa-warning"></i> Due</span>
        <table class="table table-bordered font-18 box bg-gray-light bg-red-gradient">
            <tr><td class="font-16"> Today.</td><td class="font-bold font-18"><?php echo number_format($due_today, 2); ?>  <a class="text-gray" href="falling-due?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>"> <i class="fa fa-external-link-square"></i> </a></td></tr>

            <tr><td class="font-16"> MTD.</td><td class="font-bold font-18"> <?php echo number_format($due_mtd, 2); ?><a class="text-gray" href="falling-due?start_date=<?php echo $first_month_day; ?>&end_date=<?php echo $date; ?>">  <i class="fa fa-external-link-square"></i> </a></td></tr>
        </table>
    </div>
    <?php
    if($view_summary == 1){

    ?>
    <div class="col-sm-2">
        <span class="font-18 text text-orange"><i class="fa fa-mobile-phone"></i> UTILITIES</span>
        <table class="table table-bordered font-18 box bg-gray-light bg-green-gradient">
            <tr><td class="font-16">Paybill/Till:<?php echo $summaries['c2b_shortcode']; ?></td><td class="font-bold font-18"><?php echo number_format($balances['PAYBILL_BALANCE'], 0); ?></td></tr>
            <tr><td class="font-16">B2C:<?php echo $summaries['b2c_shortcode']; ?></td><td class="font-bold font-18"><?php echo number_format($balances['UTILITY_BALANCE'], 0); ?></td></tr>
        </table>
    </div>
        <?php
    }
    ?>
    <div class="col-sm-2">
        <span class="font-18 text text-blue"><i class="fa fa-comment-o"></i> SMS</span>
        <table class="table table-bordered font-18 box bg-gray-light bg-purple-gradient">
            <tr><td class="font-16"><i class="fa fa-external-link-square"></i> Bulk:<?php echo $summaries['BULK_CODE']; ?></td><td class="font-bold font-18"><?php echo number_format($balances['SMS_BALANCE']); ?></td></tr>
            <tr><td class="font-16"><i class="fa fa-exchange"></i> VOICE:</td><td class="font-bold font-18"><?php echo number_format($balances['VOICE_BALANCE']); ?></td></tr>


        </table>
    </div>

    <div class="col-sm-2">
        <span class="font-18 text text-purple"><i class="fa fa-warning"></i> Collection Rate (MTD)</span>
        <table class="table table-bordered font-18 box bg-gray-light bg-gray">
            <tr><td><span class="font-bold" style="font-size: 2.7em;"><?php echo $cr_; ?>%</span> </td></tr>
        </table>
    </div>
</div>