<?php
session_start();
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);


// Open the CSV file
$file = fopen('../test/pembeni-loans2.csv', 'r');


$branches = array(
    "1" => 1,
    "4" => 2,
    "3" => 3,
    "2" => 4,
    "8" => 5,
    "5" => 6,
    "7" => 7,
    "12" => 8,
    "13" => 9,
    "Gatundu" => 10,
    "6" => 11,
    "9" => 12,
);

// Skip the header row if it exists
fgetcsv($file);

// Loop through each row
while (($row = fgetcsv($file)) !== false) {
    // Extract the variables
    //id[0], lid[1], branch[2], customer_id[3], loan_amount[4], application_date[5], created_at[6], interest_charged[7], loan_status[8], applicationCharged[9], due_date[10], closed_date[11], status[12]
    //1,4,4,48,12000,2024-05-02,"2024-11-28 19:37:01",2640,5,360,400,2024-05-02,1

    $uid = $row[0]+16000;
    $client_id = $row[3]+3500;
    $account_number = generateRandomString(14); //---FIX
    $enc_phone = hash('sha256', $account_number);  ////---FIX
    $product_id = 1;
    $loan_type = 0;
    $loan_amount = round($row[4]);
    $disbursed_amount = $loan_amount;
    $loan_status = 3;
    $interest_amount = round($row[7]);
    $processing_fee = round($row[9]);
    $total_repayable_amount = $loan_amount + $interest_amount + $processing_fee;
    $total_addons = $interest_amount + $processing_fee;
    $total_repaid = 0;
    $loan_balance = $total_repayable_amount;
    $period = 30;
    $period_units = 1;
    $payment_frequency = 1;

    $branch = $row[2];
    if($branch != 12 && $branch != 13){
        continue;
    }

    $branch_id = $branches[$branch];
    // $total_addons = 0;
    $given_date = $row[5];
    $next_due_date = $given_date;
    $final_due_date = dateadd($given_date,0,1,0);
    $added_by = 1;
    $allocation = 'BRANCH';
    $current_branch = $branch_id;
    $added_date = $given_date;
    $loan_stage = 4;
    $application_mode = 'MANUAL';


    $loan_balance = $total_repayable_amount;
    $disburse_state = 'DELIVERED';
    $disbursed = 1;
    $paid = 0;
    $status = 3;

    $cust = fetchonerow('o_customers',"uid='$client_id'","primary_mobile,enc_phone");

    $primary_mobile = $cust['primary_mobile'];
    $enc_phone = $cust['enc_phone'];


  //  echo "INSERT IGNORE INTO o_loans (uid, customer_id, account_number, enc_phone, product_id, loan_type,loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, period, period_units, payment_frequency, total_addons, given_date, next_due_date, final_due_date, added_by, allocation, current_branch, added_date, loan_stage, transaction_date,application_mode, disburse_state, disbursed, paid, status) VALUES ('$uid','$client_id','$account_number', '$enc_phone', '$product_id', '$loan_type','$loan_amount', '$disbursed_amount', '$total_repayable_amount', '$total_repaid', '$loan_balance', '$period', '$period_units', '$payment_frequency', '$total_addons', '$given_date', '$next_due_date', '$final_due_date', '$added_by', '$allocation', '$current_branch', '$added_date', $loan_stage, '$given_date 00:00:00', '$application_mode', '$disburse_state', '$disbursed', '$paid', '$status'); <br/>";

    echo "UPDATE o_loans SET loan_flag = '19' WHERE uid = '$uid'; <br/>";
    echo updatedb('o_loans',"loan_flag='19'","uid='$uid'");

}

// Close the file
fclose($file);