<?php
// set arrays to use
$branch_principals = [];
$branch_repayables = [];
$branch_repaid = [];
$branch_balances = [];

$loans = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan AND disbursed=1 AND status!=0","uid","DESC","1000000","current_branch, customer_id, loan_amount, total_repayable_amount, total_repaid, loan_balance");

while($l = mysqli_fetch_array($loans)){
    $branch_l = $l['current_branch'];
    $customer_id = $l['customer_id'];
    $loan_amount = $l['loan_amount'];
    $repayable_amt = $l['total_repayable_amount'];
    $repaid_amt = $l['total_repaid'];
    $loan_bal = $l['loan_balance'];


    $branch_principals = obj_add($branch_balances, $branch_l, $loan_amount);
    $branch_repayables = obj_add($branch_repayables, $branch_l, $repayable_amt);
    $branch_repaid = obj_add($branch_repaid, $branch_l, $repaid_amt);
    $branch_balances = obj_add($branch_balances, $branch_l, $loan_bal);
}

?>

        <div class="col-sm-12">
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>UID</th><th>Branch</th><th>Principal</th> <th>Total Amount Repayable</th><th>Total Amount Repaid</th><th>Balance</th> <th>Rate</th></tr>
                </thead>
                  <tbody>
                  <?php
                  $total_principal = $total_amt_repayable = $total_amt_repaid = $total_balance = 0;
                  $branches = fetchtable('o_branches',"uid > 1 $andbranch1","uid","asc","1000","uid, name");
                  while($b = mysqli_fetch_array($branches)){
                      $bid = $b['uid'];
                      $bname = $b['name'];
                      $b_principals = false_zero($branch_principals[$bid]);
                      $b_repayables = false_zero($branch_repayables[$bid]);
                      $b_repaid = false_zero($branch_repaid[$bid]);
                      $b_balances = false_zero($branch_balances[$bid]);

                      $total_principal += $b_principals;
                      $total_amt_repayable += $b_repayables;
                      $total_amt_repaid += $b_repaid;
                      $total_balance += $b_balances;

                      $b_rate = round((double)(($b_repaid / $b_repayables) * 100), 2);

                      echo "<tr><td>$bid</td><td>$bname</td><td>".number_format($b_principals, 2)."</td><td>".number_format($b_repayables, 2)."</td><td>".number_format($b_repaid, 2)."</td><td>".number_format($b_balances,2)."</td><td>$b_rate%</td></tr>";
                  }

                  $all_rate = round((double)(($total_amt_repaid / $total_amt_repayable) * 100), 2);
                  ?>
                  </tbody>

                <tfoot>
                <tr><th>#</th><th>--</th><th><?php echo number_format($total_principal, 2); ?></th> <th><?php echo number_format($total_amt_repayable, 2); ?></th><th><?php echo number_format($total_amt_repaid, 2); ?></th><th><?php echo number_format($total_balance, 2); ?></th> <th><?php echo $all_rate."%"; ?></th></tr>
                </tfoot>
            </table>


        </div>

    </div>

<?php

