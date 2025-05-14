<?php

$customers = table_to_array('o_loans',"given_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1","1000000","customer_id");

$all_customers = implode(',', $customers);
$names = table_to_obj('o_customers',"uid in ($all_customers)","1000000","uid","full_name");

$reg_fees_array = array();
$savings_array = array();

$inc = fetchtable('o_incoming_payments',"customer_id in ($all_customers) AND status=1","uid","asc","1000000","amount, payment_category, customer_id");
while($i = mysqli_fetch_array($inc)){
    $amount = $i['amount'];
    $payment_category = $i['payment_category'];
    $customer_id = $i['customer_id'];
    if($payment_category == 2) {
        $reg_fees_array = obj_add($reg_fees_array, $customer_id, $amount);
        $reg_fees_total+=$amount;
    }
    if($payment_category == 4){
        $savings_array = obj_add($savings_array, $customer_id, $amount);
        $savings_total+=$amount;
    }

}







?>

        <div class="col-sm-12">
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>Loan ID</th><th>Member ID</th><th>Full Name</th><th>Reg Fees Received</th><th>Loan Disbursed</th> <th>Interest Received</th><th>Savings Amount</th> <th>Mobile Number</th><th>Transaction Code</th></tr>
                </thead>
                  <tbody>
                  <?php
                  $loans = fetchtable('o_loans',"given_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1","uid","asc","1000000","uid, customer_id, loan_amount, total_addons, transaction_code, account_number");
                  while($l = mysqli_fetch_array($loans)){

                      $loan_id = $l['uid'];
                      $customer_id = $l['customer_id'];
                      $full_name = $names[$customer_id];
                      $reg_fees = $reg_fees_array[$customer_id];
                      $loan_disbursed = $l['loan_amount'];
                      $interest = $l['total_addons'];
                      $savings = $savings_array[$customer_id];
                      $mobile = $l['account_number'];
                      $tcode = $l['transaction_code'];
                      $disbursed_total+=$loan_disbursed;
                      $interest_total+=$interest;

                      echo "<tr><td>$loan_id</td><td>$customer_id</td><td>$full_name</td><td>$reg_fees</td><td>$loan_disbursed</td> <td>$interest</td><td>$savings</td> <td>$mobile</td><td>$tcode</td></tr>";

                  }
                  ?>

                  </tbody>

                <tfoot>
                <tr><th>Loan ID</th><th>Member ID</th><th>Full Name</th><th><?php echo $reg_fees_total; ?></th><th><?php echo $disbursed_total; ?></th> <th><?php echo $interest_total; ?></th><th><?php echo $savings_total; ?></th> <th>Mobile Number</th><th>Transaction Code</th></tr>
                </tfoot>
            </table>


        </div>

    </div>

<?php

