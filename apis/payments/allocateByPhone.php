<?php

$expected_http_method = 'POST';
include_once("../../vendor/autoload.php");
// include_once ("../../configs/allowed-ips-or-origins.php");
include_once("../../configs/conn.inc");
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");
include_once("../../php_functions/jwtAuthenticator.php");
include_once("../../php_functions/functions.php");

$data = json_decode(file_get_contents('php://input'), true);

// Check if data is present and is an array
if (!isset($data) || !is_array($data) || empty($data)) {
    sendApiResponse(400, "Invalid or empty data provided.");
}

$skipped = $allocated = 0;
$skipped_payments = [];
$allocated_payments = [];
// Iterate over each payment in the array
foreach ($data as $payment) {

    // Extract payment details from the array
    // $phone_number = make_phone_valid($payment["phone_number"]);
    $transaction_code = trim($payment["transaction_code"] ? $payment["transaction_code"] : '');
    $phone_number = trim($payment["phone_number"] ? $payment["phone_number"] : '');
    $event = trim($payment["comment"] ?  $payment["comment"] : 'Coming soon!');

    // validate event
    if (empty($event)) {
        $skipped++;
        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Comment is required!"));
        continue;
    }


    if (empty($phone_number)) {
        $skipped++;
        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Phone number is required!"));
        continue;
    }

    // check if payment is already allocated
    $pay_det = fetchmaxid('o_incoming_payments', "transaction_code = '$transaction_code'", 'loan_id, customer_id, uid');

    // check if payment uid is valid
    if ($pay_det['uid'] > 0) {
    }else{
        $skipped++;
        

        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment ID Invalid!"));

        continue;
    }

    $pay_loan_id = $pay_det['loan_id'];
    $pay_customer_id = $pay_det['customer_id'];

    if ($pay_loan_id > 0) {
        $skipped++;


        // ftech loan by $pay_loan_id from o_loans table to select status
        $loan_status = fetchrow('o_loans', "uid = $pay_loan_id", "status");

        // check if status = 6 (rejected)
        if ($loan_status['status'] == 6 && $ignore_rejected_loan_deallocation != 1) {
            $original_loan = $pay_loan_id;
            // array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment is already allocated to loan($pay_loan_id) customer($pay_customer_id)!"));
            // continue;
        }else {

            array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment is already allocated to loan($pay_loan_id) customer($pay_customer_id)!"));
            continue;
        }
    } 


    $payment = fetchonerow('o_incoming_payments', "transaction_code = '$transaction_code'", "uid, loan_id, payment_method, payment_category, amount, transaction_code, payment_date, record_method, comments, status, group_id");
    $pid = $payment['uid'];
    $original_loan = $payment['loan_id'];
    $payment_method = $payment['payment_method'];
    $payment_category = $payment['payment_category'];
    $amount = $payment['amount'];
    $transaction_code = $payment['transaction_code'];
    $payment_date = $payment['payment_date'];
    $record_method = $payment['record_method'];
    $comments = $payment['comments'];
    $status = $payment['status'];
    $group_id = $payment['group_id'];
    $branch_id = 0;
    $collector = 0;

    ////////////////////////
    if ($pid > 0) {
    } else {
        $skipped++;
        // $skipped_payments[] = [$transaction_code, "Payment ID Invalid"];
        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment ID Invalid!"));
        continue;
    }

    if ($payment_method == 4) {
        $transaction_code = "N/A";
    } else {
        if (input_length($transaction_code, 3) == 1) {
            $exists = checkrowexists('o_incoming_payments', "transaction_code=\"$transaction_code\" AND uid != $pid");

            if ($exists == 1) {
                $skipped++;
                array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Transaction code exists!"));
                continue;
            }
        } else {
            //////------Invalid user ID
            $skipped++;
            array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Transaction code required!"));
            continue;
        }
    }



    if ($amount > 0) {
    } else {
        $skipped++;
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Amount is required!"));
        continue;
    }

    ///----Customer ID from mobile number
    $customer_det = fetchonerow('o_customers', "primary_mobile='$phone_number'", "uid, branch");
    $customer_id = $customer_det["uid"] ?? 0;
    $branch_id = $customer_det["branch"] ?? 0;


    if ((input_length($payment_date, 10)) == 0) {
        $skipped++;
        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment date required!"));
        continue;
    }

    if ($payment_method == 0) {
        $skipped++;
        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment method required!"));
        continue;
    }

    $update_flds = "customer_id=$customer_id, branch_id=$branch_id, collected_by=$collector, group_id=$group_id, payment_method= $payment_method, payment_category=$payment_category, mobile_number=\"$phone_number\", amount=$amount, transaction_code=\"$transaction_code\", loan_id=0, payment_date=\"$payment_date\",  comments=\"$comments\", status=$status";
    $update = updatedb('o_incoming_payments', $update_flds, "uid=$pid");
    if ($update == 1) {
        
        if ($original_loan > 0) {
            $payments = table_to_array("o_incoming_payments", "loan_id = $original_loan AND status = 1", "100", "uid");
            $payments_csv = implode(",", $payments);
            $event .= ". Deallocated from rejected loan($original_loan).";

            updatedb("o_incoming_payments", "loan_balance = 0, loan_id = 0", "loan_id = $original_loan");
        }

        store_event('o_incoming_payments', $pid, "$event");

        $allocated++;
        $allocated_payments[] = [$transaction_code];
    } else {

        $skipped++;
        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Error updating payment!"));
        continue;
    }
}

sendApiResponse(200, "SUCCESS", 'OK', [
    'skipped_count' => $skipped,
    'skipped_payments' => $skipped_payments,
    'allocated_count' => $allocated,
    'allocated_payments' => $allocated_payments
]);

