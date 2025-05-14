<?php

$expected_http_method = 'POST';
include_once("../vendor/autoload.php");
include_once ("../configs/allowed-ips-or-origins.php");
include_once("../configs/conn.inc");
include_once("../configs/jwt.php");
include_once("../php_functions/jwtAuthUtils.php");
include_once("../php_functions/jwtAuthenticator.php");
include_once("../php_functions/functions.php");

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
    $phone_number = $payment["phone_number"];
    $transaction_code = $payment["transaction_code"];

    if (empty($phone_number) || empty($transaction_code)) {
        $skipped++;
        $skipped_payments[] = [$phone_number, $transaction_code, "Phone Number or Transaction Code Missing! "];
        continue;
    }

    // make phone number valid
    $validated_phone = make_phone_valid($phone_number);

    if (validate_phone($validated_phone) != 1) {
        $skipped++;
        $skipped_payments[] = [$phone_number, $transaction_code, "Invalid Phone Number!"];
        continue;
    }

    // check if payment is already allocated to another loan for the same customer
    $pay_det = fetchmaxid('o_incoming_payments', "mobile_number = '$phone_number' AND transaction_code = '$transaction_code'", 'loan_id, customer_id');

    $pay_loan_id = $pay_det['loan_id'];
    $pay_customer_id = $pay_det['customer_id'];
    if ($pay_loan_id > 0) {
        $skipped++;
        $skipped_payments[] = [$phone_number, $transaction_code, "Payment is already allocated to another loan($pay_loan_id) for the same customer!"];
        continue;
    } else {

        // check if payment is already allocated to another customer
        $pay_det2 = fetchmaxid('o_incoming_payments', "transaction_code = '$transaction_code'", 'loan_id, customer_id, mobile_number');
        $pay_loan_id2 = $pay_det2['loan_id'];
        $pay_customer_id2 = $pay_det2['customer_id'];
        $pay_mobile_number = $pay_det2['mobile_number'];
        if (($pay_customer_id2 > 0 || $pay_loan_id2 > 0) && $pay_mobile_number != $phone_number) {
            $skipped++;
            $skipped_payments[] = [$phone_number, $transaction_code, "Payment is already allocated to another customer($pay_customer_id2), loan code ($pay_loan_id2)!"];
            continue;
        }
    }

    $payment = fetchonerow('o_incoming_payments', "transaction_code = '$transaction_code'", "uid, loan_id, payment_method, payment_category, amount, transaction_code, payment_date, record_method, comments, status, group_id");
    $pid = $payment['uid'];

    $original_loan = $payment['loan_id'];
    $payment_method = $payment['payment_method'];
    $payment_category = $payment['payment_category'];
    $amount = $payment['amount'];
    $transaction_code = $payment['transaction_code'];;

    // fetch latest customer loan_id based on mobile phone which is upaid
    $loan_det = fetchmaxid('o_loans', "account_number = '$phone_number' AND disbursed = 1 AND paid = 0", 'uid');
    $loan_id = $loan_det['uid'] ?? 0;

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
        $skipped_payments[] = [$phone_number, $transaction_code, "Payment ID Invalid"];
        continue;
    }

    if ($payment_method == 4) {
        $transaction_code = "N/A";
    } else {
        if (input_length($transaction_code, 3) == 1) {
            $exists = checkrowexists('o_incoming_payments', "transaction_code=\"$transaction_code\" AND uid != $pid");

            if ($exists == 1) {
                $skipped++;
                $skipped_payments[] = [$phone_number, $transaction_code, "Transaction code exists"];
                continue;
            }
        } else {
            //////------Invalid user ID
            $skipped++;
            $skipped_payments[] = [$phone_number, $transaction_code, "Transaction code required"];
            continue;
        }
    }



    if ($amount > 0) {
    } else {
        $skipped++;
        $skipped_payments[] = [$phone_number, $transaction_code, "Amount is required"];
        continue;
    }

    if ((validate_phone($phone_number)) == 0) {
        $skipped++;
        $skipped_payments[] = [$phone_number, $transaction_code, "Mobile number invalid $phone_number"];
        continue;
    }

    ///----Customer ID from mobile number
    $customer_det = fetchonerow('o_customers', "primary_mobile='$phone_number'", "uid, primary_product");

    $customer_id = $customer_det["uid"] ?? 0;
    $primary_product = $customer_det["primary_product"] ?? 1;


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
        $skipped_payments[] = [$phone_number, $transaction_code, "Payment date required"];
        continue;
    }

    if ($payment_method == 0) {
        $skipped++;
        $skipped_payments[] = [$phone_number, $transaction_code, "Payment method required"];
        continue;
    }

    $update_flds = "customer_id=$customer_id, branch_id=$branch_id, collected_by=$collector, group_id=$group_id, payment_method= $payment_method, payment_category=$payment_category, mobile_number=\"$phone_number\", amount=$amount, transaction_code=\"$transaction_code\", loan_id=$loan_id, payment_date=\"$payment_date\",  comments=\"$comments\", status=$status";
    $update = updatedb('o_incoming_payments', $update_flds, "uid=$pid");
    if ($update == 1) {
        if ($loan_id > 0) {
            recalculate_loan($loan_id);

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

        if ($original_loan > 0) {
            $loan_details = fetchonerow('o_loans', "uid='$original_loan'", "final_due_date");
            $final_due_date = $loan_details['final_due_date'];
            $per = datecompare($final_due_date, $date);

            if ($per == 1) {

                ///--Due /Not due
                $upd = updatedb('o_loans', "paid=0, status=3", "uid=$original_loan AND status!=3 AND disbursed=1");
                if ($upd == 1) {
                    $event = "Loan marked as disbursed  because a payment was unallocated. The payment ID is ($pid)";
                    store_event('o_loans', $original_loan, "$event");
                }
            } else {
                ///Overdue
                $upd = updatedb('o_loans', "paid=0, status=7", "uid='$original_loan' AND status!=7 AND disbursed=1");
                if ($upd == 1) {
                    $event = "Loan marked as overdue  because a payment was unallocated. The payment ID is ($pid)";
                    store_event('o_loans', $original_loan, "$event");
                }
            }

            recalculate_loan($original_loan, true);
        }


        $userJson = json_encode($user);
        $event = "Payment updated by [$userJson] on [$fulldate]. Details Customer_id:$customer_id, branch_id: $branch_id, mobile number: $phone_number, Amount: $amount, transaction: $transaction_code, Loan id: $loan_id, payment_date: $payment_date, comments: $comments";
        $store_event_resp = store_event('o_incoming_payments', $pid, "$event");

        $proceed = 1;

        $allocated++;
        $allocated_payments[] = [$phone_number, $transaction_code];
    } else {

        $skipped++;
        $skipped_payments[] = [$phone_number, $transaction_code];
        continue;
    }
}

sendApiResponse(200, "SUCCESS", 'OK', [
    'skipped_count' => $skipped,
    'skipped_payments' => $skipped_payments,
    'allocated_count' => $allocated,
    'allocated_payments' => $allocated_payments
]);


// Function to send API response
