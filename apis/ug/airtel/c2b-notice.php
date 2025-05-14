<?php

include_once("../../../vendor/autoload.php");
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");
include_once("../../../configs/airtel-ug.php");
include_once("../../../php_functions/airtel-ug.php");
require_once('../../../php_functions/AfricasTalkingGateway.php');
$result  = file_get_contents('php://input');


$logFile = 'airtel-c2b-notice-logs.txt';
$log = fopen($logFile, "a");

fwrite($log, $result . $fulldate . "\n");
fclose($log);

$data = json_decode(trim($result), true);
$ref_id = $data["transaction"]["id"];
$status_code = $data["transaction"]["status_code"];
$message = $data["transaction"]["message"];

$sms_result =  fetchonerow('o_sms_interaction', "ref_id = '$ref_id' AND status = 1", 'uid, sender_phone, message, link_id, customer_id');
$payer_phone = $mobile_number = $number = $sms_result['sender_phone'] ?? null;
$linkId = $sms_result['link_id'] ?? null;
$amount = $sms_result['message'] ?? 0;
$amount = doubleval($amount);
$sms_uid = $sms_result['uid'] ?? 0;
$cust_id = $sms_result['customer_id'] ?? 0;

// ensure transaction succeded
if ($status_code == 'TF') {
    // send an sms feedback via Africas Talking
    if (validate_phone($number) && strlen($message) > 5) {
        send_sms_interactive($number, $message, $linkId);
        store_event('o_customers', $cust_id, "USSD Payment response => $message");
    } else {
        store_event('o_customers', $cust_id, "USSD Payment Found Invalid Phone Number or too Short Message Content.");
    }
    exit(); // no further script execution
} else {

    // we have $status_code = TS => Transaction Succeded
    // authenticate callback response
    $hash = $data['hash']; // store the hash value before unsetting it
    unset($data["hash"]); // get rid of hash
    $auth_value = isCallbackAuthentic($hash, $data);

    if ($auth_value == 0) {
        store_event('o_sms_interaction', $sms_uid, "Payment With SMS Reference ID $ref_id Callback Response Body Could not be Authenticated!");
        exit();
    }
    store_event('o_customers', $cust_id, "USSD Payment response => $message");
}

// prepare necessary data to store in the database
$transaction_id = $data["transaction"]["airtel_money_id"] ?? $ref_id;
$message_parts = explode(" ", $message);
$amount_paid = doubleval(str_replace(',', '', $message_parts[2]));
$account_balance = doubleval(str_replace(',', '', $message_parts[17]));
$payment_date = date("Y-m-d", strtotime($message_parts[19]));
$payment_time = $message_parts[20];
$branch_id = 0;
$latest_loan_id = 0;
$product_id = 0;
$account_number = "";
$customer_name = "";

////----------Update Paybill Balance
if ($account_balance > 0) {
    updatedb('o_summaries', "value_= $account_balance, last_update='$fulldate'", "uid=6");
}

if ($amount > 0 && input_length($transaction_id, 5) == 1) {
    $customer_det = fetchonerow('o_customers', "primary_mobile = $payer_phone", 'uid, branch, full_name, primary_mobile, primary_product');
    $customer_id = intval($customer_det['uid']);
    $customer_name = $customer_det["full_name"] ?? "";
    $account_number = $customer_det['primary_mobile'] ?? "";
    $product_id = $customer_det['primary_product'] ?? 0;
    if ($customer_id > 0) {
    } else {

        // checks if customer paid using alternative number
        $customer_det = fetchonerow('o_customer_contacts', "value = '$payer_phone' AND status = 1", "customer_id");
        $customer_id = intval($customer_det['customer_id']);

        if ($customer_id > 0) {
            $customer = fetchonerow('o_customers', "uid = $customer_id", 'branch, full_name, primary_mobile, primary_product');

            $customer_det['branch'] = $customer['branch'] ?? 0;
            $customer_name = $customer['full_name'] ?? "";
            $account_number = $customer_det['primary_mobile'] ?? "";
            $product_id = $customer_det['primary_product'] ?? 0;
        }
    }

    if ($customer_id > 0) {
        $branch_id = intval($customer_det['branch']);
        $latest_loan = fetchmaxid('o_loans', "customer_id = $customer_id AND disbursed = 1 AND paid = 0", "uid, current_co, current_agent");
        $latest_loan_id = intval($latest_loan['uid']);
        $collector = intval($latest_loan['current_co']);
        $current_agent = intval($latest_loan['current_agent']);
        if ($current_agent > 0) {
            $collector = $current_agent;
        } else {
            $collector = $latest_loan['current_co'];
        }
    } else {
        $latest_loan_id = 0;
        $collector = 0;
        $branch_id = 0;
    }

    $payment_method = 3;
    $status = 1;
    $details = "From USSD/STK Push ($customer_name)";
    $added_by = 1; // a default


    $fds = array('customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'payment_date', 'recorded_date', 'record_method', 'added_by', 'collected_by', 'comments', 'status');
    $vals = array($customer_id, $branch_id, $payment_method, "$mobile_number", $amount, "$transaction_id", $latest_loan_id, "$payment_date", "$fulldate", "USSD/STK PUSH", $added_by, "$collector", "$details", $status);

    $save = addtodb('o_incoming_payments', $fds, $vals);
    if ($save == 1) {
        echo "SAVED";
        if ($latest_loan_id > 0) {
            recalculate_loan($latest_loan_id);

            $ld = fetchonerow("o_incoming_payments", "transaction_code = '$transaction_id'", "uid");
            $max_pid = $ld["uid"];

            $balance = loan_balance($latest_loan_id);

            //// ====== logging
            $payload = json_encode([
                "loan_id" => $latest_loan_id,
                "balance" => $balance,
                "max_pid" => $max_pid
            ]);

            // echo "Payload BEFORE UPDATING P_BALANCE => ". $payload;

            // $logFile = 'airtel-c2b-notice-logs.txt';
            // $log = fopen($logFile, "a");

            // fwrite($log, $payload . $fulldate . "\n");
            // fclose($log);

            ////// ========== End Logging


            $balanceup = updatedb("o_incoming_payments", "loan_balance = $balance", "uid = $max_pid");

            // echo "Payload AFTER UPDATING P_BALANCE $balanceup => $payload";

            //----Notify USER if we have a valid phone number 
            if (validate_phone($account_number) && $latest_loan_id > 0) {
                if ($balance > 0) {
                    $pay_state = 'PARTIAL_PAYMENT';
                    product_notify($product_id, 0, $pay_state, 0, $latest_loan_id, $account_number);
                } else {
                    $pay_state = 'FULL_PAYMENT';
                    product_notify($product_id, 0, $pay_state, 5, $latest_loan_id, $account_number);
                }
            }
        }
    } else {
        echo "NOT SAVED";
    }
}else {
    echo "INVALID TRANSACTION $amount ";
}
