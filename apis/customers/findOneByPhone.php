<?php 

$expected_http_method = 'GET';
include_once("../../vendor/autoload.php"); // auto created when installing a dependency with composer or run composer update if have composer.json file
// include_once ("../../configs/allowed-ips-or-origins.php");
include_once("../../configs/conn.inc");
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");
include_once("../../php_functions/jwtAuthenticator.php");
include_once("../../php_functions/functions.php");

$phone_number = $_GET["phone"] ?? "";
$phone_number = make_phone_valid($phone_number);
if (validate_phone($phone_number)) {
}else{
    sendApiResponse(400, "Invalid Phone Number!");
}

// get customer details
$cust_det = fetchonerow('o_customers', "primary_mobile = '$phone_number'", "uid, full_name, primary_mobile, national_id, gender, status");
$status_names = table_to_obj('o_customer_statuses', "uid > 0", "1000", "code", "name");

if (empty($cust_det)) {
    $http_status_code = 404;
    $message = "Customer Not Found.";

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}


// extracting secondary information
$customer_arr = [
    'uid' => $cust_det['uid'],
    'full_name' => $cust_det['full_name'],
    'primary_mobile' => $cust_det['primary_mobile'],
    'national_id' => $cust_det['national_id'],
    'gender' => $cust_det['gender'],
    'status' => $status_names[$cust_det['status']] ?? null
];

sendApiResponse(200, "", "OK", $customer_arr);


