<?php

$bdo_leads = array();
$leads = fetchtable('o_customers',"status in (1,3) $andbranch_client AND date(added_date) >= '$start_date' AND date(added_date) <= '$end_date'","uid","asc","1000000","uid, branch, added_date, added_by");
while($l = mysqli_fetch_array($leads)){
    $lid = $l['uid'];
    $branch = $l['branch'];
    $added_date = $l['added_date'];
    $added_by = $l['added_by'];
    $bdo_leads = obj_add($bdo_leads, $added_by, 1);

}


$branch_loans = array();
$branch_names = table_to_obj('o_branches',"uid > 0","1000","uid","name");
$staff_names = table_to_obj('o_users',"uid > 0","1000","uid","name");
$pairs = table_to_obj('o_pairing',"status=1","10000","lo","co");

$customer_l = table_to_array('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' AND disbursed=1 AND status!=0 $andbranch_loan","1000000","customer_id");
$customer_list = implode(',', $customer_l);
$customer_loans = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","total_loans");
$customer_branches = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","branch");


$new_bdo_loans = array();
$new_bdo_loans_sum = array();
$repeat_bdo_loans = array();
$repeat_bdo_loans_sum = array();
$loans = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan AND disbursed=1 AND status!=0","uid","asc","1000000","uid, current_branch, current_lo,customer_id, loan_amount");
while($l = mysqli_fetch_array($loans)){
    $loid = $l['uid'];
    $branch_l = $l['current_branch'];
    $customer_id = $l['customer_id'];
    $current_lo = $l['current_lo'];
    $loan_amount = $l['loan_amount'];
    $customer_total_loans = $customer_loans[$customer_id];
    $customer_lo = $customer_branches[$customer_id];
    if($customer_total_loans == 1){
        $new_bdo_loans = obj_add($new_bdo_loans, $current_lo, 1);
        $new_bdo_loans_sum = obj_add($new_bdo_loans_sum, $current_lo, $loan_amount);
    }
    else if($customer_total_loans > 1){
        $repeat_bdo_loans = obj_add($repeat_bdo_loans, $current_lo, 1);
        $repeat_bdo_loans_sum = obj_add($repeat_bdo_loans_sum, $current_lo, $loan_amount);
    }
}

?>

        <div class="col-sm-12">
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>ID</th><th>BDO</th><th>Branch</th><th>New Leads</th> <th>New Customers</th><th>New Total Loans</th> <th>Repeat Customers</th><th>Repeat Total Loans</th><th>All Total Loans</th></tr>
                </thead>
                  <tbody>
                  <?php
                  $total_leads = $total_new_customers = $total_repeat_loans = $new_sum = $repeat_sum = $total_loans = $total_total_loans =0;
                  $bdos = fetchtable('o_users',"user_group = 7 $andbranch_staff AND status=1","uid","asc","1000","uid, name, branch");
                  while($b = mysqli_fetch_array($bdos)){
                        $bid = $b['uid'];
                        $bdoname = $b['name'];
                        $bdo_branch = $b['branch'];  $branch_name  = $branch_names[$bdo_branch];
                        $new_leads = false_zero($bdo_leads[$bid]);

                        $pair = $pairs[$bid];
                        if($pair > 0){
                            $co_name = " & ".$staff_names[$pair];
                        }
                        else{
                            $co_name = "";
                        }

                      $new_loans = false_zero($new_bdo_loans[$bid]);
                      $repeat_loans = false_zero($repeat_bdo_loans[$bid]);



                      $new_loans_sum = $new_bdo_loans_sum[$bid];
                      $repeat_loans_sum = $repeat_bdo_loans_sum[$bid];

                      $total_loans = $new_loans_sum + $repeat_loans_sum;

                      $total_leads+=$new_leads;
                      $total_new_customers+=$new_loans;
                      $total_repeat_loans+=$repeat_loans;
                      $new_sum+=$new_loans_sum;
                      $repeat_sum+=$repeat_loans_sum;


                      echo "<tr><td>$bid</td><td>$bdoname $co_name</td><td>$branch_name</td><td>$new_leads</td><td>$new_loans</td><td>".number_format($new_loans_sum,2)."</td><td>$repeat_loans</td><td>".number_format($repeat_loans_sum,2)."</td><td>".number_format($total_loans,2)."</td></tr>";
                      $total_loans = 0;
                  }
                  ?>
                  </tbody>

                <tfoot>
                <tr><th>ID</th><th>BDO</th><th>Branch</th><th><?php echo number_format($total_leads); ?></th> <th><?php echo number_format($total_new_customers); ?></th> <th><?php echo number_format($new_sum, 2); ?></th> <th><?php echo number_format($total_repeat_loans); ?></th><th><?php echo number_format($repeat_sum, 2); ?></th><th><?php echo number_format($repeat_sum + $new_sum) ?></th></tr>
                </tfoot>
            </table>


        </div>

    </div>

<?php

