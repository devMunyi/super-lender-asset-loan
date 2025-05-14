<?php

// files includes
include_once ("../../../configs/conn.inc");
include_once ("../../../php_functions/functions.php");

// Read the JSON file content
$paymentsJson = file_get_contents('../data/repayments.json');
$loansJson = file_get_contents('../data/loans.json');

// Convert JSON data to a PHP array
$paymentsData = json_decode($paymentsJson, true);
$loansData = json_decode($loansJson, true);

$members = [];
$branches = [];

// prepare customer id (member_no) and branch_id (branch) from loansData
if (isset($loansData['data']) && is_array($loansData['data'])) {
    // Iterate through the "data" attching each loan id to point member_no and branch
    foreach ($loansData['data'] as $loanData) {
        $uid = $loanData['id'];

        if(!isset($members[$uid])){
            $members[$uid] = $loanData['member'];
        }

        if(!isset($branches[$uid])){
            $branches[$uid] = $loanData['branch']; // most loans don't have branch attribute;
        }
    }
}


// set counters
$inserted = 0;
$skipped = 0;

// Check if "data" key exists and it is an array
if (isset($paymentsData['data']) && is_array($paymentsData['data'])) {
    // Iterate through the "data" array and print "member_no" and "branch"
    foreach ($paymentsData['data'] as $payData) {
        // Accessing the "member_no" and "branch" values
        $uid = $payData['id'] ?? 0; 
        $loan_id = $payData['loan'] ?? 0;
        $customer_id = $members[$loan_id] ?? 0;
        $branch_id = $branches[$loan_id] ?? 0;
        $payment_method = 2;
        $payment_category = 1;
        $mobile_number = $payData['member_phone'] ?? '';
        $mobile_number = str_replace([' ', '+'], '', $mobile_number);
        $mobile_number = make_phone_valid($mobile_number);

        if ($mobile_number == '254') {
            $mobile_number = '';
            $record_method = 'MANUAL';
            $payment_method = 5;
        }else {
            $record_method = 'API';
        }

        $amount = $payData['paid'] ?? 0;
        $transaction_code = 'transc-'.$uid;
        $loan_balance = $payData['balance_of_loan'] ?? 0;
        $payment_date = $payData['paid_date'] ?? '0000-00-00';
        $recorded_date = $payment_date . " 00:00:00";
        $added_by = 0;
                    

        if($amount > 0){
            $fds = array('uid','customer_id','branch_id','payment_method','payment_category','mobile_number','amount','transaction_code','loan_id','loan_balance', 'payment_date', 'added_by', 'record_method', 'recorded_date');
            $vals = array($uid, $customer_id, $branch_id, $payment_method, $payment_category,"$mobile_number", $amount,"$transaction_code",$loan_id, $loan_balance, "$payment_date", $added_by, $record_method, "$recorded_date");
            $create = addtodb('o_incoming_payments',$fds,$vals);
            echo 'Entry UID: '.$uid .' TABLE INSERT RESPONSE: '.$create .'<br>'; 
            if($create == 1)
            {
                $inserted += 1;
            }
            else
            {
                $skipped += 1;
            }
        }
        
    }
} else {
    echo "Error: Unable to read loan data." . PHP_EOL;
}

echo "INSERTED REPAYMENTS: $inserted <br>";
echo "SKIPPED REPAYMENTS: $skipped <br>";

?>