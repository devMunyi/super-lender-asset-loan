<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");



$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');


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

$branches = table_to_obj('o_branches',"status=1 $andbranch","1000", "uid","name");


//////------------Daily Dues
$total_repayable_amount_daily_array = array();
$total_repayable_amount_monthly_array = array();
$total_repaid_daily_array = array();
$total_repaid_monthly_array = array();

$total_repayable_branch_daily_array = array();
$total_repayable_branch_monthly_array = array();
$total_repaid_branch_daily_array = array();
$total_repaid_branch_daily_array_pi = array();
$total_repaid_branch_monthly_array = array();
$total_repayable_amount_daily_pi = array();

//--------Initialize daily dues with 0
for($in=0; $in<=31; ++$in){
    $total_repayable_amount_daily_array[leading_zero($in)] = 0;
    $total_repaid_daily_array[leading_zero($in)] = 0;
        foreach ($branches as $bid => $bname) {
            $total_repayable_branch_daily_array[$bid][leading_zero($in)] = 0;
            $total_repaid_branch_daily_array[$bid][leading_zero($in)] = 0;
           }
}
///-------Initialize monthly dues with 0
for($im=0; $im<=12; ++$im){
    $total_repayable_amount_monthly_array[leading_zero($im)] = 0;
    $total_repaid_monthly_array[leading_zero($im)] = 0;
    foreach ($branches as $bid => $bname) {
        $total_repayable_branch_monthly_array[$bid][leading_zero($im)] = 0;
        $total_repaid_branch_monthly_array[$bid][leading_zero($im)] = 0;
    }
}
$penalties_daily_array = array();
////-------------Daily
////------Collection rate special calculator
if($collection_rate_exclude_penalties == 1) {
    $penalties_addons = table_to_array('o_addons', "status=1 AND (addon_category != 'INTEREST' OR addon_category is null)", "100", "uid");
    $penalties_addons_list = implode(',', $penalties_addons);

    $all_loans_list = implode(',', table_to_array('o_loans', "disbursed=1 AND final_due_date >= '$this_year-$this_month-01' $andbranch_loans", "1000000", "uid"));

    $other_addons_array = array();
    $all_other_addons = fetchtable('o_loan_addons', "loan_id in ($all_loans_list) AND addon_id in ($penalties_addons_list) AND status=1", "uid", "asc", "1000000", "uid, loan_id, addon_amount");
    while ($as = mysqli_fetch_array($all_other_addons)) {
        $auid = $as['uid'];
        $alid = $as['loan_id'];
        $aamount = $as['addon_amount'];
        $other_addons_array = obj_add($other_addons_array, $alid, $aamount);
    }
}
////------End Collection rate special calculator

$loans_daily_dues = fetchtable('o_loans',"disbursed=1 AND final_due_date >= '$this_year-$this_month-01' $andbranch_loans","uid","asc","100000000","uid, customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, final_due_date, current_agent, current_branch, status");
while($dd = mysqli_fetch_array($loans_daily_dues)){
    $uid = $dd['uid'];
    $final_due_date = $dd['final_due_date'];
    $current_branch = $dd['current_branch'];
    $final_due_date_array = explode('-', $final_due_date);
    $due_day = $final_due_date_array[2];

    if($this_year == $final_due_date_array[0] && $this_month == $final_due_date_array[1]) {
        $total_repayable_amount_daily = $dd['total_repayable_amount'];
        $total_repaid_daily = $dd['total_repaid'];

         //echo "$due_day, $total_repayable_amount_daily, $total_repaid_daily <br/> ";

        if($collection_rate_exclude_penalties == 1) {
            $pens = $other_addons_array[$uid];
          //  $total_repaid_daily = $total_repaid_daily - $pens;
           // $total_repayable_amount_daily_pi = $total_repayable_amount_daily_pi - $pens;

            $penalties_daily_array = obj_add($penalties_daily_array, $due_day, $pens);
        }

        ////////////--------------------
        $total_repayable_amount_daily_array = obj_add($total_repayable_amount_daily_array, $due_day, $total_repayable_amount_daily);
        $total_repaid_daily_array = obj_add($total_repaid_daily_array, $due_day, $total_repaid_daily);

        ////////////--------------------Per Branch
        $total_repayable_branch_daily_array = obj_add_nest($total_repayable_branch_daily_array, $current_branch,$due_day, $total_repayable_amount_daily);

        $total_repaid_branch_daily_array = obj_add_nest($total_repaid_branch_daily_array, $current_branch,$due_day, $total_repaid_daily);



    }
}


