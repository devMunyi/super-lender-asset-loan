<?php
$lo_bal = array();
$lo_disbursed = array();
$lo_repaid = array();
$lo_bal_loans = array();
$lo_repayable = array();
$agents_per_branch =array();


$lo = table_to_array('o_users',"user_group='7' AND branch > 1 AND status=1 $andbranch_client","1000","uid","branch");
$co = table_to_array('o_users',"user_group='8' AND branch > 1 AND status=1 $andbranch_client","1000","uid","branch");


$agent_names = table_to_obj('o_users',"uid > 0 $andbranch_client","10000","uid","name");
$agent_branches = table_to_obj('o_users',"uid > 0 $andbranch_client","10000","uid","branch");
$branch_names = table_to_obj('o_branches',"uid > 0 $andbranch1","10000","uid","name");

$branch_targets = table_to_obj('o_targets',"target_type = 'DISBURSEMENTS' AND status=1","1000","group_id","amount");
$lo_list = fetchtable("o_users","status=1 AND user_group='7'","uid","asc","1000","uid, branch");
while($a = mysqli_fetch_array($lo_list)){
    $auid = $a['uid'];
    $ubranch = $a['branch'];
    $agents_per_branch = obj_add($agents_per_branch, $ubranch, 1);
}

$pairs = table_to_obj('o_pairing',"status=1","100000","lo","co");
$q = "disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan";



///////////////////////////////////////////--------------------------------------------------------------CUT
///
$loans_totals_array = array();
$repaid_totals_array = array();
$payments_total_array = array();
$repayable_total_array = array();
$balance_total_array = array();
$interest_array = array();
$defaulted_array = array();
$interest_addons = $membership_addons = $processing_addons = $penalty_addons = $daily_penalty_addons = array();
$branches = table_to_obj('o_branches',"uid > 0 $andbranch1","1000","uid","name");


$loan_l  = table_to_array('o_loans',"$q","10000000","uid","uid","asc");
$loan_list = implode(',', $loan_l);

$all_addons = fetchtable('o_loan_addons',"status=1 AND loan_id in ($loan_list)","uid","asc","1000000000","loan_id, addon_id, addon_amount");
while($aa = mysqli_fetch_array($all_addons)){
    $lid = $aa['loan_id'];
    $addon_id = $aa['addon_id'];
    $addon_amount = $aa['addon_amount'];


    if($addon_id == 3 || $addon_id == 4 || $addon_id == 6){
        ////-----Interest
        $interest_addons[$lid]+= $addon_amount;
    }
    elseif ($addon_id == 1){
        ////----Membership
        $membership_addons[$lid]+= $addon_amount;
    }
    elseif ($addon_id == 2){
        ////----Processing
        $processing_addons[$lid]+= $addon_amount;
    }
    elseif ($addon_id ==  5){
        ////---All penalties
        $penalty_addons[$lid]+= $addon_amount;
    }

}
///
///////////////////////////////////////////--------------------------------------------------------------CUT






