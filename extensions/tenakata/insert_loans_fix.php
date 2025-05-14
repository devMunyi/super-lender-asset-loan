<?php

//ini_set('memory_limit', '512M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// files includes
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

// Read the JSON file content
$paymentsJson = file_get_contents('loans-data.json');


// Convert JSON data to a PHP array
$paymentsData = json_decode($paymentsJson, true);

$members = [];
$branches = [];



// customer branches
$cust_branches = [];
$cust_phones = [];

$disbursementsJson = file_get_contents('loans-data.json');



$loansData = json_decode($disbursementsJson, true);

$statuses = table_to_obj('o_loan_statuses',"uid > 0","100","uid","name");




// Check if "data" key exists and it is an array
if (isset($loansData['data']) && is_array($loansData['data'])) {

    // Iterate through the "data" array
    foreach ($loansData['data'] as $loanData) {
        // echo json_encode($loanData);
        // extracting necessary data
        $uid = $loanData['id'] ?? 0;
        echo "jdjd";


        $product_id = 1;
        $status = $loanData['status'] ?? 0; // needs clarification as jisort has status not present in superlender like status
        $state = $loanData['status'] ?? 0; // needs clarification as jisort has status not present in superlender like status
        $status_name = $loanData['status_name'];
        $disbursed = 1;
        $paid = 0;





        // status handler
        // rejected
        if ($status == 10) {
            $disbursed = 0;
            $paid = 0;
            $status = 6;
            $loan_balance = 0;
            $disbursed_amount = 0;
            $total_repayable_amount = 0;
            $next_due_date = '0000-00-00';
            $final_due_date = '0000-00-00';
            // reversed
        } elseif ($status == 12) {
            $status = 11;
            $disbursed = 1;
            $paid = 0;
            $loan_balance = 0;
            $total_repayable_amount = 0;
            $next_due_date = '0000-00-00';
            $final_due_date = '0000-00-00';

            // cleared/complete
        } elseif ($status == 7) {
            $status = 5;
            $disbursed = 1;
            $paid = 1;

            // disbursed
        } elseif ($status == 1 && $given_date != '0000-00-00') {
            $status = 3; // already sent
            $disbursed = 1;
            $paid = 0;

            // pending disbursement
        } elseif ($status == 1 && $given_date == '0000-00-00') {
            $disbursed = 0;
            $paid = 0;
            $status = 1; // jus created


            // disbursed
        } elseif ($status == 9 && $given_date != '0000-00-00') {
            $status = 3;
            $disbursed = 0;
            $paid = 0;
            // pending disbursement / approved but not sent
        } elseif ($status == 9 && $given_date == '0000-00-00') {
            $disbursed = 0;
            $disbursed = 0;
            $paid = 0;
            $status = 2;
        } elseif ($given_date != '0000-00-00') {
            //$status = 3;

        } else {
            $status = 1;
            $disbursed = 0;
            $paid = 0;
        }

     //   echo "UID:$uid, Disbursed:$disbursed, Paid:$paid, Status: $status .".$statuses[$status].", Jisort: $state, $status_name<br/>";

        $update = updatedb('o_loans',"disbursed='$disbursed', paid='$paid', status='$status'","uid='$uid'");
        echo $update;

       /* $fds = array('uid', 'loan_code', 'customer_id', 'account_number', 'product_id', 'loan_type', 'loan_amount', 'disbursed_amount', 'total_repayable_amount', 'total_repaid', 'loan_balance', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_addons', 'total_deductions', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'current_instalment_amount', 'given_date', 'next_due_date', 'final_due_date', 'added_by', 'current_agent', 'current_branch', 'added_date', 'loan_stage', 'loan_flag', 'transaction_code', 'transaction_date', 'application_mode', 'disburse_state', 'disbursed', 'status');
        $vals = array($uid, $loan_code, $customer_id, $account_number, $product_id, $loan_type, $loan_amount, $disbursed_amount, $total_repayable_amount, $total_repaid, $loan_balance, $period, $period_units, $payment_frequency, $payment_breakdown, $total_addons, $total_deductions, $total_instalments, $total_instalments_paid, $current_instalment, $current_instalment_amount, "$given_date", "$next_due_date", "$final_due_date", $added_by, $current_agent, $current_branch, "$added_date", $loan_stage, $loan_flag, "$transaction_code", "$transaction_date", "$application_mode", "$disburse_state", $disbursed, $status);

        $create = addtodb('o_loans', $fds, $vals);
        echo 'Entry UID: ' . $uid . ' TABLE INSERT RESPONSE: ' . $create . '<br>';
        if ($create == 1) {
            $inserted += 1;
        } else {
            $skipped += 1;
        } */
    }
} else {
    echo "Error: Unable to read loan data." . PHP_EOL;
}


// SELECT * FROM `o_loans` WHERE status NOT IN (10, 12) order by uid DESC


?>