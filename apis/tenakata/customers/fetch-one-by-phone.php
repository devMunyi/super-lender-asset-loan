<?php 

$expected_http_method = 'POST';
include_once("../../../vendor/autoload.php"); // auto created when installing a dependency with composer or run composer update if have composer.json file
include_once ("../../../configs/allowed-ips-or-origins.php");
include_once("../../../configs/conn.inc");
include_once("../../../configs/jwt.php");
include_once("../../../php_functions/jwtAuthUtils.php");
include_once("../../../php_functions/jwtAuthenticator.php");
include_once("../../../php_functions/functions.php");

$data = json_decode(file_get_contents('php://input'), true);
$phone_number = make_phone_valid($data["phone_number"]);

if (validate_phone($phone_number)) {
}else{
    sendApiResponse(400, "Invalid Phone Number!");
}

// get customer details
$cust_det = fetchonerow('o_customers', "primary_mobile = '$phone_number'", "uid, full_name, primary_mobile, national_id, gender, sec_data, added_by");
$users_names = table_to_obj('o_users', "uid > 0", "1000000", "uid", "name");

if (empty($cust_det)) {
    $http_status_code = 404;
    $message = "Customer Not Found.";

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}


// extracting secondary information
$sec = $cust_det["sec_data"] ? json_decode($cust_det["sec_data"], true) : array();
if (empty($sec)) {
    $business_name = "";
    $core_business = "";
} else {
    $business_name = $sec['16'] ? $sec['16'] : "";
    $biz_category = trim($sec['43']) == '--Select One' ? "" : trim($sec['43']);
    $biz_type = $sec['47'];
    $core_business = "$biz_category, $biz_type";
    if (trim($core_business) == ",") {
        $core_business = "";
    }
}

$added_by = $cust_det['added_by'];
$added_by = $users_names[$added_by] ?? "";
$cust_det["business_name"] = $business_name;
$cust_det["core_business"] = $core_business;
$customer_arr = [
    'uid' => $cust_det['uid'],
    'full_name' => $cust_det['full_name'],
    'primary_mobile' => $cust_det['primary_mobile'],
    'national_id' => $cust_det['national_id'],
    'gender' => $cust_det['gender'],
    'business_name' => $business_name,
    'core_business' => $core_business,
    'added_by' => $added_by,
];

sendApiResponse(200, "", "OK", $customer_arr);


