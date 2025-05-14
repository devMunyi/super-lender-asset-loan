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
include_once ("boda-bulk.php");

////------APPLY LOAN


$session_code = $data['session_id'];
$device_id = $data['device_id'];
$mobile = $data['mobile'];



if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}

if(validate_phone(make_phone_valid($mobile)) == 0){
    $result_ = 0;
    $details_ = '"Phone is invalid"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}


   $acc = fetchonerow('o_customers',"primary_mobile='".make_phone_valid($mobile)."'","uid, full_name");
   $customer_id = $acc['uid'];
   if($customer_id > 0) {
       $new_pin = rand(1000, 9999);
       $upd = updatedb('o_customers', "pin_='" . md5($new_pin) . "'", "uid='$customer_id'");
       if ($upd == 1) {
          // send_sms_bulk(make_phone_valid($mobile), $new_pin);
           $send = send_bulk(make_phone_valid($mobile), $new_pin, 0)."<br/>";
           $fds = array('phone','message_body','queued_date','sent_date','created_by','status');
           $vals = array(make_phone_valid($mobile),"New PIN Reset on App: $new_pin", "$fulldate","$fulldate","1","2");
           $save_ = addtodb('o_sms_outgoing', $fds, $vals);

           $details_ = '"We have sent your new PIN on SMS. Please update it when you login"';
           $result_code = 111;
       } else {
           $details_ = '"Error changing your PIN, please retry"';
           $result_code = 103;
       }

       $result_ = $upd;


       echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");


   }
   else{
       $result_ = 0;
       $details_ = '"Phone does not exist, please signup"';
       $result_code = 102;
       echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
       die();
       exit();
   }



