<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$phone_number =  trim($data['phone_number']);
$amount = trim($data['amount']);
$account_number = trim($data['account_number']);
if(validate_phone($phone_number) == 0){
    $result_ = 0;
    $details_ = "Phone number not valid";
}
else{
    if($amount < 5){
        $result_ = 0;
        $details_ = "Amount should be at least 5";
    }
    else{
       $details_ = send_stk($phone_number, $amount, $account_number);
       $result_ = 1;

    }
}

echo json_encode("{\"result_\":$result_,\"details_\":$details_}");

