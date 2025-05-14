<?php
session_start();
include_once("../../../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");
include_once ("../../../php_functions/mtn_functions.php");


$logFile = '../data/customers.json';
$log = file_get_contents($logFile);

$json = json_decode($log, true);


// set counters
$inserted = 0;
$skipped = 0;
$iteration = 0;

foreach ($json as $object) {
    $first_name = sanitizeAndEscape($object['first_name'], $con) ?? '';
    $middle_name = sanitizeAndEscape($object['middle_name'], $con) ?? '';
    $last_name = sanitizeAndEscape($object['last_name'], $con);
    $date_of_birth = $object['date_of_birth'] ?? '0000-00-00';
    $location = $object['location'] ?? '';
    $id_no = $object['id_no'] ? $object['id_no'] : 'null_'.$iteration;
    $estate = $object['estate'] ?? '';
    $floor = $object['estate'] ?? '';
    $cro = $object['cro'] ?? '';
    $postal_address = $object['postal_address'] ?? '';
    $member_no = $object['member_no'] ?? 0;

    $mobile_no = preg_replace('/\s+/', '', ltrim($object['mobile_no'], "+"));
    $mobile_no = $mobile_no ? $mobile_no : 'null_'.$iteration;
    $personal_email = $object['personal_email'] ?? '';

    $comment = $object['comment'] ?? '';
    $registration_date = $object['registration_date'] ?? '0000-00-00';
    $status = $object['status'] ?? 0;
    $gender = strtolower($object['gender']);

    if ($gender == 'male') {
        $g = 'M';
    } elseif ($gender == 'female') {
        $g = 'F';
    } else {
        $g = "";
    }

    $id = $object['id'] ?? 0;
    $full_name = sanitizeAndEscape($object['full_name'], $con) ?? '';
    $status_name = $object['status_name'] ?? '';
    $branch = $object['branch'] ?? 0;
    //$branch_name = $object['branch_name'];
    $cro_username = $object['cro_username'] ?? '';

    $photo = $object['photo'] ?? '';

    $members_only_products = $object['members_only_products'] ?? 0;
    $markeplace_loan_limit  = $loan_limit = $object['markeplace_loan_limit'] ?? 0 ;
    $business_name = $object['business_name'] ?? '';
    $business_physical_address = $object['business_physical_address'] ?? '';
    $business_product_services = $object['business_product_services'] ?? '';
    $business_estate_street = $object['business_estate_street'] ?? '';
    $business_town = $object['business_town'] ?? '';
    $business_registered_owner = $object['business_registered_owner'] ?? '';
    $geo_location = $object['geo_location'];

    $id_check = $object['id_check'] ?? '';
    $phone_check = $object['phone_check'] ?? '';
    $primary_product = 1; // Nawiri
    $passport_photo = '';
    $events = '';


    if($id > 0){
        $fds = array('uid', 'full_name', 'primary_mobile', 'email_address', 'physical_address', 'national_id', 'gender', 'dob', 'added_by', 'current_agent', 'added_date', 'branch', 'primary_product', 'loan_limit', 'status', 'passport_photo', 'events');
        $vals = array("$id", "$full_name", "$mobile_no", "$personal_email", "$location", "$id_no", "$g", "$date_of_birth", "1", "0", "$registration_date 00:00:00", $branch, $primary_product, $loan_limit, 1, "$passport_photo", "$events");
        $create = addtodb('o_customers', $fds, $vals);
        echo 'Entry UID: ' . $id . ' TABLE INSERT RESPONSE: ' . $create . '<br>';
        if ($create == 1) {
            ///----Store sec data
            $inserted += 1;
        }else {
            $skipped += 1;
        }
    
    
    
    
        if ($status != 1) {
            // echo "$status_name <br/>";
        }

        $iteration += 1;
    }
}
echo "INSERTED CUSTOMERS: $inserted <br>";
echo "SKIPPED CUSTOMERS: $skipped <br>";