///------------Monthly
///

////------Collection rate special calculator
if($collection_rate_exclude_penalties == 1) {
    $penalties_addons = table_to_array('o_addons', "addon_category!='INTEREST' AND status=1", "100", "uid");
    $penalties_addons_list = implode(',', $penalties_addons);

    $all_loans_list = implode(',', table_to_array('o_loans', "disbursed=1 AND given_date >= '$this_year-01-01' $andbranch_loans", "1000000", "uid"));

    $other_addons_array = array();
    $all_other_addons = fetchtable('o_loan_addons', "loan_id in ($all_loans_list) AND addon_id in ($penalties_addons_list) AND status=1", "uid", "asc", "1000000", "uid, loan_id, addon_amount");
    while ($as = mysqli_fetch_array($all_other_addons)) {
        $auid = $as['uid'];
        $alid = $as['loan_id'];
        $aamount = $as['addon_amount'];
        $other_addons_array = obj_add($other_addons_array, $alid, $aamount);
    }
}
////------End Collection rate special calculator


/// //////-------This query is purposely wrong to use given_date in place of final_due date. Its a hot fix
$loans_monthly_dues = fetchtable('o_loans',"disbursed=1 AND given_date >= '$this_year-01-01' $andbranch_loans","uid","asc","100000000","uid, customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, final_due_date, current_agent, current_branch, status");
while($md = mysqli_fetch_array($loans_monthly_dues)){
    $uid = $md['uid'];
    $final_due_date = $md['given_date'];
    $current_branch = $md['current_branch'];
    $final_due_date_array = explode('-', $final_due_date);
    $due_month = $final_due_date_array[1];

    if($this_year == $final_due_date_array[0]) {
        $total_repayable_amount_monthly = $md['total_repayable_amount'];
        $total_repaid_monthly = $md['total_repaid'];

        //echo "$due_day, $total_repayable_amount_daily, $total_repaid_daily <br/> ";

        if($collection_rate_exclude_penalties == 1) {
          //  $pens = $other_addons_array[$uid];
           // $total_repaid_monthly = $total_repaid_monthly - $pens;
        }
        $total_repayable_amount_monthly_array = obj_add($total_repayable_amount_monthly_array, $due_month, $total_repayable_amount_monthly);
        $total_repaid_monthly_array = obj_add($total_repaid_monthly_array, $due_month, $total_repaid_monthly);

        ///////Per branch
        $total_repayable_branch_monthly_array = obj_add_nest($total_repayable_branch_monthly_array, $current_branch,$due_month, $total_repayable_amount_monthly);
        $total_repaid_branch_monthly_array = obj_add_nest($total_repaid_branch_monthly_array, $current_branch,$due_month, $total_repaid_monthly);

    }
}


if($collection_rate_exclude_penalties == 1) {
    $monthly_rates_array = array();
    $rates = fetchtable('o_report_summary_monthly',"start_date >= '$this_year-01-01' AND title='Monthly Collection Rate' AND status=1 AND fld_ = 'business'","uid","asc","100000","uid,val_,amount_");
    while($r = mysqli_fetch_array($rates)){

        $ym = $r['val_'];
        $ym_arr = explode('-', $ym);
        $month_ = $ym_arr[1];
        $amount_ = $r['amount_'];
       $monthly_rates_array[$month_] = $amount_;

      // echo "$month_,";

    }


}
//echo json_encode($monthly_rates_array);
//echo ("start_date >= '$this_year-01-01' AND title='Monthly Collection Rate' AND status=1 AND fld_ = 'business'");

