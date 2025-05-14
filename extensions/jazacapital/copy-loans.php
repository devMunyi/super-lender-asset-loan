<?php
session_start();
include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);


// Open the CSV file
$file = fopen('../../test/loansnew.csv', 'r');

$locations = array(
    "EMBAKASI" => 15,
    "HQ" => 1,
    "KAJIADO" => 22,
    "KIAMBU" => 19,
    "KIKUYU" => 18,
    "LIMURU" => 21,
    "NGONG" => 20,
    "RUIRU" => 16,
    "THIKA" => 17
);

// Skip the header row if it exists
fgetcsv($file);

// Loop through each row
while (($row = fgetcsv($file)) !== false) {
    // Extract the variables
    $uid = $row[0];
    $client_id = $row[1];
    $account_number = $row[7];
    $enc_phone = hash('sha256', $account_number);
    $product_id = 1;
    $loan_type = 0;
    $loan_amount = $row[11];
    $disbursed_amount = $row[18];
    $loan_status = $row[6];
    $interest_amount = $row[15];
    $processing_fee = $row[17];
    $total_repayable_amount = $loan_amount + $interest_amount + $processing_fee;
    $total_addons = $interest_amount + $processing_fee;
    $total_repaid = 0;
    $loan_balance = $total_repayable_amount;
    $period = 30;
    $period_units = 1;
    $payment_frequency = 1;

    $branch = $row[19];
    $branch_id = $locations[$branch];
    // $total_addons = 0;
    $g_date = $row[8];
    $parts = explode('/', $g_date);
    // Rearrange the parts into MySQL format YYYY-MM-DD
    $given_date = $parts[2] . '-' . $parts[0] . '-' . $parts[1];

    $next_due_date = $given_date;



    $final_due_date = dateadd($given_date,0,0, 30);
    $added_by = 1;
    $allocation = 'BRANCH';
    $current_branch = $branch_id;
    $added_date = $given_date;
    $loan_stage = 4;
    $application_mode = 'MANUAL';
    //$disburse_state = ;
    // $disbursed = ;
    // $paid = ;
    // $status = ;
    if($loan_status == 'Cleared' || $loan_status == 'Closed'){
        ////------Closed
        $loan_balance = 0;
        $disburse_state = 'DELIVERED';
        $disbursed = 1;
        $paid = 1;
        $status = 5;
    }
    else if($loan_status == 'UnCleared' || $loan_status == 'Disbursed'){
        /////----Default
        $loan_balance = $total_repayable_amount;
        $disburse_state = 'DELIVERED';
        $disbursed = 1;
        $paid = 0;
        $status = 3;

        echo "UPDATE o_loans SET disburse_state = '$disburse_state', disbursed='$disbursed', paid='$paid', status='$status' WHERE uid = '$uid'; <br/>";
    }
    else if($loan_status == 'Pending' || $loan_status == 'PendingA' || $loan_status == 'PendingB' || $loan_status == 'Approved'){
        ///---Not disbursed
        $loan_balance = $total_repayable_amount;
        $disburse_state = 'NONE';
        $disbursed = 0;
        $paid = 0;
        $status = 2;




    }
    else{
        $loan_balance = $total_repayable_amount;
    }



    // echo "INSERT IGNORE INTO o_loans (uid, customer_id, account_number, enc_phone, product_id, loan_type,loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, period, period_units, payment_frequency, total_addons, given_date, next_due_date, final_due_date, added_by, allocation, current_branch, added_date, loan_stage, transaction_date,application_mode, disburse_state, disbursed, paid, status) VALUES ('$uid','$client_id','$account_number', '$enc_phone', '$product_id', '$loan_type','$loan_amount', '$disbursed_amount', '$total_repayable_amount', '$total_repaid', '$loan_balance', '$period', '$period_units', '$payment_frequency', '$total_addons', '$given_date', '$next_due_date', '$final_due_date', '$added_by', '$allocation', '$current_branch', '$added_date', $loan_stage, '$given_date 00:00:00', '$application_mode', '$disburse_state', '$disbursed', '$paid', '$status'); <br/>";

    echo "UPDATE o_loans SET disburse_state = '$disburse_state', disbursed='$disbursed', paid='$paid', status='$status' WHERE uid = '$uid'; <br/>";

}

// Close the file
fclose($file);