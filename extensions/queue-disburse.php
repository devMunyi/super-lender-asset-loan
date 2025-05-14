<?php
//session_start();
include_once('../configs/20200902.php');
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");



$product_id = $_GET['p'];
$offset = $_GET['offset'];
$rpp = $_GET['rpp'] ?? 5;


$days_ago_1 = datesub($date, 0, 0, 5);

$queues = fetchtable('o_mpesa_queues', "status=1 AND  trials <= 3 AND added_date >= '$days_ago_1 00:00:00'", "uid", "asc", "$rpp");
while ($q = mysqli_fetch_array($queues)) {
    $bal = doubleval(fetchrow('o_summaries', "uid=1", "value_"));
    $b2c_bal = doubleval($bal - 300); // offset arbitrary transaction fee

    $uid = $q['uid'];
    $loan_id = $q['loan_id'];
    $q_amount = doubleval($q['amount']);

    if ($b2c_bal < $q_amount) {
        echo "Insufficient balance, $b2c_bal < $q_amount <br/>";
        // updatedb('o_mpesa_queues', "feedbackcode='INSUFFICIENT_FUND'", "uid='$uid'");
        // store_event('o_loans', $loan_id, "Automatic Loan Processing: Insufficient balance, skipped b2c processing...admin to manually update B2C balance for processing to resume.");
        // mysqli_commit($con);
        continue;
    } else {
        echo "Sufficient balance, $b2c_bal > $q_amount <br/>";
    }

    echo "Start Loan $loan_id, Q $uid <br/>";
    $processed = updatedb('o_mpesa_queues', "sent_date='$fulldate', feedbackcode='Processed', trials=trials+1, status=2", "uid='$uid'");
    if ($processed == 1) {

        $loan_d = fetchonerow('o_loans', "uid='$loan_id'", "loan_amount, disbursed_amount, customer_id, account_number, given_date, disburse_state, status, product_id");
        $loan_amount = $loan_d['loan_amount'];
        $disbursed_amount = $loan_d['disbursed_amount'];
        $customer_id = $loan_d['customer_id'];
        $account_number = $loan_d['account_number'];
        $given_date = $loan_d['given_date'];
        $time_ago = datediff($given_date, $date);
        $disburse_state = $loan_d['disburse_state'];
        $product_id = $loan_d['product_id'];
        $status = $loan_d['status'];

        $customer_details = fetchonerow('o_customers', "uid='$customer_id'", "uid, status, national_id");
        $customer_status = $customer_details['status'];
        $national_id = $customer_details['national_id'];
        $send_ = 1;
        ////////-------
        if ($disbursed_amount != $q_amount) {
            $send_ = 0;
            $feedback = "Amounts Mismatch ($loan_amount,$q_amount)";
        }
        if ($status != 2 || $disburse_state != 'NONE') {
            $send_ = 0;
            $feedback = "Already Processed($disburse_state) or wrong status($status)";
        }
        if ($time_ago >= 4) {
            $send_ = 0;
            $feedback = "Loan too old ($time_ago)";
        }

        if ($send_ == 1) {
            echo "Ready to send<br/>";
            ////------Mark item as processed

            if ($processed == 1) {
                //////------Send the money
                echo "Send Money <br/>";
                if ($send_money_with_nid_validation == 1) {
                    include_once("../mpesa/b2c-with-validation.php");
                    $send = send_money_with_nid_validation($account_number, $national_id, $loan_id, $q_amount);
                } else {
                    $send = send_money($account_number, $q_amount, $loan_id);
                }
                // $loan_update = updatedb('o_loans',"disburse_state='INITIATED'","uid='$loan_id'");
                echo "Mpesa result $send <br/>";

                //-----Notify user via SMS, check for disbursement message
                $searchString = 'Accept the service request successfully';
                if (strpos($send, $searchString) !== false) {
                    // Execute your logic here if the substring is found
                    product_notify($product_id, 0, 'DISBURSEMENT', 3, $loan_id, $account_number);
                }
                
                unset($send, $searchString);
            }
            /// Send the money
        } else {
            echo "Cant send $send_ $feedback <br/>";
        }

        store_event('o_loans', $loan_id, "Automatic Loan Processing: State:$send_ , Feedback: $feedback");
        unset($send_, $feedback);
    }
}

// mysqli_commit($con);
include_once("../configs/close_connection.inc");