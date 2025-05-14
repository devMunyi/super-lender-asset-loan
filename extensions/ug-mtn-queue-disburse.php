<?php
session_start();
include_once('../configs/20200902.php');
include_once("../php_functions/functions.php");
include_once("../php_functions/mtn_functions.php");
$_SESSION['db_name'] = $db_;
include_once("../configs/conn.inc");
include_once("../configs/mtn_configs.php");
if ($has_archive == 1) {
    include_once("../configs/archive_conn.php");
}

// $product_id = $_GET['p'];
$offset = $_GET['offset'];
$rpp = 5;

$days_ago_1 = datesub($date, 0, 0, 3);

// Start a transaction
mysqli_begin_transaction($con);

$queues_query = "SELECT `uid`, loan_id, amount, feedbackcode, tried_params, trials FROM o_mtn_queues WHERE status=1 AND trials < 4 AND (added_date >= '$days_ago_1 00:00:00' OR requeued_date >= '$days_ago_1 00:00:00') LIMIT $rpp FOR UPDATE SKIP LOCKED";

$queues = mysqli_query($con, $queues_query);

while ($q = mysqli_fetch_array($queues)) {
    $uid = intval($q['uid']);
    $loan_id = intval($q['loan_id']);
    $q_amount = doubleval($q['amount']);
    $trials = intval($q['trials']);
    $feedbackcode = $q['feedbackcode'] ?? null;

    // get the current balance
    $bal = doubleval(fetchrow('o_summaries', "uid=4", "value_"));
    $bal_ = doubleval($bal - 300); // offset abitrary transaction fee
    if($bal_ < $q_amount){
        continue;
    }


    $processed = updatedb('o_mtn_queues', "sent_date='$fulldate', feedbackcode='Processed', trials=trials+1, status=2", "uid=$uid");
    $trials += 1;
    if ($processed == 1) {

        $loan_d = fetchonerow('o_loans', "uid=$loan_id", "loan_amount, disbursed_amount, customer_id, account_number, given_date, disburse_state, status");
        $loan_amount = $loan_d['loan_amount'];
        $disbursed_amount = $loan_d['disbursed_amount'];
        $customer_id = $loan_d['customer_id'];
        $account_number = $loan_d['account_number'];
        $given_date = $loan_d['given_date'];
        $time_ago = datediff($given_date, $date);
        $disburse_state = $loan_d['disburse_state'];
        $status = $loan_d['status'];

        $customer_details = fetchonerow('o_customers', "uid=$customer_id", "uid, status");
        $customer_status = $customer_details['status'];
        $send_ = 1;
        $state = "";
        $retryMessage = "";
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
                $send_resp = mtn_send_money($account_number, $q_amount, $loan_id, $trials, $uid);
                $resp = $send_resp["payload"];
                $feedback = $send_resp["details"] ?? "No details";


                if ($resp  == "CREATED") {
                    $state = "INITIATED";
                    updatedb('o_loans', "transaction_date='$fulldate', disburse_state='INITIATED', disbursed=1", "uid=$loan_id");
                } else {
                    $state = "FAILED";
                    updatedb('o_loans', "disburse_state='FAILED', disbursed=0", "uid=$loan_id");
                }

                // $loan_update = updatedb('o_loans',"disburse_state='INITIATED'","uid='$loan_id'");
                echo "MTN response: $feedback <br/>";
            }
            /// Send the money
        } else {
            echo "Cant send $send_ $feedback <br/>";
        }

        store_event('o_loans', $loan_id, "Automatic Loan Processing: State:$state, Trials: $trials. Details: $feedback.");
    }
}

// Commit the transaction
mysqli_commit($con);

// Close the connection
mysqli_close($con);