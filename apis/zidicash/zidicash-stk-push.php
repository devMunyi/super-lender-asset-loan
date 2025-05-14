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

////------APPLY LOAN


$session_code = $data['session_id'];
$device_id = $data['device_id'];
$amount = $data['amount'];


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
if($amount < 10){
    $result_ = 0;
    $details_ = '"Please enter a Valid amount"';
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
    $cust_det = fetchonerow('o_customers',"uid=".$session_d['customer_id']."","primary_mobile");

    $phone = $cust_det['primary_mobile'];

     $stk = send_stk($phone, $amount, $phone);
     $state = 0;

    if ($state == '0') {

        ///----Check latest loan and store the event
        $result_ = 1;
        $details_ = "Please wait for the prompt sent to your phone or pay to Till directly";


        $latest_ = fetchmax('o_loans',"account_number='$phone'","given_date","uid");
        $latest_loan = $latest_['uid'];

        if($latest_loan > 0){
           // $event = "STK Pushed by customer to phone on [$fulldate] with result ($state) $desc";
           // store_event('o_loans', $latest_loan,"$event");
        }
    }
    else{
        $result_ = 0;
        $details_ = "Unable to send request ,,";

    }





        $result_code = 111;
        echo json_encode("{\"result_\":$result_,\"details_\":\"$details_\", \"result_code\":$result_code}");







}



include_once("../configs/close_connection.inc");