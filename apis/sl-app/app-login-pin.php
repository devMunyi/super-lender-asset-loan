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




$pin_number = (trim($data['pin_number']));
$device_id = trim($data['device_id']);
$session_code = trim($data['session_code']);

if((input_length($pin_number, 4)) == 0){
    $result_ = 0;
    $details_ = '"Passcode is too short."';
    $result_code = 100;
   // store_event('o_customers', 0,"$result_, $details_, $result_code");
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
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

////------Session code
$session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=8","uid, customer_id");
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
        $enc_pin = md5($pin_number);
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




/////---------------You are here because all tests passed
if($proceed === 1){
    $starting = $fulldate;

   ///---Check PIN


    $start = updatedb('o_customer_sessions',"status=1","session_code='$session_code'");


    if($start == 1){
        $result_ = 1;
        $result_code = 111;
        $details_ = '"Session active"';
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