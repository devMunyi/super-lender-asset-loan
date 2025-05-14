<?php
session_start();
include_once '../configs/20200902.php';
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");
require("../php_functions/rmqUtils.php"); // must be imported for rmq.php to work
include_once("../mpesa/b2c-with-validation.php");

$req_id = $_GET['r'];
$data  = file_get_contents('php://input');
/*
$logFile = 'log-out.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.''.date('Y-m-d H:i:s')."\n");
fclose($log);  */


$result = json_decode(trim($data), true);
$ResultCode = $result['Result']['ResultCode'];
$ResultDesc = $result['Result']['ResultDesc'] ?? 'Details Unavailable';
$TransactionID = $result['Result']['TransactionID'];
$TransactionDetails = $result['Result']['ResultParameters']['ResultParameter'];


//var_dump($TransactionDetails[0]);

foreach ($TransactionDetails as $key => $value) {
    if ($value['Key'] == 'TransactionAmount') {
        $TransactionAmount = $value['Value'];
    }
    if ($value['Key'] == 'B2CUtilityAccountAvailableFunds') {
        $B2CUtilityAccountAvailableFunds = $value['Value'];
    }
    if ($value['Key'] == 'ReceiverPartyPublicName') {
        $ReceiverPartyPublicName = $value['Value'];
    }
}

$user_d = explode('-', $ReceiverPartyPublicName);
$mobile = trim($user_d[0]);
$name = trim($user_d[1]);


///////-------------------------End of get company details

if ($req_id > 0 && $ResultCode == '0') {
    ////////-----------Its a Loan, lets update it
    $loan_det = fetchonerow('o_loans', "uid='$req_id'", "given_date, next_due_date, final_due_date, period, period_units");
    $given_date = $loan_det['given_date'];
    $next_due_date = $loan_det['next_due_date'];
    $final_due_date = $loan_det['final_due_date'];
    $period = $loan_det['period'];
    $period_units = $loan_det['period_units'];

    if ($given_date != $date) {
        $diff = datediff3($date, $given_date);
        $new_next_d = dateadd($next_due_date, 0, 0, $diff);

        //$final_due_d = move_to_monday(dateadd($final_due_date, 0,0, $diff));
        $final_due_d = move_to_monday(final_due_date($date, $period, $period_units));
        $new_dates = ": NEW Dates-> Disbursed: $date, Next Due: $new_next_d, Final Due: $final_due_d";
        $new_dates_update = ", given_date='$date', next_due_date='$new_next_d', final_due_date='$final_due_d'";
    } else {
        $new_dates = "";
        $new_dates_update = "";
    }

    $save = updatedb('o_loans', "transaction_code='$TransactionID', transaction_date='$fulldate', disburse_state='DELIVERED', disbursed=1, status=3 $new_dates_update", "uid='$req_id'");
    store_event('o_loans', $req_id, "Success: on $fulldate , M-Pesa delivered the funds with message ($ResultDesc). Transcode ($TransactionID) $new_dates");
} else if ($req_id > 0 && $ResultCode != '0') {

    $failed_b2c_msg = "Error: on $fulldate , M-Pesa was unable to deliver the funds with message ($ResultDesc). Transcode ($TransactionID)";
    // insufficient balance notice case
    $low_balance_message = "balance is insufficient";
    if (strpos($ResultDesc, $low_balance_message) !== false && strlen($ResultDesc) > strlen($low_balance_message)) {

        // immediately set b2c a low balance to prevent removing loans from queue with low balance
        $value_ = mt_rand(1, 1000);
        updatedb('o_summaries', "value_='$value_', last_update='$fulldate'", "uid=1");

        // store insufficient balance event
        store_event('o_loans', $req_id, "$failed_b2c_msg");

        // requeue the loan
        mpesa_loan_requeue($req_id, 'Requeued', QueueType::LOW_BAL);

        // clear the messages
        unset($low_balance_message, $failed_b2c_msg, $ResultDesc);

    } else {

        // other failed cases
        $save = updatedb('o_loans', "transaction_code='$TransactionID', disburse_state='FAILED', disbursed=0, status=12", "uid='$req_id'");
        store_event('o_loans', $req_id, "$failed_b2c_msg");
    }

    // ===== end of sending sms to admin
}

///---------Lets update the balance
if ($B2CUtilityAccountAvailableFunds > 0) {
    updatedb('o_summaries', "value_='$B2CUtilityAccountAvailableFunds', last_update='$fulldate'", "uid=1");
}

include_once("../configs/close_connection.inc");
