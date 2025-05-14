<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$expected_http_method = 'POST';
include_once("../../../vendor/autoload.php");
include_once("../../../php_functions/functions.php");
include_once ("../../../configs/allowed-ips-or-origins.php");
include_once("../../../configs/conn.inc");
include_once("../../../configs/jwt.php");
include_once("../../../php_functions/jwtAuthUtils.php");
include_once("../../../php_functions/jwtAuthenticator.php");

$data = json_decode(file_get_contents('php://input'), true);
$customer_id = $data["customer_id"];

if ($customer_id > 0) {
}else {
    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse(400, "Customer ID Missing!");
}

// get customer details
try{
    $cust_det = fetchonerow('o_customers', "uid = $customer_id", "loan_limit");
    $loan_limit = doubleval($cust_det['loan_limit']);

    if(empty($cust_det)){
        sendApiResponse(400, "Invalid Parameter");
    }

    sendApiResponse(200, "", "OK", $loan_limit);

}catch(Exception $e){
    $http_status_code = 500;
    $message =  $e->getMessage(); 
    
    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code$e");
    sendApiResponse($http_status_code, "Something Went Wrong!");
}finally {
    mysqli_close($con);
}
