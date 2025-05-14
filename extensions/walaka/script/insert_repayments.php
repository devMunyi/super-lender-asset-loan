<?php 

// files includes
include_once ("../../../configs/conn.inc");
include_once ("../../../php_functions/functions.php");
include_once ("../../../php_functions/mtn_functions.php");
include_once ("./reusable.php");

$loanIds = [];
$customerIds = [];
$branchIds = [];
$mobileNums = [];

$loans = fetchtable2('o_loans', 'uid > 0', 'uid', 'ASC', 'uid, loan_code, customer_id, current_branch, account_number');

while($l = mysqli_fetch_assoc($loans)){
    $uid_ = $l['uid'];
    $loan_code_ = $l['loan_code'];
    $customer_id_ = $l['customer_id'];
    $branch_ = $l['current_branch'];
    $mobile_number_ = $l['account_number'];

    if(!isset($mobileNums[$loan_code_])){
        $mobileNums[$loan_code_] = $mobile_number_;
    }

    if(!isset($branchIds[$loan_code_])){
        $branchIds[$loan_code_] = $branch_;
    }

    if(!isset($customerIds[$loan_code_])){
        $customerIds[$loan_code_] = $customer_id_;
    }

    if(!isset($loanIds[$loan_code_])){
        $loanIds[$loan_code_] = $uid_;
    }
}
$file = "../data/dagoretti_repayments.csv";
$handle = fopen($file, "r");
$is_first_row = true; // Flag variable to track the first row

// set counters 
$inserted = 0;
$skipped = 0;
$iteration = 0;
while(($rp_data = fgetcsv($handle, 1000000, ",")) !== FALSE){
    if ($is_first_row) {
        $is_first_row = false;
        continue; // Skip the first row and move to the next iteration
    }

    if($iteration > 10){
        break;
    }

    // Application ID(0), Loan No.(1), Repaid Date(2), Mpesa Code(3), Amount(4), Balance(5)
    $cust_code  = trim($rp_data[0]) ?? 0;
    $b = strtoupper($cust_code[0]);
    $cust_code = customerCode($cust_code, $b);
    $loan_num = trim($rp_data[1]) ?? 0;
    $loan_code = $cust_code.'-'.$loan_num;
    $loan_id = $loanIds[$loan_code] ?? 0;
    $customer_id = $customerIds[$loan_code] ?? 0;
    $branch_id = $branchIds[$loan_code] ?? 0;
    $payment_method = 3; // mobile payment
    $payment_category = 1; // loan_repayment, 2 // registration fee, 3 // downpayment
    $mobile_number = $mobileNums[$loan_code] ?? '';
    $amount = doubleval(trim($rp_data[4]));
    $loan_balance = doubleval(trim($rp_data[5]));

    $pdate = trim($rp_data[2]);
    $payment_date_ = null; // Initialize the variable
    if (DateTime::createFromFormat("m/d/Y H:i:s", $pdate)) {
        $payment_date_ = DateTime::createFromFormat("m/d/Y H:i:s", $pdate);
    }elseif (DateTime::createFromFormat("m-d-Y H:i:s", $pdate)) {
        $payment_date_ = DateTime::createFromFormat("m-d-Y H:i:s", $pdate);
    } elseif (DateTime::createFromFormat("m/d/Y H:i", $pdate)) {
        $payment_date_ = DateTime::createFromFormat("m/d/Y H:i", $pdate);
    }elseif(DateTime::createFromFormat("m-d-Y H:i", $pdate)) {
        $payment_date_ = DateTime::createFromFormat("m-d-Y H:i", $pdate);
    } elseif (DateTime::createFromFormat("m/d/Y", $pdate)) {
        $payment_date_ = DateTime::createFromFormat("m/d/Y", $pdate);
        // Set time explicitly to 00:00:00 when there's no time information
        $payment_date_->setTime(0, 0, 0);
    }elseif (DateTime::createFromFormat("m-d-Y", $pdate)) {
        $payment_date_ = DateTime::createFromFormat("m-d-Y", $pdate);
        // Set time explicitly to 00:00:00 when there's no time information
        $payment_date_->setTime(0, 0, 0);
    }

    $payment_date = $payment_date_ ? $payment_date_->format("Y-m-d") : '0000-00-00';
    $recorded_date = $payment_date_ ? $payment_date_->format("Y-m-d H:i:s") : '0000-00-00 00:00:00';

    $transaction_code = trim($rp_data[3]) ? trim($rp_data[3]) : strtoupper(generateRandomString(10));

    if(strlen($transaction_code) != 10 && strlen($transaction_code) > 0 && strlen($transaction_code) != 13){
        $transaction_code = $loan_code.'-'.$transaction_code;
    }
    $added_by = 0;
    $record_method = 'MANUAL';

    if($amount > 0){
        $fds = array('customer_id','branch_id','payment_method','payment_category','mobile_number','amount','transaction_code','loan_id','loan_balance', 'payment_date', 'added_by', 'record_method', 'recorded_date');
        $vals = array($customer_id, $branch_id, $payment_method, $payment_category,"$mobile_number", $amount,"$transaction_code",$loan_id, $loan_balance, "$payment_date", $added_by, $record_method, "$recorded_date");
        $create = addtodb('o_incoming_payments',$fds,$vals);
        
        echo 'Entry UID: '.$loan_code.' TABLE INSERT RESPONSE: '.$create .'<br>'; 
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

echo "INSERTED REPAYMENTS: $inserted <br>";
echo "SKIPPED REPAYMENTS: $skipped <br>";
