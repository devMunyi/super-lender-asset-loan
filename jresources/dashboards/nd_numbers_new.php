<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$today_date = $date;
$this_month_1st = first_date_of_month($today_date);
$this_month_last = last_date_of_month($today_date);
$last_month_today = datesub($today_date, 0, 1, 0);
$last_month_1st = first_date_of_month($last_month_today);
$last_month_last = last_date_of_month($last_month_today);

$last_month_name = date("M-Y", strtotime($last_month_1st));
$this_month_name = date("M-Y", strtotime($today_date));




$active_customers_array = array();

$userd = session_details();
$loan_branches = branch_permissions($userd, 'o_loans');
$branches_list = branch_permissions($userd, 'o_branches');
$pay_branches = branch_permissions($userd, 'o_incoming_payments');
$customer_branches = $user_branches = branch_permissions($userd, 'o_customers');
//echo $start_date.','.$end_date;
$disbursed_today = $disbursed_this_month = $disbursed_last_month = $due_today = $due_tomorrow = $due_in_3_days = $due_in_7_days =
    $last_month_pi = $last_month_pi_paid = $last_month_pi_cr = $total_expected_this_month_pi =0;



$loan_q = "status in(3,4,5,7) AND given_date >= '$last_month_1st' AND given_date <= '$this_month_last' AND disbursed=1 $loan_branches";
$all_loans = fetchtable('o_loans',"$loan_q","uid","asc","100000","uid, given_date, final_due_date, loan_amount, other_info, total_addons, total_deductions,total_repaid, total_repayable_amount, loan_balance, status");
while($l = mysqli_fetch_array($all_loans)){
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $loan_amount = $l['loan_amount'];
    $total_addons = $l['total_addons'];
    $total_deductions = $l['total_deductions'];
    $loan_balance = $l['loan_balance'];
    $status = $l['status'];

    $total_loan_days = false_zero(datediff3($final_due_date, $given_date));
    $days_passed = false_zero(datediff3($today_date, $given_date));

    $sec = $l['other_info'];
    $sec_obj = (json_decode($sec, true));
    $interest = $sec_obj['INTEREST_AMOUNT'];

    $total_repayable = $l['total_repayable_amount'];
    $total_repaid = $l['total_repaid'] + $total_deductions;

    $other_charges = $total_addons - $interest;
    $total_repayable_pi = $loan_amount + $interest;  ////---Making this the total repayable amount
    $total_repaid_pi = ($total_repaid - $other_charges); ////----Total repaid, we remove other charges paid

    $pi_repaid = false_zero($total_repaid_pi - $other_charges);
    $principal_paid = false_zero($pi_repaid - $interest);
    $interest_paid = false_zero($pi_repaid - $principal_paid);
    $other_charges_paid = false_zero($total_repaid - $pi_repaid);

    ///----Calculation
    if($given_date == $today_date){
        $disbursed_today+=$loan_amount;
    }
    ///-----This month disbursed
    if(isDateInRange($given_date, $this_month_1st, $today_date)){
       $disbursed_this_month+=$loan_amount;
        $this_month_total_pay+=$total_repaid;
        $total_expected_this_month_pi+=$total_repayable_pi;
        $total_collected_this_month_pi+=$pi_repaid;
        $total_collected_this_month_all+=$total_repaid;

        $expected_today = ($days_passed / $total_loan_days)*$total_repayable_pi;
        $expected_tomorrow = ($days_passed+1 / $total_loan_days)*$total_repayable_pi;
        $expected_3days = ($days_passed+2 / $total_loan_days)*$total_repayable_pi;
        $daily_expected = round(($total_repayable_pi / $total_loan_days),2);
        //----Instalments
        if($total_repaid < $expected_today){
            $instalment_balance = false_zero($expected_today - $total_repaid);
            $instalments_due_today+=$instalment_balance;
        }
        if($total_repaid < $expected_tomorrow){
            $instalment_balance = false_zero($expected_tomorrow - $total_repaid);
            $instalments_due_tomorrow+=$instalment_balance;
        }
        if($total_repaid < $expected_3days){
            $instalment_balance = false_zero($expected_3days - $total_repaid);
            $instalments_due_3days+=$instalment_balance;
        }

    }
    //-----Last month disbursed
    if(isDateInRange($given_date, $last_month_1st, $last_month_last)){
        $disbursed_last_month+=$loan_amount;
        $last_month_pi+=$total_repayable_pi;
        $last_month_pi_paid+=$total_repaid_pi;
    }
    if(isDateInRange($given_date, $last_month_1st, $date)){
        if($status == 7){
            $defaults_last_month+=$loan_balance;
        }
    }

    ///---Due today
    if($final_due_date == $today_date){
        $due_today+=$loan_balance;
    }
    ////--Due tomorrow *
    if(datesub($final_due_date,0,0, 1) == $today_date){
        $due_tomorrow+=$loan_balance;
    }
    ////--Due in 3 days *
    if(isDateInRange($final_due_date, $today_date, dateadd($today_date, 0,0, 3))){
        $due_in_3_days+=$loan_balance;
    }
    ////----Due in 7 days *
    if(isDateInRange($final_due_date, $today_date, dateadd($today_date, 0,0, 7))){
        $due_in_7_days+=$loan_balance;
    }


//    echo "$uid, $given_date, $final_due_date, $loan_amount <br/>";
}
//------Other queries
$defaults_all_time = totaltable('o_loans',"status=7 $loan_branches","loan_balance");
$total_active_loans = totaltable('o_loans',"status=3 $loan_branches","loan_balance");

