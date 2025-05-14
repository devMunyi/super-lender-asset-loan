<?php
session_start();
include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

error_reporting(0);
// Open the CSV file
$file = fopen('../../test/sal_customers.csv', 'r');

$locations = array(
    "Pipeline" => 439,
    "HQ" => 1,
    "Kiambu" => 436,
    "Thika" => 440,
    "Kinoo" => 438,
    "Ruai" => 441,
    "Ruiru" => 437,
    "Head Office" =>1
);

// Skip the header row if it exists
fgetcsv($file);
$order = 10;
// Loop through each row
while (($row = fgetcsv($file)) !== false) {
    // Extract the variables
    $uid = $order;
    $order+=1;
    $staffid = 1;

    // Concatenate columns 2, 3, and 4 into $fullname
    $fullname = addslashes($row[1]);

    // Extract and combine the date into $dob (MySQL format: YYYY-MM-DD)
    $month = $row[4];
    $day = $row[5];
    $year = $row[6];
    $dob = date('Y-m-d', strtotime("$year-$month-$day"));

    // Convert gender to M/F
    if ($row[6] === 'MALE') {
        $gender = 'M';
    } elseif ($row[7] === 'FEMALE') {
        $gender = 'F';
    } else {
        $gender = ''; // Leave as empty string for other values
    }

    // Extract remaining values
    $phone = $row[7];
    $email_address = $row[3];
    $national_id = $row[2];
    $branch_name = $row[11];
    $status = 1;


    $branch = $locations[$branch_name];

    $enc_phone = hash('sha256', $phone);

    // You can now use the variables ($uid, $fullname, $dob, $gender, $phone, $email_address, $national_id, $branch_name, $status)
    // For example, you can insert them into a database or output them
    //echo "UID: $uid, Fullname: $fullname, DOB: $dob, Gender: $gender, Phone: $phone, Email: $email_address, National ID: $national_id, Branch: $branch_name, Branch_id: $branch, Status: $status <br/>";

    echo "INSERT IGNORE INTO o_customers (uid, full_name, primary_mobile, enc_phone, phone_number_provider, email_address, national_id, gender,  added_by, branch, primary_product, loan_limit, status)
VALUES ($uid, '$fullname', '$phone', '$enc_phone', 1, '$email_address',  '$national_id', '$gender', 1, '$branch', 1, 0, $status); <br/>";






}

// Close the file
fclose($file);


