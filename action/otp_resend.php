<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../php_functions/secondary-functions.php");
include_once("../php_functions/digivas.php");
include_once("../configs/conn.inc");

$userd = session_details();


include_once("../configs/conn.inc");
//include_once ("../configs/auth.inc");


$userd = session_details();

$user_id = $userd['uid'];
$phone = $userd['phone'];
$email = $userd['email'];
$dest = $_POST['dest'];

$last_message_date = session_variables('READ',"LAST_OTP");
if(validate_date($last_message_date) == 0){
    $last_message_date = date("2020-01-01 12:12:12");
}

$ago = intval(timeDiff($last_message_date, $fulldate));

//die(errormes($ago."[$last_message_date, $fulldate]"));
if($ago < 60){
    $wait = 60 - $ago;
    echo errormes("Please wait for $wait seconds to retry");
    die();
}

$otp = generateRandomNumber(5);
$upd = updatedb('o_users',"otp_='$otp'","uid='$user_id'");
if($upd == 1){
    $set_last_message_date = session_variables('ADD',"LAST_OTP", $fulldate);
    if($dest == 'SMS') {
        $fds = array('message_body', 'created_by', 'phone', 'queued_date', 'status');
        $vals = array("Your OTP is $otp", "$user_id", "$phone", "$fulldate", '5');
        $sent = addtodb('o_sms_outgoing', $fds, $vals);
        send_sms_bulk($phone, "Your OTP is $otp");
    }
    else if($dest == 'EMAIL') {
        $sent = send_mail($otp_email, $email,"Your OTP","Your OTP is $otp");
       // echo errormes($sent);

    }
   if($sent == 1) {
       echo sucmes("OTP Resent successfully");
   }
   else{
       echo errormes("Unable to send OTP. Please contact admin");
   }
}