<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];


$userd = session_details();

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

// Create DateTime objects
$start = new DateTime($start_date);
$end = new DateTime($end_date);

// Calculate the difference
$diff = $start->diff($end);
// Get the total number of months
$months = ($diff->y * 12) + $diff->m;

$loan_branches = branch_permissions($userd, 'o_loans');
$branches_id = branch_permissions($userd, 'o_branches');

$daily_disbursements = array();
$daily_repayable_array = array();
$daily_repaid_array = array();
$branch_targets = array();
$daily_collections = array();

$branch_disb_targets = table_to_obj('o_targets', "target_type='DISBURSEMENTS' AND target_group='BRANCH' AND status=1", "1000", "group_id", "amount");
$branch_names = table_to_obj('o_branches',"uid > 0 $branches_id AND uid != 1 AND status=1","10000","uid","name");


$loans = fetchtable('o_loans',"given_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1 AND status!=0 $loan_branches","uid","asc",1000000000,"uid, loan_amount, current_branch, given_date, other_info, total_addons, total_deductions, total_repaid");
while($l = mysqli_fetch_array($loans)){

    $sec = $l['other_info'];
    $sec_obj = (json_decode($sec, true));
    $interest = $sec_obj['INTEREST_AMOUNT'];
    $total_deductions = $l['total_deductions'];

    $loan_amount = $l['loan_amount'];
    $given_date = $l['given_date'];
    $total_repaid_ = $l['total_repaid'] + $total_deductions;

    $total_addons = $l['total_addons'];


    $other_charges = $total_addons - $interest;

    $total_repayable_amount = $loan_amount + $interest;  ////---Making this the total repayable amount
    $total_repaid = false_zero($total_repaid_ - $other_charges); ////----Total repaid, we remove other charges paid


    $daily_disbursements = obj_add($daily_disbursements, $given_date, $loan_amount);
    $daily_repayable_array = obj_add($daily_repayable_array, $given_date, $total_repayable_amount);
    $daily_repaid_array = obj_add($daily_repaid_array, $given_date, $total_repaid);


}




?>
<style>
    .calendar-container {
        max-width: 100%;
        margin: 0 auto;
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
    }
    .calendar-header {
        text-align: center;
        font-size: 1.5em;
        margin-bottom: 20px;
        color: #333;
    }
    .table-calendar th, .table-calendar td {
        text-align: center;
        vertical-align: middle;
        width: 100px;
    }
    .table-calendar th {
        color: #ffffff;
        font-weight: bold;
        font-size: 18px;
        background-color: #0a568c !important;

    }
    .table-calendar td {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
    }
    .table-calendar td.success {
        font-weight: bold;
    }
    .table-calendar td.text-muted {
        color: #aaa;
    }
    .day-number {
        font-size: 2em;
        /* font-weight: bold; */
        margin-bottom: 5px;
        color: #767676;
        float: left;
        background: #f7f7f7;
        border-radius: 38px;
        padding: 5px;
        width: 50px;
    }
    .event-content {
        font-size: 1.1em;
        color: #000000;
        font-weight: bold;

    }
</style>
<div class="row">
    <div class="col-sm-2">

        <h4>Pick a Month</h4>
        <table class="tablex">
        <?php
        $currentMonth = new DateTime(); // Get the current date

        // Loop through the last 12 months
        for ($i = 0; $i < 12; $i++) {
            // Clone the current date and subtract $i months
            $month = (clone $currentMonth)->modify("-$i months");
            $real_date =  $month->format('Y-m-d');
            $real_date_1 = getFirstDayOfMonth($real_date);
            $real_date_31 = last_date_of_month($real_date);
            // Print in the format YYYY-MMM (e.g., 2024-Jan)
            echo "<tr><td><a class='text-black text-bold superlnk_' onclick=\"input_add('#start_date_dist','$real_date_1'); input_add('#end_date_dist','$real_date_31'); nd_distribution_list(); \">".$month->format('Y-M') . "</a></td></tr>";
        }
        ?>
        </table>



    </div>
    <div class="col-sm-10">


        <?php


        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);

        $month = date('m', $start_timestamp);
        $year = date('Y', $start_timestamp);


        $first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
        $total_days_in_month = date('t', $first_day_of_month);

        $month_name = date('F', $first_day_of_month);
        $day_of_week = date('w', $first_day_of_month);

        $days_of_week = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

        echo '<div class="calendar-container">';
        echo '<div class="calendar-header">' . $month_name . ' ' . $year . ' Calendar </div>';
        echo '<table class="table table-bordered table-calendar">';
        echo '<thead>';
        echo '<tr>';

        // Header row with days of the week
        foreach ($days_of_week as $day) {
            echo '<th>' . $day . '</th>';
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody><tr>';

        if ($day_of_week > 0) {
            for ($i = 0; $i < $day_of_week; $i++) {
                echo '<td></td>';
            }
        }

        $current_day = 1;

        while ($current_day <= $total_days_in_month) {
            if ($day_of_week == 7) {
                $day_of_week = 0;
                echo '</tr><tr>';
            }

            $current_date = date('Y-m-d', mktime(0, 0, 0, $month, $current_day, $year));
            if ($current_date >= $start_date && $current_date <= $end_date) {
                $nowdate = "$year-$month-".leading_zero($current_day)."";

                if($nowdate == $date){
                    $highlight = "#d2d6de";
                }
                else{
                    $highlight = "";
                }

                $disb = $daily_disbursements[$nowdate];
                $daily_repaid = $daily_repaid_array[$nowdate];
                $daily_repayable = $daily_repayable_array[$nowdate];
                $col_rate = false_zero(roundDown((($daily_repaid/$daily_repayable)*100), 2));
                echo '<td class="succ"><div style="background-color: '.$highlight.';" class="day-number">' . $current_day . '</div>';
                echo '<div class="event-content"><span  class="label label-default text-black bg-gray-light"><i class="fa fa-arrow-circle-o-up"></i> '.shortenNumber($disb).'</span>
                      <span class="text-blue label label-default bg-gray-light"><i class="fa fa-arrow-circle-o-down"></i> '.shortenNumber($col_rate).'%</span></div></td>';

            } else {
                echo '<td class="text-muted"><div class="day-number">' . $current_day . '</div></td>';
            }

            $current_day++;
            $day_of_week++;
        }

        if ($day_of_week != 7) {
            $remaining_days = 7 - $day_of_week;
            for ($i = 0; $i < $remaining_days; $i++) {
                echo '<td></td>';
            }
        }

        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        ?>





    </div>

</div>
