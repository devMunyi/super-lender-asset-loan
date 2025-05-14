<?php
session_start();
include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");

ini_set('display_errors', 0); ini_set('display_startup_errors', 0);


// Open the CSV file
$file = fopen('../../test/smw-payments.csv', 'r');

$locations = array(
    "DAGORETTI" => 6,
    "HEAD OFFICE" => 1,
    "KANGARI" => 11,
    "KIMENDE" => 12,
    "LIMURU" => 5,
    "RUAI" => 7,
    "RUIRU" => 3,
    "THIKA" => 2,
    "WANGIGE" => 4
);

// Skip the header row if it exists
fgetcsv($file);
//Transaction Code,Amount,Phone Number,Transaction Date,Value Date
//TC67M4G0AP,1,254705534473,6/3/2025,6/3/2025
// Loop through each row
$r = 1;
while (($row = fgetcsv($file)) !== false) {

    $uid = $r; //
    $client_id = 0; //
    $phone = $row[2];
    $amount = $row[4];
    $loan_id = $row[3];
    $transaction_code = $row[11];
    $dateb = $row[0];
    $payment_method = 3;
    $payment_category = 1;
    $record_method = 'MANUAL';
    $status = 1;

    $date = DateTime::createFromFormat('d-m-Y H:i', $dateb);
    $pay_date = $date->format('Y-m-d');

  //  $client_id = fetchrow('o_customers',"primary_mobile='$phone'","uid");

    ///////////////////

    if(validate_phone($phone) == 0){
        continue;
    }

    echo "INSERT IGNORE INTO o_incoming_payments (uid, customer_id, payment_method, payment_category, mobile_number, amount, transaction_code, loan_id, loan_balance, payment_date, recorded_date, record_method, status) VALUES ($uid, '$client_id', '$payment_method', '$payment_category', '$phone', '$amount', '$transaction_code', '$loan_id', '0', '$pay_date', '$pay_date', '$record_method', $status); <br/>";

    $r+=1;

}



// Close the file
fclose($file);

