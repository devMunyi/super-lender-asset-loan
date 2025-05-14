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

include_once("../../php_functions/digivas.php");
include_once("../../php_functions/textsms.php");
include_once("../../php_functions/bongasms.php");




$mobile = (trim($data['phone']));
$device_id = trim($data['device_id']);

// Save response in a text file log.txt
$logFile = 'log.txt';
$log = fopen($logFile,"a");
fwrite($log, $mobile);
fclose($log);
///

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
////----Check if a message was sent 2 minutes ago
$phone = make_phone_valid($mobile);
$last_message = fetchmax('o_sms_outgoing',"phone='$phone' AND status=2 AND created_by=1","uid","sent_date");
$last_message_time = $last_message['sent_date'];
if(input_length($last_message_time, 4) == 0){
  //  $last_message_time = date('Y-m-d H:i:s');
    $last_message_time = date("2025-01-01 10:00:00");
}

if (isMessageTimeDifferenceValid($last_message_time, $fulldate)) {

} else {
   // echo "Sufficient time has elapsed.";
    $result_ = 0;
    $diff = isMessageTimeDifferenceValid($last_message_time, $fulldate);
    $details_ = '"Please wait for 2 minutes"';
    $result_code = 132;
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
       // $send = send_via_digivas(make_phone_valid($mobile), "Your new PIN is ".$new_pin, 0)."<br/>";

        $message_body = "Your new Passcode is ".$new_pin;

        if ($sms_provider == 'DIGIVAS') {
            $send = send_via_digivas($phone, $message_body, 0);
        } elseif ($sms_provider == 'TEXTSMS') {
            $send = send_via_textsms($phone, $message_body, 0);
        } elseif ($sms_provider == 'BONGASMS') {
            $send = send_via_bonga($phone, $message_body);
        } else {
            $send = send_sms_bulk($phone, $message_body);
        }

        $fds = array('phone','message_body','queued_date','sent_date','created_by','status');
        $vals = array($phone,"Your new PIN is $new_pin", "$fulldate","$fulldate","1","2");
        $save_ = addtodb('o_sms_outgoing', $fds, $vals);

        $details_ = '"We have sent your new PassCode to SMS"';
        $result_code = 111;
    } else {
        $details_ = '"Error changing your PassCode, please retry"';
        $result_code = 103;
    }

    $result_ = $upd;


    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");


}
else{
    $result_ = 0;
    $details_ = '"Phone does not exist"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}



function isMessageTimeDifferenceValid($last_message_time, $fulldate, $min_minutes = 2) {
    // If last message time is empty, consider it valid
    if (empty($last_message_time)) {
        return true;
    }

    // Create DateTime objects
    try {
        $last_time = new DateTime($last_message_time);
        $current_time = new DateTime($fulldate);

        // Calculate the time difference in minutes
        $interval = $last_time->diff($current_time);
        $total_minutes = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;

        // Return true if the difference is at least the specified number of minutes
        return $total_minutes >= $min_minutes;
    } catch (Exception $e) {
        // Handle potential invalid date format
        return false;
    }
}



include_once("../configs/close_connection.inc");