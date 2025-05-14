<?php 
// CUSTOMER NUMBER(0=>OK),Name(1=>OK),Status(2), Gender(3) ,NATIONAL ID NO(4),DISBURSEMENT NO(5=>OK),ALTERNATIVE PHONE NO(6),
// GUARANTOR NAME(7),ID NO(8),GUARANTOR CONTACT(9),RELATIONSHIP(10),BUSINESS LOCATION(11),BUSINESS LOCATION MAP(12),
// RESIDENTIAL LOCATION MAP(13)

// files includes
include_once ("../../../configs/conn.inc");
include_once ("../../../php_functions/functions.php");
include_once ("../../../php_functions/mtn_functions.php");
include_once ("./reusable.php");

$file = "../data/dagoretti_customers.csv";

$cf_handle = fopen($file, "r");
$is_first_row = true; // Flag variable to track the first row

// set counters
$inserted = 0;
$skipped = 0;
$iteration = 0;

while(($c_data = fgetcsv($cf_handle, 1000000, ",")) !== FALSE){
    if ($is_first_row) {
        $is_first_row = false;
        continue; // Skip the first row and move to the next iteration
    }

    $customer_code = trim($c_data[0]);
    $customer_code = customerCode($customer_code);
    $full_name = trim($c_data[1]) ?? '';
    $full_name = sanitizeAndEscape($full_name, $con);
    $primary_mobile = trim(($c_data[5])) ?? '';
    $primary_mobile = str_replace([' ', '+'], '', $primary_mobile);
    $primary_mobile = make_phone_valid($primary_mobile);
    if($primary_mobile == '254'){
        $primary_mobile = generateRandomNumber(12);
    }
    $physical_address = null;
    if(!$physical_address){
        if($b == 'K'){
            $physical_address = 'Kiambu';
        }elseif($b == 'N'){
            $physical_address = 'Ngong';
        }elseif($b == 'D'){
            $physical_address = 'Dagoretti';
        }elseif($b == 'W'){
            $physical_address = 'Waiyaki Way';
        }else {
            $physical_address = '';
        }
       
    }
    $physical_address = sanitizeAndEscape($physical_address, $con);
    $passport_photo = '';
    $national_id = trim($c_data[4]) ?? '';
    $gender =  strtolower(trim($c_data[3])) ?? '';
    if($gender == 'male'){
        $gender = 'M';
    }elseif($gender == 'female'){
        $gender = 'F';
    }else {
        $gender = '';
    }

    $dob = '0000-00-00';
    $added_by = 0;
    $current_agent = 0;
    $b_initial = strtoupper($customer_code[0]);
    if($b_initial == 'K'){
        $branch = 2;
    }elseif($b_initial == 'D'){
        $branch = 3;
    }elseif($b_initial == 'N'){
        $branch = 4;
    }elseif($b_initial == 'W'){
        $branch = 5;
    }else {
        $branch = 0;
    }

    $primary_product = 1;
    $loan_limit = 0;
    $events = '';
    $ss =  strtolower(trim($c_data[2])) ?? '';
    if(strlen($ss) > 0){
        $s = $ss[0];
    }

    if($ss == 'deceased'){
        $status = 0;
    }elseif($s == 'a' || $s == 'd'){
        $status = 1;
    }elseif($s == 'b'){
        $status = 2;
    }else {
        $status = 0;
    }

    if(strlen($customer_code) > 0){
        $fds = array('customer_code','full_name','primary_mobile','physical_address','passport_photo','national_id','gender','dob','added_by','current_agent', 'branch', 'primary_product', 'loan_limit', 'events', 'status');
        $vals = array("$customer_code", "$full_name", "$primary_mobile", "$physical_address", "$passport_photo","$national_id", "$gender","$dob", $added_by, $current_agent, $branch, $primary_product, $loan_limit, "$events", $status);
        $create = addtodb('o_customers' ,$fds, $vals);
        echo 'CUSTOMER CODE: '.$customer_code .' TABLE INSERT RESPONSE: '.$create .'<br>'; 
        if($create == 1)
        {
            $inserted += 1;
        }
        else
        {
            $skipped += 1;
        }
    }

    $iteration ++;
}

echo "INSERTED CUSTOMERS: $inserted <br>";
echo "SKIPPED CUSTOMERS: $skipped <br>";
// 4092053
?>