<?php
//session_start();
// enable error reporting
error_reporting(E_ALL);

include_once('../configs/20200902.php');
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");



$product_id = $_GET['p'];
$offset = $_GET['offset'];
$rpp = $_GET['rpp'] ?? 1;


$days_ago_1 = datesub($date, 0, 0, 2);



$queues = fetchtable('o_mpesa_queues', "added_date >= '$days_ago_1 00:00:00' AND loan_id = 604366", "uid", "asc", "$rpp");
while ($q = mysqli_fetch_array($queues)) {
    $uid = $q['uid'];
    $loan_id = $q['loan_id'];
    $q_amount = $q['amount'];

    echo "Start Loan $loan_id, Q $uid <br/>";
    $processed = updatedb('o_mpesa_queues', "sent_date='$fulldate', feedbackcode='Processed', trials=trials+1, status=2", "uid='$uid'");
    if ($processed == 1) {

        $loan_d = fetchonerow('o_loans', "uid=$loan_id", "loan_amount, disbursed_amount, customer_id, account_number, given_date, disburse_state, status, product_id, with_ni_validation");

        echo "loan_details ===>". json_encode($loan_d) ." <br/>";

        $loan_amount = $loan_d['loan_amount'];
        $disbursed_amount = $loan_d['disbursed_amount'];
        $customer_id = $loan_d['customer_id'];
        $account_number = $loan_d['account_number'];
        $given_date = $loan_d['given_date'];
        $time_ago = datediff($given_date, $date);

        echo "loan_amount => $loan_amount <br/>";
        echo "disbursed_amount => $disbursed_amount <br/>";
        echo "customer_id => $customer_id <br/>";
        echo "account_number => $account_number <br/>";
        echo "given_date => $given_date <br/>";
        echo "Today => $date <br/>";
        echo "Days Passed => $time_ago <br/>";

        $disburse_state = $loan_d['disburse_state'];
        $product_id = $loan_d['product_id'];
        $status = $loan_d['status'];
        $with_ni_validation = $loan_d['with_ni_validation'] ?? 0;

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

            echo "feedback: $feedback <br/>";
        }
        if ($time_ago >= 4) {
            $send_ = 0;
            $feedback = "Loan too old ($time_ago)";
        }

        exit("Send $send_ $feedback <br/>");
        break;

        if ($send_ == 1) {
            echo "Ready to send<br/>";
            ////------Mark item as processed

            if ($processed == 1) {
                //////------Send the money
                echo "Send Money <br/>";
                if ($send_money_with_nid_validation == 1 && $with_ni_validation == 1) {
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
                //  sleep(5); // delay the next iteration by 2 seconds
            }
            /// Send the money
        } else {
            echo "Cant send $send_ $feedback <br/>";
        }

        store_event('o_loans', $loan_id, "Automatic Loan Processing: State:$send_ , Feedback: $feedback");
    }
}
