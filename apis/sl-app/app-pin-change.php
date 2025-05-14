<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

include_once ("../../configs/conn.inc");
include_once ("../../php_functions/functions.php");



$mobile = (trim($data['phone']));
$device_id = trim($data['device_id']);
$current_passcode = trim($data['current_passcode']);
$new_passcode = trim($data['new_passcode']);
$session_code = trim($data['session_code']);




if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
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
$cid = $session_d['customer_id'];


$current_pin = fetchrow('o_customers',"uid='$cid'","pin_");

if($current_pin != md5($current_passcode)){
    $result_ = 0;
    $details_ = '"Current passcode '.$current_pin.','.$current_passcode.' is invalid"';
    $result_code = 134;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();

}

if(input_between(4,4, $new_passcode) == 0){
    $result_ = 0;
    $details_ = '"New passcode must be at least 4"';
    $result_code = 134;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}


$upd = updatedb('o_customers', "pin_='" . md5($new_passcode) . "'", "uid='$cid'");
if($upd == 1){
    $details_ = '"Passcode changed successfully"';
    $result_code = 111;
    $result_ = 1;
} else {
    $details_ = '"Error changing your PassCode, please retry"';
    $result_code = 103;
    $result_ = 0;
}

echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");






include_once("../configs/close_connection.inc");