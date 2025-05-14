<?php
session_start();
include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");

ini_set('display_errors', 0); ini_set('display_startup_errors', 0);


// Open the CSV file
$file = fopen('../../test/pembeni-payments.csv', 'r');

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

// Loop through each row
while (($row = fgetcsv($file)) !== false) {

    $uid = $row[0];
    $client_id = $row[1];
    $phone = $row[2];
    $amount = $row[3];
    $loan_id = $row[4];
    $transaction_code = $row[5];
    $dateb = $row[6];
    $payment_method = 3;
    $payment_category = 1;
    $record_method = 'MANUAL';
    $status = 1;


    $parts = explode('/', $dateb);
    // Rearrange the parts into MySQL format YYYY-MM-DD
    $pay_date = $parts[2] . '-' . $parts[0] . '-' . $parts[1];


    echo "INSERT IGNORE INTO o_incoming_payments (uid, customer_id, payment_method, payment_category, mobile_number, amount, transaction_code, loan_id, loan_balance, payment_date, recorded_date, record_method, status) VALUES ($uid, '$client_id', '$payment_method', '$payment_category', '$phone', '$amount', '$transaction_code', '$loan_id', '0', '$pay_date', '$pay_date', '$record_method', $status); <br/>";

}

// Close the file
fclose($file);


