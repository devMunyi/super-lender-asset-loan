<?php
include_once("../services/util.php");

if ($balance <  0) {
    //===== Begin overpayment split handler
    $data = array(
        'customer_id' => $customer_id,
        'branch_id' => $branch_id,
        'group_id' => $group_id,
        'payment_method' => $payment_method,
        'payment_for' => $payment_for,
        'mobile_number' => $MSISDN,
        'amount' => $TransAmount,
        'parent_pid' => $max_pid,
        'transaction_code' => $TransID,
        'loan_id' => $latest_loan_id,
        'loan_code' => $BillRefNumber,
        'balance' => $balance,
        'payment_date' => $TransTime,
        'record_method' => 'API',
        'added_by' => $added_by,
        'collected_by' => $current_agent,
        'comments' => 'From API',
        'status' => 1
    );

    splitOverpayment($data);
}
