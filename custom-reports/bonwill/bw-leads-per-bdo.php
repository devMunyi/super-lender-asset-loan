<?php


$start_datetime = date('Y-m-d 00:00:00', strtotime($start_date));
$end_datetime = date('Y-m-d 23:59:59', strtotime($end_date));

$bdo_leads = array();
$leads = fetchtable('o_customers',"status in (1,3)  AND added_date >= '$start_datetime' AND added_date <= '$end_datetime'","uid","asc","1000000","uid, branch, added_date, added_by");
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

$customer_l = table_to_array('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' AND disbursed=1 AND status!=0","1000000","customer_id");
$customer_list = implode(',', $customer_l);
$customer_loans = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","total_loans");
$customer_branches = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","branch");


$new_bdo_loans = array();
$new_bdo_loans_sum = array();
$repeat_bdo_loans = array();
$repeat_bdo_loans_sum = array();
$bdo_collected = array();
$bdo_balance = array();
$bdo_repayable = array();
$loans = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date'  AND disbursed=1 AND status!=0","uid","asc","1000000","uid, current_branch, current_lo,customer_id, loan_amount, total_repaid, loan_balance, total_repayable_amount, added_by");
while($l = mysqli_fetch_array($loans)){
    $loid = $l['uid'];
    $branch_l = $l['current_branch'];
    $customer_id = $l['customer_id'];
    $current_lo = intval($l['current_lo']) > 0 ? intval($l['current_lo']) : intval($l['added_by']);
    $loan_amount = $l['loan_amount'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = $l['loan_balance'];
    $total_repayable_amount = $l['total_repayable_amount'];

    $bdo_collected =obj_add($bdo_collected, $current_lo, $total_repaid);
    $bdo_balance = obj_add($bdo_balance, $current_lo, $loan_balance);
    $bdo_repayable = obj_add($bdo_repayable, $current_lo, $total_repayable_amount);
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
                <tr><th>ID</th><th>BDO</th><th>Branch</th><th>New Leads</th> <th>New Customers</th><th>New Total Loans</th> <th>Repeat Customers</th><th>Repeat Total Loans</th><th>All Total Loans</th><th>Total Repayable</th><th>Paid</th><th>Balance</th><th>Rate</th></tr>
                </thead>
                  <tbody>
                  <?php
                  $total_leads = $total_new_customers = $total_repeat_loans = $new_sum = $repeat_sum = $total_loans = $total_total_loans = $lo_rate = 0;
                  $bdos = fetchtable('o_users',"user_group IN (1, 8, 7, 22) AND status=1","uid","asc","1000","uid, name, branch");

                  echo "bods ===> ". json_encode($bdos) . "<br>";
                  echo "bdo_leads ===> ". json_encode($bdo_leads) . "<br>";
                  echo "new_bdo_loans ===>". json_encode($new_bdo_loans) . "<br>";
                    echo "repeat_bdo_loans ===>". json_encode($repeat_bdo_loans) . "<br>";
                    echo "bdo_collected ===>" .json_encode($bdo_collected) . "<br>";
                    echo "bdo_balance ===>" .json_encode($bdo_balance) . "<br>";


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
                      $lo_collections = false_zero($bdo_collected[$bid]);
                      $lo_balance = false_zero($bdo_balance[$bid]);

                      echo "bid ==> $bid, lo_balance ===>" .json_encode($lo_balance) . "<br>";
                      $lo_repayable = false_zero($bdo_repayable[$bid]);

                       $lo_rate = false_zero(round(($lo_collections/$lo_repayable)*100, 2));

                      $new_loans_sum = $new_bdo_loans_sum[$bid];
                      $repeat_loans_sum = $repeat_bdo_loans_sum[$bid];

                      $total_loans = $new_loans_sum + $repeat_loans_sum;

                      $total_leads+=$new_leads;
                      $total_new_customers+=$new_loans;
                      $total_repeat_loans+=$repeat_loans;
                      $new_sum+=$new_loans_sum;
                      $repeat_sum+=$repeat_loans_sum;


                      echo "<tr><td>$bid</td><td>$bdoname $co_name</td><td>$branch_name</td><td>$new_leads</td><td>$new_loans</td><td>".number_format($new_loans_sum,2)."</td><td>$repeat_loans</td><td>".number_format($repeat_loans_sum,2)."</td><td>".number_format($total_loans,2)."</td><td>".number_format($lo_repayable,2)."</td><td>".number_format($lo_collections,2)."</td><td>".number_format($lo_balance,2)."</td><td>".number_format($lo_rate,2)."%</td></tr>";
                      $total_loans = 0;
                      $lo_rate = 0;
                  }
                  ?>
                  </tbody>

                <tfoot>
                <tr><th>ID</th><th>BDO</th><th>Branch</th><th><?php echo number_format($total_leads); ?></th> <th><?php echo number_format($total_new_customers); ?></th> <th><?php echo number_format($new_sum, 2); ?></th> <th><?php echo number_format($total_repeat_loans); ?></th><th><?php echo number_format($repeat_sum, 2); ?></th><th><?php echo number_format($repeat_sum + $new_sum) ?></th><th>--</th><th>--</th><th>--</th><th>--</th></tr>
                </tfoot>
            </table>


        </div>

    </div>

<?php

