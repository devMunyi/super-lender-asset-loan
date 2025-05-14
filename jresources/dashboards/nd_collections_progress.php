<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

$userd = session_details();
$loan_branches = branch_permissions($userd, 'o_loans');
$branches_id = branch_permissions($userd, 'o_branches');

$branch_disbursements = array();
$branch_repayables = array();
$branch_penalties = array();////////////////////--------------------Collection Rate FIX
$branch_repaid = array();
$branch_balance = array();
$branch_names = table_to_obj('o_branches',"uid > 0 $branches_id","10000","uid","name");



//$q = "given_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1 AND status!=0 $loan_branches";
$q = "status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' AND disbursed=1 $loan_branches";


$loans = fetchtable('o_loans',"$q","uid","asc",1000000000,"uid, loan_amount, current_branch, total_repayable_amount,total_repaid, loan_balance, other_info, total_addons, total_deductions");
while($l = mysqli_fetch_array($loans)){

    //$total_repayable_amount = $l['total_repayable_amount'];  ////We are making the total repayable interest and principal only
    $sec = $l['other_info'];
    $sec_obj = (json_decode($sec, true));
    $interest = $sec_obj['INTEREST_AMOUNT'];
    $penalty = $sec_obj['PENALTY_AMOUNT'];

    $loan_amount = $l['loan_amount'];
    $branch_id = $l['current_branch'];
    // $total_repaid = $l['total_repaid']; /////------We are deducting other charges from total repaid
    $loan_balance = $l['loan_balance'];
    $total_addons = $l['total_addons'];
    $total_deductions = $l['total_deductions'];

    $total_repaid_ = $l['total_repaid'] + $total_deductions;
    $other_charges = $total_addons - $interest;
    $tt_adds+=$total_addons;
    $tt_repaid+=$total_repaid_;
    $tt_interest+=$interest;
    $tt_penalty+=$penalty;    /////////////////////---------------COLLECTION RATE FIX
    $tt_other_charges+=$other_charges;

    $total_repayable_amount = $loan_amount + $interest;  ////---Making this the total repayable amount
    $total_repaid = false_zero($total_repaid_ - $other_charges); ////----Total repaid, we remove other charges paid
    $tt_repaid_pi+=$total_repaid;

    $branch_disbursements = obj_add($branch_disbursements, $branch_id, $loan_amount);

    $branch_repayables = obj_add($branch_repayables, $branch_id, $total_repayable_amount);
    $branch_repaid = obj_add($branch_repaid, $branch_id, $total_repaid);
    $branch_balance = obj_add($branch_balance, $branch_id, false_zero($loan_balance));
    $branch_penalties = obj_add($branch_penalties, $branch_id, $penalty);  //////////////---------------COLLECTION RATE FIX


}


//echo "Addons".number_format($tt_adds).", Paid_all".number_format($tt_repaid), "Repaid_pi:".number_format($tt_repaid_pi), "Interest:".number_format($tt_interest)." Other Charges:".money($tt_other_charges);

?>

<table class="tablex">
    <thead>
    <tr><th>Branch</th><th>Total Disbursed</th><th>Total Repayable</th><th>Collected</th><th>Deficit</th><th class="pull-right">Rate %</th></tr>

    </thead>
    <tbody>

    <?php
    foreach ($branch_names as $bid => $bname ){

        $disb = $branch_disbursements[$bid];
        $branch_repayable = $branch_repayables[$bid];
        $collected = $branch_repaid[$bid];
        $deficit = false_zero($branch_balance[$bid]);
        $penalty = $branch_penalties[$bid]; //////////////////////-------FIX COLLECTION RATE

        $disb_t+=$disb;
        $branch_repayable_t+=$branch_repayable;
        $collected_t+=$collected;
        $penalty_t+=$penalty;
        $deficit_t+=$deficit;

        $rate = roundDown((($collected/($branch_repayable))*100), 2);



        echo "<tr><td>$bname</td><td>".money($disb)."</td><td>".money($branch_repayable)."</td><td>".money($collected)."</td><td>".money($deficit)."</td><td><span class=\"label bg-gray disabled pull-right text-black font-16 font-bold\"> $rate%</span></td></tr>";

    }
    $rate_a = roundDown((($collected_t/($branch_repayable_t))*100), 2);
   // echo $penalty_t;

    ?>
    </tbody>

    <tfoot class="bg-gray font-bold">

    <?php
    echo "<tr><th>Total</th><th>".money($disb_t)."</th><th>".money($branch_repayable_t)."</th><th>".money($collected_t)."</th><th>".money($deficit_t)."</th><th><span class=\"label bg-purple-gradient pull-right  font-16 font-bold\"> $rate_a%</span></th></tr>";


    ?>
    </tfoot>



</table>

<div class="container-fluid">
    <div style="position: relative; width: 100%; max-width: 600px; margin-top: 10px;">
        <div style="position: absolute;    right: 100px;    top: 0px;    font-weight: bold;    font-size: 14px; color: #63cd4f;    text-shadow: 1px 1px 2px rgb(255 255 255 / 30%);">
            <?php
            echo "$rate_a %";
            ?>
        </div>
        <div style="background-color: #e0e0e0; height: 20px; border-radius: 10px; overflow: hidden;">
            <div style="width: <?php echo $rate_a; ?>%; background-color: #47833e; height: 100%; border-radius: 10px 0 0 10px; transition: width 0.5s;">
                <!-- Green progress bar -->
            </div>
        </div>
    </div>
</div>