<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$start_date = $_GET['start_date']; ////'2021-01-01';
$end_date = $_GET['end_date'];  //'2021-04-12';


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

/////////--------------
$branches = table_to_obj('o_branches',"status=1 AND uid > 0 $andbranch ","1000", "uid","name");
$branch_disb = array();
$branch_coll = array();
$branch_repayable = array();
$branch_bal = array();
$kpi_measured = table_to_obj('o_user_groups',"uid>0","1000","uid","kpi_measured");
$staff_groups = table_to_obj('o_users',"uid>0","100000","uid","user_group");
$staff_obj = table_to_obj('o_users',"uid>0 $andbranch_staff","100000","uid","name");
$staff_status = table_to_obj('o_users',"uid>0","100000","uid","status");
$staff_disb = array();
$loan_total = array();
$staff_coll = array();
$lo_bal = array();
$co_bal = array();
$products = table_to_obj('o_loan_products',"status=1","1000", "uid","name");
$product_disb = array();
$product_coll = array();
$product_bal = array();

////////////-------------Disbursements
$loans_monthly = fetchtable('o_loans',"disbursed=1 AND given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loans","given_date","asc","100000000","uid, customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, current_branch, current_lo, current_co, status, paid");
while($dm = mysqli_fetch_array($loans_monthly)){
             //////////----Fetch required data
             $paid = $dm['paid'];
             $loan_amount = $dm['loan_amount'];
             $total_repaid = $dm['total_repaid'];
             $loan_balance = $dm['loan_balance'];
             $total_repayable_amount = $dm['total_repayable_amount'];
              ////====Per BDO
              $current_lo = $dm['current_lo'];
              $current_co = $dm['current_co'];
               $staff_disb = obj_add($staff_disb, $current_lo, $loan_amount);
               $staff_coll = obj_add($staff_coll, $current_co, $total_repaid);
               $staff_coll = obj_add($staff_coll, $current_co, $total_repaid);
                $lo_bal = obj_add($lo_bal, $current_lo, $loan_balance);
                $co_bal = obj_add($co_bal, $current_co, $loan_balance);
               

              ////====Per Branch
              $current_branch = $dm['current_branch'];
              $branch_disb = obj_add($branch_disb, $current_branch, $loan_amount);
             $branch_coll = obj_add($branch_coll, $current_branch, $total_repaid);
             $branch_repayable = obj_add($branch_repayable, $current_branch, $total_repayable_amount);
            $branch_bal = obj_add($branch_bal, $current_branch, $loan_balance);
              ////====Per Product
              $loan_product = $dm['product_id'];
              $product_disb =   obj_add($product_disb, $loan_product, $loan_amount);
              $product_coll = obj_add($product_coll, $loan_product, $total_repaid);
            $product_bal = obj_add($product_bal, $loan_product, $loan_balance);
              
            // echo "Loan AMount $loan_amount, Total Paid: $total_repaid LO $current_lo <br/>";

      }

///////---------------collections ----Collected above, might backfire one day because it doesn't account for unresolved payments





?>
<div class="col-md-7">
    <div class="box box-primary box-solid">
        <div class="box-header">
            <h3 class="box-title">Branch Performance</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">

            <table class="table table-condensed table-striped">
                <tr><th>Branch</th><th>Disbursements</th><th>Loan Total</th><th>Collections</th> <th>OutStanding</th><th>Rate</th> </tr>
                <?php
                foreach($branches as $bid => $bname) {
                    $disbursements = $branch_disb[$bid];
                    $collections = $branch_coll[$bid];
                    $outstanding = $branch_bal[$bid];
                    $loan_total = $branch_repayable[$bid];
                    $rate = round(($collections/$loan_total)*100, 2);

                    $branch_total_disb = $branch_total_disb + $disbursements;
                    $branch_total_coll = $branch_total_coll + $collections;
                    $branch_total_bal = $branch_total_bal + $outstanding;
                    $branch_total_repayable = $branch_total_repayable + $loan_total;


                    echo "<tr><td>$bname</td><td>".money($disbursements)."</td><td>".money($loan_total)."</td><td>".money($collections)."</td><td>".money($outstanding)."</td><td class='text-bold'>".$rate."%</td></tr>";
                }
                $rate_av = round(($branch_total_coll/$branch_total_repayable)*100, 2);
                ?>
                <tr class="font-18 text-blue"><th>Total.</th><th><?php echo money($branch_total_disb); ?></th><th><?php echo money($branch_total_repayable); ?></th><th><?php echo money($branch_total_coll); ?></th><th><?php echo money($branch_total_bal); ?></th><th><?php echo money($rate_av); ?>%</th></tr>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
</div>
<div class="col-lg-5">
    <div class="box box-success box-solid">
        <div class="box-header">
            <h3 class="box-title">BDO Performance </h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body max-height">

            <table class="table table-condensed table-striped">
                <tr><th>ID</th><th>BDO</th><th>Disbursements</th><th>Collections</th> <th>Outstanding</th></tr>

                <?php
                foreach($staff_obj as $staff => $staff_name) {
                    $user_group = $staff_groups[$staff];
                    $staff_state = $staff_status[$staff];

                    if($kpi_measured[$user_group] > 0 && $staff_state == 1){
                    $disbursements = $staff_disb[$staff];
                    $collections = $staff_coll[$staff];
                    $balance = $lo_bal[$staff] + $co_bal[$staff];


                    $disbursements_total = $disbursements_total + $disbursements;
                    $collections_total = $collections_total + $collections;
                    $total_balance = $total_balance + $balance;

                    echo "<tr><td>$staff</td><td>$staff_name</td><td>".money($disbursements)."</td><td>".money($collections)."</td><td>".money($balance)."</td></tr>";
                    }
                }
                ?>

                <tr class="font-18 text-blue"><th></th><th>Total.</th><th><?php echo money($disbursements_total); ?></th><th><?php echo money($collections_total); ?></th><th><?php echo money($total_balance); ?></th></tr>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
</div>
<div class="col-lg-5">
    <div class="box box-danger box-solid">
        <div class="box-header">
            <h3 class="box-title">Product Performance</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">

            <table class="table table-striped">
                <tr><th>Product</th><th>Disbursements</th><th>Collections</th><th>Outstanding</th> </tr>
                <?php
                foreach($products as $pid => $pname) {

                    $disbursements = $product_disb[$pid];
                    $collections = $product_coll[$pid];
                    $outstanding = $product_bal[$pid];

                    $product_total_disb = $product_total_disb + $disbursements;
                    $product_total_coll = $product_total_coll + $collections;
                    $product_total_bal = $product_total_bal + $outstanding;
                    echo "<tr><td>$pname</td><td>".money($disbursements)."</td><td>".money($collections)."</td><td>".money($outstanding)."</td></tr>";
                }
                ?>

                <tr class="font-18 text-blue"><th>Total.</th><th><?php echo money($product_total_disb); ?></th><th><?php echo money($product_total_coll); ?></th><th><?php echo money($product_total_bal); ?></th></tr>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
</div>
<?php
include_once ("../../configs/close_connection.inc");

?>