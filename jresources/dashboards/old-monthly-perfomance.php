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


//////-----------Collections
if ($show_monthly_dashboard == 'TRUE') {
    $collections_monthly = fetchtable('o_incoming_payments', "status=1 AND payment_date >= '$this_year-01-01' $andbranch_payments", "uid", "asc", "10000000", "amount, payment_date");
    $month_total_collections_array = array();
    $total_collected_monthly = 0;
    while ($mc = mysqli_fetch_array($collections_monthly)) {
        $paid_date = $mc['payment_date'];
        $paid_date_array = explode('-', $paid_date);
        $pay_day = $paid_date_array[1];



        if ($this_year == $paid_date_array[0]) {
            $payment_amount = $mc['amount'];
            if ($month_total_collections_array[$pay_day] > 0) {
                $month_total_collections_array[$pay_day] = $payment_amount + $month_total_collections_array[$pay_day];
            } else {
                $month_total_collections_array[$pay_day] = $payment_amount;
            }
            $total_collected_monthly = $total_collected_monthly + $payment_amount;
        }
    }


    /////------------------New customers
    $new_clients_monthly = fetchtable('o_customers', "status=1 AND added_date >= '$this_year-01-01 00:00:00' $andbranch_customers", "uid", "asc", "10000000", "DATE(added_date) as added_date");
    $month_total_customers = array();
    $total_join_monthly = 0;
    while ($dcu = mysqli_fetch_array($new_clients_monthly)) {

        $join_date = $dcu['added_date'];
        $join_date_array = explode('-', $join_date);
        $join_month = $join_date_array[1];


        if ($this_year == $join_date_array[0]) {
            $total_joins = 0;
            if ($month_total_customers[$join_month] > 0) {
                $month_total_customers[$join_month] =  $month_total_customers[$join_month] + 1;
            } else {
                $month_total_customers[$join_month] = 1;
            }
            $total_join_monthly = $total_join_monthly + 1;
        }
    }

?>
    <!-- /.box-header -->
    <div class="box-body" id="monthly-performance">
        <table class="table table-striped">
            <tr>
                <th>Month</th>
                <th>Disbursements</th>
                <th>Collections</th>
                <th>New Clients</th>
            </tr>
            <?php
            $loans_monthly = fetchtable('o_loans', "disbursed=1 AND given_date >= '$this_year-01-01' $andbranch_loans AND status!=0", "uid", "asc", "10000000", "customer_id, given_date, loan_amount");
            $month_totals_array = array();
            $total_disbursed_monthly = 0;
            $new_clients = array();

            while ($dm = mysqli_fetch_array($loans_monthly)) {
                $customer_id = intval($dm['customer_id'] ?? 0);
                if(!in_array($customer_id, $new_clients) && $customer_id > 0){
                    array_push($new_clients, $customer_id);
                }

                $given_date = $dm['given_date'];
                $given_date_array = explode('-', $given_date);
                $loan_month = $given_date_array[1];
                if ($this_year == $given_date_array[0]) {
                    $loan_amount = $dm['loan_amount'];
                    if ($month_totals_array[$loan_month] > 0) {
                        $month_totals_array[$loan_month] = $loan_amount + $month_totals_array[$loan_month];
                    } else {
                        $month_totals_array[$loan_month] = $loan_amount;
                    }
                    $total_disbursed_monthly = $total_disbursed_monthly + $loan_amount;
                }
            }

            $month_of_year = date('m');
            for ($m = 1; $m <= $month_of_year; ++$m) {
                $mtc = $month_total_customers[leading_zero($m)];
                if ($mtc < 1) {
                    $mtc = 0;
                }
                echo " <tr><td>" . month_name($m) . "</td><td>" . money($month_totals_array[leading_zero($m)]) . "</td><td>" . money($month_total_collections_array[leading_zero($m)]) . "</td><td>" . $mtc . "</td></tr>";
            }

            ?>

            <tr class="font-18 text-blue">
                <th>Total.</th>
                <th><?php echo money($total_disbursed_monthly); ?></th>
                <th><?php echo money($total_collected_monthly); ?></th>
                <th><?php // echo $total_join_monthly; ?></th>
                <th><?php echo count($new_clients); ?></th>
            </tr>
        </table>
    <?php
}
    ?>
    </div>
    <!-- /.box-body -->