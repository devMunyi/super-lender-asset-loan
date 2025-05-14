<?php
$loans_array = array();
$customers_array = array();

$loans = fetchtable('o_loans',"given_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1 AND status!=0", "uid","asc", "100000","uid, customer_id");
while($ll = mysqli_fetch_array($loans)){
    $lid = $ll['uid'];
    $customer_id = $ll['customer_id'];
    array_push($loans_array, $lid);
    array_push($customers_array, $customer_id);
}
$loan_list = implode(',', $loans_array);
$customer_list = implode(',', $customers_array);

$loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","50","uid","name");

$payments_array = array();
$payments = fetchtable('o_incoming_payments',"loan_id in ($loan_list)","uid","asc","1000000","uid, loan_id, amount");
while($p = mysqli_fetch_array($payments)){
    $pid = $p['uid'];
    $loan_id = $p['loan_id'];
    $amount = $p['amount'];
    $payments_array = obj_add($payments_array, $loan_id, $amount);
}
$customer_names = table_to_obj('o_customers',"uid in ($customer_list)",100000,"uid","full_name");

?>

    <div class="col-sm-12">
        <table id="example2" class="table table-condensed table-striped table-bordered">
            <thead>
            <tr><th>UID</th><th>Customer Name</th><th>Phone</th><th>Loan Amount</th><th>Total Repayable</th><th>Repaid</th><th>Real Payments*</th><th>Difference</th><th>Given Date</th><th>Status</th></tr>
            </thead>

            <tbody>
            <?php
              $loans = fetchtable('o_loans',"uid in ($loan_list) AND total_repaid > 0","uid","asc","100000000","uid, customer_id, account_number, loan_amount, total_repayable_amount, total_repaid, loan_balance, given_date, status");
              while($l = mysqli_fetch_array($loans)){
                  $uid = $l['uid'];
                  $customer_id = $l['customer_id'];
                  $mobile = $l['account_number'];
                  $loan_amount = $l['loan_amount'];
                  $total_repayable_amount = $l['total_repayable_amount'];
                  $total_repaid = $l['total_repaid'];
                  $loan_balance = $l['loan_balance'];
                  $given_date = $l['given_date'];
                  $status = $l['status'];
                  $state = $loan_statuses[$status];
                  $payments_amount = $payments_array[$uid];

                  if($payments_amount == $total_repaid){
                      continue;
                  }

                  $customer_name = $customer_names[$customer_id];
                  $difference = $total_repayable_amount - $payments_amount;

                  echo "<tr><td>$uid</td><td>$customer_name</td><td>$mobile</td><td>".number_format($loan_amount)."</td><td>".number_format($total_repayable_amount)."</td><td>".number_format($total_repaid)."</td><td>".number_format($payments_amount)."</td><td>".number_format($difference)."</td><td>$given_date</td><td>$state</td> </tr>";
              }




            ?>
            </tbody>

            <tfoot>

            </tfoot>
        </table>


    </div>

    </div>