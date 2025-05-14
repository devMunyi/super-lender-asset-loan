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


$session_code = $data['session_id'];
$device_id = $data['device_id'];

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
    ////----You have customer ID

    $savings = fetchtable('o_incoming_payments',"customer_id='".$session_d['customer_id']."' AND status=1 AND payment_category=4","uid","desc","1000","uid, mobile_number, amount, transaction_code , payment_date");
   $all_savings_array = array();
   $savings_count = 0;
    while($s = mysqli_fetch_array($savings)){
        $has_savings = 1;
        $one_saving = array();
        $one_saving['uid'] = $s['uid'];
        $one_saving['mobile_number'] = $s['mobile_number'];
        $one_saving['amount'] = round($s['amount'],0);
        $one_saving['transaction_code'] = $s['transaction_code'];
        $one_saving['payment_date'] = $s['payment_date'];

        $all_savings_array[$savings_count] = $one_saving;
        $savings_count = $savings_count + 1;
    }


    $result_ = 1;
    $details_ = json_encode(json_encode($all_savings_array));
    $result_code = 111;

    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");

}



