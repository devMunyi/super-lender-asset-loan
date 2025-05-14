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




$repay_amount = (trim($data['repay_amount']));
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







/////---------------You are here because all tests passed, Repay Loan
if($proceed === 1){
    $amount = $repay_amount;
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