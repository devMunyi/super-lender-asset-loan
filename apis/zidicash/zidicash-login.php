<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();


$_SESSION['db_name'] = 'zidicash_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");




$mobile = make_phone_valid(trim($data['mob_']));
$password = trim($data['pass_']);
$device_id = trim($data['device_id']);

if((validate_phone($mobile)) == 0){
    $result_ = 0;
    $details_ = '"The phone number is invalid'.$mobile.'"';
    $result_code = 100;
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((input_length($password, 4)) == 0){
    $result_ = 0;
    $details_ = '"The PIN should be 4 characters"';
    $result_code = 101;
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}

/////-----Check if user exists
$user_ = fetchonerow('o_customers',"primary_mobile='$mobile'","uid, full_name, email_address, national_id, pin_, device_id, status");
if($user_['uid'] < 1){
    $result_ = 0;
    $details_ = '"Phone not registered."';
    $result_code = 103;
    store_event('o_customers', $user_['uid'],"$result_, $details_, $result_code");
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
///----Check if PIN is valid
$enc_pin = md5($password);
if($enc_pin != $user_['pin_']){
    $result_ = 0;
    $details_ = '"Your PIN is incorrect"';
    $result_code = 104;
    store_event('o_customers', $user_['uid'],"$result_, $details_, $result_code");
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
else{
    $proceed = 1;
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


/////---------------You are here because all tests passed
if($proceed === 1){
    $starting = $fulldate;
    $session_code = generateRandomString(32);
    $ending = dateadd($date, 0, 0, 1).' 00:00';
    $fds = array('customer_id','device_id','session_code','started_date','ending_date','status');
    $vals = array($user_['uid'], "$device_id", "$session_code", "$starting", "$ending", 1);
    $start = addtodb('o_customer_sessions',$fds, $vals);
    if($start == 1){
        $result_ = 1;
        $result_code = 111;
        $details_ = '"'.$session_code.'"';
    }
    else{
        $result_ = 0;
        $result_code = 106;
        $details_ = '"Error! Unable to login, please press login again"';
    }
    store_event('o_customers', $user_['uid'],"$result_, $details_, $result_code");

    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
}



include_once("../configs/close_connection.inc");