<?php

$branch_leads = array();
$leads = fetchtable('o_customers',"status in (1,3) $andbranch_client AND date(added_date) >= '$start_date' AND date(added_date) <= '$end_date'","uid","asc","1000000","uid, branch, added_date");
while($l = mysqli_fetch_array($leads)){
    $lid = $l['uid'];
    $branch = $l['branch'];
    $added_date = $l['added_date'];
    $branch_leads = obj_add($branch_leads, $branch, 1);

}

$customer_l = table_to_array('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' AND disbursed=1 AND status!=0 $andbranch_loan","1000000","customer_id");
$customer_list = implode(',', $customer_l);
$customer_loans = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","total_loans");
$customer_branches = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","branch");


$new_branch_loans = array();
$new_branch_loans_sum = array();
$repeat_branch_loans = array();
$repeat_branch_loans_sum = array();
$active_custs = []; // store already iterated active customers
$active_custs_branches = []; // to store key value pair branch uid => customer uid
$loans = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan AND disbursed=1 AND status!=0","uid","asc","1000000","uid, current_branch, customer_id, loan_amount");
while($l = mysqli_fetch_array($loans)){
    $loid = $l['uid'];
    $branch_l = $l['current_branch'];
    $customer_id = $l['customer_id'];
    $loan_amount = $l['loan_amount'];
    $customer_total_loans = $customer_loans[$customer_id];
    $customer_branch = $customer_branches[$customer_id];
    
    if($customer_total_loans == 1){
        $new_branch_loans = obj_add($new_branch_loans, $branch_l, 1);
        $new_branch_loans_sum = obj_add($new_branch_loans_sum, $branch_l, $loan_amount);
    }
    else if($customer_total_loans > 1){
        $repeat_branch_loans = obj_add($repeat_branch_loans, $branch_l, 1);
        $repeat_branch_loans_sum = obj_add($repeat_branch_loans_sum, $branch_l, $loan_amount);
    }

    // add active customer to array if not yet added
    if(!isset($active_custs[$customer_id])){
        $active_custs[$customer_id] = 1;
        $active_custs_branches = obj_add($active_custs_branches, $branch_l, 1);
    }
}

?>

        <div class="col-sm-12">
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>ID</th><th>Branch</th><th>New Leads</th> <th>New Customers</th><th>Active Customers</th><th>New Total Loans</th> <th>Repeat Customers</th><th>Repeat Total Loans</th><th>All Total Loans</th></tr>
                </thead>
                  <tbody>
                  <?php
                  $total_leads = $total_new_customers = $total_active_customers = $total_repeat_loans = $new_sum = $repeat_sum = $total_loans = $total_total_loans =0;
                  $branches = fetchtable('o_branches',"uid > 1 $andbranch1","uid","asc","1000","uid, name");
                  while($b = mysqli_fetch_array($branches)){
                      $bid = $b['uid'];
                      $bname = $b['name'];
                      $new_leads = false_zero($branch_leads[$bid]);
                      $new_loans = false_zero($new_branch_loans[$bid]);
                      $active_custs_b = false_zero($active_custs_branches[$bid]);
                      $repeat_loans = false_zero($repeat_branch_loans[$bid]);

                      $new_loans_sum = $new_branch_loans_sum[$bid];
                      $repeat_loans_sum = $repeat_branch_loans_sum[$bid];

                      $total_loans = $new_loans_sum + $repeat_loans_sum;

                      $total_leads+=$new_leads;
                      $total_new_customers+=$new_loans;
                      $total_active_customers += $active_custs_b;
                      $total_repeat_loans+=$repeat_loans;
                      $new_sum+=$new_loans_sum;
                      $repeat_sum+=$repeat_loans_sum;


                      echo "<tr><td>$bid</td><td>$bname</td><td>$new_leads</td><td>$new_loans</td><td>$active_custs_b</td><td>".number_format($new_loans_sum,2)."</td><td>$repeat_loans</td><td>".number_format($repeat_loans_sum,2)."</td><td>".number_format($total_loans,2)."</td></tr>";
                      $total_loans = 0;
                  }
                  ?>
                  </tbody>

                <tfoot>
                <tr><th>#</th><th>--</th><th><?php echo number_format($total_leads); ?></th> <th><?php echo number_format($total_new_customers); ?></th> <th><?php echo number_format($total_active_customers); ?></th> <th><?php echo number_format($new_sum, 2); ?></th> <th><?php echo number_format($total_repeat_loans); ?></th><th><?php echo number_format($repeat_sum, 2); ?></th><th><?php echo number_format($repeat_sum + $new_sum) ?></th></tr>
                </tfoot>
            </table>


        </div>

    </div>

<?php

