<?php

$bdo_leads = array();
$leads = fetchtable('o_customers',"status in (3) $andbranch_client AND added_date >= '$start_date 00:00:00' AND added_date <= '$end_date 23:59:59'","uid","asc","1000000","uid, branch, added_date, added_by");
while($l = mysqli_fetch_array($leads)){
    $lid = $l['uid'];
    $branch = $l['branch'];
    $added_date = $l['added_date'];
    $added_by = $l['added_by'];
    $bdo_leads = obj_add($bdo_leads, $added_by, 1);

}


$branch_loans = array();
$branch_names = table_to_obj('o_branches',"uid > 0","1000","uid","name");
$staff_names = table_to_obj('o_users',"uid > 0 AND status=1 AND user_group in (7, 8)","1000","uid","name");
$pairs = table_to_obj('o_pairing',"status=1","10000","lo","co");

$customer_l = table_to_array('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' AND disbursed=1 AND status!=0 $andbranch_loan","1000000","customer_id");
$customer_list = implode(',', $customer_l);
$customer_loans = table_to_obj('o_customers',"uid in ($customer_list)","1000000","uid","total_loans");
$customer_branches = table_to_obj('o_customers',"uid in ($customer_list)","1000000","uid","branch");


$new_bdo_loans = array();

$new_bdo_loans_sum = array();
$repeat_bdo_loans = array();
$repeat_bdo_loans_sum = array();

// unique customer uids tracker
$new_cust_uids_tracker = array();
$repeat_cust_uids_tracker = array();

/////----------Loans taken before

$older_customer_loans = array();
$during_customer_loans = array();
$loans_older = fetchtable('o_loans',"given_date < '$start_date'  $andbranch_loan AND disbursed=1 AND status!=0 AND customer_id in ($customer_list)","uid","asc","1000000","uid, current_branch, current_lo,customer_id, loan_amount");
while($p = mysqli_fetch_array($loans_older)) {
    $loid = $p['uid'];
    $branch_l = $p['current_branch'];
    $customer_id = $p['customer_id'];
    $current_lo = $p['current_lo'];
    $older_customer_loans = obj_add($older_customer_loans, $customer_id, 1); ///Loans taken before this period selected

}


///------------End of loans taken before
///------------Loans taken during
$loans_during = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date'  $andbranch_loan AND disbursed=1 AND status!=0 AND customer_id in ($customer_list)","uid","asc","1000000","uid, current_branch, current_lo,customer_id, loan_amount");
while($d = mysqli_fetch_array($loans_during)) {
    $loid = $d['uid'];
    $branch_l = $d['current_branch'];
    $customer_id = $d['customer_id'];
    $current_lo = $d['current_lo'];
    $during_customer_loans = obj_add($during_customer_loans, $customer_id, 1); ///Loans taken during this period

}


///-------------End of loans taken during


$loans = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan AND disbursed=1 AND status!=0","uid","asc","1000000","uid, current_branch, current_lo,customer_id, loan_amount");
while($l = mysqli_fetch_array($loans)){
    $loid = $l['uid'];
    $branch_l = $l['current_branch'];
    $customer_id = $l['customer_id'];
    $current_lo = $l['current_lo'];
    $loan_amount = $l['loan_amount'];

    $customer_older_loans = $older_customer_loans[$customer_id];
    $customer_during_loans = $during_customer_loans[$customer_id];
    //$customer_total_loans = $customer_loans[$customer_id];
    $customer_total_loans = $customer_older_loans + $customer_during_loans;
    $customer_lo = $customer_branches[$customer_id];
    if($customer_during_loans > 0 && $customer_older_loans < 1){
        $new_bdo_loans_sum = obj_add($new_bdo_loans_sum, $current_lo, $loan_amount);

        if(!isset($new_cust_uids_tracker[$customer_id])){
            $new_cust_uids_tracker[$customer_id] = 1;
            $new_bdo_loans = obj_add($new_bdo_loans, $current_lo, 1);

        }
    }
    else if($customer_during_loans > 0 && $customer_older_loans > 0){
        $repeat_bdo_loans_sum = obj_add($repeat_bdo_loans_sum, $current_lo, $loan_amount);

        if(!isset($repeat_cust_uids_tracker[$customer_id])){
            $repeat_cust_uids_tracker[$customer_id] = 1;
            $repeat_bdo_loans = obj_add($repeat_bdo_loans, $current_lo, 1);
        }

    }
}

?>

        <div class="col-sm-12">
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>ID</th><th>BDO</th><th>Branch</th><th>New Leads</th> <th>New Customers</th><th>New Total Loans</th> <th>Repeat Customers</th><th>Repeat Total Loans</th><th>Active Customers</th><th>All Total Loans</th></tr>
                </thead>
                  <tbody>
                  <?php
                  $total_leads = $total_new_customers = $total_repeat_loans = $total_active_customers = $new_sum = $repeat_sum = $total_loans = $total_total_loans =0;
                  $bdos = fetchtable('o_users',"user_group = 7 $andbranch_client AND status = 1","branch","asc","10000","uid, name, branch");

                  $bdo_uids_tracker = array();
                  while($b = mysqli_fetch_array($bdos)){
                        $bid = $b['uid'];
                        $branch = $b['branch']; // to allow ordering by branch
                        $bdoname = trim($b['name']);
                        $bdo_branch = $b['branch'];  $branch_name  = $branch_names[$bdo_branch];
                        $new_leads = intval($bdo_leads[$bid]);

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
                      $active_customers = $new_loans + $repeat_loans;
                      $total_active_customers += $active_customers;


                      if(!isset($bdo_uids_tracker[$bid])){
                        echo "<tr><td>$branch</td><td>$bdoname $co_name</td><td>$branch_name</td><td>$new_leads</td><td>$new_loans</td><td>".number_format($new_loans_sum,2)."</td><td>$repeat_loans</td><td>".number_format($repeat_loans_sum,2)."</td><td>$active_customers</td><td>".number_format($total_loans,2)."</td></tr>";
                        $total_loans = 0;

                        $bdo_uids_tracker[$bid] = 1;
                      }                      
                  }
                  ?>
                  </tbody>

                <tfoot>
                <tr><th>--</th><th>--</th><th>--</th><th><?php echo number_format($total_leads); ?></th> <th><?php echo number_format($total_new_customers); ?></th> <th><?php echo number_format($new_sum, 2); ?></th> <th><?php echo number_format($total_repeat_loans); ?></th><th><?php echo number_format($repeat_sum, 2); ?></th><th><?php echo number_format($total_active_customers); ?></th><th><?php echo number_format($repeat_sum + $new_sum) ?></th></tr>
                </tfoot>
            </table>


        </div>

    </div>

<?php
