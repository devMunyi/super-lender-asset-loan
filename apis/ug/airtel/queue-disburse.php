<?php
session_start();
include_once("../../../vendor/autoload.php");
include_once('../../../configs/20200902.php');
include_once("../../../php_functions/functions.php");
include_once("../../../php_functions/airtel-ug.php");
$_SESSION['db_name'] = $db_;
include_once("../../../configs/conn.inc");
include_once("../../../configs/airtel-ug.php");
if ($has_archive == 1) {
    include_once("../../../configs/archive_conn.php");
}

$rpp = $_GET['rpp'] ?? 5;
$days_ago_1 = datesub($date, 0, 0, 1);

// Start a transaction
mysqli_begin_transaction($con);

$queues_query = "SELECT `uid`, loan_id, amount, feedbackcode, tried_params, trials FROM o_airtel_ug_queues WHERE status=1 AND trials < 3 AND (added_date >= '$days_ago_1 00:00:00' OR requeued_date >= '$days_ago_1 00:00:00') LIMIT $rpp FOR UPDATE SKIP LOCKED";

$queues = mysqli_query($con, $queues_query);

while ($q = mysqli_fetch_array($queues)) {
    $uid = $queue_id = intval($q['uid']);
    $loan_id = intval($q['loan_id']);
    $q_amount = doubleval($q['amount']);
    $trials = intval($q['trials']);
    $feedbackcode = $q['feedbackcode'] ?? null;

     // get the current balance
     $bal = doubleval(fetchrow('o_summaries', "uid=5", "value_"));
     $bal_ = doubleval($bal - 300); // offset abitrary transaction fee
     if($bal_ < $q_amount){
         continue;
     }

    // echo "Start Loan $loan_id, Q $uid <br/>";
    $processed = updatedb('o_airtel_ug_queues', "sent_date='$fulldate', feedbackcode='Processed', trials=trials+1, status=2", "uid=$uid");
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

        $customer_details = fetchonerow('o_customers', "uid=$customer_id", "uid, full_name");
        // $customer_status = $customer_details['status'];
        $customer_name = $customer_details['full_name'];
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
            /// echo "Ready to send<br/>";
            ////------Mark item as processed

            if ($processed == 1) {
                //////------Send the money
                /// echo "Send Money <br/>";
                $send_resp = airtel_ug_send_money($account_number, $customer_name, $q_amount, $loan_id);

                // var_dump($send_resp);
                $transc_status = $send_resp["payload"]['status'] ?? 'FAILED';
                $transc_resp_code = $send_resp["payload"]['response_code'] ?? '';
                $feedback = $message = $send_resp["message"] ?? '';
                $financialTransactionId = $send_resp['details']['data']['transaction']['reference_id'] ?? $send_resp['details']['data']['transaction']['id'] ?? '';

                if ($transc_status  == "SUCCESSFUL") {
                    $loan_det = fetchonerow('o_loans', "uid='$loan_id'", "given_date, next_due_date, final_due_date, period, period_units");
                    $given_date = $loan_det['given_date'];
                    $next_due_date = $loan_det['next_due_date'];
                    $final_due_date = $loan_det['final_due_date'];
                    $period = $loan_det['period'];
                    $period_units = $loan_det['period_units'];

                    if ($given_date != $date) {
                        $diff = datediff3($date, $given_date);
                        $new_next_d = dateadd($next_due_date, 0, 0, $diff);
                        // $final_due_d = move_to_monday(dateadd($final_due_date, 0, 0, $diff));
                        $final_due_d = move_to_monday(final_due_date($date, $period, $period_units));
                        $new_dates = "NEW Dates-> Disbursed: $date, Next Due: $new_next_d, Final Due: $final_due_d";
                        $new_dates_update = ", given_date='$date', next_due_date='$new_next_d', final_due_date='$final_due_d'";
                    } else {
                        $new_dates = "";
                        $new_dates_update = "";
                    }

                    $save = updatedb('o_loans', "transaction_code='$financialTransactionId',transaction_date='$fulldate', disburse_state='DELIVERED', disbursed=1, status=3 $new_dates_update", "uid=$loan_id");
                    store_event('o_loans', $loan_id, "Transaction was successful at $fulldate. Airtel Money delivered the funds. reference_id ($financialTransactionId): $new_dates");


                    ///--------- update the b2c balance
                    $new_balance = airtelB2CGetBalFromMessage($message);
                    updateUgAirtelUtilityBal($new_balance);
                } else {

                    // mark it as failed and store the event
                    updatedb('o_loans', "transaction_code='$financialTransactionId', disburse_state='FAILED', disbursed=0", "uid=$loan_id");

                    store_event('o_loans', $loan_id, "AirtelMoney was unable to deliver the funds with message ($feedback). Transcode ($transc_resp_code).");
                }

                // echo "Airtel response: $feedback <br/>";
            }
        } else {

            store_event('o_loans', $loan_id, "Automatic Loan Processing: State:$state, Trials: $trials. Details: $feedback");
            /// echo "Cant send $send_ $feedback <br/>";
        }
    }
}

// Commit the transaction
mysqli_commit($con);

// close the connection
mysqli_close($con);
