<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$unsent = fetchtable('o_sms_outgoing', "status=1", "uid", "asc", "20", "uid, phone, message_body");
while ($un = mysqli_fetch_array($unsent)) {

    $uid = $un['uid'];
    $phone = $un['phone'];
    $message_body = $un['message_body'];
    $update_ = updatedb('o_sms_outgoing', "status=2,  sent_date='$fulldate'", "uid='$uid'"); ////Mark SMS as sent already

    if ((validate_phone(($phone))) == 1 && input_available($message_body) == 1) {
        echo send_bulk($phone, $message_body)."<br/>";
    }

}


function send_bulk($number, $message)
{
    global $BONGA_SMS_serviceID, $BONGA_SMS_key, $BONGA_SMS_secret, $BONGA_SMS_apiClientID;
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://167.172.14.50:4002/v1/send-sms',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('apiClientID' => $BONGA_SMS_apiClientID, 'serviceID' => $BONGA_SMS_serviceID, 'key' => $BONGA_SMS_key, 'secret' => $BONGA_SMS_secret, 'txtMessage' => "$message", 'MSISDN' => "$number"),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;

}