?>

<div class="row">
<div class="col-lg-6" >
    <div class="box box-primary box-solid">
        <div class="box-header">
            <h3 class="box-title">Daily Collections</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body max-height">
           <table class="table table-striped">
               <thead><tr><th>Day</th><th>Due Amount</th><th>Collected</th><th>Balance</th><th>Collection Rate</th></tr></thead>
                <tbody>
                <?php
                $due_amount_total = $paid_amount_total = $balance_total = 0;
                for($i = 1; $i <= $this_day; ++$i){

                 $due_amount = $total_repayable_amount_daily_array[leading_zero($i)];
                 $paid_amount = $total_repaid_daily_array[leading_zero($i)];
                 $balance = $due_amount - $paid_amount;

                 $penalties = $penalties_daily_array[leading_zero($i)];
                 $rate = false_zero(floor(($paid_amount/$due_amount)*100));
                if($collection_rate_exclude_penalties == 1) {
                    $rate = false_zero(floor((($paid_amount- $penalties)/ ($due_amount-$penalties)) * 100));
                }

                 $due_amount_total = $due_amount_total + $due_amount;
                 $paid_amount_total = $paid_amount_total + $paid_amount;
                 $balance_total = $balance_total + $balance;


                    echo "<tr><td>$this_year-$this_month-".leading_zero($i)."</td><td>".money($due_amount)."</td><td>".money($paid_amount)."</td><td>".money($balance)."</td><td class='font-16 font-bold text-black'>".false_zero($rate)."%</td></tr>";

                }
                $rate = floor(($paid_amount_total/$due_amount_total)*100);
                ?>

                </tbody>
               <tfoot><tr><th>Total</th><th><?php echo money($due_amount_total); ?></th><th><?php echo money($paid_amount_total); ?></th><th><?php echo money($balance_total); ?></th><th class='font-24 font-bold text-blue'><?php echo $rate; ?>%</th></tr></tfoot>
           </table>

        </div>
        <!-- /.box-body -->
    </div>
</div>


<div class="col-lg-6">
    <div class="box box-solid">
        <div class="box-header bg-navy">
            <h3 class="box-title">Monthly Collections </i></h3>
        </div>

        <!-- /.box-header -->
        <div class="box-body">
           
            <table style="display: no;" class="table table-striped">
                <thead><tr><th>Month</th><th>Due Amount</th><th>Collected</th><th>Balance</th><th>Collection Rate</th></tr></thead>
                <tbody>
                <?php
                $due_amount_total = $paid_amount_total = $balance_total = 0;
                for($i = 1; $i <= $this_month; ++$i){

                    $due_amount = $total_repayable_amount_monthly_array[leading_zero($i)];
                    $paid_amount = $total_repaid_monthly_array[leading_zero($i)];
                    $balance = $due_amount - $paid_amount;

                if($collection_rate_exclude_penalties == 1) {
                    $rate = $monthly_rates_array[leading_zero($i)];
                }else {
                    $rate = false_zero(floor(($paid_amount / $due_amount) * 100));
                }


                    $due_amount_total = $due_amount_total + $due_amount;
                    $paid_amount_total = $paid_amount_total + $paid_amount;
                    $balance_total = $balance_total + $balance;


                    echo "<tr><td>$this_year-".month_name($i)."</td><td>".money($due_amount)."</td><td>".money($paid_amount)."</td><td>".money($balance)."</td><td class='font-16 font-bold text-black'>".false_zero($rate)."%</td></tr>";

                }
                $rate = floor(($paid_amount_total/$due_amount_total)*100);
                ?>
                </tbody>
                <tfoot><tr><th>Total</th><th><?php echo money($due_amount_total); ?></th><th><?php echo money($paid_amount_total); ?></th><th><?php echo money($balance_total); ?></th><th class='font-24 font-bold text-blue'><?php echo $rate; ?>%</th></tr></tfoot>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
</div>


</div>
<?php
include_once ("../../configs/close_connection.inc");
?>
