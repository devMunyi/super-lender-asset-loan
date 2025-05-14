<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

?>


<table class="table table-condensed table-striped" id="example2">
    <thead>
    <tr><th>UID</th>
        <th>Full Name</th>
        <th>Phone</th>
        <th>Loan Amount</th>
        <th>Repayable total</th>
        <th>Repaid</th>
        <th>Balance</th>
        <th>Given Date</th>
        <th>Due Date</th>
        <th>Product</th>
        <th>Status</th>


    </tr>
    </thead>
<?php
$all_customers = table_to_array('o_loans',"disbursed = 1 AND status != 0 AND given_date BETWEEN '$start_date' AND '$end_date'","1000000","customer_id");
$all_custs = implode(',', $all_customers);
$customer_loans_array = array();

$customer_names = table_to_obj('o_customers',"uid in ($all_custs)","1000000","uid","full_name");
$statuses_array = table_to_obj("o_loan_statuses","uid > 0","100","uid","name");
$products_array = table_to_obj('o_loan_products',"uid > 0","100","uid","name");
$customer_loans = fetchtable('o_loans',"disbursed = 1 AND status != 0 AND customer_id in ($all_custs)","uid","asc","10000000","uid, customer_id");
while($cl = mysqli_fetch_array($customer_loans)){
    $customer_id_ = $cl['customer_id'];
    $customer_loans_array = obj_add($customer_loans_array, $customer_id_, 1);
}

echo "<tbody>";
$loans = fetchtable('o_loans',"disbursed = 1 AND status != 0 AND given_date BETWEEN '$start_date' AND '$end_date'","uid","asc","10000000","uid, customer_id, account_number, loan_amount, total_repayable_amount, total_repaid, loan_balance,  status, product_id, given_date, final_due_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $loan_id = $l['uid'];
    $customer_id = $l['customer_id'];
    $account_number = $l['account_number'];
    $loan_amount = $l['loan_amount'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = $l['loan_balance'];
    $given_date = $l['given_date'];
    $due_date = $l['final_due_date'];
    $status = $l['status'];            $status_name = $statuses_array[$status];
    $product_id = $l['product_id'];    $product_name = $products_array[$product_id];
    $full_name = $customer_names[$customer_id];
    $total_loans = $customer_loans_array[$customer_id];

    if($total_loans == 1) {

        echo "<tr><td>$uid</td>
        <td>$full_name</td>
        <td>$account_number</td>
        <td>$loan_amount</td>
        <td>$total_repayable_amount</td>
        <td>$total_repaid</td>
        <td>$loan_balance</td>
        <td>$given_date</td>
        <td>$due_date</td>
         <td>$product_name</td>
        <td>$status_name</td>
        </tr>";
    }


}
echo "</tbody>";
?>
</table>


