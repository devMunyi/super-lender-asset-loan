<?php
include_once("../configs/conn.inc");
include_once('../configs/20200902.php');
include_once("../php_functions/functions.php");
include_once("../php_functions/mpesa.php");
include_once("../mpesa/b2c-with-validation.php");



$product_id = $_GET['p'] ?? 1;
$rpp = $_GET['rpp'] ? : 5;
$days_ago_1 = datesub($date, 0, 0, 5);

// Start a transaction
mysqli_begin_transaction($con);

$queues_query = "SELECT `uid`, loan_id, amount, feedbackcode FROM o_mpesa_queues WHERE status=1 AND trials <= 3 AND added_date >= '$days_ago_1 00:00:00' LIMIT $rpp FOR UPDATE SKIP LOCKED";

$queues = mysqli_query($con, $queues_query);

if (mysqli_num_rows($queues) > 0) {
    $mpesa_configs = fetchonerow('o_mpesa_configs', "uid=1", "property_value, initiator_name, security_credential, enc_token, enc_token_key");

    while ($q = mysqli_fetch_array($queues)) {

        $bal = doubleval(fetchrow('o_summaries', "uid=1", "value_"));
        $b2c_balance = doubleval($bal - 300); // offset abitrary transaction fee

        $uid = $q['uid'];
        $loan_id = $q['loan_id'];
        $q_amount = doubleval($q['amount']);
        $feedbackcode = trim($q['feedbackcode'] ?? '');
        echo "Start Loan $loan_id, Q $uid <br/>";

        // Check if the b2c balance is enough to process the loan
        if ($b2c_balance < $q_amount) {
            // Commit the transaction for the current row to release the lock
            // mysqli_commit($con);
            unset($b2c_balance, $q_amount, $loan_id);
            
            // exit the loop current iteration
            continue;
        } 

        try {

            $processed = updatedb('o_mpesa_queues', "sent_date='$fulldate', feedbackcode='Processed', trials=trials+1, status=2", "uid=$uid");
            if ($processed == 1) {
                // Commit the transaction for the current row to release the lock soonest possible

                $loan_d = fetchonerow('o_loans', "uid='$loan_id'", "loan_amount, disbursed_amount, customer_id, account_number, given_date, disburse_state, status, product_id, with_ni_validation");
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
                $with_ni_validation = $loan_d['with_ni_validation'] ?? 0;
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

                if ($send_ == 1 && $processed == 1) {
                    echo "Ready to send<br/>";
                    ////------Mark item as processed
                    //////------Send the money
                    echo "Send Money <br/>";
                    if ($send_money_with_nid_validation == 1 && $with_ni_validation == 1) {
                        $send = send_money_with_nid_validation_v2($account_number, $national_id, $loan_id, $q_amount, $mpesa_configs);
                    } else {
                        $send = send_money_v2($account_number, $q_amount, $loan_id, $mpesa_configs);
                    }
                    // $loan_update = updatedb('o_loans',"disburse_state='INITIATED'","uid='$loan_id'");
                    echo "Mpesa result $send <br/>";

                    //-----Notify user via SMS, check for disbursement message
                    $searchString = 'Accept the service request successfully';
                    if (strpos($send, $searchString) !== false) {
                        $send_ = 3;
                        // Execute your logic here if the substring is found
                        product_notify($product_id, 0, 'DISBURSEMENT', 3, $loan_id, $account_number);
                    }
                } else {
                    echo "Cant send $send_ $feedback <br/>";
                }
            } else {
                echo "Can't send $send_ $feedback <br/>";
            }
        } catch (Exception $e) {
            // Handle exceptions here
            $msg = $e->getMessage();
            echo "Error: " . $msg . "<br/>";
            $feedback = "Error: " . $msg;
        } finally {

            // Commit the transaction for the current row to release the lock
            // mysqli_commit($con);

            // store the event
            store_event('o_loans', $loan_id, "Automatic Loan Processing: State:$send_, Feedback: $feedback.");

            $successfulB2C = 'Accept the service request successfully';
            if (
                strpos($send, $successfulB2C) !== false
            ) {
                // persist transaction datetime
                updatedb("o_loans", "transaction_date='$fulldate'", "uid='$loan_id'");
            }

            // clean up the variables
            unset($send,  $successfulB2C, $b2c_balance, $q_amount, $loan_id);
            unset($send_, $feedback, $searchString);
        }
    }
}

// Commit the transaction
mysqli_commit($con);

// Close the connection
mysqli_close($con);