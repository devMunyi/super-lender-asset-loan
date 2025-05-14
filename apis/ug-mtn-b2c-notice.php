<?php
session_start();
include_once '../configs/20200902.php';

$data  = file_get_contents('php://input');
$result = json_decode($data, true);

$trans_uid = trim($result['financialTransactionId']);
$trans_uid2 = trim($result['externalId']);
$trans_amount = trim($result['amount']);

$parts = explode('-', $trans_uid2);

$company = intval($parts[0]);
$loan_id = intval($parts[1]);
$trials = intval($parts[2]);
$queue_id = intval($parts[3]);

$financialTransactionId = $trans_uid > 0 ? $trans_uid : $trans_uid2; // unique transaction id
$transc_status = $result['status'] ?? 'UNKNOWN'; // can be: PENDING, SUCCESSFUL, FAILED
$TransactionDetails = '';
// We cannot rely on status code as it returns 200 regardless of whether the transaction status is: 
// a) PENDING, b) SUCCESSFUL or c) FAILED
if ($transc_status == "PENDING") {
    $TransactionDetails = $result['reason'];
}

if ($transc_status == "FAILED") {
    $TransactionDetails = $result['reason'];
}

if ($company > 0 && $loan_id > 0) {

    include_once("../php_functions/functions.php");
    include_once("../php_functions/mtn_functions.php");
    $company_d = company_details($company);
    if ($company_d['uid'] > 0) {

        $db = $company_d['db_name'];
        $_SESSION['db_name'] = $db;
        include_once("../configs/conn.inc");
        include_once("../configs/mtn_configs.php");
        ///////-------------------------End of get company details

        if ($loan_id > 0 && $transc_status === 'SUCCESSFUL') {
            ////////-----------Its a Loan, lets update it
            $loan_det = fetchonerow('o_loans', "uid=$loan_id", "given_date, next_due_date, final_due_date, period, period_units");
            $given_date = $loan_det['given_date'];
            $next_due_date = $loan_det['next_due_date'];
            $final_due_date = $loan_det['final_due_date'];
            $TransactionDetails = 'SUCCESSFUL';
            $period = $loan_det['period'];
            $period_units = $loan_det['period_units'];

            if ($given_date != $date) {
                $diff = datediff3($date, $given_date);
                $new_next_d = dateadd($next_due_date, 0, 0, $diff);

                // $final_due_d = move_to_monday(dateadd($final_due_date, 0, 0, $diff));
                $final_due_d = move_to_monday(final_due_date($date, $period, $period_units));
                $new_dates = " NEW Dates-> Disbursed: $date, Next Due: $new_next_d, Final Due: $final_due_d";
                $new_dates_update = ", given_date='$date', next_due_date='$new_next_d', final_due_date='$final_due_d'";
            } else {
                $new_dates = "";
                $new_dates_update = "";
            }

            // mark loan as disbursed
            $save = updatedb('o_loans', "transaction_code='$financialTransactionId',transaction_date='$fulldate', disburse_state='DELIVERED', disbursed=1, status=3 $new_dates_update", "uid=$loan_id");

            // store success event
            store_event('o_loans', $loan_id, "Transaction was successful. MTN delivered the funds with message ($TransactionDetails). Transcode ($financialTransactionId). $new_dates");
        } else {
            if ($TransactionDetails === 'NOT_ENOUGH_FUNDS') {
                // return loan to the queue for auto resend
                mtn_loan_requeue($loan_id, $queue_id, $TransactionDetails);
            } else {

                // mark loan as failed
                $save = updatedb('o_loans', "transaction_code='$financialTransactionId', disburse_state='FAILED', disbursed=0", "uid=$loan_id");
            }

            // store event
            store_event('o_loans', $loan_id, "Transaction Failed. MTN was unable to deliver the funds with message ($TransactionDetails). Transcode ($financialTransactionId).");
        }

        ///---------Lets update the balance
        updateMtnUtilityBalance();
    }
} else {
    ///------Probably a suspense request
}
