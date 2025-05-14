<?php
$lo_bal = array();
$lo_disbursed = array();
$lo_repaid = array();
$lo_bal_loans = array();
$lo_repayable = array();

$lo = table_to_array('o_users',"tag='LO' AND status=1","1000","uid");
$co = table_to_array('o_users',"tag='CO' AND status=1","1000","uid");
$other = table_to_array('o_users',"tag!='CO' AND tag!='LO' AND status=1","1000","uid");

$agent_names = table_to_obj('o_users',"uid > 0","10000","uid","name");
$agent_branches = table_to_obj('o_users',"uid > 0","10000","uid","branch");
$branch_names = table_to_obj('o_branches',"uid > 0","10000","uid","name");



?>
    <div class="row">
        <div class="col-sm-12">
            <?php
            $loans_monthly = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan ","loan_balance","desc","100000000","loan_amount, total_repaid, total_repayable_amount, loan_balance, current_lo, current_co");
            while($dm = mysqli_fetch_array($loans_monthly)) {
                //////////----Fetch required data
                $loan_amount = $dm['loan_amount'];
                $total_repaid = $dm['total_repaid'];
                $loan_balance = $dm['loan_balance'];
                $repayable = $dm['total_repayable_amount'];


                $current_lo = $dm['current_lo'];
                $current_co = $dm['current_co'];

                $lo_bal = obj_add($lo_bal, $current_lo, $loan_balance);
                $lo_bal = obj_add($lo_bal, $current_co, $loan_balance);

                $lo_disbursed = obj_add($lo_disbursed, $current_lo, $loan_amount);
                $lo_disbursed = obj_add($lo_disbursed, $current_co, $loan_amount);

                $lo_repaid = obj_add($lo_repaid, $current_lo, $total_repaid);
                $lo_repaid = obj_add($lo_repaid, $current_co, $total_repaid);

                $lo_repayable = obj_add($lo_repayable, $current_lo, $repayable);
                $lo_repayable = obj_add($lo_repayable, $current_co, $repayable);

                $lo_bal_loans = obj_add($lo_bal_loans, $current_lo, 1);
                $lo_bal_loans = obj_add($lo_bal_loans, $current_co, 1);
            }
            ?>
            <h4>Loan Agents (LO)</h4>
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>Agent</th><th>Branch</th><th>Total Disbursed</th> <th>Total Paid</th> <th>Total Balance</th><th>Rate</th><th>Total Loans</th></tr>
                </thead>
                <tbody>
                <?php
                $total_repayable_total = 0;
                for($i = 0; $i <= sizeof($lo); ++$i){
                    $agent_id = $lo[$i];
                    $agent_branch = $agent_branches[$agent_id];
                    $agent_name = $agent_names[$agent_id];
                    $agent_branch_name = $branch_names[$agent_branch];
                    $total_disbursed = $lo_disbursed[$agent_id];
                    $total_repayable = $lo_repayable[$agent_id];
                    $total_repaid = $lo_repaid[$agent_id];
                    $total_balance = $lo_bal[$agent_id];
                    $total_loans = $lo_bal_loans[$agent_id];

                    $rate_ = round(($total_repaid/$total_repayable), 2)*100;

                    $total_disbursed_total = $total_disbursed_total + $total_disbursed;
                    $total_repaid_total = $total_repaid_total + $total_repaid;
                    $total_balance_total = $total_balance_total + $total_balance;
                    $total_loans_total = $total_loans_total + $total_loans;
                    $total_repayable_total = $total_repayable_total + $total_repayable;

                    echo " <tr><td>$agent_name</td><td>$agent_branch_name</td><td>".money($total_disbursed)."</td> <td>".money($total_repaid)."</td> <td>".money($total_balance)."</td><td>$rate_%</td> <td>".$total_loans."</td></tr>";

                }
                $rate_a = round(($total_repaid_total/$total_repayable_total), 2)*100;

                ?>

                </tbody>
                <tfoot>
                <tr><th>Total</th><th>--</th><th><?php echo money($total_disbursed_total); ?></th> <th><?php echo money($total_repaid_total); ?></th> <th><?php echo money($total_balance_total); ?></th><th><?php echo $rate_a; ?>%</th><th><?php echo $total_loans_total; ?></th></tr>
                </tfoot>
            </table>
        </div>
        <div class="col-sm-12">
            <h4>Collection Agents (CO)</h4>
            <table class="table table-striped table-bordered table-condensed" id="example3">
                <thead>
                <tr><th>Agent</th><th>Branch</th><th>Total Disbursed</th> <th>Total Paid</th> <th>Total Balance</th><th>Rate</th><th>Total Loans</th></tr>
                </thead>
                <?php
                $i = $total_disbursed = $total_repaid = $total_balance = $total_loans = $total_disbursed_total = $total_repaid_total = $total_balance_total = $total_loans_total = $total_repayable = $rate = 0;

                for($i = 0; $i <= sizeof($co); ++$i){
                    $agent_id = $co[$i];
                    $agent_branch = $agent_branches[$agent_id];
                    $agent_name = $agent_names[$agent_id];
                    $agent_branch_name = $branch_names[$agent_branch];
                    $total_disbursed = $lo_disbursed[$agent_id];
                    $total_repaid = $lo_repaid[$agent_id];
                    $total_balance = $lo_bal[$agent_id];
                    $total_loans = $lo_bal_loans[$agent_id];
                    $total_repayable = $lo_repayable[$agent_id];

                    $rate_ = round(($total_repaid/$total_repayable), 2)*100;

                    $total_disbursed_total = $total_disbursed_total + $total_disbursed;
                    $total_repaid_total = $total_repaid_total + $total_repaid;
                    $total_balance_total = $total_balance_total + $total_balance;
                    $total_loans_total = $total_loans_total + $total_loans;

                    echo " <tr><td>$agent_name </td><td>$agent_branch_name</td><td>".money($total_disbursed)."</td> <td>".money($total_repaid)."</td> <td>".money($total_balance)."</td><td>$rate_%</td><td>".$total_loans."</td></tr>";

                }
                $rate_a = round(($total_repaid_total/$total_repayable_total), 2)*100;
                ?>

                <tfoot>
                <tr><th>Total</th><th>--</th><th><?php echo money($total_disbursed_total); ?></th> <th><?php echo money($total_repaid_total); ?></th> <th><?php echo money($total_balance_total); ?></th><th><?php echo $rate_a; ?>%</th><th><?php echo $total_loans_total; ?></th></tr>
                </tfoot>
            </table>


        </div>

        <div class="col-sm-12">
            <h4>Wrongly Allocated / Allocated to other agents</h4>
            <table class="table table-striped table-bordered table-condensed" id="example3">
                <thead>
                <tr><th>Staff</th><th>Branch</th><th>Total Disbursed</th> <th>Total Paid</th> <th>Total Balance</th><th>Total Loans</th></tr>
                </thead>
                <?php


                $i = $total_disbursed = $total_repaid = $total_balance = $total_loans = $total_disbursed_total = $total_repaid_total = $total_balance_total = $total_loans_total = 0;
                for($i = 0; $i <= sizeof($other); ++$i){
                    $agent_id = $other[$i];
                    $agent_branch = $agent_branches[$agent_id];
                    $agent_name = $agent_names[$agent_id];
                    $agent_branch_name = $branch_names[$agent_branch];
                    $total_disbursed = $lo_disbursed[$agent_id];
                    $total_repaid = $lo_repaid[$agent_id];
                    $total_balance = $lo_bal[$agent_id];
                    $total_loans = $lo_bal_loans[$agent_id];

                    $total_disbursed_total = $total_disbursed_total + $total_disbursed;
                    $total_repaid_total = $total_repaid_total + $total_repaid;
                    $total_balance_total = $total_balance_total + $total_balance;
                    $total_loans_total = $total_loans_total + $total_loans;

                    if($total_disbursed > 0) {

                        echo " <tr><td>$agent_name </td><td>$agent_branch_name</td><td>" . money($total_disbursed) . "</td> <td>" . money($total_repaid) . "</td> <td>" . money($total_balance) . "</td><td>" . $total_loans . "</td></tr>";
                    }

                }
                ?>

                <tfoot>
                <tr><th>Total</th><th>--</th><th><?php echo money($total_disbursed_total); ?></th> <th><?php echo money($total_repaid_total); ?></th> <th><?php echo money($total_balance_total); ?></th><th><?php echo $total_loans_total; ?></th></tr>
                </tfoot>
            </table>


        </div>

    </div>

<?php

