<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
$userbranch = $userd['branch'];
$view_summary = permission($userd['uid'],'o_summaries',"0","read_");
if($view_summary == 1) {
    $andbranch_payments = "";
    $andbranch_customers = "";
    $andbranch_loans = "";
    $andbranch = "";
    $andbranch_staff = "";
}
else{
    $andbranch_payments = "AND branch_id='$userbranch'";
    $andbranch_customers = "AND branch='$userbranch'";
    $andbranch_loans = "AND current_branch='$userbranch'";
    $andbranch = "AND uid='$userbranch'";
    $andbranch_staff = "AND branch='$userbranch'";
}

$start_date = $_GET['start_date']; ////'2021-01-01';
$end_date = $_GET['end_date'];  //'2021-04-12';
$branches = table_to_obj('o_branches',"status=1","1000", "uid","name");
$branch_income = array();
$daily_income = array();
$period = array();

$start = new DateTime($start_date);
$end = new DateTime($end_date);

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


///--------------------------End of period


$loans_monthly = fetchtable('o_loans',"disbursed=1 AND final_due_date >= '$start_date' AND final_due_date <= '$end_date' $andbranch_loans","given_date","asc","100000000","uid, given_date, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, current_branch, current_lo, current_co, status");
while($dm = mysqli_fetch_array($loans_monthly)) {
//////////----Fetch required data
    $disbursed_amount = $dm['disbursed_amount'];
    $total_repaid = $dm['total_repaid'];
    $current_branch = $dm['current_branch'];
    $given_date = $dm['given_date'];

    array_push($period, $given_date);

    $income = false_zero($total_repaid - $disbursed_amount);

    $branch_income = obj_add($branch_income, $current_branch, $income);
    $daily_income = obj_add($daily_income, $given_date, $income);

}


$income_string = implode(',', $daily_income);




?>


<div class="col-lg-5">
    <div class="box box-primary box-solid">
        <div class="box-header">
            <h3 class="box-title">Loan Income Per Branch</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body max-height">

            <table class="table table-striped">
                <tr><th>Branch</th><th>Period Income</th></tr>
                <?php
                foreach($branches as $bid => $bname) {
                    $income = $branch_income[$bid];
                    $branch_income_total = $branch_income_total + $income;


                    echo "<tr><td>$bname</td><td>".money($income)."</td></tr>";
                }
                ?>

                <tr class="font-18 text-blue"><th>Total.</th><th><?php echo money($branch_income_total); ?></th></tr>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
</div>


<div class="col-lg-7">
    <div class="box box-solid">
        <div class="box-header bg-navy">
            <h3 class="box-title">Total Income Trend <i class="fa fa-line-chart"></i></h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <canvas id="myChart1" style="width:100%;max-width:100%; height: 400px;"></canvas>
        </div>
        <!-- /.box-body -->
    </div>
</div>
<?php
include_once ("../../configs/close_connection.inc");
?>

<script>

    x = '<?php echo $pr; ?>';
    income = [<?php echo $income_string; ?>];

    console.log("Disb"+disb);
    console.log("Coll"+col);
    xValues = x.split(',');
    console.log(xValues);

    new Chart("myChart1", {
        type: "line",
        data: {
            labels: xValues,
            datasets: [{
                label: 'Income',
                data: disb,
                borderColor: "green",
                fill: false
            }]
        },
        options: {
            legend: {display: true}

        },

    });

</script>