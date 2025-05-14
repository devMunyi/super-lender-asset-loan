<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$start_date = $_GET['start_date']; ////'2021-01-01';
$end_date = $_GET['end_date'];  //'2021-04-12';

$start = new DateTime($start_date);
$end = new DateTime($end_date);

$userd = session_details();
$userbranch = $userd['branch'];
$view_summary = permission($userd['uid'], 'o_summaries', "0", "read_");
$inarchive_ = $_SESSION['archives'] ?? 0;
if ($view_summary == 1 || $inarchive_ == 1 || $allow_bdos == 1) {

    $andbranch_loans = "";
    $andbranch_payments = "";
    $andbranch_customers = "";
    $andbranch = "";
    $andbranch_staff = "";
} else {

    $andbranch_loans = "AND current_branch = $userbranch";
    $andbranch_payments = "AND branch_id = $userbranch";
    $andbranch_customers = "AND branch = $userbranch";
    $andbranch = "AND uid = $userbranch";
    $andbranch_staff = "AND branch = $userbranch";

    //////-----Check users who view multiple branches
    $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch");
    if (sizeof($staff_branches) > 0) {
        ///------Staff has been set to view multiple branches
        if($userbranch > 0){
            array_push($staff_branches, $userbranch);
        }
        $staff_branches_list = implode(",", $staff_branches);

        $andbranch_loans = "AND current_branch IN ($staff_branches_list)";
        $andbranch_payments = "AND branch_id IN ($staff_branches_list)";
        $andbranch_customers = "AND branch IN ($staff_branches_list)";
        $andbranch = "AND uid IN ($staff_branches_list)";
        $andbranch_staff = "AND branch IN ($staff_branches_list)";
    }
}

///-----------------------Period
$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($start, $interval, $end);
$year_month_day = array();

foreach ($period as $dt) {
    $day = $dt->format("M-d");
    $dayf = $dt->format('Y-m-d');
    array_push($periods, $day);
    array_push($year_month_day, $dayf);
}
$pr = implode(',', $year_month_day);

/////////--------------
$branches = table_to_obj('o_branches', "status=1 $andbranch", "1000", "uid", "name");
$branch_disb = array();
$branch_coll = array();
$branch_bal = array();
$branch_bal_loans = array();

$staff_obj = table_to_obj('o_users', "uid>0 $andbranch_staff", "100000", "uid", "name");
$pair_obj = table_to_obj('o_pairing', "status=1", "100000", "lo", "co");

$lo_bal = array();
$lo_bal_loans = array();
$co_bal = array();
$co_bal_loans = array();
$products = table_to_obj('o_loan_products', "status=1", "1000", "uid", "name");
$product_disb = array();
$product_coll = array();
$product_bal = array();
$daily_balance = array();
$top_defaulters = array();

////////////-------------Disbursements
$loans_monthly = fetchtable('o_loans', "disbursed=1 AND paid=0 AND status=7 AND loan_balance > 0 AND final_due_date >= '$start_date' AND final_due_date <= '$end_date' $andbranch_loans", "loan_balance", "desc", "100000000", "uid, given_date ,customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, current_branch, current_lo, current_co, status");
while ($dm = mysqli_fetch_array($loans_monthly)) {
    //////////----Fetch required data
    $loan_amount = $dm['loan_amount'];
    $total_repaid = $dm['total_repaid'];
    $loan_balance = $dm['loan_balance'];
    $customer_id = $dm['customer_id'];

    $top_defaulters[$customer_id] = $loan_balance;


    $given_date = $dm['given_date'];
    // array_push($period, $given_date);
    ////====Per BDO
    $current_lo = $dm['current_lo'];
    $current_co = $dm['current_co'];

    $lo_bal = obj_add($lo_bal, $current_lo, $loan_balance);
    $lo_bal_loans = obj_add($lo_bal_loans, $current_lo, 1);
    $co_bal = obj_add($co_bal, $current_co, $loan_balance);
    $co_bal_loans = obj_add($co_bal_loans, $current_co, 1);
    ////====Per Branch
    $current_branch = $dm['current_branch'];
    $branch_bal = obj_add($branch_bal, $current_branch, $loan_balance);
    $branch_bal_loans = obj_add($branch_bal_loans, $current_branch, 1);
    ////====Per Product
    $loan_product = $dm['product_id'];
    $product_bal = obj_add($product_bal, $loan_product, $loan_balance);
    // echo "Loan AMount $loan_amount, Total Paid: $total_repaid LO $current_lo <br/>";
    $daily_balance = obj_add($daily_balance, $given_date, $loan_balance);
}
$bal_string = implode(',', $daily_balance);



