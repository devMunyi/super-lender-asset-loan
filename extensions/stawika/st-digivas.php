<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");
include_once ("../php_functions/digivas.php");


$unsent = fetchtable('o_sms_outgoing', "status=1", "uid", "asc", "20", "uid, phone, message_body");
while ($un = mysqli_fetch_array($unsent)) {

    $uid = $un['uid'];
    $phone = $un['phone'];
    $message_body = $un['message_body'];
    $update_ = updatedb('o_sms_outgoing', "status=2", "uid='$uid'"); ////Mark SMS as sent already

    if ((validate_phone(($phone))) == 1 && input_available($message_body) == 1) {
        $balance = send_bulk($phone, $message_body, $uid)."<br/>";
        echo $balance;
    }

}






