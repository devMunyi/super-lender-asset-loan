<?php

// files includes
include_once ("../../configs/conn.inc");
include_once ("../../php_functions/functions.php");

// Read the JSON file content
$paymentsJson = file_get_contents('repayments.json');
$loansJson = file_get_contents('applied_loans.json');
$disbursementsJson = file_get_contents('loan_disbursements.json');
$membersJson = file_get_contents('members.json');

// Convert JSON data to a PHP array
$paymentsData = json_decode($paymentsJson, true);
$loansData = json_decode($loansJson, true);
$disbursementsData = json_decode($disbursementsJson, true);
$membersData = json_decode($membersJson, true);


// get members/customers branch info
if (isset($membersData['data']) && is_array($membersData['data'])) {

    // set arrays to use
    $members_branch = [];
    
    foreach ($membersData['data'] as $memberData) {
        $member_id = $memberData['id'];
        $branch_uid = $memberData['branch'] ?? 0;
        $members_branch[$member_id] = $branch_uid;
    }
}

// get loans transactions code info
if (isset($disbursementsData['data']) && is_array($disbursementsData['data'])) {

    // set arrays to use
    $trans_codes = [];
    $disb_amounts = [];
    $disbIteration = [];
    
    
    foreach ($disbursementsData['data'] as $disbData) {
        
        $disb_loan_id = intval($disbData['loan']);
        $disb_amount = doubleval($disbData['credit']);
        $trans_code = $disbData['transaction_code'] ?? '';

        // stores iterations target to reach max 3 which contains credited amount
        $disbIteration = obj_add($disbIteration, $disb_loan_id, 1);
        if($disb_loan_id > 0 && !isset($trans_codes[$disb_loan_id]) && $disbIteration[$disb_loan_id] < 4){
            $trans_codes[$disb_loan_id] = $trans_code;
            $disb_amounts[$disb_loan_id] = $disb_amount;
        }
    }
}


if (isset($paymentsData['data']) && is_array($paymentsData['data'])) {

    // set arrays to use
    $repaid = [];
    $current_instalments = [];
    $current_instalment_amounts = [];
    $addons = [];
    $next_due_dates = []; 
    $final_due_dates = []; 
    $instalments_p_count =  [];
    $isOverdueLoan = [];
    
    foreach ($paymentsData['data'] as $payData) {

        // set necessary variables
        $loan_id = intval($payData['loan']);
        $payment_no = $payData['payment_no'] ?? 0;
        $interest = doubleval($payData['interest']);
        $fees = doubleval($payData['fees']);
        $penalties = doubleval($payData['penalties']);
        $paid = doubleval($payData['paid']);
        $bal = doubleval($payData['balance_of_loan']);
        $outstanding = doubleval(($payData['outstanding']));
        $due = doubleval($payData['due']);
        $expected_instal_repay_date = new DateTime($payData['date']);
        $expected_instal_repay_date_r = $payData['date'];
        $current_date = new DateTime($date);


        // (1) handle overdue loans status loans
        if(isset($expected_instal_repay_date) && $expected_instal_repay_date < $current_date){
            $isOverdueLoan[$loan_id] = 'YES';
        }else {
            $isOverdueLoan[$loan_id] = 'NO';
        }


        // (2.1) total_repaid handler
        if($loan_id > 0 && isset($repaid[$loan_id])){
            $repaid = obj_add($repaid, $loan_id, $paid);
        }
        // (2.2) handle a skip for first entry which doesn't constitute payment instalment
        if(!isset($repaid[$loan_id])){
            $repaid[$loan_id] = 0;
        }


        // (3) loan_balance, (4) current_instalment, (5) current_instalment_amount, 
        // and (6) addons handler
        if(isset($expected_instal_repay_date) && $current_date > $expected_instal_repay_date) {
            $current_instalments[$loan_id] = $payment_no;
            
            if($paid >= $due){
                $current_instalment_amounts[$loan_id] = $paid - $due;
            }else{
                $current_instalment_amounts[$loan_id] = $due - $paid;
            }

            // addons
            $add_sum = $interest + $fees + $penalties;
            $addons = obj_add($addons, $loan_id, $add_sum);
        }

        // (7) next_due_date handler
        if(isset($expected_instal_repay_date) && $current_date <= $expected_instal_repay_date && !isset($next_due_dates[$loan_id])) {
            $next_due_dates[$loan_id] = $expected_instal_repay_date_r; 

            // past recorded loans
        }else if(isset($expected_instal_repay_date) && $current_date >              $expected_instal_repay_date){
            $next_due_dates[$loan_id] = $expected_instal_repay_date_r;
        }   

        // (8) final_due_date handler
        if(isset($expected_instal_repay_date) && $current_date <= $expected_instal_repay_date) {
            $final_due_dates[$loan_id] = $expected_instal_repay_date_r; // for final_due_date
        }elseif(isset($expected_instal_repay_date) && $current_date > $expected_instal_repay_date){
            $final_due_dates[$loan_id] = $expected_instal_repay_date_r; // for final_due_date
        }
        
        // (9.1) total_instalments_paid handler
        if($loan_id > 0 && isset($instalments_p_count[$loan_id]) && $paid > 0 && !$outstanding > 0){
            $instalments_p_count = obj_add($instalments_p_count, $loan_id, 1);
        }
        // (9.2) handle a skip for first entry which doesn't constitute payment instalment
        if(!isset($instalments_p_count[$loan_id])){
            $instalments_p_count[$loan_id] = 0;
        }
    }
}


