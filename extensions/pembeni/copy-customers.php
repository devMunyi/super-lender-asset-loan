<?php
session_start();
include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");

ini_set('display_errors', 0); ini_set('display_startup_errors', 0); error_reporting(E_ALL);


// Open the CSV file
$file = fopen('../../test/pembeni-customers.csv', 'r');

$locations = array(
    "HQ" => 1,
    "Thika" => 2,
    "Ruiru" => 3,
    "Wangige" => 4,
    "Limuru" => 5,
    "Dagoretti" => 6,
    "Ruai" => 7,
    "Uthiru" => 8,
    "Githunguri" => 9,
    "Gatundu" => 10,
    "Kangari" => 11,
    "Kimende" => 12,
);

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
    $uid = $row[0];

    // Concatenate columns 2, 3, and 4 into $fullname
    $fullname = $row[1] . ' ' . $row[2] . ' ' . $row[3];

    // Extract and combine the date into $dob (MySQL format: YYYY-MM-DD)
    $month = $row[4];
    $day = $row[5];
    $year = $row[6];
    $dob = date('Y-m-d', strtotime("$year-$month-$day"));

    // Convert gender to M/F
    if ($row[7] === 'Male') {
        $gender = 'M';
    } elseif ($row[7] === 'Female') {
        $gender = 'F';
    } else {
        $gender = ''; // Leave as empty string for other values
    }

    // Extract remaining values
    $phone = $row[9];
    $email_address = $row[8];
    $national_id = $row[10];
    $branch_name = $row[12];
    $status = $row[13];

    if($row[13] == 'New'){
        $status = 1;
    }
    else{
        $status = 2;
    }

    $branch = $locations[$branch_name];

    $enc_phone = hash('sha256', $phone);

    // You can now use the variables ($uid, $fullname, $dob, $gender, $phone, $email_address, $national_id, $branch_name, $status)
    // For example, you can insert them into a database or output them
    //echo "UID: $uid, Fullname: $fullname, DOB: $dob, Gender: $gender, Phone: $phone, Email: $email_address, National ID: $national_id, Branch: $branch_name, Branch_id: $branch, Status: $status <br/>";

    echo "INSERT IGNORE INTO o_customers (uid, full_name, primary_mobile, enc_phone, phone_number_provider, email_address, national_id, gender, dob, added_by, branch, primary_product, loan_limit, status)
VALUES ($uid, '$fullname', '$phone', '$enc_phone', 1, '$email_address',  '$national_id', '$gender', '$dob', 0, '$branch', 1, 0, $status); <br/>";






}

// Close the file
fclose($file);


