<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();
$_SESSION['db_name'] = 'bodafund_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

////------APPLY LOAN


$session_code = $data['session_id'];
$device_id = $data['device_id'];
$pin = $data['pin'];



if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((input_length($session_code, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid Session Code"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}


$session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=1","uid, customer_id");
if($session_d['uid'] < 1){
    $result_ = 0;
    $details_ = '"Session Invalid"';
    $result_code = 107;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
else{
    $cust_id = $session_d['customer_id'];
}

if(strlen($pin) != 4){
    $result_ = 0;
    $details_ = '"PIN is invalid"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}


$pin_enc = md5($pin);
$upd = updatedb('o_customers',"pin_='$pin_enc'","uid='$cust_id'");
if($upd == 1) {

    $result_ = 1;
    $details_ = '"Success! your PIN has been updated"';
    $result_code = 111;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");

}
else{
    $result_ = 0;
    $details_ = '"Error, unable to update PIN"';
    $result_code = 101;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
}




