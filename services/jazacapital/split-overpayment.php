<?php
////======= Begin handle overpayment service 217596
session_start();
include_once("../configs/20200902.php");
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
include_once("./util.php");


// $all_loans_with_overpayment = fetchtable2("o_loans", "loan_balance < 0 AND paid = 1 AND disbursed = 1", "uid", "ASC", "uid, loan_balance");


$limit = $_GET['l'] ?? 100;

echo "Limit: $limit </br>";

$all_loans_with_overpayment = fetchtable("o_loans", "loan_balance <= -10 AND paid = 1 AND disbursed = 1 AND given_date >= '2024-12-01'", "uid", "ASC", "$limit", "uid, loan_balance"); // limit to 100 records to avoid timeout and be able to run the script multiple times with less blocking if they are many records

$count = 0;

while($lwo = mysqli_fetch_assoc($all_loans_with_overpayment)){
    $loan_id = $lwo['uid'];
    $loan_balance = abs($lwo['loan_balance']);

    $fetch_resp = fetchmaxid("o_incoming_payments", "loan_id = $loan_id AND status = 1 AND amount > $loan_balance", "uid, transaction_code, amount, customer_id, branch_id, group_id, loan_code, payment_method, payment_category, mobile_number, payment_date, record_method, added_by, collected_by, comments, status");

    $transaction_code = $fetch_resp['transaction_code'];
    $max_pid = $fetch_resp['uid'];
    $amount = $fetch_resp['amount'];
    $customer_id = $fetch_resp['customer_id'];
    $branch_id = $fetch_resp['branch_id'];
    $group_id = $fetch_resp['group_id'];
    $loan_code = $fetch_resp['loan_code'];
    $payment_method = $fetch_resp['payment_method'];
    $payment_for = $fetch_resp['payment_category'];
    $mobile_number = $fetch_resp['mobile_number'];
    $payment_date = $fetch_resp['payment_date'];
    $record_method = $fetch_resp['record_method'];
    $added_by = $fetch_resp['added_by'];
    $collected_by = $fetch_resp['collected_by'];
    $comments = $fetch_resp['comments'];
    $status = $fetch_resp['status'];

    $data = array(
        'customer_id' => $customer_id,
        'branch_id' => $branch_id,
        'group_id' => $group_id,
        'payment_method' => $payment_method,
        'payment_for' => $payment_for,
        'mobile_number' => $mobile_number,
        'amount' => $amount,
        'parent_pid' => $max_pid,
        'transaction_code' => $transaction_code,
        'loan_id' => $loan_id,
        'loan_code' => $loan_code,
        'balance' => $loan_balance,
        'payment_date' => $payment_date,
        'record_method' => 'API',
        'added_by' => $added_by,
        'collected_by' => $collected_by,
        'comments' => 'From API',
        'status' => 1
    );
    // split overpayment
    splitOverpayment($data);

    // recalculte loan
    recalculate_loan($loan_id, true);

    // handle allocation of new split
    handle_second_split_alloc($transaction_code);

    if($count > 20){
        // return;
    }
    $count++;
}

echo "Handled $count overpayments";


function handle_second_split_alloc($transaction_code){
    $fetch_resp = fetchmaxid("o_incoming_payments", "status = 1 AND SUBSTRING(transaction_code, 8) = '$transaction_code'", "uid, transaction_code, amount, loan_id, customer_id");
    $loan_id = intval($fetch_resp['loan_id']);
    $max_pid = intval($fetch_resp['uid']);
    $customer_id = intval($fetch_resp['customer_id']);
    $amount = doubleval($fetch_resp['amount']);

    if($loan_id == 0 && $customer_id > 0 && $amount > 0){
        // fetch customer's latest loan which is not yet cleared
        $latest_loan_det = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid");
        $latest_loan_unpaid = intval($latest_loan_det['uid']);
        
        /// QL81NZMYMZ 150985
        if($latest_loan_unpaid > 0){
            // allocate the second split to the latest loan
            updatedb("o_incoming_payments", "loan_id = $latest_loan_unpaid", "uid = $max_pid");

            // recalculte loan
            recalculate_loan($latest_loan_unpaid, true);

            // get the latest loan balance
            $balance = loan_balance($latest_loan_unpaid);

            // sync the loan balance with the second split
            $updated = updatedb("o_incoming_payments", "loan_balance = $balance", "uid = $max_pid");
            

            echo "handle_second_split_alloc updatedb => $updated, latest_loan_unpaid => $latest_loan_unpaid, amount => $amount </br>";
        }
    }
}


///==== End handle overpayment service
///
include_once("../configs/close_connection.inc");