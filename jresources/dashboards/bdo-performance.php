<?php
session_start();

include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();
$year = $_GET['year'];
$month = $_GET['month'];
$branch = $_GET['branch'];
$product = $_GET['product'];

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


$bdo_list = array();
$bdo_array = array();
$lo_list = array();
?>
<?php
$bdos = fetchtable('o_users',"status=1 $andbranch_staff","branch","asc","100000","uid, name, user_group, branch");
while($b = mysqli_fetch_array($bdos)){
    $uid = $b['uid'];
    $name = $b['name'];
    $user_group = $b['user_group'];
    $branch = $b['branch'];
    $bdo_names[$uid]  = $name;
    array_push($bdo_array, $uid);
    if($user_group == 3){
        array_push($lo_list, $uid);
    }
}

$branch_targets_array = table_to_obj('o_targets',"target_group='BRANCH' AND target_type='DISBURSEMENTS' AND status=1","1000","group_id","amount");

$pair = table_to_obj('o_pairing',"status=1","1000","lo","co");

$bdo_branches = table_to_obj('o_users',"status!=0 $andbranch_staff","100000","uid","branch");
$branch_name_array = table_to_obj('o_branches',"status=1 $andbranch","1000","uid","name");

$client_target_array = table_to_obj('o_targets',"target_group='BRANCH' AND target_type='NEW_CUSTOMERS' AND status=1","1000","group_id","amount");
$disb_target_array = table_to_obj('o_targets',"target_group='BDO' AND target_type='DISBURSEMENTS' AND status=1","1000","group_id","amount");

$client_actual_array = array();
$all_clients = fetchtable('o_customers',"status=1 AND year(added_date) = '$year' AND month(added_date) = '$month' $andbranch_customers","uid","asc","1000000","uid, added_by");
while($c = mysqli_fetch_array($all_clients)){
    $cid = $c['uid'];
    $added_by = $c['added_by'];
    $client_actual_array = obj_add($client_actual_array, $added_by, 1);

}

$loan_disb_array = array();
$loans_disb = fetchtable('o_loans',"disbursed=1 AND status!=0 AND year(given_date) = '$year' AND month(given_date) = '$month' $andbranch_loans","uid","asc","1000000","loan_amount, current_lo, current_co");
while($l = mysqli_fetch_array($loans_disb)){
    $loan_amount = $l['loan_amount'];
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    $loan_disb_array = obj_add($loan_disb_array, $current_lo, $loan_amount);
}

$loans_due_array = array();
$loans_paid_array = array();
$balance_array = array();
$defaulted_array = array();
$arrears_array = array();
$loans_due = fetchtable('o_loans',"disbursed=1 AND status!=0  AND year(final_due_date) = '$year' AND month(final_due_date) = '$month' $andbranch_loans","uid","asc","1000000","loan_amount, current_lo, total_repayable_amount, total_repaid, current_co, loan_balance, status");
while($ld = mysqli_fetch_array($loans_due)){
    $total_repayable_amount = $ld['total_repayable_amount'];
    $current_lo = $ld['current_lo'];
    $current_co = $ld['current_co'];
    $total_repaid = $ld['total_repaid'];
    $loan_balance = $ld['loan_balance'];

    $status = $ld['status'];

    $loans_due_array = obj_add($loans_due_array, $current_lo, $total_repayable_amount);
    $loans_paid_array = obj_add($loans_paid_array, $current_lo, $total_repaid);
    $balance_array = obj_add($balance_array, $current_lo, $loan_balance);

    if($status == 7){
        $defaulted_array = obj_add($defaulted_array, $current_lo, $loan_balance);
    }
    if($status == 3){
        $arrears_array = obj_add($arrears_array, $current_lo, $loan_balance);
    }
}

?>
    <table class="table table-bordered table-striped">
        <thead>
        <tr><th>Branch</th><th>BDO</th><th>Client Target</th><th>Actual Client</th><th>Rate</th><th>Disb Target</th><th>Disbursed</th><th>Progress</th><th>Expected</th><th>Collected</th><th>Rate</th><th>Defaulted</th><th>PAR</th><th>Arrears</th></tr>
        </thead>
        <tbody>
        <?php
        $total_client_target = $total_client_actual = $av_client_rate = $total_disb_target = $total_disb_actual = $av_disb_rate = $total_due = $total_collected = $av_collection_rate = $total_defaulted = $total_par = $total_arrears = 0;
         for($i = 0; $i <= sizeof($lo_list); ++$i){
             $lo_id = $lo_list[$i];
             $bdo_name = $bdo_names[$lo_id];
             $co = $bdo_names[$pair[$lo_id]];
             $branch_name = $branch_name_array[$bdo_branches[$lo_id]];


             $client_target = round($client_target_array[$bdo_branches[$lo_id]]/2,0);
             if($client_target < 1){$client_target = 100;}
             $client_actual = $client_actual_array[$lo_id];
             $client_rate = round((($client_actual/$client_target)*100),2);

           ///  $disb_target = $disb_target_array[$lo_id];
             $disb_target = ($branch_targets_array[$bdo_branches[$lo_id]]/2);
             if($disb_target < 1){$disb_target = 2000000;}
             $disb_actual = $loan_disb_array[$lo_id];
             $disb_rate = round((($disb_actual/$disb_target)*100),2);

             $due = $loans_due_array[$lo_id];
             $collected = $loans_paid_array[$lo_id];
             $collection_rate = round((($collected/$due)*100),2);
             $defaulted = $defaulted_array[$lo_id];
             $par = round((($defaulted/$due)*100),2);

             $arrears = $arrears_array[$lo_id];



             echo " <tr><td>$branch_name</td><td>$bdo_name <i class='fa fa-chain'></i> $co</td><td>".money($client_target)."</td><td>".($client_actual)."</td><td class='font-14 label label-default bg-black-gradient'>$client_rate%</td><td>".money($disb_target)."</td><td>".money($disb_actual)."</td><td  class='font-14 label label-default bg-black-gradient'>$disb_rate%</td><td>".money($due)."</td><td>".money($collected)."</td><td  class='font-14 label label-default bg-black-gradient'>$collection_rate%</td><td>".money($defaulted)."</td><td>$par</td><td>".money($arrears)."</td></tr>
";
             $total_client_target = $total_client_target + $client_target;
             $total_client_actual = $total_client_actual + $client_actual;
             $total_disb_target = $total_disb_target + $disb_target;
             $total_disb_actual = $total_disb_actual = $disb_actual;
             $total_due = $total_due + $due;
             $total_collected = $total_collected + $collected;
             $total_defaulted+=$defaulted;
             $total_par+=$par;
             $total_arrears+=$arrears;

         }
         $av_client_rate = round((($total_client_actual/$total_client_target)*100),2);
         $av_disb_rate = round((($total_disb_actual/$total_disb_target)*100),2);
         $av_collection_rate = round((($total_collected/$total_due)*100),2);
        ?>
          </tbody>
        <tfoot>
        <tr><th>Branch</th><th>BDO</th><th><?php echo money($total_client_target); ?></th><th><?php echo ($total_client_actual); ?></th><th><?php echo ($av_client_rate); ?></th><th><?php echo money($total_disb_target)?></th><th><?php echo money($total_disb_actual)?></th><th><?php echo ($av_disb_rate)?></th><th><?php echo $av_collection_rate;?></th><th><?php echo money($total_defaulted);?></th><th><?php echo money($total_par);?></th><th><?php echo money($total_arrears);?></th></tr>
        </tfoot>
    </table>


</div>
<?php
include_once ("../../configs/close_connection.inc");
?>