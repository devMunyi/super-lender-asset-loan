<?php
//ini_set('memory_limit','512M');
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$search_type = $_GET['search_type']; //'DAILY';
$start_date = $_GET['start_date']; ////'2021-01-01';
$end_date = $_GET['end_date'];  //'2021-04-12';

$userd = session_details();
$userbranch = $userd['branch'];
$view_summary = permission($userd['uid'], 'o_summaries', "0", "read_");
$inarchive_ = $_SESSION['archives'] ?? 0;
if ($view_summary == 1 || $inarchive_ == 1 || $allow_bdos == 1) {
    $andbranch_loans = "";
    $andbranch_payments = "";
} else {

    $andbranch_loans = "AND current_branch = $userbranch";
    $andbranch_payments = "AND branch_id = $userbranch";

    //////-----Check users who view multiple branches
    $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch");
    if (count($staff_branches) > 0) {
        ///------Staff has been set to view multiple branches
        if ($userbranch > 0) {
            array_push($staff_branches, $userbranch);
        }
        $staff_branches_list = implode(",", $staff_branches);

        $andbranch_loans = "AND current_branch IN ($staff_branches_list)";
        $andbranch_payments = "AND branch_id IN ($staff_branches_list)";
    }
}

$branches_array = array();
$staff_array = array();
$products_array = array();
$branch_disb_targets = array();
$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');


$start = new DateTime($start_date);
$interval = new DateInterval('P1M');
$end = new DateTime($end_date);
$period = new DatePeriod($start, $interval, $end);

$periods = array();
if ($search_type == 'MONTHLY') {
    $year_month = array();
    foreach ($period as $dt) {
        // echo $dt->format('F Y') . PHP_EOL;
        $month = $dt->format('F');
        $month_year = $dt->format('Y-m');
        array_push($periods, $month);
        array_push($year_month, $month_year);
    }
    $pr = implode(',', $year_month);
} else if ($search_type == 'DAILY') {
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($start, $interval, $end);
    $year_month_day = array();

    foreach ($period as $dt) {
        $day = $dt->format("M-d");
        $dayf = $dt->format('Y-m-d');
        array_push($periods, $day);
        array_push($year_month_day, $dayf);
    }
    array_push($year_month_day, $date);
    $pr = implode(',', $year_month_day);
}

////////////-------------Disbursements
$loans_monthly = fetchtable('o_loans', "disbursed=1 AND given_date >= '$start_date' AND given_date <= '$end_date' AND status!=0 $andbranch_loans", "given_date", "asc", "100000000", "given_date, loan_amount");
$month_total_disbursed_array = array();
$total_disbursed_monthly = 0;
while ($dm = mysqli_fetch_array($loans_monthly)) {
    $given_date = $dm['given_date'];
    $given_date_array = explode('-', $given_date);
    $loan_day = $given_date_array[2];
    $loan_month = $given_date_array[1];
    $loan_year = $given_date_array[0];
    $ym = "$loan_year-$loan_month";
    $md = "$loan_year-$loan_month-$loan_day";



    $loan_amount = $dm['loan_amount'];
    ////------------Month Fill
    if ($search_type == 'MONTHLY') {
        if ($month_total_disbursed_array[$ym] > 0) {
            $month_total_disbursed_array[$ym] = $loan_amount + $month_total_disbursed_array[$ym];
        } else {
            $month_total_disbursed_array[$ym] = $loan_amount;
        }
    } elseif ($search_type == 'DAILY') {
        if ($month_total_disbursed_array[$md] > 0) {
            $month_total_disbursed_array[$md] = $loan_amount + $month_total_disbursed_array[$md];
        } else {
            $month_total_disbursed_array[$md] = $loan_amount;
        }
    }
    $total_disbursed_monthly = $total_disbursed_monthly + $loan_amount;
}
///////---------------collections
$collections_monthly = fetchtable('o_incoming_payments', "status=1 AND payment_date >= '$start_date' AND payment_date <= '$end_date' $andbranch_payments", "payment_date", "asc", "100000000", "payment_date, amount");
$month_total_collections_array = array();
$total_collected_monthly = 0;
while ($dc = mysqli_fetch_array($collections_monthly)) {
    $paid_date = $dc['payment_date'];
    $paid_date_array = explode('-', $paid_date);
    $pay_day = $paid_date_array[2];
    $loan_day = $paid_date_array[2];
    $loan_month = $paid_date_array[1];
    $loan_year = $paid_date_array[0];
    $ym = "$loan_year-$loan_month";
    $md = "$loan_year-$loan_month-$loan_day";


    $payment_amount = $dc['amount'];
    if ($search_type == 'MONTHLY') {
        if ($month_total_collections_array[$ym] > 0) {
            $month_total_collections_array[$ym] = $payment_amount + $month_total_collections_array[$ym];
        } else {
            $month_total_collections_array[$ym] = $payment_amount;
        }
    } elseif ($search_type == 'DAILY') {
        if ($month_total_collections_array[$md] > 0) {
            $month_total_collections_array[$md] = $payment_amount + $month_total_collections_array[$md];
        } else {
            $month_total_collections_array[$md] = $payment_amount;
        }
    }

    $total_collected_monthly = $total_collected_monthly + $payment_amount;
}


////-------
$col_array = array();
$disb_array = array();


if ($search_type == 'MONTHLY') {
    ///----Loop through months
    for ($p = 0; $p <= count($year_month); ++$p) {
        $col_ = round($month_total_collections_array[$year_month[$p]], 0);
        $disb_ = round($month_total_disbursed_array[$year_month[$p]], 0);

        array_push($col_array, false_zero($col_));
        array_push($disb_array, false_zero($disb_));
    }
    /// ----
} else if ($search_type == 'DAILY') {
    ///----Loop through days
    for ($p = 0; $p <= count($year_month_day); ++$p) {

        $col_ = round($month_total_collections_array[$year_month_day[$p]], 0);
        $disb_ = round($month_total_disbursed_array[$year_month_day[$p]], 0);
        array_push($col_array, false_zero($col_));
        array_push($disb_array, false_zero($disb_));
    }
}



$col_string = implode(',', $col_array);
$disb_string = implode(',', $disb_array);


?>

<div class="box-body">
    <div class="row">
        <div class="col-md-8">
            <canvas id="myChart" style="width:100%;max-width:100%; height: 400px;"></canvas>
        </div>
        <div class="col-md-4">
            <h3> Totals</h3>
            <table class="table table-bordered font-16">
                <tr>
                    <td>Total Disbursement</td>
                    <td class="font-bold"><?php echo money($total_disbursed_monthly); ?></td>
                </tr>
                <tr>
                    <td>Total Collection</td>
                    <td class="font-bold"><?php echo money($total_collected_monthly); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<!-- /.box-body -->
<?php
include_once("../../configs/close_connection.inc");

?>



<script>
    x = '<?php echo $pr; ?>';
    disb = [<?php echo $disb_string; ?>];
    col = [<?php echo $col_string; ?>];

    xValues = x.split(',');
    // console.log(xValues);

    new Chart("myChart", {
        type: "line",
        data: {
            labels: xValues,
            datasets: [{
                label: 'Disbursements',
                data: disb,
                borderColor: "red",
                fill: false
            }, {
                label: 'Collections',
                data: col,
                borderColor: "blue",
                fill: false
            }]
        },
        options: {
            legend: {
                display: true
            }

        },

    });
</script>