// set counters
$inserted = 0;
$skipped = 0;

// Check if "data" key exists and it is an array
if (isset($loansData['data']) && is_array($loansData['data'])) {
    // Iterate through the "data" array
    foreach ($loansData['data'] as $loanData) {
        // echo json_encode($loanData);
        // extracting necessary data
        $uid = $loanData['id'] ?? 0; 
        $loan_code = $loanData['loan_reference_no'] ?? '';
        $customer_id = $loanData['member'] ?? 0;
        $account_number = $loanData['member_mobile_no'] ?? '';
        $account_number = str_replace([' ', '+'], '', $account_number);
        $product_id = 0;
        $loan_type = $loanData['loan_type'] ?? 0;
        $loan_amount = $loanData['amount'] ?? 0;
        $disbursed_amount = $loanData['approved_amount'] ?? 0;
        $total_repaid = $repaid[$uid] ?? 0;
        $period = 30;
        $period_units = 1;
        $payment_frequency = 1;
        $payment_breakdown = '';
        $total_addons = $addons[$uid] ?? 0;
        $total_repayable_amount = $disbursed_amount + $total_addons;
        $loan_balance = $total_repayable_amount - $total_repaid;
        $total_deductions = 0.00;
        $total_instalments = 4;
        $given_date = $loanData['disbursement_date'] ?? '0000-00-00';
        $status = $loanData['status'] ?? 0;
        if($status == 12){
            $next_due_date = '0000-00-00';
        }else {
            $next_due_date = $next_due_dates[$uid] ?? '0000-00-00';
        } 

        if($status == 12){
            $final_due_date = '0000-00-00';
        }else {
            $final_due_date = $final_due_dates[$uid] ?? '0000-00-00';
        } 

        $total_instalments_paid = $instalments_p_count[$uid] ?? 0;
        $current_instalment = $current_instalments[$uid] ?? 0;
        

        // loans available statuses
         //1 => active
         //7 => complete
         //10 => rejected
         //12 => reversed
        if ($status == 1) {
            $current_instalment_amount = $current_instalment_amounts[$uid] ?? 0;
        } else {
            $current_instalment_amount = 0;
        }

        // loans status update to match that of superlender
        $overdueLoan = $isOverdueLoan[$uid] ?? '';

        /// ovedue loan statuses
        if($overdueLoan == 'YES'){
            $status = 7;
        
        // other statuses
        }else {

            // active
            if($status == 1 && $paid == 0){
                $status = 3;
            // partially paid
            }elseif($status == 1 && $paid > 0){
                $status = 4;
            // cleared/complete
            }elseif($status == 7) {
                $status = 5;
            // rejected
            }else if($status == 10) {
                $status = 6;
            // reversed
            }elseif($status == 12){
                $status = 11;
            }
        }

        $added_by = 0;
        $current_agent = 0;
        $current_branch = $members_branch[$customer_id] ?? 0;
        $added_date = $loanData['date_of_loan_application'] ?? '0000-00-00 00:00:00';
        $loan_stage = 0;
        $loan_flag = 0;
        if($disbursed_amount == $disb_amounts[$uid]){
            $transaction_code = $trans_codes[$uid] ?? '';
        }else {
            $transaction_code = '';
        }
        $transaction_date = '0000-00-00 00:00:00';
        $application_mode = 'Manual';
        $disburse_state = $loanData['status_name'] ?? 'Unknown';

        if($given_date == '0000-00-00'){
            $disbursed = 0;
        }else {
            $disbursed = 1;
        }

        $fds = array('uid','loan_code','customer_id','account_number','product_id','loan_type','loan_amount','disbursed_amount','total_repayable_amount','total_repaid', 'loan_balance', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_addons', 'total_deductions', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'current_instalment_amount', 'given_date', 'next_due_date', 'final_due_date', 'added_by', 'current_agent', 'current_branch', 'added_date', 'loan_stage', 'loan_flag', 'transaction_code', 'transaction_date', 'application_mode', 'disburse_state', 'disbursed', 'status');
        $vals = array($uid, $loan_code, $customer_id, $account_number, $product_id, $loan_type, $loan_amount, $disbursed_amount, $total_repayable_amount, $total_repaid, $loan_balance, $period, $period_units, $payment_frequency, $payment_breakdown, $total_addons, $total_deductions, $total_instalments, $total_instalments_paid, $current_instalment, $current_instalment_amount, "$given_date", "$next_due_date", "$final_due_date", $added_by, $current_agent, $current_branch, "$added_date", $loan_stage, $loan_flag, "$transaction_code", "$transaction_date", "$application_mode", "$disburse_state", $disbursed, $status);
        
        $create = addtodb('o_loans' ,$fds, $vals);
        echo 'Entry UID: '.$uid .'DB_INSERT RESPONSE: '.$create .'<br>'; 
        if($create == 1)
        {
            $inserted += 1;
        }
        else
        {
            $skipped += 1;
        }
    }

} else {
    echo "Error: Unable to read loan data." . PHP_EOL;
}

echo "INSERTED LOANS: $inserted <br>";
echo "SKIPPED LOANS: $skipped <br>";

?>
