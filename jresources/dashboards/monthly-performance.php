<?php
session_start();

include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();
$year = $_GET['year'];
$month = $_GET['month'];
$branch = $_GET['branch'];
$product = $_GET['product'];

$interest_factor = 1.20;   //////This will need to be calculated correctly and read from the database

$andother = "";
if($branch > 0){
    $andother.=" AND current_branch='$branch' ";
}
if($product > 0){
    $andother.=" AND product_id = '$product' ";
}
$userd = session_details();
$userbranch = $userd['branch'];
$view_summary = permission($userd['uid'],'o_summaries',"0","read_");
if($view_summary == 1 || $allow_bdos == 1) {
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

/////////////////-------------------------Disbursement

$all_loans = array();
$all_customers = array();
$loan_branches_array = array();
$loan_amounts_array = array();
$loan_customer_array = array();
$loan_total_repayable_amount_array = array();
$loan_total_repaid_array = array();
$loan_status_array = array();
$branch_loan_count_array = array();
$fully_paid = 0;
$total_repaid_total = 0 ;
$total_repayable_branch =  array();
$total_repaid_branch = array();

$branch_name_arrays = table_to_obj('o_branches',"status=1 $andbranch","100","uid","name");
$branches_array = table_to_array('o_branches',"status=1 $andbranch","100","uid");

$disb = fetchtable('o_loans',"disbursed=1 AND status!=0 AND YEAR(given_date)='$year' AND MONTH(given_date)='$month' $andother $andbranch_loans","uid","asc","5000000","uid, loan_amount, customer_id, total_repayable_amount, total_repaid, current_branch , status");
while($r = mysqli_fetch_array($disb)){
    $lid = $r['uid'];
    $current_branch = $r['current_branch'];
    $loan_amount = $r['loan_amount'];
    $total_repayable_amount = $r['total_repayable_amount'];
    $total_repaid = $r['total_repaid'];
    $customer_id = $r['customer_id'];
    $status = $r['status'];

    if($status == 5){
        $fully_paid = $fully_paid + 1;
    }

    $loan_branches_array[$lid] = $current_branch;
    $loan_amounts_array[$lid] = $loan_amount;
    $loan_total_repayable_amount_array[$lid] = $total_repayable_amount;
    $loan_total_repaid_array[$lid] = $total_repaid;
    $loan_customer_array[$lid] = $customer_id;
    $loan_status_array[$lid] = $status;
    $total_repaid_total = $total_repaid_total + $total_repaid;

    array_push($all_loans, $lid);
    array_push($all_customers, $customer_id);
    $branch_loan_count_array = obj_add($branch_loan_count_array, $current_branch, 1);
    $total_repayable_branch = obj_add($total_repayable_branch, $current_branch, $total_repayable_amount);
    $total_repaid_branch = obj_add($total_repaid_branch, $current_branch, $total_repaid);
}

$loan_addons_array = array();
$addons_array = table_to_obj('o_addons',"uid > 0","1000","uid","name");

$all_loans_list = implode(',', $all_loans);
$total_joining_fees = 0;
$total_admin_fees = 0;
$total_interest = 0;
$total_penalties = 0;
$total_late_interest = 0;
$total_principle = 0;
$total_addon_amount = 0;
$addons = fetchtable('o_loan_addons',"loan_id in ($all_loans_list) AND status=1","uid","asc","100000","uid, loan_id, addon_id, addon_amount");
while($a = mysqli_fetch_array($addons)){
    $aid = $a['addon_id'];
    $aloan_id = $a['loan_id'];
    $addon_amount = $a['addon_amount'];
    $loan_addons_array[$aloan_id][$aid] = $addon_amount;
    $branch_id = $loan_branches_array[$aloan_id];

    if($aid == 2){
        $total_joining_fees = $total_joining_fees + $addon_amount;
        $joining_fees_array_branch = obj_add($joining_fees_array_branch, $branch_id, $addon_amount);
    }
    if($aid == 5){
        $total_admin_fees = $total_admin_fees + $addon_amount;

    }
    if($aid == 8 || $aid == 4){
        $total_interest = $total_interest + $addon_amount;
        $interest_array_branch = obj_add($interest_array_branch, $branch_id, $addon_amount);
    }
    if($aid == 7 || $aid == 3){
        $total_penalties = $total_penalties + $addon_amount;
        $total_penalties_branch = obj_add($total_penalties_branch, $branch_id, $addon_amount);
    }
    if($aid == 9){
        $total_late_interest = $total_late_interest + $addon_amount;
        $late_fees_branch = obj_add($late_fees_branch, $branch_id, $addon_amount);

    }

    $total_addon_amount = $total_addon_amount + $addon_amount;


}

$principle_paid = $total_repaid_total - $total_addon_amount;


////////////////--------------------------Customers loans

$loans_per_customer = array();
$customers_list = implode(',', $all_customers);
$customers = fetchtable('o_loans',"status=1 AND customer_id in ($customers_list) $andbranch_loans","uid","asc","100000","customer_id");
while ($c = mysqli_fetch_array($customers)){
    $cid = $c['uid'];
    $customer_id = $c['customer_id'];
    $loans_per_customer[$customer_id] = obj_add($loans_per_customer, $customer_id, 1);
}


////-----------------------Loop through loans again
$total_disbursements = 0;
$total_disbursements_branch = array();
$total_repayable = 0;
$repaid_all = 0;
$repaid_principle_interest_total = 0;
$percentage = 0;
$new_customers = 0;


for($i=0; $i<=sizeof($all_loans); ++$i){
    $loan_id = $all_loans[$i];
    $disbursement = $loan_amounts_array[$loan_id];
    $repaid_amount = $loan_total_repaid_array[$loan_id];
    $customer_ = $loan_customer_array[$loan_id];
    $loan_branch = $loan_branches_array[$loan_id];

    $customer_total_loans = $loans_per_customer[$customer_];
    if($customer_total_loans == 1){
        $new_customers = $new_customers + 1;
    }

    $principle_interest = $disbursement * 1.2;

    $other_charges =  $loan_addons_array[$loan_id][9] + $loan_addons_array[$loan_id][7] + $loan_addons_array[$loan_id][3] + $loan_addons_array[$loan_id][5] + $loan_addons_array[$loan_id][2];

    $repaid_principle_interest = force_to($repaid_amount, $principle_interest);

    $total_disbursements = $total_disbursements + $disbursement;
    $total_disbursements_branch = obj_add($total_disbursements_branch, $loan_branch, $disbursement);

    $total_repaid = $total_repaid + $repaid_amount;
    $repaid_principle_interest_total = $repaid_principle_interest_total + $repaid_principle_interest;
    $repaid_all_branch = obj_add($repaid_all_branch, $loan_branch, $repaid_principle_interest_total);

}

$total_expected = $total_disbursements * $interest_factor;
$percentage_repaid = round(($repaid_principle_interest_total/$total_expected)*100, 2);
$average_loan_ = round($total_disbursements/sizeof($all_loans), 2);



?>
<div class="container p-3 my-3">
    <h4 class="text-black text-bold text-uppercase">Summary</h4>
    <div class="row">
        <div class="col-sm-3">
            <table class="table table-bordered">
                <tr><td>Disbursements</td><td class="font-16 text-bold"><?php echo money($total_disbursements); ?></td></tr>
                <tr><td>Expected</td><td class="font-16 text-bold"><?php echo money($total_expected); ?></td></tr>
                <tr><td>Amount Repaid</td><td class="font-16 text-bold"><?php echo money($repaid_principle_interest_total); ?></td></tr>
                <tr><td>Percentage</td><td class="font-24 text-bold"><?php echo false_zero($percentage_repaid); ?>%</td></tr>
            </table>
        </div>
        <div class="col-sm-3">
            <table class="table table-bordered">
                <tr><td>New Member Loans:</td><td class="font-16 text-bold"><?php echo $new_customers; ?></td></tr>
                <tr><td>Total Loans Issued:</td><td class="font-16 text-bold"><?php echo number_format(sizeof($all_loans)); ?></td></tr>
                <tr><td>Average Loan Amount:</td><td class="font-16 text-bold"><?php echo money($average_loan_); ?></td></tr>
                <tr><td>Loans Fully Repaid:</td><td class="font-16 text-bold"><?php echo number_format($fully_paid); ?></td></tr>
            </table>

        </div>
        <div class="col-sm-3">
            <table class="table table-bordered">
                <tr><td>Joining Fees:</td><td class="font-16 text-bold"><?php echo money(($total_joining_fees)); ?></td></tr>
                <tr><td>Admin Fees:</td><td class="font-16 text-bold"><?php echo money($total_admin_fees); ?></td></tr>
                <tr><td>Interest:</td><td class="font-16 text-bold"><?php echo money($total_interest); ?></td></tr>
            </table>
        </div>
        <div class="col-sm-3">
            <table class="table table-bordered">
                <tr><td>Penalties:</td><td class="font-16 text-bold"><?php echo money($total_penalties); ?></td></tr>
                <tr><td>Late Interest:</td><td class="font-16 text-bold"><?php echo money($total_late_interest); ?></td></tr>
                <tr><td>Principal:</td><td class="font-16 text-bold"><?php echo money($principle_paid); ?></td></tr>
                <tr><td>OverPayments:</td><td class="font-16 text-bold">--</td></tr>
            </table>
        </div>
    </div>
    <h4 class="text-black text-bold text-uppercase">INCOME</h4>
    <div class="well well-lg d-flex justify-content-center bd-highlight mb-3" style="text-align: center;">

        <table class="table  bd-highlight font-18 " style="width: 40%; text-align: center;">
            <tr><td>Total Collected:</td><td class="text-bold"><?php echo money($repaid_principle_interest_total); ?></td></tr>
            <tr><td>Total Disbursed:</td><td class="text-bold"><?php echo money($total_disbursements); ?></td></tr>
            <tr class="font-24 well-sm bg-orange"><td>Gross Revenue:</td><td class="text-bold"><?php echo money($total_disbursements); ?></td></tr>
            <tr><td>Registration Fees:</td><td class="text-bold"><?php echo money(($total_joining_fees)); ?></td></tr>
            <tr><td>Admin Fees:</td><td class="text-bold"><?php echo money(($total_admin_fees)); ?></td></tr>
            <tr class="font-24 well-sm bg-blue-gradient"><td>Total Gross Revenue:</td><td class="text-bold"><?php echo money($repaid_principle_interest_total - $total_disbursements); ?></td></tr>
        </table>

    </div>
    <h4 class="text-black text-bold text-uppercase">Breakdown</h4>
    <div class="well well-sm box">

        <table class="table table-condensed table-striped table-bordered">
            <thead>
            <tr><th>Branch</th>	<th>Loan_count</th><th>	Disbursed</th><th>	Expected</th><th>Join Fees</th><th>	Interest</th><th>	Principal</th><th>	Penalties</th><th>	OverPayments</th><th>	LostInterest</th><th>	Recovered </th>	<th>Recovery</th><th>	Total collected	Revenue</th></tr>
            </thead>
            <tbody>
            <?php
            foreach ($branch_name_arrays as $bid => $bname){
                if($bid > 1) {
                    $total_loans_b = $branch_loan_count_array[$bid];
                    $disbursed_b = $total_disbursements_branch[$bid];
                    $repayable_b = $disbursed_b * 1.24;
                    $lost_interest = ($disbursed_b * 1.30) - $repayable_b;
                    $join_fee_b = $joining_fees_array_branch[$bid];
                    $interest_b = $interest_array_branch[$bid];
                    $principal_b = $total_disbursements_branch[$bid];
                    $penalties_b = $total_penalties_branch[$bid];
                    $late_b = $late_fees_branch[$bid];
                    $repaid_b = $total_repaid_branch[$bid];
                    $repayable_total_b = $total_repayable_branch[$bid];

                    $recovered = force_to($repayable_total_b, $repaid_b);     /////-------Total loan plus interest
                    $overpayment = false_zero($repaid_b-$repayable_total_b);
                    $total_collected_b = $repaid_b;
                    $recovery = round(($repaid_b / $repayable_total_b) * 100, 2);
                    $revenue = $repayable_total_b - $repaid_b;


                    echo "  <tr><td>$bname</td>	<td>$total_loans_b</td><td>	" . money($disbursed_b) . "</td><td>" . money($repayable_b) . "</td><td>" . money($join_fee_b) . "</td><td>" . money($interest_b) . "</td><td>	" . money($principal_b) . "</td><td>" . money($penalties_b) . "</td><td>	".money($overpayment)."</td><td>	".money($lost_interest)."</td><td><span>".money($recovered) . " </span></td>	<td class='font-18 label label-default bg-black-gradient'>" .money( $recovery ). "%</td><td>" . money($revenue) . "</td></tr>";
                }
            }


            ?>

            </tbody>

        </table>
    </div>

</div>
<?php
include_once ("../../configs/close_connection.inc");
?>