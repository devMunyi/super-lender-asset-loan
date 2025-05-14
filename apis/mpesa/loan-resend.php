<?php

include_once '../../configs/20200902.php';
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");



$maxDaysAgo = datesub($date, 0, 0, 133);

echo "maxDaysAgo: $maxDaysAgo <br/>";

$mpesa_configs = fetchonerow('o_mpesa_configs', "uid=1", "property_value, initiator_name, security_credential, enc_token, enc_token_key");

if (count($mpesaB2CStatusCheckLoanProducts) > 0) {
    $targetLoanProductsString = implode(",", $mpesaB2CStatusCheckLoanProducts);

    $sql = "SELECT l.uid from o_loans l left join o_events e ON e.fld = l.uid where l.given_date >= '$maxDaysAgo' AND l.disburse_state = 'NONE' AND l.disbursed = 0 AND l.status = 2 AND l.product_id IN ($targetLoanProductsString) AND e.tbl = 'o_loans' AND e.event_details = 'Mobile Money Initiated via queue' order by l.uid DESC limit 5;";

    try {

        $sqlQueryResult = mysqli_query($con, $sql);

        if (mysqli_num_rows($sqlQueryResult) > 0) {
            $pendingLoansUid = array();
            while ($row = mysqli_fetch_array($sqlQueryResult)) {
                $pendingLoansUid[] = $row['uid'];
            }
        }else {
            exit(errormes("No pending loans found"));
        }
    } catch (Exception $e) {
        exit(errormes("Error. Try again"));
    }
} else {
    exit(errormes("No target loan products found"));
}


foreach ($pendingLoansUid as $loan_id) {

    echo "Pending Loans List: $loan_id <br/>";


}

exit(sucmes("Success"));


$loan_id = decurl($_POST['loan_id']);

///////----------------Validation
if ($loan_id > 0) {
} else {

    exit(errormes("Loan code needed"));
}

$loan_d = fetchonerow('o_loans', "uid='$loan_id'");
$disbursed_amount = $loan_d['disbursed_amount'];
$msisdn = $loan_d['account_number'];
$proceed = 0;
$update_loan_stage = updatedb('o_loans', "disburse_state='NONE'", "uid=" . $loan_id);
if ($update_loan_stage == 1) {


    ///-----Queue Message
    $queued = fetchonerow('o_mpesa_queues', "loan_id='$loan_id'", "trials, status, uid");
    $q_status = $queued['status'];
    $q_trials = $queued['trials'];
    $q_uid = $queued['uid'];

    if ($q_uid > 0) {
        /////----Already there
        if ($q_trials > 2) {
            exit(errormes("Error. Already resent"));
        } else {
            if ($q_status == 1) {
                exit(errormes("Error. Already queued"));
            } else {
                $update_ = updatedb('o_mpesa_queues', "status=1, feedbackcode='Requeued'", "loan_id='$loan_id'");
                if ($update_ == 1) {
                    echo sucmes("Success. Resent");
                    $event = "Loan resent by [" . $userd['name'] . "(" . $userd['email'] . ")] on [$fulldate]";
                    store_event('o_loans', $loan_id, "$event");
                    $proceed = 1;
                } else {
                    exit(errormes("Error. Unable to resend"));
                }
            }
        }
    } else {
        ////----Not queued, queue
        $fds = array('loan_id', 'amount', 'added_date', 'trials', 'status');
        $vals = array("$loan_id", "$disbursed_amount", "$fulldate", '1', '1');
        $queue = addtodb('o_mpesa_queues', $fds, $vals);
        if ($queue == 1) {
            echo sucmes("Success. Money resent");
            $event = "Loan resent by the system via cron service on [$fulldate]";
            store_event('o_loans', $loan_id, "$event");
        } else {
            exit(errormes("Error. Not resent"));
        }
    }
} else {
    exit(errormes("Oops!.An error occurred. Try again"));
}
