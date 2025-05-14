<?php
session_start();

include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$from_ = $_GET['from'];
$to_ = $_GET['to'];
$product = $_GET['product_id'];

//////----------------------Adds
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reports</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>



<div class="container">

    <?php


    echo "<table class='table table-striped'>";



    echo "<table class='table table-condensed' id='example2'>";
    echo "<thead>";
    echo "<tr><th>Loan</th><th>Phone</th><th>Given Date</th><th>Due Date</th><th>Loan Total</th>  <th>Total Loan Balance</th><th>Due Today</th><th>Total Repaid</th><th>Instalment Balance</th><th>Status</th></tr>";
echo "</thead><tbody>";


    $loan_statuses = table_to_obj('o_loan_statuses',"uid>0","100","uid","name");
    $loans = fetchtable('o_loans',"disbursed=1 AND given_date BETWEEN '$from_' AND '$to_' AND status !=0 AND paid=0","uid","asc","100000","uid, customer_id, given_date, final_due_date, account_number, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, total_addons, transaction_code, status");
    while($l = mysqli_fetch_array($loans)){

        $uid = $l['uid'];
        $customer_id = $l['customer_id'];
        $account_number = $l['account_number'];
        $product_id = $l['product_id'];
        $loan_amount = $l['loan_amount'];
        $given_date = $l['given_date'];
        $final_due_date = $l['final_due_date'];
        $disbursed_amount = $l['disbursed_amount'];
        $total_repayable_amount = $l['total_repayable_amount'];
        $total_repaid = $l['total_repaid'];
        $loan_balance = $l['loan_balance'];
        $total_addons = $l['total_addons'];
        $transaction_code = $l['transaction_code'];
        $status = $l['status'];

        $days_ago = datediff3($date, $given_date);
        $total_days = datediff3($given_date, $final_due_date);
        $due_amount_today = round((($days_ago/$total_days)*$total_repayable_amount),0);
        $instalment_balance = false_zero($due_amount_today - $total_repaid);

        $state = $loan_statuses[$status];




        echo "<tr><td>$uid</td><td>$account_number</td><td>$given_date".fancydate($given_date)."</td><td>$final_due_date</td><td>$total_repayable_amount</td><td>$loan_balance</td><td>$due_amount_today</td><td>$total_repaid</td><td>$instalment_balance</td> <td>$state</td></tr>";

    }


    echo "</tbody><tfoot>";
    echo "<tr><th>Loan</th><th>Phone</th><th>Given Date</th><th>Due Date</th><th>Loan Total</th>  <th>Total Loan Balance</th><th>Due Today</th><th>Total Repaid</th><th>Instalment Balance</th><th>Status</th></tr>";
    echo "</tfoot></table>";




    echo "</table>";

    ?>
</div>

</body>
</html>

