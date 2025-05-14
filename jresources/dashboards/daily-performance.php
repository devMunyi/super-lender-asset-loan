<?php

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');

$userd = session_details();
$userbranch = $userd['branch'];
$view_summary = permission($userd['uid'], 'o_summaries', "0", "read_");
if ($view_summary == 1 || $allow_bdos == 1) {
    $andbranch_loans = "";
    $andbranch_payments = "";
    $andbranch_customers = "";
} else {

    $andbranch_loans = "AND current_branch = $userbranch";
    $andbranch_payments = "AND branch_id = $userbranch";
    $andbranch_customers = "AND branch = $userbranch";

    //////-----Check users who view multiple branches
    $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
    if (sizeof($staff_branches) > 0) {
        ///------Staff has been set to view multiple branches
        array_push($staff_branches, $userd['branch']);
        $staff_branches_list = implode(",", $staff_branches);

        $andbranch_loans = "AND current_branch IN ($staff_branches_list)";
        $andbranch = "AND uid IN ($staff_branches_list)";
        $andbranch_payments = "AND branch_id IN ($staff_branches_list)";
        $andbranch_customers = "AND branch IN ($staff_branches_list)";
    }
}

////------------Disbursements
$loans_daily = fetchtable('o_loans', "disbursed=1 AND given_date >= '$this_year-$this_month-01'  $andbranch_loans AND status!=0", "uid", "asc", "5000000", "uid, customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, final_due_date, current_agent, current_branch, status");

// $loans_d = mysqli_fetch_all($loans_daily, MYSQLI_ASSOC);
// echo json_encode($loans_d);
// return;

$day_totals_array = array();
while ($dt = mysqli_fetch_array($loans_daily)) {
    $given_date = $dt['given_date'];
    $given_date_array = explode('-', $given_date);
    $loan_day = $given_date_array[2];

    if ($this_year == $given_date_array[0] && $this_month == $given_date_array[1]) {
        $loan_amount = $dt['loan_amount'];
        if ($day_totals_array[$loan_day] > 0) {
            $day_totals_array[$loan_day] = $loan_amount + $day_totals_array[$loan_day];
        } else {
            $day_totals_array[$loan_day] = $loan_amount;
        }
        $total_disbursed_daily = $total_disbursed_daily + $loan_amount;
    }
}



//////-----------Collections
$collections_daily = fetchtable('o_incoming_payments', "status=1 AND payment_date >= '$this_year-$this_month-01' $andbranch_payments", "uid", "asc", "5000000", "uid, customer_id, branch_id, amount,payment_date");

// $collections_d = mysqli_fetch_all($collections_daily, MYSQLI_ASSOC);
// echo json_encode($collections_d);
// return;

$total_collected_daily = 0;
$day_total_collections_array = array();
while ($dc = mysqli_fetch_array($collections_daily)) {
    $paid_date = $dc['payment_date'];
    $paid_date_array = explode('-', $paid_date);
    $pay_day = $paid_date_array[2];

    if ($this_year == $paid_date_array[0] && $this_month == $paid_date_array[1]) {
        $payment_amount = $dc['amount'];
        if ($day_total_collections_array[$pay_day] > 0) {
            $day_total_collections_array[$pay_day] = $payment_amount + $day_total_collections_array[$pay_day];
        } else {
            $day_total_collections_array[$pay_day] = $payment_amount;
        }
        $total_collected_daily = $total_collected_daily + $payment_amount;
    }
}

/////------------------New customers
// $new_clients_daily = fetchtable('o_customers', "status=1 AND added_date  >= '$this_year-$this_month-01 00:00:00' $andbranch_customers", "uid", "asc", "2000000", "uid, branch, primary_product, DATE(added_date) as added_date");

// $new_clients_d = mysqli_fetch_all($new_clients_daily, MYSQLI_ASSOC);
// echo json_encode($new_clients_d);
// return;

// $day_total_customers = array();
// $total_join_daily = 0;
// while ($dcu = mysqli_fetch_array($new_clients_daily)) {

//     $join_date = $dcu['added_date'];
//     $join_date_array = explode('-', $join_date);
//     $join_day = $join_date_array[2];

//     if ($this_year == $join_date_array[0] && $this_month == $join_date_array[1]) {
//         $total_joins = 0;
//         if ($day_total_customers[$join_day] > 0) {
//             $day_total_customers[$join_day] =  $day_total_customers[$join_day] + 1;
//         } else {
//             $day_total_customers[$join_day] = 1;
//         }
//         $total_join_daily = $total_join_daily + 1;
//     }
// }


?>
<table class="table table-striped">
    <tr>
        <th>Date</th>
        <th>Disbursements</th>
        <th>Collections</th>
    </tr>
    <?php
    for ($i = 1; $i <= $this_day; ++$i) {

        echo "<tr><td>$this_year-$this_month-" . leading_zero($i) . "</td><td>" . money($day_totals_array[leading_zero($i)]) . "</td><td>" . money($day_total_collections_array[leading_zero($i)]) . "</td></tr>";
    }

    ?>

    <tr class="font-18 text-blue">
        <th>Total.</th>
        <th><?php echo money($total_disbursed_daily); ?></th>
        <th><?php echo money($total_collected_daily); ?></th>
    </tr>
</table>