



<table class="table table-condensed table-striped" id="example2">
    <thead>
    <tr><th>UID</th>
        <th>Phone</th>
        <th>Product</th>
        <th>Amount Given</th>
        <th>Addons</th>
        <th>Total Amount</th>
        <th>Total Paid</th>
        <th>Balance</th>
        <th>Repayment Rate</th>
        <th>Transcode</th>
        <th>Disb. Date</th>
        <th>Due Date</th>
        <th>Disbursed Days Ago</th>
        <th>Due Days Ago</th>
        <th>Status</th>
    </tr>
    </thead>
<?php
$products = table_to_obj('o_loan_products',"uid > 0","100","uid","name");
$loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","100","uid","name");
echo "<tbody>";
$loans = fetchtable('o_loans',"given_date  BETWEEN '$start_date' AND '$end_date' AND disbursed=1 AND status != 0 AND paid=0","uid","asc","10000000","uid, given_date, account_number, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, total_addons, final_due_date, transaction_code, product_id, status");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $mobile = $l['account_number'];
    $amount = $l['loan_amount'];
    $disbursed_amount = $l['disbursed_amount'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $total_addons = $l['total_addons'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = $l['loan_balance'];
    $product_id = $l['product_id'];
    $given_date = $l['given_date'];        $given_ago = datediff3($given_date, $date);
    $final_due_date = $l['final_due_date'];  $due_ago = datediff3($final_due_date, $date);
    $transaction_code = $l['transaction_code'];
    $status = $l['status'];   $status_name = $loan_statuses[$status];

    $product_name = $products[$product_id];

    $repayment_rate = round(($total_repaid/$total_repayable_amount)*100,2);

    if(date_greater($final_due_date, $date) == 1){
        $min = "-";
    }
    else{
        $min = "";
    }

    echo " <tr><td>$uid</td>
        <td>$mobile</td>
        <td>$product_name</td>
        <td>$amount</td>
        <td>$total_addons</td>
        <td>$total_repayable_amount</td>
        <td>$total_repaid</td>
        <td>$loan_balance</td>
        <td>$repayment_rate%</td>
        <td>$transaction_code</td>
        <td>$given_date</td>
        <td>$final_due_date</td>
        <td>$given_ago</td>
        <td>$min$due_ago</td>
        <td>$status_name</td>
    </tr>";
}
echo "</tbody>";
?>
</table>


