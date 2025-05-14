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
    $loan_id = intval($payment["loan_id"]);
    $event = trim($payment["comment"] ?  $payment["comment"] : 'Coming soon!');

    // validate event
    if (empty($event)) {
        $skipped++;
        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Comment is required!"));
        continue;
    }

    if (empty($loan_id)) {
        $skipped++;
        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Loan ID is required!"));
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

        // payment is already allocated to another loan for the same customer
        if($pay_loan_id == $loan_id){
            // $skipped_payments[] = [$transaction_code, "Payment is already allocated to the same loan($pay_loan_id) customer($pay_customer_id)!"];
            // push transaction as key and error as value
            array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment is already allocated to the same loan($pay_loan_id) customer($pay_customer_id)!"));
            continue;
        }

        if($pay_loan_id != $loan_id){
            // $skipped_payments[] = [$transaction_code, "Payment is already allocated to another loan($pay_loan_id) customer($pay_customer_id)!"];

            // push transaction as key and error as value
            array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment is already allocated to another loan($pay_loan_id) customer($pay_customer_id)!"));
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
                // $skipped_payments[] = [$transaction_code, "Transaction code exists"];
                // push transaction as key and error as value
                array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Transaction code exists!"));
                continue;
            }
        } else {
            //////------Invalid user ID
            $skipped++;
            // $skipped_payments[] = [$transaction_code, "Transaction code required"];

            // push transaction as key and error as value
            array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Transaction code required!"));
            continue;
        }
    }



    if ($amount > 0) {
    } else {
        $skipped++;
        // $skipped_payments[] = [$transaction_code, "Amount is required"];
        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Amount is required!"));
        continue;
    }

    ///----Customer ID from mobile number
    $customer_det = fetchonerow('o_loans', "uid=$loan_id", "customer_id, product_id");
    $customer_id = $customer_det["customer_id"] ?? 0;
    $primary_product = $customer_det["product_id"] ?? 1;
    $phone_number = fetchrow('o_customers', "uid=$customer_id", "primary_mobile");


    if ($loan_id > 0) {
        $collector = intval((fetchrow('o_loans', "uid = '$loan_id'", "current_agent")));
        $exists = checkrowexists('o_loans', "uid = $loan_id AND status != 0");
        if ($exists == 0) {
            //  die(errormes("The loan code doesn't exist"));
            //  exit();
        } else {
            $customer_id = fetchrow('o_loans', "uid= $loan_id", "customer_id");
            $branch_id = fetchrow("o_customers", "uid=$customer_id", "branch");
        }
    } else {
        //  die(errormes("Please enter loan code"));
        // exit();
    }

    if ((input_length($payment_date, 10)) == 0) {
        $skipped++;
        // $skipped_payments[] = [$transaction_code, "Payment date required"];

        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment date required!"));
        continue;
    }

    if ($payment_method == 0) {
        $skipped++;
        // $skipped_payments[] = [$transaction_code, "Payment method required"];

        // push transaction as key and error as value
        array_push($skipped_payments, array("transaction_code" => $transaction_code, "error" => "Payment method required!"));
        continue;
    }

    $update_flds = "customer_id=$customer_id, branch_id=$branch_id, collected_by=$collector, group_id=$group_id, payment_method= $payment_method, payment_category=$payment_category, amount=$amount, transaction_code=\"$transaction_code\", loan_id=$loan_id, payment_date=\"$payment_date\",  comments=\"$comments\", status=$status";
    $update = updatedb('o_incoming_payments', $update_flds, "uid=$pid");
    if ($update == 1) {
        if ($loan_id > 0) {
            recalculate_loan($loan_id, true);

            // update newly allocated loan
            $ld = fetchmaxid("o_incoming_payments", "status > 0 AND loan_id = $loan_id", "uid, added_by");
            $max_pid = $ld["uid"];
            $balance = loan_balance($loan_id);

            /////-------Check the after save script
            $primary_product = $primary_product ? $primary_product : 1;
            $scr = after_script($primary_product, "SPLIT_PAYMENT");

            // optionally handle payment splitting
            if ($scr !== 0) {
                $added_by = $ld["added_by"] ?? 0;
                include_once("../../$scr");
            } else {
                updatedb("o_incoming_payments", "loan_balance = $balance", "uid = $max_pid");
            }
            ////-------End of check after save script

            recalculate_loan($loan_id, true);
        }

        $userJson = json_encode($user);
        // $event = "Payment allocated by [$userJson] on [$fulldate]. Details Customer_id:$customer_id, branch_id: $branch_id, mobile number: $phone_number, Amount: $amount, transaction: $transaction_code, Loan id: $loan_id, payment_date: $payment_date, comments: $comments";
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