?>
    <div class="row">
        <div class="col-sm-12">
            <?php
            $loans_monthly = fetchtable('o_loans',"$q","loan_balance","desc","100000000","uid, loan_amount, total_repaid, total_repayable_amount, other_info,loan_balance, current_lo, current_co, total_addons");
            while($dm = mysqli_fetch_array($loans_monthly)) {
                //////////----Fetch required data
                $lid = $dm['uid'];
                $loan_amount = $dm['loan_amount'];
                $total_repaid = $dm['total_repaid'];
                $loan_balance = $dm['loan_balance'];
                $repayable = $dm['total_repayable_amount'];
                $total_addons = $dm['total_addons'];

                $penalty_amount = $penalty_addons[$lid];

                $sec = $dm['other_info'];
                $sec_obj = (json_decode($sec, true));
                $interest = $sec_obj['INTEREST_AMOUNT'];
                $other_charges = $total_addons - $interest;
                $pi_repayable = $loan_amount + $interest;
                $pi_repaid  = $total_repaid - ($other_charges-$penalty_amount);
                


                $current_lo = $dm['current_lo'];
                $current_co = $dm['current_co'];

                $lo_bal = obj_add($lo_bal, $current_lo, $loan_balance);
                $lo_bal = obj_add($lo_bal, $current_co, $loan_balance);

                $lo_disbursed = obj_add($lo_disbursed, $current_lo, $loan_amount);
                $lo_disbursed = obj_add($lo_disbursed, $current_co, $loan_amount);

                $lo_repaid = obj_add($lo_repaid, $current_lo, $pi_repaid);
                $lo_repaid = obj_add($lo_repaid, $current_co, $pi_repaid);

                $lo_repayable = obj_add($lo_repayable, $current_lo, $pi_repayable);
                $lo_repayable = obj_add($lo_repayable, $current_co, $pi_repayable);

                $lo_bal_loans = obj_add($lo_bal_loans, $current_lo, 1);
                $lo_bal_loans = obj_add($lo_bal_loans, $current_co, 1);

               // echo "Loan: $lid, Amt: $loan_amount, Rep:$pi_repayable, Paid: $pi_repaid <br/>";
            }
            ?>
            <h4>Loan Agents</h4>
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead class="bg-primary bg-black-gradient">
                <tr><th>Pair</th><th>Branch</th><th>*Disb Target</th><th>Total Disbursed</th><th>Deficit</th><th>*Disb Rate</th><th>*Active Customer Target</th><th>*Active Customer Actual</th><th>*Active Customer Rate</th> <th>Total Paid</th> <th>Total Balance</th><th>Coll.  Rate</th><th>Total Loans</th></tr>
                </thead>
                <tbody>
                <?php
                $total_repayable_total = $agent_target_total = $agent_target_customers_total = 0;

                for($i = 0; $i < sizeof($lo); ++$i){
                    $agent_id = $lo[$i];

                    $pair_id = $pairs[$agent_id];

                    $agent_branch = $agent_branches[$agent_id];
                    $branch_target = $branch_targets[$agent_branch];
                    $total_agents = $agents_per_branch[$agent_branch];
                    $agent_target = ceil(($branch_target / $total_agents));
                    $agent_target_customers = ceil($agent_target / 10000);

                    $agent_name = $agent_names[$agent_id];
                    $pair_name = $agent_names[$pair_id];
                    $agent_branch_name = $branch_names[$agent_branch];
                    $total_disbursed = $lo_disbursed[$agent_id];
                    $total_repayable = $lo_repayable[$agent_id];
                    $total_repaid = $lo_repaid[$agent_id];
                    $total_balance = $lo_bal[$agent_id];
                    $total_loans = $lo_bal_loans[$agent_id];


                    $rate_ = round((($total_repaid/$total_repayable))*100, 2);
                    $disb_rate = round((($total_disbursed/$agent_target))*100, 2);
                    $active_rate = round((($total_loans/$agent_target_customers))*100, 2);
                    $total_deficit = $agent_target - $total_disbursed;

                    $total_disbursed_total = $total_disbursed_total + $total_disbursed;
                    $total_repaid_total = $total_repaid_total + $total_repaid;
                    $total_balance_total = $total_balance_total + $total_balance;
                    $total_loans_total = $total_loans_total + $total_loans;
                    $total_repayable_total = $total_repayable_total + $total_repayable;
                    $total_deficit_total = $total_deficit_total + $total_deficit;

                    $agent_target_total+=$agent_target;
                    $agent_target_customers_total+=$agent_target_customers;

                    echo " <tr><td><b>LO:</b> $agent_name <br/> <b>CO:</b> $pair_name</td><td>$agent_branch_name</td><td>".money($agent_target)."</td><td>".money($total_disbursed)."</td><td>".money($total_deficit)."</td><td>".$disb_rate."%</td> <td>".$agent_target_customers."</td> <td>".$total_loans."</td> <td>".$active_rate."%</td> <td>".money($total_repaid)."</td> <td>".money($total_balance)."</td><td>".round($rate_, 2)."%</td> <td>".$total_loans."</td></tr>";

                }
                $rate_a = round((($total_repaid_total/$total_repayable_total)*100), 2);
                $disb_rate_total = round((($total_disbursed_total/$agent_target_total)*100), 2);
                $active_rate_total = round((($total_loans_total/$agent_target_customers_total)*100), 2);

                ?>

                </tbody>
                <tfoot>
                <tr><th>Total</th><th>--</th><th><?php echo money($agent_target_total); ?></th> <th><?php echo money($total_disbursed_total); ?></th> <th><?php echo money($total_deficit_total); ?></th>  <td><?php echo $disb_rate_total; ?>%</td><th><?php echo $agent_target_customers_total; ?></th><th><?php echo $total_loans_total; ?></th><th><?php echo ($active_rate_total); ?>%</th><th><?php echo money($total_repaid_total); ?></th> <th><?php echo money($total_balance_total); ?></th><th><?php echo $rate_a; ?>%</th><th><?php echo $total_loans_total; ?></th></tr>
                </tfoot>
            </table>
        </div>


    </div>

<?php

