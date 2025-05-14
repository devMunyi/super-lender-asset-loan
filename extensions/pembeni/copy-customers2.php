<?php
session_start();
include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");

ini_set('display_errors', 0); ini_set('display_startup_errors', 0); error_reporting(E_ALL);


// Open the CSV file
$file = fopen('../../test/pembeni-customers1.csv', 'r');


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
//id[0], branch[1], name[2], gender[3], phone_number[4], national_id[5], regDate[6], credit_limit[7], address[8], town[9], dob[10]

// Skip the header row if it exists
fgetcsv($file);

// Loop through each row
while (($row = fgetcsv($file)) !== false) {
    // Extract the variables
    $uid = $row[0]+3500;
    $phone = $row[4];
    $enc_phone = hash('sha256', $phone);
    $fullname = addslashes($row[2]);
    $email_address = "";
    $address = addslashes($row[8].', '.$row[9]);
    $national_id = $row[5];
    $g = $row[3];
    if($g == 1){
        $gender = 'M';
    }
    else{
        $gender = 'F';
    }
    $dob = $row[10];
    $bran = $row[1];
    $branch = $branches[$bran];

    echo "INSERT IGNORE INTO o_customers (uid, full_name, primary_mobile, enc_phone, phone_number_provider, email_address, physical_address, national_id, gender, dob, added_by, branch, primary_product, loan_limit, status)
VALUES ($uid, '$fullname', '$phone', '$enc_phone', 1, '$email_address', '$address', '$national_id', '$gender', '$dob', 0, '$branch', 1, 0, 2); <br/>";






}

// Close the file
fclose($file);