////-----Sumarries
$last_month_pi_cr = false_zero(truncateToTwoDecimals((($last_month_pi_paid/$last_month_pi)*100)));
$total_cr_this_month_pi = false_zero(truncateToTwoDecimals((($total_collected_this_month_pi/$total_expected_this_month_pi)*100)));


?>

<table class="table">

    <tr class="text-center">
        <td colspan="2" class="bg-purple border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($disbursed_today) ?></div>
            Disbursed Today
        </td>
        <td class="bg-purple border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($disbursed_this_month) ?></div>
            Disbursed This Month
        </td>
        <td class="bg-purple border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($disbursed_last_month) ?></div>
            Disbursed Last Month
        </td>


        <td class="bg-blue border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($due_today) ?></div>
            Due Today
        </td>
        <td class="bg-blue border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($due_tomorrow) ?></div>
            Due Tomorrow
        </td>
        <td class="bg-blue border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($due_in_3_days) ?></div>
            Due 3 Days
        </td>
        <td class="bg-blue border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($due_in_7_days) ?></div>
            Due 7 Days
        </td>


    </tr>
    <tr class="text-center">
        <td colspan="2" class="bg-black border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($last_month_pi) ?></div>
            Total Expected(P+I), <?php echo $last_month_name ?>
        </td>
        <td colspan="2" class="bg-black border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($last_month_pi_paid) ?></div>
            Total Collected(P+I), <?php echo $last_month_name ?>
        </td>
        <td class="bg-black border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo $last_month_pi_cr; ?>%</div>
            Collection Rate(P+I), <?php echo $last_month_name ?>
        </td>
        <td class="bg-black border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($this_month_total_pay) ?></div>
            Partial Payments(Full), <?php echo $this_month_name ?>
        </td>



        <td colspan="2" class="bg-green border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($total_expected_this_month_pi); ?></div>
            Total Expected (<?php echo $this_month_name; ?>)
        </td>


    </tr>
    <tr class="text-center">
        <td colspan="2" class="bg-green border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($total_collected_this_month_pi); ?></div>
            Total Collected P+I (<?php echo $this_month_name; ?>)
        </td>
        <td class="bg-green border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo $total_cr_this_month_pi;  ?>%</div>
            Collection Rate
        </td>
        <td colspan="2" class="bg-green border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($total_collected_this_month_all); ?></div>
            Total Collected P+I+C (<?php echo $this_month_name; ?>)
        </td>

        <td class="bg-red border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($total_active_loans); ?></div>
            Total Active Loans <a class="fa fa-info" title="Loans disbursed but not overdue"></a>
        </td>
        <td class="bg-red border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($total_active_loans_pi); ?></div>
            Total Active Loans (P+I)
        </td>
        <td colspan="2" class="bg-red border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo $total_new_customers_last_month;?></div>
            New Customers, (<?php echo $last_month_name; ?>)
        </td>

    </tr>
    <tr>
        <td class="bg-red border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo $total_new_customers_this_month;?></div>
            New Customers, (<?php echo $this_month_name;?>)
        </td>

        <td class="bg-red border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo $total_active_customers_all;?></div>
            Total Active Customers
        </td>




        <td class="bg-gray-active border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($defaults_last_month); ?></div>
            Defaults (<?php echo $last_month_name ?> todate)
        </td>
        <td class="bg-gray-active border-right">
            <div class="font-18 font-bold font-24 font-bold">--</div>
            Defaults Collected (<?php echo $last_month_name ?> todate)
        </td>
        <td class="bg-gray-active border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($defaults_all_time); ?></div>
            Total Defaults, (All Time)
        </td>
        <td class="bg-orange-active border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($instalments_due_today); ?></div>
            Instalments Due Today
        </td>
        <td class="bg-orange-active border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($instalments_due_tomorrow); ?></div>
            Instalments Due Tomorrow
        </td>
        <td class="bg-orange-active border-right">
            <div class="font-18 font-bold font-24 font-bold"><?php echo number_format($instalments_due_3days); ?></div>
            Instalments Due in 3 Days
        </td>



    </tr>





</table>