<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);

session_start();

include_once ("../../configs/conn.inc");
include_once ("../../php_functions/functions.php");




$device_id = trim($data['device_id']);
$session_code = trim($data['session_code']);


if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}

////------Session code
$session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=1","uid, customer_id");
if($session_d['uid'] < 1){
    $result_ = 0;
    $details_ = '"Session Invalid"';
    $result_code = 154;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
else {
    ////----You have customer ID
   $cid = $session_d['customer_id'];

/////-----Check if user exists
    $user_ = fetchonerow('o_customers', "uid='$cid'", "uid, full_name, email_address, national_id, pin_, device_id, status");
    if ($user_['uid'] < 1) {
        $result_ = 0;
        $details_ = '"Account not found"';
        $result_code = 103;
        store_event('o_customers', $user_['uid'], "$result_, $details_, $result_code");
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        die();
        exit();
    }
    else{
        /////////////------------------_Exists PIN
       $proceed = 1;
    }
}




if($user_['status'] != 1){
    $result_ = 0;
    $details_ = '"Your Account is not active"';
    $result_code = 105;
    store_event('o_customers', $user_['uid'],"$result_, $details_, $result_code");
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}




/////---------------You are here because all tests passed, Apply Loan
if($proceed === 1){
    $result_ = 1;
    $details_ = '"Session valid"';
    $result_code = 111;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
}




include_once("../configs/close_connection.inc");