?>
<div class="col-lg-4">
    <div class="box box-success box-solid">
        <div class="box-header">
            <h3 class="box-title">Top Defaulters</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body max-height">

            <table class="table table-striped">
                <tr>
                    <th>Customer</th>
                    <th>Balance</th>
                </tr>
                <?php
                $c = 0;
                $top_defaulters_totals = 0;
                foreach ($top_defaulters as $did => $amount) {
                    $c += 1;
                    if ($c < 10) {
                        if ($did > 0) {
                            $top_defaulters_totals = $top_defaulters_totals + $amount;
                            $customer = fetchrow('o_customers', "uid = '$did' $andbranch_customers", "full_name");

                            echo "<tr><td>$customer</td><td>" . money($amount) . "</td></tr>";
                        }
                    }
                }
                ?>

                <tr class="font-18 text-blue">
                    <th>Total.</th>
                    <th><?php echo money($top_defaulters_totals); ?></th>
                </tr>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
</div>
<div class="col-lg-4">
    <div class="box box-primary box-solid">
        <div class="box-header">
            <h3 class="box-title">Per Branch</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body max-height">

            <table class="table table-striped">
                <tr>
                    <th>Branch</th>
                    <th>Outstanding Amount</th>
                    <th>Total Loans</th>
                </tr>
                <?php

                foreach ($branches as $bid => $bname) {

                    $outstanding = $branch_bal[$bid];
                    $count = $branch_bal_loans[$bid];
                    $branch_total_bal = $branch_total_bal + $outstanding;
                    $branch_total_count = $branch_total_count + $count;

                    echo "<tr><td>$bname</td><td>" . money($outstanding) . "</td><td>" . false_zero($count) . "</td></tr>";
                }

                ?>


                <tr class="font-18 text-blue">
                    <th>Total.</th>
                    <th><?php echo money($branch_total_bal); ?></th>
                    <th><?php echo $branch_total_count; ?></th>
                </tr>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
</div>
<div class="col-lg-4">
    <div class="box box-solid">
        <div class="box-header bg-black">
            <h3 class="box-title">Per BDO</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body max-height">

            <table class="table table-striped">
                <tr>
                    <th>LO-CO Pair</th>
                    <th>Outstanding Amount</th>
                    <th>Total Loans</th>
                </tr>
                <?php
                foreach ($pair_obj as $lo => $co) {

                    $balance = $lo_bal[$lo];
                    $count = $lo_bal_loans[$lo];

                    $total_balance = $total_balance + $balance;
                    $total_count = $total_count + $count;

                    echo "<tr><td>" . $staff_obj[$lo] . '-' . $staff_obj[$co] . "</td><td>" . money($balance) . "</td><td>" . false_zero($count) . "</td></tr>";
                }
                ?>

                <tr class="font-18 text-blue">
                    <th>Total.</th>
                    <th><?php echo money($total_balance); ?></th>
                    <th><?php echo $total_count; ?></th>
                </tr>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
</div>

<?php
include_once("../../configs/close_connection.inc");
?>

<script>
    /*
    x = '<?php echo $pr; ?>';
    income = [<?php echo $bal_string; ?>];



    console.log("Disb"+disb);
    console.log("Coll"+col);
    xValues = x.split(',');
    console.log(xValues);

    new Chart("myChart2", {
        type: "line",
        data: {
            labels: xValues,
            datasets: [{
                label: 'Default',
                data: disb,
                borderColor: "red",
                fill: false
            }]
        },
        options: {
            legend: {display: true}

        },

    });
*/
</script>